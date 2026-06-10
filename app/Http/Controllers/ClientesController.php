<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Mpdf\Mpdf;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;


class ClientesController extends Controller
{
    public function indexClientes()
    {
        // Consulta base usando Query Builder
        $clientes = DB::table('sa_closed_px as scp')
            ->leftJoin(DB::raw('(
            SELECT a.*
            FROM sa_leads_assessment a
            INNER JOIN (
                SELECT lead_id, MAX(procedure_date) AS ult_fecha
                FROM sa_leads_assessment
                GROUP BY lead_id
            ) b ON a.lead_id = b.lead_id AND a.procedure_date = b.ult_fecha
        ) sla'), 'sla.lead_id', '=', 'scp.lead_id')
            ->leftJoin('sa_leads as sl', 'sl.id', '=', 'scp.lead_id')
            ->leftJoin(DB::raw('(
            SELECT ep.lead_id, MAX(ep.num_med_record) AS num_med_record
            FROM enf_procedures ep
            GROUP BY ep.lead_id
        ) ep'), 'ep.lead_id', '=', 'scp.lead_id')
            ->select(
                'scp.id',
                'scp.lead_id',
                'scp.status',
                DB::raw("CONCAT(sla.first_name, ' ', sla.last_name) as name"),

                // ✅ RAW para ordenar/filtrar
                DB::raw("DATE(sla.procedure_date) as procedure_date_raw"),

                // ✅ Texto para mostrar
                DB::raw("DATE_FORMAT(sla.procedure_date, '%d/%m/%Y') as procedure_date"),

                'sla.procedure_type',
                'sla.clinic',
                'sla.status',
                DB::raw("COALESCE(sl.seller, 'desconocido') as seller"),
                DB::raw("COALESCE(ep.num_med_record, 'NA') as num_med_record"),
                DB::raw("COALESCE(sl.phone, '') as phone"),
            )
            ->whereNotNull('sla.first_name')
            ->whereNotNull('sla.last_name')
            ->whereNotNull('sla.procedure_date')
            ->orderByRaw("CASE WHEN DATE(sla.procedure_date) = CURDATE() THEN 0 ELSE 1 END")
            ->orderByDesc('sla.procedure_date')
            ->get();


        // Retornamos la vista con los datos
        //dd($clientes);
        return view('crm.clientes.clientes', compact('clientes'));
    }

    public function updateStatus(Request $request)
    {
        // Validación básica
        $payload = $request->validate([
            'lead_id' => 'required|integer',
            'current_status_val' => 'required|integer',
            'chosen_status_lbl' => 'required|string',
            'num_med_record' => 'nullable|integer',
            'cancel_reason' => 'nullable|string|max:1000',
        ]);

        $lead_id = (int) $payload['lead_id'];
        $current_status = (int) $payload['current_status_val'];
        $chosen_status = $payload['chosen_status_lbl'];

        try {
            $result = DB::transaction(function () use ($lead_id, $current_status, $chosen_status, $payload) {

                // Obtener clinic desde la última assessment (por fecha)
                $clinic = DB::table('sa_leads_assessment')
                    ->where('lead_id', $lead_id)
                    ->orderByDesc('procedure_date')
                    ->value('clinic');

                if (!$clinic) {
                    return [
                        'success' => false,
                        'message' => 'No se encontró la clínica del lead.',
                    ];
                }

                // Variables de respuesta
                $newStatus = null;
                $message = '';

                // ---------- CASO: estatus actual = 2 (Exp. Asignado) ----------
                if ($current_status === 2) {

                    if ($chosen_status === 'Cancelado') {
                        // Eliminar procedimiento(s) y poner status = 0
                        DB::table('enf_procedures')->where('lead_id', $lead_id)->delete();

                        DB::table('sa_closed_px')->where('lead_id', $lead_id)->update([
                            'status' => 0
                        ]);

                        $newStatus = 0;
                        $message = 'Eliminado de Procedimientos y cliente marcado como Cancelado.';
                    } elseif ($chosen_status === 'Próximo') {
                        DB::table('sa_closed_px')->where('lead_id', $lead_id)->update([
                            'status' => 1
                        ]);

                        $newStatus = 1;
                        $message = 'Cliente actualizado a Próximo correctamente.';
                    } else {
                        return ['success' => false, 'message' => 'Transición inválida desde estado 2.'];
                    }

                    // opcional: registrar log
                    Log::info("Status change for lead {$lead_id}: {$current_status} -> {$newStatus}");
                    return ['success' => true, 'message' => $message, 'new_status' => $newStatus];
                }

                // ---------- CASO: estatus actual = 1 (Próximo) ----------
                if ($current_status === 1) {

                    if ($chosen_status === 'Asignar Exped.') {
                        // num_med_record obligatorio
                        if (empty($payload['num_med_record'])) {
                            return ['success' => false, 'message' => 'El número de expediente es requerido para asignar.'];
                        }
                        $num_med_record = (int) $payload['num_med_record'];

                        // Verificar si ya existe expediente (touchup)
                        $exists = DB::table('enf_procedures')->where('num_med_record', $num_med_record)->exists();
                        $touchup = $exists ? 1 : 0;

                        // Actualizar status a 2
                        DB::table('sa_closed_px')->where('lead_id', $lead_id)->update(['status' => 2]);

                        // Insertar en enf_procedures
                        DB::table('enf_procedures')->insert([
                            'lead_id' => $lead_id,
                            'clinic' => $clinic,
                            'num_med_record' => $num_med_record,
                            'touchup' => $touchup,
                            'room' => 0,
                            'specialist' => '',
                            'notes' => '',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $newStatus = 2;
                        $message = 'Expediente asignado correctamente.';
                    } elseif ($chosen_status === 'Cancelado') {
                        // Cambiar a cancelado (0)
                        DB::table('sa_closed_px')->where('lead_id', $lead_id)->update(['status' => 0]);

                        $newStatus = 0;
                        $message = 'Cliente cancelado correctamente.';
                    } else {
                        return ['success' => false, 'message' => 'Transición inválida desde estado 1.'];
                    }

                    Log::info("Status change for lead {$lead_id}: {$current_status} -> {$newStatus}");
                    return ['success' => true, 'message' => $message, 'new_status' => $newStatus];
                }

                // ---------- CASO: estatus actual = 0 (Cancelado) ----------
                if ($current_status === 0) {
                    // Normalmente solo puede volver a Próximo (1)
                    DB::table('sa_closed_px')->where('lead_id', $lead_id)->update(['status' => 1]);

                    $newStatus = 1;
                    $message = 'Cliente actualizado a Próximo correctamente.';

                    Log::info("Status change for lead {$lead_id}: {$current_status} -> {$newStatus}");
                    return ['success' => true, 'message' => $message, 'new_status' => $newStatus];
                }

                return ['success' => false, 'message' => 'Transición de estatus no contemplada.'];
            });

            // Si $result viene con success false
            if (isset($result['success']) && $result['success'] === false) {
                return response()->json($result);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error updateStatus: ' . $e->getMessage(), ['lead_id' => $lead_id ?? null]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPatientByRecord(Request $request)
    {
        try {
            $num = $request->num_med_record;

            if (!$num || !is_numeric($num)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes proporcionar un num_med_record válido.'
                ]);
            }

            $result = DB::table('sa_leads_assessment as sig')
                ->join('enf_procedures as ep', 'sig.lead_id', '=', 'ep.lead_id')
                ->select(
                    DB::raw("CONCAT(sig.first_name, ' ', sig.last_name) AS fullname"),
                    'sig.clinic'
                )
                ->where('ep.num_med_record', $num)
                ->orderBy('sig.created_at', 'DESC')
                ->first();

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró ningún paciente con ese número de expediente.'
                ]);
            }

            return response()->json([
                'success' => true,
                'fullname' => $result->fullname,
                'clinic' => $result->clinic
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateMedicalRecord(Request $request)
    {
        try {
            $old = $request->num_med_record;   // número actual
            $new = $request->numero;           // nuevo número

            if (!$old || !$new) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes ingresar ambos números.'
                ]);
            }

            if (!is_numeric($old) || !is_numeric($new)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los números deben ser numéricos.'
                ]);
            }

            // Buscar el expediente existente
            $procedure = DB::table('enf_procedures')
                ->where('num_med_record', $old)
                ->first();

            if (!$procedure) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró un expediente con ese número.'
                ]);
            }

            // Validar si el nuevo número ya existe
            $exists = DB::table('enf_procedures')
                ->where('num_med_record', $new)
                ->first();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'El nuevo número ya está asignado a otro paciente.'
                ]);
            }

            // actualixza el número de expediente
            DB::table('enf_procedures')
                ->where('num_med_record', $old)
                ->update([
                    'num_med_record' => $new
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Número de expediente actualizado correctamente.'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    // ------------------- funciones de tabs de clientes -------------------

    // Historial de transacciones
    public function transactions($leadId)
    {
        // COTIZACIÓN
        $quote = DB::table('sa_closed_px')
            ->where('lead_id', $leadId)
            ->select('quoted_cash_amount', 'quoted_cc_amount', 'installments')
            ->first();

        $quoted = $quote->quoted_cash_amount ?? 0;

        // PAGOS
        $payments = DB::table('sa_info_payment_px')
            ->where('lead_id', $leadId)
            ->where('status', 1)
            ->orderBy('payment_date', 'desc')
            ->select(
                'id',
                'type',
                'method',
                'public_notes',
                'amount',
                DB::raw("DATE_FORMAT(payment_date, '%d/%m/%Y') as date")
            )
            ->get()
            ->map(function ($payment) use ($leadId) {

            $prefixMap = [
                'producto'    => 'producto',
                'anticipo'    => 'anticipo',
                'abono'       => 'abono',
                'liquidacion' => 'liquidacion'
            ];

            $prefix = $prefixMap[$payment->type] ?? 'producto';

            $baseDirRel = "storage/leads/{$leadId}/receipts";
            $baseDirAbs = public_path($baseDirRel);

            $receiptPdf = null;
            $receiptImg = null;

            // 1) Primero: patrón tipo_id
            $pdfExts = ['pdf'];
            $imgExts = ['jpg', 'jpeg', 'png', 'webp'];

            foreach ($pdfExts as $ext) {
                $rel = "{$baseDirRel}/{$prefix}_{$payment->id}.{$ext}";
                if (file_exists(public_path($rel))) { $receiptPdf = asset($rel); break; }
            }

            foreach ($imgExts as $ext) {
                $rel = "{$baseDirRel}/{$prefix}_{$payment->id}.{$ext}";
                if (file_exists(public_path($rel))) { $receiptImg = asset($rel); break; }
            }

            // 2) Fallback: si NO hay imagen, buscar receipt_* (por si se guardó con timestamp)
            if (!$receiptImg && is_dir($baseDirAbs)) {
                $matches = glob($baseDirAbs . "/receipt_*.{jpg,jpeg,png,webp}", GLOB_BRACE);
                if (!empty($matches)) {
                    // el más reciente
                    usort($matches, fn($a,$b) => filemtime($b) <=> filemtime($a));
                    $last = basename($matches[0]);
                    $receiptImg = asset("{$baseDirRel}/{$last}");
                }
            }

            // 3) Fallback: si NO hay pdf, buscar {prefix}_{id}.pdf por si acaso ya lo intentaste arriba
            // (ya está cubierto)

            return [
                'id' => $payment->id,
                'type' => ucfirst($payment->type),
                'date' => $payment->date,
                'amount' => $payment->amount,
                'payment_method' => $payment->method ?? '-',
                'notes' => $payment->public_notes ?? '-',
                'receipt_url' => $receiptPdf,   // PDF
                'receipt_img' => $receiptImg,   // IMAGEN
            ];
        });

        // RESUMEN
        $totalPaid = $payments->sum('amount');
        $pending = $quoted - $totalPaid;

        return response()->json([
            'summary' => [
                'quoted'  => $quoted,
                'paid'    => $totalPaid,
                'pending' => $pending
            ],
            'data' => $payments
        ]);
    }

    // Eliminar transacción
    public function deletePayment($paymentId)
    {
        $payment = DB::table('sa_info_payment_px')->where('id', $paymentId)->first();

        if (!$payment) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        /*
        $receiptPath = public_path(
            "storage/leads/{$payment->lead_id}/receipts/producto_{$paymentId}.pdf"
        );
      
        if (file_exists($receiptPath)) {
            unlink($receiptPath);
        }
      */
        // Eliminado lógico (recomendado)
        DB::table('sa_info_payment_px')
            ->where('id', $paymentId)
            ->update(['status' => 0]);

        return response()->json(['message' => 'Pago eliminado']);
    }


        //Actualizar valoracion
       public function assessment($leadId)
{
    $leadId = (int)$leadId;

    $lead = DB::table('sa_leads')
        ->where('id', $leadId)
        ->first();

    $assessment = DB::table('sa_leads_assessment')
        ->where('lead_id', $leadId)
        ->where('status', 1)
        ->orderByDesc('id')
        ->first();

    $photoUrl = null;

    // solo buscar foto si existe assessment
    if ($assessment) {
        $dir = public_path("storage/leads/{$leadId}/assessment");

        
        $photoPath = $this->getAssessmentImageById($dir, (int)$assessment->id);

        if ($photoPath) {
            $filename = basename($photoPath);
            $photoUrl = asset("storage/leads/{$leadId}/assessment/{$filename}");
        }
    }

    return response()->json([
        'lead' => $lead,
        'assessment' => $assessment,
        'photo_url' => $photoUrl,
    ]);
}



        public function storeAssessment(Request $request, $leadId)
{
    $leadId = (int)$leadId;

    $openDate = $request->boolean('open_date');

    $request->validate([
        'assessment_date'       => 'required|date',
        'client_firstname'      => 'required',
        'client_lastname'       => 'required',
        'procedure_type'        => 'required',
        'assessment_employee'   => 'required',
        'first_meet_type'       => 'required',
        'clinic'                => 'required',
        'assessment_type'       => 'required',
        'description'           => 'required',
        'photo'                 => 'nullable|image|max:4096',
        'procedure_date'        => $openDate ? 'nullable|date' : 'required|date',

        // para editar
        'assessment_id'         => 'nullable|integer',
    ]);

    $procedureDate = $openDate ? null : $request->procedure_date;

    $payload = [
        'date'            => $request->assessment_date,
        'first_name'      => $request->client_firstname,
        'last_name'       => $request->client_lastname,
        'procedure_date'  => $procedureDate,
        'procedure_type'  => $request->procedure_type,
        'closer'          => $request->assessment_employee,
        'first_meet_type' => $request->first_meet_type,
        'clinic'          => $request->clinic,
        'type'            => $request->assessment_type,
        'notes'           => $request->description,
        'gender'          => $request->client_gender,
        'phone'           => $request->client_phone,
        'updated_at'      => now(),
    ];

    $assessmentId = (int)($request->input('assessment_id') ?? 0);

    if ($assessmentId > 0) {
        // ✅ UPDATE (no crear otro)
        DB::table('sa_leads_assessment')
            ->where('id', $assessmentId)
            ->where('lead_id', $leadId)
            ->update($payload);

    } else {
        // ✅ INSERT (primera vez)
        DB::table('sa_leads_assessment')
            ->where('lead_id', $leadId)
            ->update(['status' => 0]);

        $assessmentId = DB::table('sa_leads_assessment')->insertGetId($payload + [
            'lead_id'     => $leadId,
            'created_by'  => auth()->id(),
            'status'      => 1,
            'created_at'  => now(),
        ]);
    }

    // FOTO
    if ($request->hasFile('photo')) {
        $photo = $request->file('photo');
        if (!$photo->isValid()) return back()->with('error', 'La foto es inválida.');

        $publicDir = public_path("storage/leads/{$leadId}/assessment");
        if (!is_dir($publicDir)) mkdir($publicDir, 0775, true);

        $ext = strtolower($photo->getClientOriginalExtension() ?: 'jpg');
        if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) $ext = 'jpg';

        $fileName = "design_{$assessmentId}.{$ext}";
        $photo->move($publicDir, $fileName);
    }

    return back()->with('success', 'Valoración guardada correctamente');
}


    /* --------------------------- Seccion de funciones auxiliares xd --------------------------- */

private function getAssessmentImageById(string $dir, int $assessmentId): ?string
{
    if (!File::isDirectory($dir)) return null;

    $patterns = [
        $dir . "/design_{$assessmentId}.jpg",
        $dir . "/design_{$assessmentId}.jpeg",
        $dir . "/design_{$assessmentId}.png",
        $dir . "/design_{$assessmentId}.webp",
    ];

    foreach ($patterns as $p) {
        if (File::exists($p)) return $p;
    }

    return null;
}


    private function uploadAssessmentPdfToBunny(int $leadId, string $localPath, string $fileName): string
{
    $client = new Client();

    $storageZone = env('BUNNY_STORAGE_ZONE_LEADS');      // rdi-cdmx-leads
    $apiKey      = env('BUNNY_API_KEY_LEADS');
    $region      = env('BUNNY_REGION');                  // LA
    $cdnBase     = env('BUNNY_CDN_BASE_LEADS', 'https://rdi-cdmx-leads.b-cdn.net');

    // Crear “carpetas” (opcional pero ok)
    $this->createBunnyFolder($client, (string)$leadId, $storageZone, $apiKey, $region);
    $this->createBunnyFolder($client, "{$leadId}/assessment", $storageZone, $apiKey, $region);

    $remotePath = "{$leadId}/assessment/{$fileName}";
    $uploadUrl  = "https://{$region}.storage.bunnycdn.com/{$storageZone}/{$remotePath}";

    $resp = $client->put($uploadUrl, [
        'headers' => [
            'AccessKey'    => $apiKey,
            'Content-Type' => 'application/pdf',
        ],
        'body' => fopen($localPath, 'r'),
    ]);

    // Validación simple (opcional pero recomendable)
    $code = $resp->getStatusCode();
    if (!in_array($code, [200, 201])) {
        throw new \RuntimeException("Bunny upload failed (HTTP {$code})");
    }

    return rtrim($cdnBase, '/') . '/' . $remotePath;
}
    //Generar pdf de valoracion
    public function valoracionPdf(int $leadId)
{
    try {
    $lead = DB::table('sa_leads')->where('id', $leadId)->first();
    if (!$lead) abort(404, 'Lead no encontrado');

    // Última valoración activa (ajusta si tu lógica es distinta)
    $assessment = DB::table('sa_leads_assessment')
        ->where('lead_id', $leadId)
        ->where('status', 1)
        ->orderByDesc('id')
        ->first();

    if (!$assessment) abort(404, 'No hay valoración activa');

    $name = trim(($assessment->first_name ?? $lead->first_name ?? '') . ' ' . ($assessment->last_name ?? $lead->last_name ?? ''));
    $clinic = $assessment->clinic ?? $lead->clinic ?? '';

    // Formatos de fecha como el CRM viejo (YYYY-MM-DD => DD/MM/YYYY)
    $fmtDate = fn($d) => ($d && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d))
        ? substr($d, 8, 2).'/'.substr($d, 5, 2).'/'.substr($d, 0, 4)
        : ($d ?: '-');

    $assessmentDate = $fmtDate($assessment->date ?? null);

    // En viejo: open_date -> 2030-01-01 "Por definir"
    $procedureRaw = $assessment->procedure_date ?? null;
    $procedureDate = ($procedureRaw === '2030-01-01') ? 'Por definir' : $fmtDate($procedureRaw);

    $procedureType = $assessment->procedure_type ?? '-';
    $closer        = $assessment->closer ?? '-';

    // Notas: en viejo solo imprime 3 renglones (separando por saltos)
    $notes = (string)($assessment->notes ?? '');
    $notesLines = preg_split("/\r\n|\n|\r/", $notes);
    $notesLines = array_slice(array_pad($notesLines, 3, ''), 0, 3);

    // PDF base según clínica (ajusta paths reales)
    $basePdf = base_path('resources/pdf_templates/valoracion.pdf');


    if (!File::exists($basePdf)) {
        abort(500, "No existe PDF base: {$basePdf}");
    }

    $mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'orientation' => 'P',
    'format' => 'Letter',
    'tempDir' => storage_path('app/mpdf-temp'),
    ]);


    $mpdf->SetTitle("Valoración_{$name}");

    $pagecount = $mpdf->SetSourceFile($basePdf);
    $tplId = $mpdf->ImportPage($pagecount);
    $mpdf->UseTemplate($tplId);
    $mpdf->SetFont('sans-serif', 'B', 12);


    // Helpers (igual que tu viejo)
    $writeText = function(float $x, float $y, string $text) use ($mpdf) {
        $mpdf->WriteText($x, $y, $text);
    };

    // Coordenadas (las mismas que tu PHP viejo)
    $writeText(38,   46,   $name);
    $writeText(168.9,40.4, $assessmentDate);
    $writeText(73,   59.6, $procedureDate);
    $writeText(161,  59.6, $procedureType);

    // Notes 3 líneas
    $writeText(30, 215.50, $notesLines[0] ?? '');
    $writeText(30, 225.50, $notesLines[1] ?? '');
    $writeText(30, 235.50, $notesLines[2] ?? '');

    $writeText(130, 271, $closer);

    $assessmentDir = public_path("storage/leads/{$leadId}/assessment");
    // buscar la del assessment actual: design_{id}.(jpg|jpeg|png|webp)
    $photoPath = $this->getAssessmentImageById($assessmentDir, (int)$assessment->id);

    if ($photoPath && File::exists($photoPath)) {
        // Detectar orientación (como viejo)
        [$w, $h] = @getimagesize($photoPath) ?: [0,0];
        if ($w > $h) {
            // Horizontal
            $mpdf->Image($photoPath, 45, 82, 133, 100);
        } else {
            // Vertical
            $mpdf->Image($photoPath, 75, 82, 75, 100);
        }
    }
    // Si no hay imagen, simplemente no se pone nada (como pediste)

    $timestamp = time();
    $fileName  = "valoracion_{$timestamp}.pdf";

    // Guardar local temporal
    $tmpDir = storage_path("app/tmp/leads/{$leadId}/assessment");
    File::ensureDirectoryExists($tmpDir);

    $localPath = $tmpDir . DIRECTORY_SEPARATOR . $fileName;

    // Guardar PDF a archivo
    $mpdf->Output($localPath, 'F');

    // Subir a Bunny
    $cdnUrl = $this->uploadAssessmentPdfToBunny($leadId, $localPath, $fileName);

    // Borrar temporal (opcional)
    @unlink($localPath);

    $filename = "valoracion_{$leadId}_" . time() . ".pdf";
    return response($mpdf->Output($filename, 'S'), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="'.$filename.'"',
    ]);
    } catch (\Throwable $e) {
    Log::error('PDF Valoracion Error', [
        'lead_id' => $leadId,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);
    abort(500, $e->getMessage());
}
}

        //MOstrar pdf existente
    public function getAssessmentPdf(int $leadId)
        {
            $client = new Client();

            $storageZone = env('BUNNY_STORAGE_ZONE_LEADS');
            $apiKey      = env('BUNNY_API_KEY_LEADS');
            $region      = env('BUNNY_REGION');
            $cdnBase     = env('BUNNY_CDN_BASE_LEADS', 'https://rdi-cdmx-leads.b-cdn.net');

            $folderUrl = "https://{$region}.storage.bunnycdn.com/{$storageZone}/{$leadId}/assessment/";

            try {
                $response = $client->get($folderUrl, [
                    'headers' => ['AccessKey' => $apiKey],
                ]);

                $items = json_decode($response->getBody()->getContents(), true) ?? [];

                // Filtrar solo archivos PDF (no directorios)
                $pdfs = array_values(array_filter($items, function ($it) {
                    $name = $it['ObjectName'] ?? '';
                    // Bunny suele incluir IsDirectory
                    if (!empty($it['IsDirectory'])) return false;
                    return str_ends_with(strtolower($name), '.pdf');
                }));

                if (!count($pdfs)) {
                    return response()->json(['exists' => false]);
                }

                // Ordenar por fecha (Bunny puede mandar LastChanged / DateCreated)
                usort($pdfs, function ($a, $b) {
                    $da = $a['LastChanged'] ?? $a['DateCreated'] ?? '';
                    $db = $b['LastChanged'] ?? $b['DateCreated'] ?? '';
                    return strcmp($da, $db); // asc
                });

                $latest = end($pdfs);
                $objectName = $latest['ObjectName']; // ej: "3772/assessment/valoracion_....pdf" o solo "valoracion_....pdf"

                // Si ObjectName NO trae el path, lo armamos
                if (!str_contains($objectName, '/')) {
                    $objectName = "{$leadId}/assessment/{$objectName}";
                }

                $url = rtrim($cdnBase, '/') . '/' . ltrim($objectName, '/');

                return response()->json([
                    'exists'   => true,
                    'url'      => $url,
                    'filename' => basename($objectName),
                    'meta'     => [
                        'lastChanged' => $latest['LastChanged'] ?? null,
                        'size'        => $latest['Length'] ?? null,
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::warning('getAssessmentPdf failed', [
                    'lead_id' => $leadId,
                    'error'   => $e->getMessage(),
                ]);
                return response()->json(['exists' => false]);
            }
        }

    // funciod de traer el nombre de los pacientes por su numero de expediente
    public function getPatientByRecord2(Request $request)
    {
        try {
            $num = $request->num_med_record;

            if (!$num || !is_numeric($num)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes proporcionar un num_med_record válido.'
                ]);
            }

            $result = DB::table('sa_leads_assessment as sig')
                ->join('enf_procedures as ep', 'sig.lead_id', '=', 'ep.lead_id')
                ->select(
                    DB::raw("CONCAT(sig.first_name, ' ', sig.last_name) AS fullname"),
                    'sig.clinic'
                )
                ->where('ep.num_med_record', $num)
                ->orderBy('sig.created_at', 'DESC')
                ->first();

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró ningún paciente con ese número de expediente.'
                ]);
            }

            return response()->json([
                'success' => true,
                'fullname' => $result->fullname,
                'clinic' => $result->clinic
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

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
}
