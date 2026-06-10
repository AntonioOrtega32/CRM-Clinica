<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use GuzzleHttp\Client;
use Mpdf\Mpdf;

class LeadsController extends Controller
{

    //---------------------------------- Inicio de funciones de leads ---------------------------------------
    //Index de leads
    public function index()
    {
        return view('crm.leads.indexLeads');
    }
    //Llenar datatable de leads en desuso
   /* public function datatable(Request $request)
{
    $draw   = (int) $request->input('draw', 0);
    $start  = (int) $request->input('start', 0);
    $length = (int) $request->input('length', 15);

    // Filtros
    $min = $request->input('min'); // YYYY-MM-DD
    $max = $request->input('max'); // YYYY-MM-DD
    $clinic = $request->input('clinic'); // Santa Fe/Pedregal/Queretaro
    $seller = $request->input('seller'); // texto o "" (en tu tabla dice "seller")
    $semaforo = $request->input('semaforo'); // Cierre/Interesado/Valoración/Tratamiento

    // Base query
    $base = DB::table('sa_leads as l')
        ->select([
            'l.id',
            'l.first_name',
            'l.last_name',
            'l.clinic',
            'l.phone',
            'l.interested_in',
            'l.semaforo',
            'l.seller',
            'l.created_at',
        ]);

    // Total sin filtros
    $recordsTotal = DB::table('sa_leads')->count();

    // Aplicar filtros
    if ($min) {
        $base->whereDate('l.created_at', '>=', $min);
    }
    if ($max) {
        $base->whereDate('l.created_at', '<=', $max);
    }
    if ($clinic) {
        $base->where('l.clinic', $clinic);
    }
    if ($seller) {
        $base->where('l.seller', $seller);
    }
    if ($semaforo) {
        $base->where('l.semaforo', $semaforo);
    }

    // Búsqueda global
    $search = $request->input('search.value');
    if ($search !== null && $search !== '') {
        $base->where(function ($q) use ($search) {
            $q->where('l.id', 'like', "%{$search}%")
              ->orWhere('l.first_name', 'like', "%{$search}%")
              ->orWhere('l.last_name', 'like', "%{$search}%")
              ->orWhere('l.phone', 'like', "%{$search}%")
              ->orWhere('l.interested_in', 'like', "%{$search}%")
              ->orWhere('l.semaforo', 'like', "%{$search}%")
              ->orWhere('l.seller', 'like', "%{$search}%")
              ->orWhere('l.clinic', 'like', "%{$search}%");
        });
    }

    // recordsFiltered (con filtros + búsqueda)
    $recordsFiltered = (clone $base)->count();

    // Orden
    $orderColIndex = (int) $request->input('order.0.column', 0);
    $orderDir      = $request->input('order.0.dir', 'desc');

    $cols = [
        0 => 'l.id',
        1 => 'l.first_name',
        2 => 'l.clinic',
        3 => 'l.phone',
        4 => 'l.interested_in',
        5 => 'l.semaforo',
        6 => 'l.seller',
        7 => 'l.created_at',
    ];

    $orderCol = $cols[$orderColIndex] ?? 'l.id';
    $base->orderBy($orderCol, $orderDir);

    // Paginación
    $rows = $base->offset($start)->limit($length)->get();

    // Formato para DataTables (puedes devolver arrays o objetos)
    $data = $rows->map(function ($r) {
        $name = trim($r->first_name.' '.$r->last_name);
        return [
            $r->id,
            $name,
            $r->clinic,
            $r->phone,
            $r->interested_in,
            $r->semaforo,
            $r->seller,
            Carbon::parse($r->created_at)->format('Y-m-d'),
        ];
    });

    return response()->json([
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data' => $data,
    ]);
}
*/


// Cargar todos los leads sin paginación ni filtros
public function dataAll()
{
    $rows = DB::table('sa_leads')
        ->select('id','created_at','first_name','last_name','clinic','phone','interested_in','semaforo','seller')
        ->orderByDesc('id')
        ->get();

    // DataTables client-side acepta {data: [...]}
    $data = $rows->map(function($r){
        return [
            'id' => $r->id,
            'created_at' => date('Y-m-d', strtotime($r->created_at)), // para filtrar fácil
            'name' => trim($r->first_name.' '.$r->last_name),
            'clinic' => $r->clinic,
            'phone' => $r->phone,
            'interested_in' => $r->interested_in,
            'semaforo' => $r->semaforo,
            'seller' => $r->seller,
        ];
    });

    return response()->json(['data' => $data]);
}

//Semaforo de leads
public function updateSemaforo(Request $request, int $leadId)
{
    $request->validate([
        'semaforo' => 'required|in:Cierre,Interesado,Valoración,Tratamiento',
    ]);

    DB::table('sa_leads')
      ->where('id', $leadId)
      ->update(['semaforo' => $request->semaforo]);

    return response()->json(['success' => true]);
}


    // ------------------ FUNCIONES DE SUBTABS DE CLIENTES ------------------
    public function show(Request $request, $id)
    {
        // 1. Saber si viene desde CLIENTES
        $isClient = $request->query('client') === 'yes';

        // 2. Datos principales del Lead + última valoración
        $lead = DB::table('sa_leads as sl')
            ->leftJoin('sa_leads_assessment as sla', function ($join) {
                $join->on('sla.lead_id', '=', 'sl.id')
                    ->whereRaw('sla.procedure_date = (
                    SELECT MAX(procedure_date)
                    FROM sa_leads_assessment
                    WHERE lead_id = sl.id
                )');
            })
            ->where('sl.id', $id)
            ->select(
                'sl.*',
                DB::raw("CONCAT(sla.first_name, ' ', sla.last_name) as full_name"),
                'sla.procedure_type',
                'sla.procedure_date',
                'sla.clinic',
                'sl.phone',
                'sl.seller',
                'sla.first_meet_type',
                'sla.type AS assessment_type',
                'sla.date AS assessment_date',
                'sla.notes',
                'sla.status',
                'sla.gender',
                'sla.phone',
            )
            ->first();

        if (!$lead) {
            abort(404, 'Lead no encontrado');
        }

        // 3. Número de expediente
        $numExp = DB::table('enf_procedures')
            ->where('lead_id', $id)
            ->max('num_med_record');

        // 4. Cotización (UNA sola)
        $quote = DB::table('sa_closed_px')
            ->where('lead_id', $id)
            ->select(
                'quoted_cash_amount',
                'quoted_cc_amount',
                'installments'
            )
            ->first();

        // 5. Pagos válidos (como CRM viejo)
        $payments = DB::table('sa_info_payment_px')
            ->where('lead_id', $id)
            ->where('status', 1)
            ->whereIn('type', ['abono', 'anticipo', 'liquidacion'])
            ->orderBy('payment_date', 'desc')
            ->select(
                'payment_date',
                'type',
                'amount'
            )
            ->get();

        // 6. Cálculos financieros (LÓGICA VIEJA)
        $totalPaid = $payments->sum('amount');

        $pendingAmount = 0;
        if ($quote) {
            $pendingAmount = ($quote->quoted_cash_amount ?? 0) - $totalPaid;
        }

        // 7. Retornar vista
        return view('crm.leads.verLead', [
            'lead' => $lead,
            'numExp' => $numExp,
            'isClient' => $isClient,

            // PX / PROCEDURE
            'payments' => $payments,
            'quote' => $quote,
            'totalPaid' => $totalPaid,
            'pendingAmount' => $pendingAmount,
                'totalAmount' => max($pendingAmount, 0),

        ]);
    }

    public function updateSummary(Request $request, $Id)
{
    $data = $request->validate([
        'quoted_cash_amount' => 'required|numeric|min:0',
        'quoted_cc_amount'   => 'required|numeric|min:0',
        'installments'       => 'required|string|max:255',
    ]);

    DB::beginTransaction();

    try {
        // si en tu tabla la llave es lead_id
        $exists = DB::table('sa_closed_px')->where('lead_id', $Id)->exists();

        if ($exists) {
            DB::table('sa_closed_px')
                ->where('lead_id', $Id)
                ->update([
                    'quoted_cash_amount' => $data['quoted_cash_amount'],
                    'quoted_cc_amount'   => $data['quoted_cc_amount'],
                    'installments'       => $data['installments'],
                    'updated_at'         => now(), // si existe
                ]);
        } else {
            DB::table('sa_closed_px')->insert([
                'lead_id'            => $Id,
                'quoted_cash_amount' => $data['quoted_cash_amount'],
                'quoted_cc_amount'   => $data['quoted_cc_amount'],
                'installments'       => $data['installments'],
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        DB::commit();

        // Si lo mandas por fetch/ajax
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Info. actualizada correctamente'
            ]);
        }

        return back()->with('success', 'Info. actualizada correctamente');

    } catch (\Throwable $e) {
        DB::rollBack();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Contacta al Administrador',
                'error'   => $e->getMessage(),
            ], 500);
        }

        return back()->with('error', 'Contacta al Administrador');
    }
}

    public function getPhotos(Request $request, $leadId)
    {
        $type = 'photos';

        $client = new Client();

        $storageZone = env('BUNNY_STORAGE_ZONE_LEADS');
        $apiKey      = env('BUNNY_API_KEY_LEADS');
        $region      = env('BUNNY_REGION');

        $folder = "https://{$region}.storage.bunnycdn.com/{$storageZone}/{$leadId}/{$type}/";

        try {
            $response = $client->get($folder, [
                'headers' => [
                    'AccessKey' => $apiKey,
                    'accept' => '*/*',
                ]
            ]);

            $files = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $files = [];
        }

        $images = [];

        if (is_array($files)) {
            foreach ($files as $file) {
                $fileName = basename($file['ObjectName']);
                $images[] = "https://{$storageZone}.b-cdn.net/{$leadId}/{$type}/{$fileName}";
            }
        }

        return response()->json([
            'images' => $images
        ]); 
    }


    // Funciones de guardar las imagenes de photos
    public function uploadPhotos(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|integer',
            'photos.*' => 'required|image|max:5120',
        ]);

        $leadId = $request->lead_id;
        $type   = 'photos';

        $client = new Client();

        $storageZone = env('BUNNY_STORAGE_ZONE_LEADS');
        $apiKey      = env('BUNNY_API_KEY_LEADS');
        $region      = env('BUNNY_REGION');

        // Crear carpetas
        $this->createBunnyFolder($client, $leadId, $storageZone, $apiKey, $region);
        $this->createBunnyFolder($client, "{$leadId}/{$type}", $storageZone, $apiKey, $region);

        foreach ($request->file('photos') as $file) {

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension    = $file->getClientOriginalExtension();

            $cleanName = Str::slug($originalName);
            $finalName = $cleanName . '-' . time() . '.' . $extension;

            $client->put(
                "https://{$region}.storage.bunnycdn.com/{$storageZone}/{$leadId}/{$type}/{$finalName}",
                [
                    'headers' => [
                        'AccessKey' => $apiKey,
                        'Content-Type' => $file->getMimeType(),
                    ],
                    'body' => fopen($file->getPathname(), 'r'),
                ]
            );
        }

        return response()->json(['success' => true]);
    }


    // Borrar photos
    public function deletePhoto(Request $request, $leadId)
    {
        $request->validate([
            'filename' => 'required|string'
        ]);

        $fileName = basename($request->filename);
        $type     = 'photos';

        $client = new Client();

        $storageZone = env('BUNNY_STORAGE_ZONE_LEADS');
        $apiKey      = env('BUNNY_API_KEY_LEADS');
        $region      = env('BUNNY_REGION');

        $filePath = "https://{$region}.storage.bunnycdn.com/{$storageZone}/{$leadId}/{$type}/{$fileName}";

        try {
            $client->delete($filePath, [
                'headers' => [
                    'AccessKey' => $apiKey,
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Imagen eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar la imagen'
            ], 500);
        }
    }


    //Funciones de histria clinica
    public function getHC(Request $request)
    {
        $leadId = $request->lead_id;
        $type = 'hc';

        $storageZone = env('BUNNY_STORAGE_ZONE_LEADS');
        $apiKey = env('BUNNY_API_KEY_LEADS');
        $region = env('BUNNY_REGION');

        $client = new Client();
        $files = [];

        try {
            $response = $client->get(
                "https://{$region}.storage.bunnycdn.com/{$storageZone}/{$leadId}/{$type}/",
                [
                    'headers' => ['AccessKey' => $apiKey]
                ]
            );

            $files = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
        }

        $data = [];

        foreach ($files as $file) {
            $name = basename($file['ObjectName']);
            $data[] = [
                'name' => $name,
                'url' => "https://{$storageZone}.b-cdn.net/{$leadId}/{$type}/{$name}"
            ];
        }

        return response()->json(['files' => $data]);
    }

    // Subir historia clinica
    public function uploadHC(Request $request)
    {
        $leadId = $request->lead_id;
        $type = 'hc';

        $storageZone = env('BUNNY_STORAGE_ZONE_LEADS');
        $apiKey = env('BUNNY_API_KEY_LEADS');
        $region = env('BUNNY_REGION');

        $client = new Client();

        foreach ($request->file('files') as $file) {

            $fileName = $file->getClientOriginalName();
            $path = "{$leadId}/{$type}/{$fileName}";

            $client->put(
                "https://{$region}.storage.bunnycdn.com/{$storageZone}/{$path}",
                [
                    'headers' => [
                        'AccessKey' => $apiKey,
                        'Content-Type' => $file->getMimeType(),
                    ],
                    'body' => fopen($file->getRealPath(), 'r')
                ]
            );
        }

        return response()->json(['success' => true]);
    }
    // Borrar historia clinica
    public function deleteHC(Request $request, $leadId)
    {
        $filename = $request->filename;
        $type = 'hc';

        $storageZone = env('BUNNY_STORAGE_ZONE_LEADS');
        $apiKey = env('BUNNY_API_KEY_LEADS');
        $region = env('BUNNY_REGION');

        $client = new Client();

        $client->delete(
            "https://{$region}.storage.bunnycdn.com/{$storageZone}/{$leadId}/{$type}/{$filename}",
            ['headers' => ['AccessKey' => $apiKey]]
        );

        return response()->json(['success' => true]);
    }


    public function getID(Request $request)
    {
        $leadId = $request->lead_id;
        $type   = 'id';

        $client = new Client();

        $storageZone = env('BUNNY_STORAGE_ZONE_LEADS');
        $apiKey      = env('BUNNY_API_KEY_LEADS');
        $region      = env('BUNNY_REGION');

        $folder = "https://{$region}.storage.bunnycdn.com/{$storageZone}/{$leadId}/{$type}/";

        try {
            $response = $client->get($folder, [
                'headers' => [
                    'AccessKey' => $apiKey,
                    'accept' => '*/*',
                ]
            ]);

            $files = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $files = [];
        }

        $preview = [];
        $config  = [];
        $i = 0;

        if (is_array($files)) {
            foreach ($files as $file) {


                if (!empty($file['IsDirectory']) && $file['IsDirectory']) {
                    continue;
                }

                $fileName = basename($file['ObjectName']);
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $originSrc = "https://{$storageZone}.b-cdn.net/{$leadId}/{$type}/{$fileName}";

                $preview[] = $originSrc;

                $config[] = [
                    'caption' => $fileName,
                    'type' => in_array($ext, ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf',
                    'key' => $i++,
                ];
            }
        }

        return response()->json([
            'initialPreview' => $preview,
            'initialPreviewConfig' => $config,
            'initialPreviewAsData' => true,
        ]);
    }

    //Subir ID
    public function uploadID(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|integer',
            'fileid.*' => 'required|file|max:10240',
        ]);

        $leadId = $request->lead_id;
        $type   = 'id';

        $storageZone = env('BUNNY_STORAGE_ZONE_LEADS');
        $apiKey      = env('BUNNY_API_KEY_LEADS');
        $region      = env('BUNNY_REGION');

        $client = new Client();

        foreach ($request->file('fileid') as $file) {

            $filename = time() . '_' . $file->getClientOriginalName();
            $path = "{$leadId}/{$type}/{$filename}";

            $url = "https://{$region}.storage.bunnycdn.com/{$storageZone}/{$path}";

            $client->put($url, [
                'headers' => [
                    'AccessKey' => $apiKey,
                    'Content-Type' => $file->getMimeType(),
                ],
                'body' => fopen($file->getRealPath(), 'r'),
            ]);
        }

        return response()->json(['success' => true]);
    }

    // Borrar ID
    public function deleteID(Request $request, $leadId)
    {
        $request->validate([
            'filename' => 'required|string'
        ]);

        $filename = $request->filename;
        $type = 'id';

        $storageZone = env('BUNNY_STORAGE_ZONE_LEADS');
        $apiKey      = env('BUNNY_API_KEY_LEADS');
        $region      = env('BUNNY_REGION');

        $client = new Client();

        $url = "https://{$region}.storage.bunnycdn.com/{$storageZone}/{$leadId}/{$type}/{$filename}";

        try {
            $client->delete($url, [
                'headers' => [
                    'AccessKey' => $apiKey,
                ]
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el archivo'
            ], 500);
        }
    }


    //Funciones de archivos de laboratorios
    public function getLabs(Request $request)
    {
        $leadId = $request->lead_id;
        $type   = 'labs';

        $client = new Client();

        $storageZone = env('BUNNY_STORAGE_ZONE_LEADS');
        $apiKey      = env('BUNNY_API_KEY_LEADS');
        $region      = env('BUNNY_REGION');

        $folder = "https://{$region}.storage.bunnycdn.com/{$storageZone}/{$leadId}/{$type}/";

        try {
            $response = $client->get($folder, [
                'headers' => [
                    'AccessKey' => $apiKey,
                    'accept' => '*/*',
                ]
            ]);

            $files = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $files = [];
        }

        $preview = [];
        $config  = [];
        $i = 0;

        if (is_array($files)) {
            foreach ($files as $file) {

                if (!empty($file['IsDirectory']) && $file['IsDirectory']) {
                    continue;
                }

                $fileName = basename($file['ObjectName']);
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $originSrc = "https://{$storageZone}.b-cdn.net/{$leadId}/{$type}/{$fileName}";

                $preview[] = $originSrc;

                $config[] = [
                    'caption' => $fileName,
                    'type' => in_array($ext, ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf',
                    'key' => $i++,
                ];
            }
        }

        return response()->json([
            'initialPreview' => $preview,
            'initialPreviewConfig' => $config,
            'initialPreviewAsData' => true,
        ]);
    }

    //Subir archivos de laboratorios
    public function uploadLabs(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|integer',
            'filelabs.*' => 'required|file|max:10240',
        ]);

        $leadId = $request->lead_id;
        $type   = 'labs';

        $storageZone = env('BUNNY_STORAGE_ZONE_LEADS');
        $apiKey      = env('BUNNY_API_KEY_LEADS');
        $region      = env('BUNNY_REGION');

        $client = new Client();

        foreach ($request->file('filelabs') as $file) {

            $filename = time() . '_' . $file->getClientOriginalName();
            $path = "{$leadId}/{$type}/{$filename}";

            $url = "https://{$region}.storage.bunnycdn.com/{$storageZone}/{$path}";

            $client->put($url, [
                'headers' => [
                    'AccessKey' => $apiKey,
                    'Content-Type' => $file->getMimeType(),
                ],
                'body' => fopen($file->getRealPath(), 'r'),
            ]);
        }

        return response()->json(['success' => true]);
    }

    // Borrar archivos de laboratorios
    public function deleteLabs(Request $request, $leadId)
    {
        $request->validate([
            'filename' => 'required|string'
        ]);

        $filename = $request->filename;
        $type = 'labs';

        $storageZone = env('BUNNY_STORAGE_ZONE_LEADS');
        $apiKey      = env('BUNNY_API_KEY_LEADS');
        $region      = env('BUNNY_REGION');

        $client = new Client();

        $url = "https://{$region}.storage.bunnycdn.com/{$storageZone}/{$leadId}/{$type}/{$filename}";

        try {
            $client->delete($url, [
                'headers' => [
                    'AccessKey' => $apiKey,
                ]
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el archivo'
            ], 500);
        }
    }

    //-------------------------------- Fin de funciones de subtabs de clientes ---------------------------------------



    // ------------------ FUNCIONES AUXILIARES ------------------

    //Crear carpeta en el conejo feo ese de Bunny
    private function createBunnyFolder($client, $path, $storageZone, $apiKey, $region)
    {
        try {
            $client->put(
                "https://{$region}.storage.bunnycdn.com/{$storageZone}/{$path}/",
                [
                    'headers' => [
                        'AccessKey' => $apiKey,
                    ],
                ]
            );
        } catch (\Exception $e) {
        }
    }

    // Funciones de generar contrato

    public function generarContrato(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:255',
            'fecha'    => 'required|date',
            'monto'    => 'required|numeric|min:0',
            'anticipo' => 'required|numeric|min:0',
        ]);

        $nombre   = $request->nombre;
        $fecha    = $request->fecha;
        $monto    = floatval($request->monto);
        $anticipo = floatval($request->anticipo);

        // Evitar números negativos
        $saldo = max($monto - $anticipo, 0);

        // Formatos
        $fechaFormateada  = $this->formatearFechaEspanol($fecha);

        $montoFormateado    = $this->formatoMoneda($monto);
        $anticipoFormateado = $this->formatoMoneda($anticipo);
        $saldoFormateado    = $this->formatoMoneda($saldo);

        $montoLetras    = $this->numeroALetras($monto);
        $anticipoLetras = $this->numeroALetras($anticipo);
        $saldoLetras    = $this->numeroALetras($saldo);

        // Instancia mPDF
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 20,
            'margin_bottom' => 20,
        ]);

        // Renderizar vista
        $html = view('pdf.contrato', compact(
            'nombre',
            'fechaFormateada',
            'montoFormateado',
            'montoLetras',
            'anticipoFormateado',
            'anticipoLetras',
            'saldoFormateado',
            'saldoLetras'
        ))->render();

        $mpdf->WriteHTML($html);

        return response(
            $mpdf->Output('CONTRATO_' . strtoupper($nombre) . '.pdf', 'I'),
            200,
            ['Content-Type' => 'application/pdf']
        );
        //dd($html);
    }

    /* =====================================================
     |  FORMATO MONEDA
     ===================================================== */
    private function formatoMoneda($num)
    {
        return '$' . number_format($num, 2, '.', ',');
    }

    /* =====================================================
     |  FECHA EN ESPAÑOL
     ===================================================== */
    private function formatearFechaEspanol($fecha)
    {
        $meses = [
            1 => 'enero',
            2 => 'febrero',
            3 => 'marzo',
            4 => 'abril',
            5 => 'mayo',
            6 => 'junio',
            7 => 'julio',
            8 => 'agosto',
            9 => 'septiembre',
            10 => 'octubre',
            11 => 'noviembre',
            12 => 'diciembre'
        ];

        $timestamp = strtotime($fecha);

        return date('j', $timestamp) .
            ' de ' .
            $meses[(int)date('n', $timestamp)] .
            ' del ' .
            date('Y', $timestamp);
    }

    /* =====================================================
     |  NUMERO A LETRAS (MX)
     ===================================================== */
    private function numeroALetras($numero)
    {
        $formatter = new \NumberFormatter("es_MX", \NumberFormatter::SPELLOUT);
        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 2);

        $entero = floor($numero);
        $decimal = round(($numero - $entero) * 100);

        $letras = strtoupper($formatter->format($entero));

        return "{$letras} PESOS {$decimal}/100 M.N.";
    }

public function anticipoJson(int $id)
{
    // ================= LEAD =================
    $lead = DB::table('sa_leads')
        ->where('id', $id)
        ->select('id', 'first_name', 'last_name', 'clinic', 'interested_in')
        ->first();

    if (!$lead) {
        return response()->json([
            'success' => false,
            'message' => 'Lead no encontrado'
        ], 404);
    }

    // ================= ASSESSMENT (FECHA INJERTO) =================
    $assessment = DB::table('sa_leads_assessment')
        ->where('lead_id', $id)
        ->orderByDesc('id')
        ->select('procedure_date')
        ->first();

    $procedureDate = 'fecha_abierta';

    if ($assessment && !empty($assessment->procedure_date)) {

        $rawDate = trim($assessment->procedure_date);

        $invalidDates = [
            '0000-00-00',
            '00-00-0000',
            '2030-01-01',
            '01-01-2030',
        ];

        if (!in_array($rawDate, $invalidDates, true)) {
            try {
                $procedureDate = \Carbon\Carbon::parse($rawDate)->format('Y-m-d');
            } catch (\Exception $e) {
                $procedureDate = 'fecha_abierta';
            }
        }
    }

    // ================= COTIZACIÓN =================
    $quote = DB::table('sa_closed_px')
        ->where('lead_id', $id)
        ->select('quoted_cash_amount', 'quoted_cc_amount', 'installments')
        ->first();

    $totalAmount = $quote->quoted_cash_amount
        ?? $quote->quoted_cc_amount
        ?? 0;

    // ================= PAGOS =================
    $totalPaid = DB::table('sa_info_payment_px')
        ->where('lead_id', $id)
        ->where('status', 1)
        ->whereIn('type', ['abono', 'anticipo'])
        ->sum('amount');

    $pendingAmount = max($totalAmount - $totalPaid, 0);

    // ================= RESPONSE =================
    return response()->json([
        'success' => true,

        'lead' => [
            'id'            => $lead->id,
            'name'          => trim($lead->first_name . ' ' . $lead->last_name),
            'clinic'        => $lead->clinic,
            'interested_in' => $lead->interested_in,
        ],

        'assessment' => [
            'procedure_date' => $procedureDate,
        ],

        'anticipo' => [
            'total'   => (float) $totalAmount,
            'paid'    => (float) $totalPaid,
            'pending' => (float) $pendingAmount,
        ]
    ]);
}


public function liquidacionJson(int $id)
{
    // ================= LEAD =================
    $lead = DB::table('sa_leads')
        ->where('id', $id)
        ->select('id', 'first_name', 'last_name', 'clinic')
        ->first();

    if (!$lead) {
        return response()->json([
            'success' => false,
            'message' => 'Lead no encontrado'
        ], 404);
    }

    // ================= ASSESSMENT =================
    $assessment = DB::table('sa_leads_assessment')
        ->where('lead_id', $id)
        ->orderByDesc('id')
        ->select('procedure_date', 'procedure_type')
        ->first();

    $procedureDate = 'fecha_abierta';
    $procedureType = '';

    if ($assessment) {
        $procedureType = $assessment->procedure_type ?? '';

        if (!empty($assessment->procedure_date)) {
            $invalidDates = [
                '0000-00-00',
                '00-00-0000',
                '2030-01-01',
                '01-01-2030',
            ];

            if (!in_array($assessment->procedure_date, $invalidDates, true)) {
                try {
                    $procedureDate = \Carbon\Carbon::parse(
                        $assessment->procedure_date
                    )->format('Y-m-d');
                } catch (\Exception $e) {
                    $procedureDate = 'fecha_abierta';
                }
            }
        }
    }

    // ================= COTIZACIÓN =================
    $quote = DB::table('sa_closed_px')
        ->where('lead_id', $id)
        ->select('quoted_cash_amount', 'quoted_cc_amount')
        ->first();

    $procedureTotal = $quote->quoted_cash_amount
        ?? $quote->quoted_cc_amount
        ?? 0;

    // ================= PAGOS (INCLUYE LIQUIDACIONES) =================
    $totalPaid = DB::table('sa_info_payment_px')
        ->where('lead_id', $id)
        ->where('status', 1)
        ->whereIn('type', ['abono', 'anticipo', 'liquidacion'])
        ->sum('amount');

    $pendingAmount = max($procedureTotal - $totalPaid, 0);

    // ================= RESPONSE =================
    return response()->json([
        'success' => true,

        'lead' => [
            'id'           => $lead->id,
            'patient_name' => trim($lead->first_name . ' ' . $lead->last_name),
            'clinic'       => $lead->clinic,
        ],

        'procedure' => [
            'procedure_date' => $procedureDate,
            'procedure_type' => $procedureType,
        ],

        'liquidacion' => [
            'procedure_total' => (float) $procedureTotal,
            'total_paid'      => (float) $totalPaid,
            'pending_amount'  => (float) $pendingAmount,
        ]
    ]);
}


//GUardar enfermedad actual
public function getHealth(int $leadId)
{
    $assessment = DB::table('sa_leads_assessment')
        ->select('id', 'lead_id', 'enfermedades')
        ->where('lead_id', $leadId)
        ->where('status', 1)
        ->orderByDesc('id')
        ->first();

    // Si no hay valoración activa, puedes regresar vacío en vez de 404
    if (!$assessment) {
        return response()->json([
            'success' => true,
            'data' => [
                'lead_id' => $leadId,
                'assessment_id' => null,
                'health_conditions' => '',
            ]
        ]);
    }

    return response()->json([
        'success' => true,
        'data' => [
            'lead_id' => $assessment->lead_id,
            'assessment_id' => $assessment->id,
            'health_conditions' => $assessment->enfermedades ?? '',
        ]
    ]);
}

public function saveHealth(Request $request, int $leadId)
{
    $request->validate([
        'health_conditions' => 'required|string|max:2000',
    ]);

    // Buscar valoración activa
    $assessment = DB::table('sa_leads_assessment')
        ->select('id')
        ->where('lead_id', $leadId)
        ->where('status', 1)
        ->orderByDesc('id')
        ->first();

    if (!$assessment) {
        return response()->json([
            'success' => false,
            'message' => 'No hay valoración activa para este lead. Primero guarda una valoración.'
        ], 422);
    }

    DB::table('sa_leads_assessment')
        ->where('id', $assessment->id)
        ->update([
            'enfermedades' => trim($request->health_conditions),
            'updated_at' => now(),
        ]);

    return response()->json([
        'success' => true,
        'message' => 'Historia clínica guardada.',
    ]);
}
}
