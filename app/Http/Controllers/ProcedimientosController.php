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
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Illuminate\Support\Facades\File;

use App\Models\EnfSign;
use DateTime;

class ProcedimientosController extends Controller
{
    public function indexProcedimientos()
    {
        $procedimientos = DB::table('enf_procedures as ep')
            ->join('sa_leads_assessment as sla', 'ep.lead_id', '=', 'sla.lead_id')
            ->leftJoin(DB::raw('(
            SELECT
                num_med,
                AVG(status_conformidad) AS avg_conf,
                SUM(status_conformidad) AS suma_conf,
                COUNT(*) AS total_notas
            FROM medical_notes
            GROUP BY num_med
        ) as mn_stats'), 'mn_stats.num_med', '=', 'ep.num_med_record')
            ->select(
                'sla.lead_id',
                DB::raw("CONCAT(sla.first_name, ' ', sla.last_name) AS name"),

                // ✅ fecha real (para ordenar/filtrar)
                'sla.procedure_date as procedure_date_raw',

                // ✅ fecha para mostrar (y mostrar Por definir si es 2030-01-01)
                DB::raw("
                CASE
                    WHEN sla.procedure_date = '2030-01-01' OR sla.procedure_date IS NULL
                        THEN 'Por definir'
                    ELSE DATE_FORMAT(sla.procedure_date, '%d/%m/%y')
                END AS procedure_date
            "),

                'sla.procedure_type',
                'ep.num_med_record',
                'ep.touchup',
                'ep.room',
                'ep.specialist',
                'ep.clinic',
                DB::raw('IFNULL(ROUND(mn_stats.avg_conf, 1), 0) AS avg_conf'),
                DB::raw('IFNULL(mn_stats.total_notas, 0) AS total_notas'),
                DB::raw('IFNULL(mn_stats.suma_conf, 0) AS suma_conformidad')
            )
            ->where('sla.status', '<>', 0)
            ->whereNotIn('ep.specialist', [
                'Laura Herrera',
                'Ana Gabriela Villarreal Medina',
                'Javier Romo',
                'Dra. Fernanda Bojorquez',
                'Itzel Rodríguez',
                'Antonio Pérez'
            ])
            // ✅ MÁS RECIENTES PRIMERO; manda "Por definir" al final
            ->orderByRaw("CASE WHEN sla.procedure_date = '2030-01-01' OR sla.procedure_date IS NULL THEN 1 ELSE 0 END ASC")
            ->orderBy('sla.procedure_date', 'desc')
            ->get();

        return view('crm.procedimientos.procedimientos', compact('procedimientos'));
    }


    public function info($id)
    {
        $proc = DB::table('enf_procedures as ep')
            ->join('sa_leads_assessment as sla', 'ep.lead_id', '=', 'sla.lead_id')
            ->where('ep.lead_id', $id)
            ->select(
                'sla.lead_id',
                DB::raw("CONCAT(sla.first_name, ' ', sla.last_name) AS name"),
                'sla.procedure_type',
                'ep.num_med_record',
                'ep.room',
                'ep.specialist',
                'ep.clinic',
                'ep.notes',
                DB::raw("DATE_FORMAT(sla.procedure_date, '%d/%m/%Y') AS procedure_date")
            )
            ->first();

        if (!$proc) {
            return response()->json(['success' => false, 'message' => 'Procedimiento no encontrado']);
        }

        return response()->json(['success' => true, 'procedimiento' => $proc]);
    }

    public function update(Request $request)
    {
        // Validar los campos editables
        $request->validate([
            'procedure_id' => 'required|integer|exists:enf_procedures,lead_id',
            'room' => 'required|string|max:50',
            'specialist' => 'required|string|max:100',
            'notes',
        ]);

        try {
            // Actualizar el procedimiento
            $updated = DB::table('enf_procedures')
                ->where('lead_id', $request->procedure_id)
                ->update([
                    'room' => $request->room,
                    'specialist' => $request->specialist,
                    'notes' => $request->notes,
                    'updated_at' => now(),
                ]);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Procedimiento actualizado correctamente!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se realizaron cambios.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ]);
        }
    }

    // FUNCIONES DE VER LAS FOTOS DE PROCEDIMIENTOS CON el desgraciado de BUNNY

    public function verFotos($num_med_record, $step = 'pre')
    {
        // Info del procedimiento y paciente
        $paciente = DB::table('enf_procedures as ep')
            ->join('sa_leads_assessment as sla', 'ep.lead_id', '=', 'sla.lead_id')
            ->select(
                DB::raw("CONCAT(sla.first_name, ' ', sla.last_name) as name"),
                'sla.procedure_date',
                'sla.procedure_type',
                'sla.enfermedades',
                'ep.clinic',
                'ep.num_med_record',
                'ep.touchup',
                'ep.room',
                'ep.specialist',
                'ep.notes',
                'ep.status',
                'ep.phase'
            )
            ->where('ep.num_med_record', $num_med_record)
            ->where('sla.status', '!=', 0)
            ->first();

        // Buscar la última fase dada de alta
        $lastAltaStep = DB::table('enf_procedures')
            ->where('num_med_record', $num_med_record)
            ->where('status', 'alta')
            ->orderByDesc('id')
            ->value('phase');

        $folderNames = [
            'pre'       => 'Pre Procedimiento',
            'diseno'    => 'Diseño',
            'post'      => 'Post Procedimiento',
            '24horas'   => '24 Horas',
            '10dias'    => '10 Días',
            '1mes'      => '1 Mes',
            '3meses'    => '3 Meses',
            '6meses'    => '6 Meses',
            '9meses'    => '9 Meses',
            '12meses'   => '12 Meses',
            '15meses'   => '15 Meses',
            '18meses'   => '18 Meses',
            '21meses'   => '21 Meses',
            'post_alta' => 'Post Alta'
        ];

        $imagenes = [];

        try {
            // Determinar storage zone según clínica
            if ($paciente->clinic === 'Queretaro') {
                $storageZone = env('BUNNY_STORAGE_ZONE_QRO');
                $apiKey = env('BUNNY_API_KEY_QRO');
            } else {
                $storageZone = env('BUNNY_STORAGE_ZONE');  // CDMX por default
                $apiKey = env('BUNNY_API_KEY');
            }

            // Bunny API: URL para listar archivos
            $folderUrl = "https://la.storage.bunnycdn.com/{$storageZone}/{$num_med_record}/{$step}/";

            $response = Http::withHeaders([
                'AccessKey' => $apiKey,
                'Accept' => 'application/json'
            ])->get($folderUrl);

            if ($response->ok()) {

                $files = $response->json();

                // 1) Separar originales y detectar si existe el folder thumb
                $originals = [];
                $hasThumbDir = false;

                foreach ($files as $f) {
                    if (!empty($f['IsDirectory']) && $f['ObjectName'] === 'thumb') {
                        $hasThumbDir = true;
                        continue;
                    }
                    if (!empty($f['IsDirectory'])) continue;

                    $originals[] = $f['ObjectName'];
                }

                // 2) Listar thumbs reales (para no asumir extensión)
                $thumbMap = []; // key: slug(baseName) => thumbObjectName real

                if ($hasThumbDir) {
                    $thumbUrl = "https://la.storage.bunnycdn.com/{$storageZone}/{$num_med_record}/{$step}/thumb/";

                    $thumbResp = Http::withHeaders([
                        'AccessKey' => $apiKey,
                        'Accept' => 'application/json'
                    ])->get($thumbUrl);

                    if ($thumbResp->ok()) {
                        foreach ($thumbResp->json() as $t) {
                            if (!empty($t['IsDirectory'])) continue;

                            $tName = $t['ObjectName'];
                            $tBase = pathinfo($tName, PATHINFO_FILENAME);
                            $key   = Str::slug($tBase) ?: ('img_' . md5($tName));

                            // Preferir jpg si hay varios con misma key
                            $ext = strtolower(pathinfo($tName, PATHINFO_EXTENSION));
                            if (!isset($thumbMap[$key]) || $ext === 'jpg' || $ext === 'jpeg') {
                                $thumbMap[$key] = $tName;
                            }
                        }
                    }
                }

                // 3) Armar respuesta final
                foreach ($originals as $name) {

                    $original = "https://{$storageZone}.b-cdn.net/{$num_med_record}/{$step}/" . rawurlencode($name);

                    $baseName = pathinfo($name, PATHINFO_FILENAME);
                    $key      = Str::slug($baseName) ?: ('img_' . md5($name));

                    // a) Intento 1: thumb real existente (cualquier extensión)
                    if (isset($thumbMap[$key])) {
                        $thumbName = $thumbMap[$key];
                    } else {
                        // b) Fallback: lo que hoy estás generando (jpg)
                        $thumbName = $key . '.jpg';
                    }

                    $thumb = "https://{$storageZone}.b-cdn.net/{$num_med_record}/{$step}/thumb/" . rawurlencode($thumbName);

                    $imagenes[] = [
                        'name'  => $name,
                        'url'   => $original,
                        'thumb' => $thumb,
                    ];
                }
            }
        } catch (\Exception $e) {
            session()->flash('error', 'No se pudieron cargar las fotos, pos que hiciste!?: ' . $e->getMessage());
        }

        return view('crm.procedimientos.opciones.verFotos', compact('num_med_record', 'step', 'imagenes', 'paciente', 'folderNames', 'lastAltaStep'));
    }


    public function subirFoto(Request $request, $num_med_record, $step = 'pre')
    {
        $clinica = $request->input('clinica');

        if (!$clinica) return back()->with('error', 'No se especificó la clínica.');

        // ✅ ahora esperamos arreglo
        $files = $request->file('foto');

        // Si viene single por algún motivo, lo normalizamos a array
        if ($files && !is_array($files)) {
            $files = [$files];
        }

        if (!$files || count($files) === 0) {
            return back()->with('error', 'No se ha seleccionado un archivo válido.');
        }

        // Storage según clínica
        if ($clinica === 'Queretaro') {
            $storageZone = env('BUNNY_STORAGE_ZONE_QRO');
            $apiKey = env('BUNNY_API_KEY_QRO');
        } else {
            $storageZone = env('BUNNY_STORAGE_ZONE');
            $apiKey = env('BUNNY_API_KEY');
        }

        $subidas = 0;

        foreach ($files as $file) {
            if (!$file || !$file->isValid()) continue;

            $origExt  = strtolower($file->getClientOriginalExtension());
            $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeBase = Str::slug($origName);

            if ($safeBase === '') {
                // si un archivo viene mal, no tiramos todo
                continue;
            }

            // Si es HEIC/HEIF → convertir a JPG para poder generar thumb
            $realPathToRead = $file->getRealPath();
            $uploadMime = $file->getMimeType();

            $isHeic = in_array($origExt, ['heic', 'heif'])
                || str_contains((string)$uploadMime, 'heic')
                || str_contains((string)$uploadMime, 'heif');

            $convertedPath = null;

            if ($isHeic) {
                $convertedPath = sys_get_temp_dir() . "/{$safeBase}_" . time() . "_" . uniqid() . ".jpg";

                if (!extension_loaded('imagick')) {
                    // si no hay imagick, saltamos esta foto (no tiramos todo)
                    continue;
                }

                try {
                    $im = new \Imagick();
                    $im->readImage($file->getRealPath());
                    $im->setImageFormat('jpeg');
                    $im->setImageCompressionQuality(85);
                    $im->writeImage($convertedPath);
                    $im->clear();
                    $im->destroy();

                    $realPathToRead = $convertedPath;
                    $uploadMime = 'image/jpeg';
                } catch (\Throwable $e) {
                    // saltar esta foto
                    continue;
                }
            }

            // ------------------------------
            // SUBIR ORIGINAL
            // ------------------------------
            $fileName = $file->getClientOriginalName();
            $fileNameEncoded = rawurlencode($fileName);

            $pathOriginal = "{$num_med_record}/{$step}/{$fileNameEncoded}";
            $urlOriginal  = "https://la.storage.bunnycdn.com/{$storageZone}/{$pathOriginal}";

            try {
                Http::withHeaders([
                    'AccessKey'    => $apiKey,
                    'Content-Type' => $file->getMimeType(),
                ])->send('PUT', $urlOriginal, [
                    'body' => file_get_contents($file->getRealPath())
                ]);
            } catch (\Throwable $e) {
                // falló esta foto, seguimos con las demás
                if ($convertedPath && file_exists($convertedPath)) @unlink($convertedPath);
                continue;
            }

            // ------------------------------
            // CREAR THUMB
            // ------------------------------
            try {
                $thumbTempPath = sys_get_temp_dir() . "/thumb_{$safeBase}_" . time() . "_" . uniqid() . ".jpg";

                $driver = extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
                $manager = new ImageManager($driver);

                $image = $manager->read($realPathToRead);
                $image = $image->scale(width: 350);
                $image->toJpeg(80)->save($thumbTempPath);

                if (!file_exists($thumbTempPath)) throw new \Exception("Thumb NO generado");
            } catch (\Throwable $e) {
                if ($convertedPath && file_exists($convertedPath)) @unlink($convertedPath);
                continue;
            }

            // ------------------------------
            // SUBIR THUMB
            // ------------------------------
            $thumbName = $safeBase . ".jpg";
            $thumbNameEncoded = rawurlencode($thumbName);

            $pathThumb = "{$num_med_record}/{$step}/thumb/{$thumbNameEncoded}";
            $urlThumb  = "https://la.storage.bunnycdn.com/{$storageZone}/{$pathThumb}";

            try {
                Http::withHeaders([
                    'AccessKey'    => $apiKey,
                    'Content-Type' => 'image/jpeg',
                ])->send('PUT', $urlThumb, [
                    'body' => file_get_contents($thumbTempPath)
                ]);
            } catch (\Throwable $e) {
                if ($convertedPath && file_exists($convertedPath)) @unlink($convertedPath);
                continue;
            }

            // limpieza temp
            if (isset($thumbTempPath) && file_exists($thumbTempPath)) @unlink($thumbTempPath);
            if ($convertedPath && file_exists($convertedPath)) @unlink($convertedPath);

            $subidas++;
        }

        return redirect()
            ->route('panel.procedimientos.fotos', ['num_med_record' => $num_med_record, 'step' => $step])
            ->with(
                $subidas > 0 ? 'success' : 'error',
                $subidas > 0
                    ? "Se subieron {$subidas} foto(s) correctamente."
                    : "No se pudo subir ninguna foto (revisa formato/HEIC/Imagick)."
            );
    }


    // Eliminar foto y su thumb de Bunny
    public function eliminarFoto(Request $request)
    {
        $url = $request->input('url');

        if (!$url) {
            return redirect()->back()->with('error', 'No se especificó la URL del archivo.');
        }

        try {
            // Detectar si la foto pertenece a CDMX o QRO
            if (str_contains($url, env('BUNNY_STORAGE_ZONE_QRO'))) {
                $storageZone = env('BUNNY_STORAGE_ZONE_QRO');
                $apiKey = env('BUNNY_API_KEY_QRO');
            } else {
                $storageZone = env('BUNNY_STORAGE_ZONE');
                $apiKey = env('BUNNY_API_KEY');
            }

            // Extraer ruta del dominio CDN
            $path = str_replace("https://{$storageZone}.b-cdn.net/", '', $url);

            //  URL del archivo en Bunny
            $apiUrlOriginal = "https://la.storage.bunnycdn.com/{$storageZone}/{$path}";

            //  thumb: insertar /thumb/ antes del nombre del archivo
            $thumbPath = preg_replace('/([^\/]+)$/', 'thumb/$1', $path);
            $apiUrlThumb = "https://la.storage.bunnycdn.com/{$storageZone}/{$thumbPath}";

            // --- Eliminar archivo original ---
            $deleteOriginal = Http::withHeaders([
                'AccessKey' => $apiKey,
            ])->delete($apiUrlOriginal);

            // --- Eliminar thumb ---
            $deleteThumb = Http::withHeaders([
                'AccessKey' => $apiKey,
            ])->delete($apiUrlThumb);

            if ($deleteOriginal->successful() || $deleteThumb->successful()) {
                return redirect()->back()->with('success', 'Foto y thumb eliminados correctamente.');
            }

            return redirect()->back()->with('error', 'No se pudieron eliminar los archivos. Código: ' . $deleteOriginal->status());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar la foto: ' . $e->getMessage());
        }
    }
    //Final de funciones de fotos primer procedimiento


    //Prueba de api bunny NO BORRAR
    public function testBunny($num_med_record, $step = 'pre')
    {
        //$apiKey = env('BUNNY_API_KEY');
        $storageZone = env('BUNNY_STORAGE_ZONE');
        $folderUrl = "https://la.storage.bunnycdn.com/{$storageZone}/{$num_med_record}/{$step}/";

        try {
            $response = Http::withHeaders([
                'AccessKey' => env('BUNNY_API_KEY'),
                'Accept' => 'application/json',
            ])->get($folderUrl);

            if ($response->status() != 200) {
                return response()->json([
                    'success' => false,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

            $files = $response->json();

            $imagenes = [];
            if (!empty($files)) {
                foreach ($files as $file) {
                    $imagenes[] = [
                        'name' => $file['ObjectName'],
                        'url' => "https://{$storageZone}.b-cdn.net/{$num_med_record}/{$step}/{$file['ObjectName']}"
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'imagenes' => $imagenes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }


    // FUNCIONES DE LAS NOTAS MEDICAS

    public function addNote(Request $request)
    {
        try {
            $request->validate([
                'num_med_record' => 'required|integer',
                'phase' => 'required|string',
                'author' => 'required|string',
                'note' => 'required|string',
                'date' => 'required|date',
                'clinic' => 'required|string',
                'procedure_type' => 'required|string',
                'status_conformidad' => 'required|integer|min:1|max:5',
                'audio_file' => 'nullable|file|mimes:webm,mp3,wav,ogg|max:20480',
            ]);

            $audio_url = null;

            // Guardar audio si existe
            if ($request->hasFile('audio_file')) {
                $folder = 'audio/audio_notes/' . $request->num_med_record . '/' . $request->phase;

                // Crea la carpeta si no existe
                $destinationPath = public_path($folder);
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                // Nombre único para evitar colisiones
                $fileName = uniqid() . '.' . $request->file('audio_file')->getClientOriginalExtension();

                // Mueve el archivo
                $request->file('audio_file')->move($destinationPath, $fileName);

                // Guarda la ruta pública
                $audio_url = $folder . '/' . $fileName;
            }


            DB::table('medical_notes')->insert([
                'num_med' => $request->num_med_record,
                'phase' => $request->phase,
                'note' => $request->note,
                'date' => $request->date,
                'author' => $request->author,
                'procedure_type' => $request->procedure_type,
                'clinic' => $request->clinic,
                'status_conformidad' => $request->status_conformidad,
                'audio_url' => $audio_url,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Nota guardada correctamente.']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function deleteNote($id)
    {
        try {
            $note = DB::table('medical_notes')->where('id', $id)->first();
            if (!$note) {
                return response()->json(['status' => 'error', 'message' => 'Nota no encontrada.']);
            }

            // Eliminar el archivo si existe
            if (!empty($note->audio_url) && Storage::exists(str_replace('storage/', 'public/', $note->audio_url))) {
                Storage::delete(str_replace('storage/', 'public/', $note->audio_url));
            }

            DB::table('medical_notes')->where('id', $id)->delete();

            return response()->json(['status' => 'success', 'message' => 'Nota eliminada correctamente.']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function getNotes(Request $request)
    {
        $request->validate([
            'num_med_record' => 'required|integer',
            'phase' => 'nullable|string',
            'clinic' => 'nullable|string',
            'procedure_type' => 'nullable|string',
        ]);

        $notes = DB::table('medical_notes as m')
            ->leftJoin('users as u', 'u.id', '=', 'm.author')
            ->select(
                'm.id',
                'm.num_med',
                'm.phase',
                'm.note',
                'm.date',
                'm.procedure_type',
                DB::raw("IFNULL(u.name, 'Sin autor') AS author_name"),
                'm.audio_url',
                'm.status_conformidad'
            )
            ->when($request->phase, fn($q) => $q->where('m.phase', $request->phase))
            ->when($request->clinic, fn($q) => $q->where('m.clinic', $request->clinic))
            ->when($request->procedure_type, fn($q) => $q->where('m.procedure_type', $request->procedure_type))
            ->where('m.num_med', $request->num_med_record)
            ->orderByDesc('m.date')
            ->get()
            ->map(function ($note) {
                $note->date = Carbon::parse($note->date)->translatedFormat('d M Y');
                $note->conformidad_texto = match ((int)$note->status_conformidad) {
                    1 => 'Muy insatisfecho',
                    2 => 'Insatisfecho',
                    3 => 'Neutral',
                    4 => 'Satisfecho',
                    5 => 'Muy satisfecho',
                    default => 'Sin evaluar'
                };
                return $note;
            });

        return response()->json(['status' => 'success', 'data' => $notes]);
    }

    // logica paara dar de alta carajo
    public function actualizarAlta(Request $request)
    {
        try {
            $num_med_record = $request->num_med_record;
            $phase = $request->phase;

            // Actualizamos el último procedimiento del expediente
            DB::table('enf_procedures')
                ->where('num_med_record', $num_med_record)
                ->orderByDesc('id')
                ->limit(1)
                ->update([
                    'status' => 'alta',
                    'phase' => $phase,
                ]);


            //dd($num_med_record);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // funciones del segundo procedimiento ↓

    public function verFotosTouchup($num_med_record, $step = 'pre')
    {
        // Info del paciente
        $paciente = DB::table('enf_procedures as ep')
            ->join('sa_leads_assessment as sla', 'ep.lead_id', '=', 'sla.lead_id')
            ->select(
                DB::raw("CONCAT(sla.first_name, ' ', sla.last_name) as name"),
                'sla.procedure_date',
                'sla.procedure_type',
                'sla.enfermedades',
                'ep.clinic',
                'ep.num_med_record',
                'ep.touchup',
                'ep.room',
                'ep.specialist',
                'ep.notes',
                'ep.status',
                'ep.phase'
            )
            ->where('ep.num_med_record', $num_med_record)
            ->where('sla.status', '!=', 0)
            ->first();

        if (!$paciente) {
            return back()->with('error', 'Paciente no encontrado.');
        }

        // ultima fase del retoque dada de alta
        $lastAltaStep = DB::table('enf_procedures')
            ->where('num_med_record', $num_med_record)
            ->where('touchup', '1')
            ->where('status', 'alta')
            ->orderByDesc('id')
            ->value('phase');

        // Determinar storage
        $isQro = strtolower($paciente->clinic) === 'Queretaro';
        $apiKey = $isQro ? env('BUNNY_API_KEY_QRO') : env('BUNNY_API_KEY');
        $storageZone = $isQro ? env('BUNNY_STORAGE_ZONE_QRO') : env('BUNNY_STORAGE_ZONE');

        $folderPath = "{$num_med_record}/retoque/{$step}/";
        $folderUrl = "https://la.storage.bunnycdn.com/{$storageZone}/{$folderPath}";

        $imagenes = [];

        try {
            // Obtener archivos
            $response = Http::withHeaders([
                'AccessKey' => $apiKey,
                'Accept' => 'application/json'
            ])->get($folderUrl);

            if ($response->ok()) {
                $files = $response->json();

                foreach ($files as $file) {
                    if ($file['IsDirectory'] && $file['ObjectName'] === 'thumb') {
                        continue;
                    }

                    if ($file['IsDirectory']) continue;

                    $nombre = $file['ObjectName'];

                    $nombre = $file['ObjectName'];

                    $original = "https://{$storageZone}.b-cdn.net/{$folderPath}" . rawurlencode($nombre);

                    // thumb SIEMPRE es .jpg con slug del filename (como en el primero)
                    $baseName  = pathinfo($nombre, PATHINFO_FILENAME);
                    $thumbName = Str::slug($baseName) . '.jpg';

                    $thumb = "https://{$storageZone}.b-cdn.net/{$folderPath}thumb/" . rawurlencode($thumbName);


                    $imagenes[] = [
                        'name' => $nombre,
                        'url'  => $original,
                        'thumb' => $thumb
                    ];
                }
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar imágenes: ' . $e->getMessage());
        }

        $folderNames = [
            'pre' => 'Pre Procedimiento',
            'diseno' => 'Diseño',
            'post' => 'Post Procedimiento',
            '24horas' => '24 Horas',
            '10dias' => '10 Días',
            '1mes' => '1 Mes',
            '3meses' => '3 Meses',
            '6meses' => '6 Meses',
            '9meses' => '9 Meses',
            '12meses' => '12 Meses',
            '15meses' => '15 Meses',
            '18meses' => '18 Meses',
            '21meses' => '21 Meses',
            'post_alta' => 'Post Alta',
        ];

        return view('crm.procedimientos.opciones.verFotosSegundo', compact(
            'num_med_record',
            'step',
            'imagenes',
            'paciente',
            'folderNames',
            'lastAltaStep'
        ));
    }


    // Subiir fotos en segundo procedimiento (Prototipo para usar en primer y segundo procedimiento)

    public function subirFotoSegundo(Request $request, $num_med_record, $step = 'pre')
    {
        $files = $request->file('foto'); // viene de foto[]
        if (!$files) {
            return back()->with('error', 'No se seleccionaron archivos.');
        }

        // ✅ normaliza a array
        if (!is_array($files)) $files = [$files];

        // Detectar clínica
        $paciente = DB::table('enf_procedures')
            ->where('num_med_record', $num_med_record)
            ->select('clinic')
            ->first();

        if (!$paciente) {
            return back()->with('error', 'No se encontró el paciente.');
        }

        $clinicLower = strtolower(trim((string)$paciente->clinic));
        $isQro = in_array($clinicLower, ['Queretaro', 'querétaro', 'qro'], true);

        $apiKey = $isQro ? env('BUNNY_API_KEY_QRO') : env('BUNNY_API_KEY');
        $storageZone = $isQro ? env('BUNNY_STORAGE_ZONE_QRO') : env('BUNNY_STORAGE_ZONE');

        // Carpeta touchup
        $folderPath = "{$num_med_record}/retoque/{$step}/";

        $errores = [];

        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                $errores[] = 'Archivo inválido detectado.';
                continue;
            }

            // Nombres
            $origExt  = strtolower($file->getClientOriginalExtension());
            $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeBase = Str::slug($origName);

            if ($safeBase === '') {
                $errores[] = "Nombre inválido: {$file->getClientOriginalName()}";
                continue;
            }

            $uploadMime = (string) $file->getMimeType();
            $isHeic = in_array($origExt, ['heic', 'heif'], true)
                || str_contains($uploadMime, 'heic')
                || str_contains($uploadMime, 'heif');

            // =========================
            // 1) SUBIR ORIGINAL
            // =========================
            $fileName = $file->getClientOriginalName();
            $fileNameEncoded = rawurlencode($fileName);

            $pathOriginal = "{$folderPath}{$fileNameEncoded}";
            $urlOriginal  = "https://la.storage.bunnycdn.com/{$storageZone}/{$pathOriginal}";

            try {
                Http::withHeaders([
                    'AccessKey'    => $apiKey,
                    'Content-Type' => $file->getMimeType(),
                ])->send('PUT', $urlOriginal, [
                    'body' => file_get_contents($file->getRealPath())
                ]);
            } catch (\Throwable $e) {
                $errores[] = "Error subiendo original ({$fileName}): " . $e->getMessage();
                continue;
            }

            // =========================
            // 2) PREPARAR PARA THUMB
            // =========================
            $realPathToRead = $file->getRealPath();

            if ($isHeic) {
                if (!extension_loaded('imagick')) {
                    $errores[] = "HEIC requiere Imagick ({$fileName}).";
                    continue;
                }

                $convertedPath = sys_get_temp_dir() . "/{$safeBase}_" . time() . ".jpg";

                try {
                    $im = new \Imagick();
                    $im->readImage($file->getRealPath());
                    $im->setImageFormat('jpeg');
                    $im->setImageCompressionQuality(85);
                    $im->writeImage($convertedPath);
                    $im->clear();
                    $im->destroy();

                    $realPathToRead = $convertedPath;
                } catch (\Throwable $e) {
                    $errores[] = "No se pudo convertir HEIC, si leiste el manual? ({$fileName}): " . $e->getMessage();
                    continue;
                }
            }

            // =========================
            // 3) GENERAR THUMB
            // =========================
            try {
                $thumbTempPath = sys_get_temp_dir() . "/thumb_{$safeBase}_" . time() . ".jpg";

                $driver  = extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
                $manager = new ImageManager($driver);

                $image = $manager->read($realPathToRead);
                $image = $image->scale(width: 350);
                $image->toJpeg(80)->save($thumbTempPath);

                if (!file_exists($thumbTempPath)) throw new \Exception("Thumb no generado");
            } catch (\Throwable $e) {
                $errores[] = "Error thumb ({$fileName}): " . $e->getMessage();
                continue;
            }

            // =========================
            // 4) SUBIR THUMB (slug.jpg)
            // =========================
            $thumbName = $safeBase . ".jpg";
            $thumbNameEncoded = rawurlencode($thumbName);

            $pathThumb = "{$folderPath}thumb/{$thumbNameEncoded}";
            $urlThumb  = "https://la.storage.bunnycdn.com/{$storageZone}/{$pathThumb}";

            try {
                Http::withHeaders([
                    'AccessKey'    => $apiKey,
                    'Content-Type' => 'image/jpeg',
                ])->send('PUT', $urlThumb, [
                    'body' => file_get_contents($thumbTempPath)
                ]);
            } catch (\Throwable $e) {
                $errores[] = "Falló thumb ({$fileName}): " . $e->getMessage();
                continue;
            }
        }

        if (!empty($errores)) {
            // Mensajes de error
            return redirect()
                ->route('panel.procedimientos.fotos.touchup', ['num_med_record' => $num_med_record, 'step' => $step])
                ->with('error', "Se subieron algunas, pero hubo errores:\n" . implode("\n", $errores));
        }

        return redirect()
            ->route('panel.procedimientos.fotos.touchup', ['num_med_record' => $num_med_record, 'step' => $step])
            ->with('success', 'Fotos y thumbnails subidos correctamente.');
    }


    // El meetodo para eliminar fotos en segundo procedimiento sería similar al anterior
    public function eliminarFotoSegundo(Request $request)
    {
        $url = $request->input('url');

        if (!$url) {
            return back()->with('error', 'No se especificó la URL del archivo.');
        }

        // Detectar storage zone
        if (str_contains($url, env('BUNNY_STORAGE_ZONE_QRO'))) {
            $storageZone = env('BUNNY_STORAGE_ZONE_QRO');
            $apiKey = env('BUNNY_API_KEY_QRO');
        } else {
            $storageZone = env('BUNNY_STORAGE_ZONE');
            $apiKey = env('BUNNY_API_KEY');
        }

        try {
            // 1) Extraer ruta interna desde CDN
            $path = str_replace("https://{$storageZone}.b-cdn.net/", '', $url);

            // 2) URL API para borrar original
            $apiUrlOriginal = "https://la.storage.bunnycdn.com/{$storageZone}/{$path}";

            // 3) Calcular thumb correcto
            //    path ejemplo: 324/retoque/post_alta/IMG_9661.HEIC
            $fileName = basename($path); // IMG_9661.HEIC
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $baseName = pathinfo($fileName, PATHINFO_FILENAME);

            // Si es HEIC/HEIF -> thumb = slug(base).jpg
            if (in_array($ext, ['heic', 'heif'])) {
                $thumbFile = Str::slug($baseName) . '.jpg';
            } else {
                // para jpg/png/etc -> conserva nombre (como tu lógica de listado)
                $thumbFile = $fileName;
            }

            // Reemplazar el filename final por thumb/<thumbFile>
            $thumbPath = preg_replace('/[^\/]+$/', 'thumb/' . $thumbFile, $path);
            $apiUrlThumb = "https://la.storage.bunnycdn.com/{$storageZone}/{$thumbPath}";

            // 4) Borrar original
            Http::withHeaders(['AccessKey' => $apiKey])->delete($apiUrlOriginal);

            // 5) Borrar thumb (si no existe, Bunny puede responder 404; lo ignoramos)
            Http::withHeaders(['AccessKey' => $apiKey])->delete($apiUrlThumb);

            return back()->with('success', 'Foto y thumb eliminados correctamente.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
    // Fin funciones segundo procedimiento

    //inician funciones de micro
    public function verFotosMicro($num_med_record, $step = 'pre')
    {
        $paciente = DB::table('enf_procedures as ep')
            ->join('sa_leads_assessment as sla', 'ep.lead_id', '=', 'sla.lead_id')
            ->select(
                DB::raw("CONCAT(sla.first_name, ' ', sla.last_name) as name"),
                'sla.procedure_date',
                'sla.procedure_type',
                'sla.enfermedades',
                'ep.clinic',
                'ep.num_med_record',
                'ep.room',
                'ep.specialist',
                'ep.notes',
                'ep.status',
                'ep.phase'
            )
            ->where('ep.num_med_record', $num_med_record)
            ->where('sla.status', '!=', 0)
            ->first();

        $lastAltaStep = DB::table('enf_procedures')
            ->where('num_med_record', $num_med_record)
            ->where('touchup', '1')
            ->where('status', 'alta')
            ->orderByDesc('id')
            ->value('phase');

        if (!$paciente) return back()->with('error', 'Paciente no encontrado.');

        $isQro = strtolower($paciente->clinic) === 'Queretaro';
        $apiKey = $isQro ? env('BUNNY_API_KEY_QRO') : env('BUNNY_API_KEY');
        $storageZone = $isQro ? env('BUNNY_STORAGE_ZONE_QRO') : env('BUNNY_STORAGE_ZONE');

        $folderPath = "{$num_med_record}/micro/{$step}/";
        $folderUrl  = "https://la.storage.bunnycdn.com/{$storageZone}/{$folderPath}";

        $imagenes = [];

        try {
            $response = Http::withHeaders([
                'AccessKey' => $apiKey,
                'Accept' => 'application/json'
            ])->get($folderUrl);

            if ($response->ok()) {
                foreach ($response->json() as $file) {
                    if ($file['IsDirectory'] && $file['ObjectName'] === 'thumb') continue;
                    if ($file['IsDirectory']) continue;

                    $name = $file['ObjectName'];

                    $original = "https://{$storageZone}.b-cdn.net/{$folderPath}" . rawurlencode($name);

                    $baseName  = pathinfo($name, PATHINFO_FILENAME);
                    $thumbName = Str::slug($baseName) . '.jpg';

                    $thumb = "https://{$storageZone}.b-cdn.net/{$folderPath}thumb/" . rawurlencode($thumbName);

                    $imagenes[] = [
                        'name'  => $name,
                        'url'   => $original,
                        'thumb' => $thumb,
                    ];
                }
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar imágenes: ' . $e->getMessage());
        }

        $folderNames = [
            'pre' => 'Pre Procedimiento',
            'diseno' => 'Diseño',
            'post' => 'Post Procedimiento',
            '24horas' => '24 Horas',
            '10dias' => '10 Días',
            '1mes' => '1 Mes',
            '3meses' => '3 Meses',
            '6meses' => '6 Meses',
            '9meses' => '9 Meses',
            '12meses' => '12 Meses',
            '15meses' => '15 Meses',
            '18meses' => '18 Meses',
            '21meses' => '21 Meses',
            'post_alta' => 'Post Alta',
        ];

        return view('crm.procedimientos.opciones.verFotosMicro', compact(
            'num_med_record',
            'step',
            'imagenes',
            'paciente',
            'folderNames',
            'lastAltaStep'
        ));
    }


    public function subirFotoMicro(Request $request, $num_med_record, $step = 'pre')
    {
        $files = $request->file('foto');

        if (!$files) {
            return back()->with('error', 'No se ha seleccionado un archivo válido.');
        }

        $files = is_array($files) ? $files : [$files];

        $paciente = DB::table('enf_procedures')
            ->where('num_med_record', $num_med_record)
            ->select('clinic')
            ->first();

        if (!$paciente) return back()->with('error', 'No se encontró el paciente.');

        $clinicLower = strtolower(trim($paciente->clinic ?? ''));
        $isQro = in_array($clinicLower, ['queretaro', 'querétaro', 'qro'], true);

        $apiKey = $isQro ? env('BUNNY_API_KEY_QRO') : env('BUNNY_API_KEY');
        $storageZone = $isQro ? env('BUNNY_STORAGE_ZONE_QRO') : env('BUNNY_STORAGE_ZONE');

        foreach ($files as $file) {

            if (!$file instanceof \Illuminate\Http\UploadedFile || !$file->isValid()) {
                return back()->with('error', 'Uno o más archivos no son válidos? No me digas HEIC otra vez...');
            }

            $origExt  = strtolower($file->getClientOriginalExtension());
            $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeBase = Str::slug($origName) ?: ('img_' . time() . '_' . uniqid());

            $realPathToRead = $file->getRealPath();
            $uploadMime = $file->getMimeType();

            $isHeic = in_array($origExt, ['heic', 'heif'])
                || str_contains((string)$uploadMime, 'heic')
                || str_contains((string)$uploadMime, 'heif');

            // Convertir HEIC/HEIF a JPG SOLO para generar thumb
            if ($isHeic) {
                if (!extension_loaded('imagick')) {
                    return back()->with('error', 'El servidor no tiene Imagick (necesario para HEIC).');
                }

                $convertedPath = sys_get_temp_dir() . "/{$safeBase}_" . time() . ".jpg";

                try {
                    $im = new \Imagick();
                    $im->readImage($file->getRealPath());
                    $im->setImageFormat('jpeg');
                    $im->setImageCompressionQuality(85);
                    $im->writeImage($convertedPath);
                    $im->clear();
                    $im->destroy();

                    $realPathToRead = $convertedPath;
                    $uploadMime = 'image/jpeg';
                } catch (\Throwable $e) {
                    return back()->with('error', 'No se pudo convertir HEIC a JPG: ' . $e->getMessage());
                }
            }

            // ===== SUBIR ORIGINAL (se sube el archivo original tal cual) =====
            $fileName = $file->getClientOriginalName();
            $fileNameEncoded = rawurlencode($fileName);

            $pathOriginal = "{$num_med_record}/micro/{$step}/{$fileNameEncoded}";
            $urlOriginal  = "https://la.storage.bunnycdn.com/{$storageZone}/{$pathOriginal}";

            try {
                Http::withHeaders([
                    'AccessKey'    => $apiKey,
                    'Content-Type' => $file->getMimeType(),
                ])->send('PUT', $urlOriginal, [
                    'body' => file_get_contents($file->getRealPath())
                ]);
            } catch (\Throwable $e) {
                return back()->with('error', 'Error al subir original: ' . $e->getMessage());
            }

            // ===== GENERAR THUMB =====
            try {
                $thumbTempPath = sys_get_temp_dir() . "/thumb_{$safeBase}_" . time() . ".jpg";

                $driver  = extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
                $manager = new ImageManager($driver);

                $image = $manager->read($realPathToRead);
                $image = $image->scale(width: 350);
                $image->toJpeg(80)->save($thumbTempPath);

                if (!file_exists($thumbTempPath)) throw new \Exception("Thumb NO generado");
            } catch (\Throwable $e) {
                return back()->with('error', 'Original se subió, pero falló el thumb: ' . $e->getMessage());
            }

            // ===== SUBIR THUMB (SIEMPRE JPG) =====
            $thumbName = $safeBase . ".jpg";
            $pathThumb = "{$num_med_record}/micro/{$step}/thumb/" . rawurlencode($thumbName);
            $urlThumb  = "https://la.storage.bunnycdn.com/{$storageZone}/{$pathThumb}";

            try {
                Http::withHeaders([
                    'AccessKey'    => $apiKey,
                    'Content-Type' => 'image/jpeg',
                ])->send('PUT', $urlThumb, [
                    'body' => file_get_contents($thumbTempPath)
                ]);
            } catch (\Throwable $e) {
                return back()->with('error', 'Original se subió, pero falló el thumb: ' . $e->getMessage());
            }
        }

        return redirect()
            ->route('panel.procedimientos.fotos.micro', ['num_med_record' => $num_med_record, 'step' => $step])
            ->with('success', 'Foto(s) y thumbnail(s) subidos correctamente.');
    }



    public function eliminarFotoMicro(Request $request)
    {
        $url = $request->input('url');
        if (!$url) return back()->with('error', 'No se especificó la URL del archivo.');

        if (str_contains($url, env('BUNNY_STORAGE_ZONE_QRO'))) {
            $storageZone = env('BUNNY_STORAGE_ZONE_QRO');
            $apiKey = env('BUNNY_API_KEY_QRO');
        } else {
            $storageZone = env('BUNNY_STORAGE_ZONE');
            $apiKey = env('BUNNY_API_KEY');
        }

        try {
            $path = str_replace("https://{$storageZone}.b-cdn.net/", '', $url);

            // borrar original
            Http::withHeaders(['AccessKey' => $apiKey])
                ->delete("https://la.storage.bunnycdn.com/{$storageZone}/{$path}");

            // calcular thumb correcto
            $fileName = basename($path);
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $baseName = pathinfo($fileName, PATHINFO_FILENAME);

            if (in_array($ext, ['heic', 'heif'])) {
                $thumbFile = Str::slug($baseName) . '.jpg';
            } else {
                $thumbFile = $fileName;
            }

            $thumbPath = preg_replace('/[^\/]+$/', 'thumb/' . $thumbFile, $path);

            Http::withHeaders(['AccessKey' => $apiKey])
                ->delete("https://la.storage.bunnycdn.com/{$storageZone}/{$thumbPath}");

            return back()->with('success', 'Foto y thumb eliminados correctamente.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
    // Fin funciones de micro


    /*-------------------------------------
        ------- Funciones de Tratamientos------
        --------------------------------------*/


    public function guardarTreatment(Request $request)
    {
        $request->validate([
            'num_med_record' => 'required',
            'date' => 'required|date',
            'clinic' => 'required',
            'doctor' => 'required',
            'type' => 'required',
            'notes' => 'nullable|string',
            'created_by' => 'required'
        ]);

        DB::table('enf_treatments_appointments')->insert([
            'num_med_record' => $request->num_med_record,
            'date' => $request->date,
            'clinic' => $request->clinic,
            'doctor' => $request->doctor,
            'type' => $request->type,
            'notes' => $request->notes,
            'created_by' => $request->created_by,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['status' => 'success']);
    }

    //Guardar nota de tratamiento
    public function guardarNotaTratamiento(Request $request)
    {
        try {
            $identifierType = $request->input('identifier_type');

            // Si es sin expediente (id) usamos identifier directo
            if ($identifierType === 'id') {
                DB::table('enf_treatments_notes')->insert([
                    'identifier'       => $request->input('identifier'),
                    'identifier_type'  => 'id',
                    'clinic'           => $request->input('clinic'), // puede ser null
                    'phase'            => $request->input('phase'),
                    'note'             => $request->input('note'),
                    'date'             => $request->input('date'),
                    'author'           => $request->input('author'),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            } else {
                // Con expediente
                DB::table('enf_treatments_notes')->insert([
                    'identifier'       => $request->input('num_med_record'),
                    'identifier_type'  => 'exp',
                    'clinic'           => $request->input('clinic'),
                    'phase'            => $request->input('phase'),
                    'note'             => $request->input('note'),
                    'date'             => $request->input('date'),
                    'author'           => $request->input('author'),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Nota guardada correctamente.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //Eliminar nota de tratamiento
    public function eliminarNotaTratamiento($id)
    {
        $nota = DB::table('enf_treatments_notes')->where('id', $id)->first();

        if (!$nota) {
            return response()->json(['status' => 'error', 'message' => 'Nota no encontrada']);
        }

        DB::table('enf_treatments_notes')->where('id', $id)->delete();

        return response()->json(['status' => 'success']);
    }


    //Mostrar las fotos y notas de tratamiento
    public function verFotosTreatment($num_med_record, $treatmentId, $trat = null)
    {
        //Variable para el boton de regresar
        $trat = $trat;
        // Datos del paciente
        $paciente = DB::table('enf_procedures as ep')
            ->join('sa_leads_assessment as sla', 'ep.lead_id', '=', 'sla.lead_id')
            ->select(
                DB::raw("CONCAT(sla.first_name, ' ', sla.last_name) as name"),
                'sla.procedure_date',
                'sla.procedure_type',
                'sla.enfermedades',
                'ep.clinic',
                'ep.num_med_record',
                'ep.touchup',
                'ep.room',
                'ep.specialist',
                'ep.notes',
                'ep.status'
            )
            ->where('ep.num_med_record', $num_med_record)
            ->where('sla.status', '!=', 0)
            ->first();

        if (!$paciente) return back()->with('error', 'Paciente no encontrado.');

        $clinicLower = strtolower($paciente->clinic);
        $isQro = $clinicLower === 'Queretaro';

        $apiKey = $isQro ? env('BUNNY_API_KEY_QRO') : env('BUNNY_API_KEY');
        $storageZone = $isQro ? env('BUNNY_STORAGE_ZONE_QRO') : env('BUNNY_STORAGE_ZONE');

        // Obtener lista de tratamientos
        $tratamientos = DB::table('enf_treatments_appointments')
            ->where('num_med_record', $num_med_record)
            ->get();

        // Si no se selecciona treatment -> tomar primero
        if (empty($treatmentId) && $tratamientos->count() > 0) {
            $treatmentId = $tratamientos->first()->id;
        }

        // Obtener fotos
        $imagenes = $this->obtenerFotosTreatment($num_med_record, $treatmentId, $storageZone, $apiKey);

        return view('crm.procedimientos.opciones.VerFotosTratamiento', compact(
            'num_med_record',
            'treatmentId',
            'imagenes',
            'paciente',
            'tratamientos',
            'trat',
        )); //dd($nota);
    }

    public function subirFotoTreatment(Request $request, $num_med_record, $treatmentId)
    {
        $file = $request->file('foto');
        if (!$file || !$file->isValid()) {
            return response()->json(['status' => 'error', 'message' => 'Archivo inválido.'], 422);
        }

        $paciente = DB::table('enf_procedures')->where('num_med_record', $num_med_record)->first();
        if (!$paciente) {
            return response()->json(['status' => 'error', 'message' => 'Paciente no encontrado.'], 404);
        }

        $isQro = strtolower($paciente->clinic) === 'queretaro';
        $apiKey = $isQro ? env('BUNNY_API_KEY_QRO') : env('BUNNY_API_KEY');
        $storageZone = $isQro ? env('BUNNY_STORAGE_ZONE_QRO') : env('BUNNY_STORAGE_ZONE');

        // ===== nombres seguros =====
        $origExt  = strtolower($file->getClientOriginalExtension());
        $origBase = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeBase = Str::slug($origBase) ?: ('img_' . time());

        // Para leer y generar thumb
        $realPathToRead = $file->getRealPath();
        $mime = (string) $file->getMimeType();

        $isHeic = in_array($origExt, ['heic', 'heif'])
            || str_contains($mime, 'heic')
            || str_contains($mime, 'heif');

        if ($isHeic) {
            if (!extension_loaded('imagick')) {
                return response()->json(['status' => 'error', 'message' => 'El servidor no tiene Imagick (necesario para HEIC).'], 500);
            }

            $convertedPath = sys_get_temp_dir() . "/{$safeBase}_" . time() . ".jpg";

            try {
                $im = new \Imagick();
                $im->readImage($file->getRealPath()); // requiere libheif
                $im->setImageFormat('jpeg');
                $im->setImageCompressionQuality(85);
                $im->writeImage($convertedPath);
                $im->clear();
                $im->destroy();

                $realPathToRead = $convertedPath;
            } catch (\Throwable $e) {
                return response()->json(['status' => 'error', 'message' => 'No se pudo convertir HEIC a JPG: ' . $e->getMessage()], 500);
            }
        }

        // ===== subir ORIGINAL (con nombre real) =====
        $fileName = $file->getClientOriginalName();
        $fileNameEncoded = rawurlencode($fileName);

        $basePath = "treatments_new/exp/exp_{$num_med_record}/{$treatmentId}/";
        $pathOriginal = $basePath . $fileNameEncoded;
        $urlOriginal  = "https://la.storage.bunnycdn.com/{$storageZone}/{$pathOriginal}";

        try {
            Http::withHeaders([
                'AccessKey'    => $apiKey,
                'Content-Type' => $file->getMimeType(),
            ])->send('PUT', $urlOriginal, [
                'body' => file_get_contents($file->getRealPath())
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Error subiendo original: ' . $e->getMessage()], 500);
        }

        // ===== generar THUMB (siempre JPG) =====
        try {
            $thumbTempPath = sys_get_temp_dir() . "/thumb_{$safeBase}_" . time() . ".jpg";

            $driver  = extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
            $manager = new ImageManager($driver);

            $image = $manager->read($realPathToRead);
            $image = $image->scale(width: 350);
            $image->toJpeg(80)->save($thumbTempPath);

            if (!file_exists($thumbTempPath)) {
                throw new \Exception("Thumb NO generado");
            }
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Error generando thumbnail: ' . $e->getMessage()], 500);
        }

        // ===== subir THUMB (nombre: slug.jpg) =====
        $thumbName = $safeBase . ".jpg";
        $pathThumb = $basePath . "thumb/" . rawurlencode($thumbName);
        $urlThumb  = "https://la.storage.bunnycdn.com/{$storageZone}/{$pathThumb}";

        try {
            Http::withHeaders([
                'AccessKey'    => $apiKey,
                'Content-Type' => 'image/jpeg',
            ])->send('PUT', $urlThumb, [
                'body' => file_get_contents($thumbTempPath)
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Original subido, pero falló thumbnail: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Foto y thumbnail subidos correctamente.'
        ], 200);
    }


    public function eliminarFotoTreatment(Request $request)
    {
        $url = $request->input('url');
        if (!$url) return response()->json(['error' => 'URL no especificada'], 400);

        $isQro = str_contains($url, env('BUNNY_STORAGE_ZONE_QRO'));
        $storageZone = $isQro ? env('BUNNY_STORAGE_ZONE_QRO') : env('BUNNY_STORAGE_ZONE');
        $apiKey = $isQro ? env('BUNNY_API_KEY_QRO') : env('BUNNY_API_KEY');

        try {
            $path = str_replace("https://{$storageZone}.b-cdn.net/", '', $url);

            // borrar original
            Http::withHeaders(['AccessKey' => $apiKey])
                ->delete("https://la.storage.bunnycdn.com/{$storageZone}/{$path}");

            // borrar thumb slug.jpg
            $fileName = basename($path);
            $base = pathinfo($fileName, PATHINFO_FILENAME);
            $thumbFile = (Str::slug($base) ?: $base) . '.jpg';

            $thumbPath = pathinfo($path, PATHINFO_DIRNAME) . "/thumb/" . $thumbFile;

            Http::withHeaders(['AccessKey' => $apiKey])
                ->delete("https://la.storage.bunnycdn.com/{$storageZone}/{$thumbPath}");

            return response()->json([
                'success' => true,
                'message' => 'Foto y thumbnail eliminados correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function obtenerFotosTreatment($num_med_record, $treatmentId, $storageZone, $apiKey)
    {
        $imagenes = [];
        $basePath = "treatments_new/exp/exp_{$num_med_record}/{$treatmentId}/";
        $thumbFolder = "{$basePath}/thumb/";

        try {
            // Archivos originales
            $resp = Http::withHeaders([
                'AccessKey' => $apiKey,
                'Accept' => 'application/json'
            ])->get("https://la.storage.bunnycdn.com/{$storageZone}/{$basePath}");

            $files = $resp->ok() ? $resp->json() : [];

            // Thumbnails
            $respThumb = Http::withHeaders([
                'AccessKey' => $apiKey,
                'Accept' => 'application/json'
            ])->get("https://la.storage.bunnycdn.com/{$storageZone}/{$thumbFolder}");

            $thumbs = $respThumb->ok() ? $respThumb->json() : [];

            // Mapa de thumbnails
            $thumbMap = [];
            foreach ($thumbs as $t) {
                if (!$t['IsDirectory']) {
                    $thumbMap[pathinfo($t['ObjectName'], PATHINFO_FILENAME)] = $t['ObjectName'];
                }
            }

            // Armar arrays finales
            foreach ($files as $f) {
                if ($f['IsDirectory']) continue;

                $originalName = $f['ObjectName'];
                $base = pathinfo($originalName, PATHINFO_FILENAME);

                $originalUrl = "https://{$storageZone}.b-cdn.net/{$basePath}" . rawurlencode($originalName);

                $thumbUrl = isset($thumbMap[$base])
                    ? "https://{$storageZone}.b-cdn.net/{$thumbFolder}" . rawurlencode($thumbMap[$base])
                    : $originalUrl;

                $imagenes[] = [
                    'name'  => $originalName,
                    'url'   => $originalUrl,
                    'thumb' => $thumbUrl
                ];
            }
        } catch (\Exception $e) {
        }

        return $imagenes;
    }

    public function obtenerFotosTreatmentJson($num_med_record, $treatmentId)
    {
        $paciente = DB::table('enf_procedures')
            ->where('num_med_record', $num_med_record)
            ->first();

        if (!$paciente) {
            return response()->json([
                'status' => 'error',
                'message' => 'Paciente no encontrado'
            ], 404);
        }

        // ========= NOTA INICIAL DEL TRATAMIENTO =========
        $tratamiento = DB::table('enf_treatments_appointments')
            ->where('num_med_record', $num_med_record)
            ->where('id', $treatmentId)
            ->first();

        $notaInicial = $tratamiento->notes ?? '';


        // ========= FOTOS =========
        $storageZone = strtolower($paciente->clinic) === 'queretaro'
            ? env('BUNNY_STORAGE_ZONE_QRO')
            : env('BUNNY_STORAGE_ZONE');

        $apiKey = strtolower($paciente->clinic) === 'queretaro'
            ? env('BUNNY_API_KEY_QRO')
            : env('BUNNY_API_KEY');

        $imagenes = $this->obtenerFotosTreatment(
            $num_med_record,
            $treatmentId,
            $storageZone,
            $apiKey
        );

        return response()->json([
            'status'       => 'success',
            'data'         => $imagenes,
            'nota_inicial' => $notaInicial,
        ]);
    }
    public function showNotas(Request $request)
    {
        $request->validate([
            'identifier' => 'required',
            'identifier_type' => 'required',
            'phase' => 'nullable|string'
        ]);
        try {
            $identifier = $request->identifier;
            $identifier_type = $request->identifier_type;
            $phase = $request->phase;

            $notas = DB::table('enf_treatments_notes')
                ->where('identifier', $identifier)
                ->where('identifier_type', $identifier_type)
                ->when($phase, function ($q) use ($phase) {
                    return $q->where('phase', $phase);
                })
                ->orderBy('date', 'DESC')
                ->get();

            if ($notas->isEmpty()) {
                return response()->json([
                    'status' => 'no_results',
                    'message' => 'No se encontraron notas para este tratamiento.'
                ]);
            }

            // Convertir fecha al formato español y obtener autor
            foreach ($notas as $n) {

                // Fecha bonita estilo CRM
                $dt = Carbon::parse($n->date);
                $meses = [1 => 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sept', 'Oct', 'Nov', 'Dic'];
                $n->date = $dt->format("d") . " de " . $meses[$dt->month] . ", " . $dt->year;

                // Autor
                //$n->author_name = DB::table('usuarios')->where('id', $n->author)->value('nombre') ?? 'Desconocido';
            }

            return response()->json([
                'status' => 'success',
                'data' => $notas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
    // ===========================
    // OBTENER NOMBRE DEL PACIENTE
    // ===========================
    public function getPatientName(Request $request)
    {
        try {

            $clinic = $request->clinic ?? null;
            $num = $request->num_med_record;
            $name = $request->name; // ← Nuevo parámetro para buscar por nombre

            if (!$num && !$name) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes proporcionar num_med_record o name.'
                ]);
            }

            // === BASE DEL QUERY ===
            $query = DB::table('sa_leads_assessment as sig')
                ->join('enf_procedures as ep', 'sig.lead_id', '=', 'ep.lead_id')
                ->select(
                    DB::raw("CONCAT(sig.first_name, ' ', sig.last_name) AS fullname"),
                    'sig.clinic'
                );

            // === FILTRO POR EXPEDIENTE ===
            if ($num) {
                if (!is_numeric($num)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'num_med_record debe ser numérico.'
                    ]);
                }
                $query->where('ep.num_med_record', $num);
            }

            // === FILTRO POR NOMBRE (LIKE) ===
            if ($name) {
                $query->where(function ($q) use ($name) {
                    $q->where('sig.first_name', 'LIKE', "%$name%")
                        ->orWhere('sig.last_name', 'LIKE', "%$name%")
                        ->orWhere(DB::raw("CONCAT(sig.first_name, ' ', sig.last_name)"), 'LIKE', "%$name%");
                });
            }

            // === REGLA QUERÉTARO ===
            if ($clinic === "Queretaro") {
                $query->where('sig.clinic', 'Queretaro');
            } else {
                $query->where('sig.clinic', '!=', 'Queretaro');
            }

            // === RESULTADO PRINCIPAL ===
            $result = $query->orderBy('sig.created_at', 'DESC')->first();

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron resultados con esos criterios.'
                ]);
            }

            // === OBTENER TODAS LAS CLÍNICAS RELACIONADAS ===
            $clinicQuery = DB::table('sa_leads_assessment')
                ->join('enf_procedures', 'sa_leads_assessment.lead_id', '=', 'enf_procedures.lead_id')
                ->select('sa_leads_assessment.clinic')
                ->distinct();

            if ($num) {
                $clinicQuery->where('enf_procedures.num_med_record', $num);
            }

            if ($clinic === "Queretaro") {
                $clinicQuery->where('sa_leads_assessment.clinic', 'Santa fe');
            }

            $clinics = $clinicQuery->pluck('clinic');

            return response()->json([
                'success' => true,
                'message' => 'Consulta exitosa',
                'fullname' => $result->fullname,
                'clinic_found' => $result->clinic,
                'clinics' => $clinics
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // AGREGAR TRATAMIENTO
    public function addTreatment(Request $request)
    {
        try {
            // ✅ Bloqueo total para expediente (temporal)
            if ($request->filled('num_med_record') || $request->filled('clinic_num_med') || $request->no_record != 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Temporalmente deshabilitado: no se pueden añadir tratamientos para pacientes con expediente.'
                ], 422);
            }

            // === MODO SIN EXPEDIENTE ===
            $request->validate([
                'name_manual' => ['required', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
                // 'name'        => ['required', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
                'clinic_exp'  => ['required', 'in:Santa Fe,Pedregal,Queretaro'], // ajusta a tus valores reales
            ]);

            DB::table('enf_treatments')->insert([
                'num_med_record' => null,
                'name'           => trim($request->name),         // ✅ tratamiento
                'clinic'         => trim($request->clinic_exp),
                'created_at'     => now(),
                // 👇 SOLO si tienes una columna para el nombre del paciente
                'name'   => trim($request->name_manual),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tratamiento añadido sin expediente.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validación fallida.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function indexTratamientos()
    {
        $tratamientos = DB::table('enf_treatments as t')
            ->leftJoin('enf_treatments_appointments as a', 't.num_med_record', '=', 'a.num_med_record')
            ->leftJoin('enf_treatments_appointments_ext as ext', 't.id', '=', 'ext.px_id')
            ->select([
                't.name',
                't.id as treatment_id', // <-- para sin-exp este ES el px_id
                't.num_med_record',
                't.clinic',
                't.created_at',

                DB::raw('IFNULL(MAX(a.date), MAX(ext.date)) as date'),
                DB::raw('IFNULL(MAX(a.doctor), MAX(ext.doctor)) as doctor'),
                DB::raw('IFNULL(MAX(a.type), MAX(ext.type)) as type'),
                DB::raw('IFNULL(MAX(a.notes), MAX(ext.notes)) as notes'),
                DB::raw('IFNULL(MAX(a.created_by), MAX(ext.created_by)) as created_by'),

                // SOLO para sin-exp: id del último tratamiento (fila) en ext
                DB::raw('MAX(ext.id) as ext_treatment_id'),
            ])
            ->groupBy(
                't.name',
                't.id',
                't.num_med_record',
                't.clinic',
                't.created_at'
            )
            ->orderBy('t.id', 'desc')
            ->get();

        return view('crm.procedimientos.tratamientos', compact('tratamientos'));
    }


    public function verFotosTreatmentSinExpediente($px_id, $treatmentId = null)
    {
        // Nombre desde enf_treatments (siempre)
        $row = DB::table('enf_treatments')
            ->where('id', $px_id)
            ->select('name', 'clinic')
            ->first();

        $nombre  = $row->name   ?? null;
        $clinica = $row->clinic ?? null;

        // Lista completa para carrusel
        $tratamientos = DB::table('enf_treatments_appointments_ext')
            ->where('px_id', $px_id)
            ->orderByDesc('date')
            ->get();

        // ✅ NO seleccionar nada por default
        $selected = null;

        // Si viene treatmentId, intentar seleccionar ese
        if ($treatmentId && $tratamientos->isNotEmpty()) {
            $selected = $tratamientos->firstWhere('id', (int) $treatmentId);

            // Si el id no existe, deja selected en null (NO forzar primero)
            // Si prefieres 404:
            // if (!$selected) abort(404, 'Tratamiento no encontrado.');
        }

        // Header/paciente
        $paciente = (object) [
            'name'           => $nombre ?: 'Paciente sin expediente',
            'clinic'         => $selected?->clinic ?: ($clinica ?: 'Sin clínica asignada'),
            'procedure_type' => $selected?->type ?? '',
            'notes'          => $selected?->notes ?? 'Sin notas',
        ];

        // Imágenes SOLO si hay treatment seleccionado
        $imagenes = [];
        if ($selected) {
            $storageZone = env('BUNNY_STORAGE_ZONE');
            $apiKey      = env('BUNNY_API_KEY');

            $imagenes = $this->obtenerFotosTreatmentSinExp(
                $px_id,
                (int) $selected->id,
                $storageZone,
                $apiKey
            );
        }

        return view('crm.procedimientos.opciones.verFotosTratamientosSinExp', [
            'paciente'     => $paciente,
            'imagenes'     => $imagenes,
            'px_id'        => (int) $px_id,
            'treatmentId'  => $selected ? (int) $selected->id : null,
            'tratamientos' => $tratamientos,
        ]);
    }

    private function obtenerFotosTreatmentSinExp($px_id, $treatmentId, $storageZone, $apiKey)
    {
        $imagenes = [];
        $basePath = "treatments_new/id/id_{$px_id}/{$treatmentId}/";
        $thumbPath = $basePath . "thumb/";

        try {
            // 1) Listar originales
            $resp = Http::withHeaders([
                'AccessKey' => $apiKey,
                'Accept' => 'application/json'
            ])->get("https://la.storage.bunnycdn.com/{$storageZone}/{$basePath}");

            $files = $resp->ok() ? $resp->json() : [];

            // 2) Listar thumbs (si existe la carpeta)
            $respThumb = Http::withHeaders([
                'AccessKey' => $apiKey,
                'Accept' => 'application/json'
            ])->get("https://la.storage.bunnycdn.com/{$storageZone}/{$thumbPath}");

            $thumbFiles = $respThumb->ok() ? $respThumb->json() : [];

            // 3) Mapear thumbs por filename (sin extensión)
            $thumbMap = [];
            foreach ($thumbFiles as $t) {
                if (!empty($t['IsDirectory'])) continue;
                $thumbMap[pathinfo($t['ObjectName'], PATHINFO_FILENAME)] = $t['ObjectName'];
            }

            // 4) Armar la galería final
            foreach ($files as $f) {
                if (!empty($f['IsDirectory'])) continue; // ignora folders (incluye thumb)
                $name = $f['ObjectName'];

                $originalUrl = "https://{$storageZone}.b-cdn.net/{$basePath}" . rawurlencode($name);

                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $baseName = pathinfo($name, PATHINFO_FILENAME);

                // Regla: el thumb real es slug(base).jpg si HEIC/HEIF, si no, puede ser el mismo nombre (si existe)
                $expectedThumbBase = in_array($ext, ['heic', 'heif'])
                    ? \Illuminate\Support\Str::slug($baseName)
                    : $baseName;

                // Si existe en thumb/, úsalo. Si no, intenta slug(base) por compatibilidad.
                $thumbObject =
                    $thumbMap[$expectedThumbBase] ??
                    $thumbMap[\Illuminate\Support\Str::slug($baseName)] ??
                    null;

                $thumbUrl = $thumbObject
                    ? "https://{$storageZone}.b-cdn.net/{$thumbPath}" . rawurlencode($thumbObject)
                    : $originalUrl; // fallback

                $imagenes[] = [
                    'name'  => $name,
                    'url'   => $originalUrl,
                    'thumb' => $thumbUrl,
                ];
            }
        } catch (\Throwable $e) {
            Log::error("obtenerFotosTreatmentSinExp error: " . $e->getMessage());
            return [];
        }

        return $imagenes;
    }



    public function obtenerFotosTreatmentSinExpJson($px_id, $treatmentId)
    {
        $tratamiento = DB::table('enf_treatments_appointments_ext')
            ->where('px_id', $px_id)
            ->where('id', $treatmentId)
            ->first();

        if (!$tratamiento) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tratamiento sin expediente no encontrado.'
            ], 404);
        }

        $notaInicial = $tratamiento->notes ?? '';

        $clinicLower = strtolower(trim($tratamiento->clinic ?? ''));
        $isQro = in_array($clinicLower, ['queretaro', 'querétaro', 'qro'], true);

        $storageZone = $isQro ? env('BUNNY_STORAGE_ZONE_QRO') : env('BUNNY_STORAGE_ZONE');
        $apiKey      = $isQro ? env('BUNNY_API_KEY_QRO')      : env('BUNNY_API_KEY');

        $imagenes = $this->obtenerFotosTreatmentSinExp($px_id, $treatmentId, $storageZone, $apiKey);

        return response()->json([
            'status'       => 'success',
            'data'         => $imagenes,
            'nota_inicial' => $notaInicial,
            'px_id'        => (int) $px_id,
            'treatmentId'  => (int) $treatmentId,
        ]);
    }

    //Subir foto tratamiento sin expediente
    public function subirFotoTreatmentSinExp(Request $request, $px_id, $treatmentId)
    {
        // OJO: NO uses "image" si quieres aceptar HEIC
        $request->validate([
            'foto' => 'required|file|max:100240', // ~100MB (ajusta a lo que quieras)
        ]);

        $file = $request->file('foto');
        if (!$file || !$file->isValid()) {
            return response()->json(['status' => 'error', 'message' => 'Archivo inválido.'], 422);
        }

        // Storage según clínica (si quieres fijo CDMX, deja sólo BUNNY_STORAGE_ZONE)
        $tratamiento = DB::table('enf_treatments_appointments_ext')
            ->where('px_id', $px_id)
            ->where('id', $treatmentId)
            ->first();

        $clinicLower = strtolower(trim($tratamiento->clinic ?? ''));
        $isQro = in_array($clinicLower, ['queretaro', 'querétaro', 'qro'], true);

        $storageZone = $isQro ? env('BUNNY_STORAGE_ZONE_QRO') : env('BUNNY_STORAGE_ZONE');
        $apiKey      = $isQro ? env('BUNNY_API_KEY_QRO')      : env('BUNNY_API_KEY');

        // Nombre seguro
        $origExt  = strtolower($file->getClientOriginalExtension());
        $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeBase = Str::slug($origName) ?: ('img_' . time());

        // Detectar HEIC/HEIF
        $mime = (string) $file->getMimeType();
        $isHeic = in_array($origExt, ['heic', 'heif'], true)
            || str_contains($mime, 'heic')
            || str_contains($mime, 'heif');

        // Carpeta
        $basePath = "treatments_new/id/id_{$px_id}/{$treatmentId}/";

        // =========================
        // 1) SUBIR ORIGINAL (tal cual)
        // =========================
        $originalNameToStore = $file->getClientOriginalName();
        $originalNameEncoded = rawurlencode($originalNameToStore);

        $pathOriginal = "{$basePath}{$originalNameEncoded}";
        $urlOriginal  = "https://la.storage.bunnycdn.com/{$storageZone}/{$pathOriginal}";

        try {
            Http::withHeaders([
                'AccessKey'    => $apiKey,
                'Content-Type' => $file->getMimeType() ?: 'application/octet-stream',
            ])->send('PUT', $urlOriginal, [
                'body' => file_get_contents($file->getRealPath()),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al subir original: ' . $e->getMessage()], 500);
        }

        // =========================
        // 2) PREPARAR INPUT PARA THUMB
        // =========================
        $realPathToRead = $file->getRealPath();

        if ($isHeic) {
            if (!extension_loaded('imagick')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El servidor no tiene Imagick (requerido para HEIC/HEIF).'
                ], 422);
            }

            $convertedPath = sys_get_temp_dir() . "/{$safeBase}_" . time() . ".jpg";

            try {
                $im = new \Imagick();
                $im->readImage($realPathToRead);   // requiere libheif
                $im->setImageFormat('jpeg');
                $im->setImageCompressionQuality(85);
                $im->writeImage($convertedPath);
                $im->clear();
                $im->destroy();

                $realPathToRead = $convertedPath;
            } catch (\Throwable $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se pudo convertir HEIC a JPG: ' . $e->getMessage()
                ], 422);
            }
        }

        // =========================
        // 3) GENERAR THUMB (jpg)
        // =========================
        try {
            $thumbTempPath = sys_get_temp_dir() . "/thumb_{$safeBase}_" . time() . ".jpg";

            // Driver: Imagick si existe (mejor), si no GD
            $driver = extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
            $manager = new ImageManager($driver);

            $image = $manager->read($realPathToRead);
            $image = $image->scale(width: 350);
            $image->toJpeg(80)->save($thumbTempPath);

            if (!file_exists($thumbTempPath)) {
                throw new \Exception('Thumb no generado');
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'El original subió, pero falló el thumb: ' . $e->getMessage()
            ], 500);
        }

        // =========================
        // 4) SUBIR THUMB (slug.jpg)
        // =========================
        $thumbName = $safeBase . '.jpg';
        $thumbNameEncoded = rawurlencode($thumbName);

        $pathThumb = "{$basePath}thumb/{$thumbNameEncoded}";
        $urlThumb  = "https://la.storage.bunnycdn.com/{$storageZone}/{$pathThumb}";

        try {
            Http::withHeaders([
                'AccessKey'    => $apiKey,
                'Content-Type' => 'image/jpeg',
            ])->send('PUT', $urlThumb, [
                'body' => file_get_contents($thumbTempPath),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'El original subió, pero falló subir el thumb: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Foto y thumbnail subidos correctamente.'
        ]);
    }

    public function eliminarFotoTratamientoSinExp(Request $request, $px_id, $treatmentId)
    {
        $url = $request->input('url');

        if (!$url) {
            return response()->json(['status' => 'error', 'message' => 'URL requerida'], 422);
        }

        try {
            // Detectar storage por clínica desde el treatment (así no dependes del URL)
            $tratamiento = DB::table('enf_treatments_appointments_ext')
                ->where('px_id', $px_id)
                ->where('id', $treatmentId)
                ->first();

            $clinicLower = strtolower(trim($tratamiento->clinic ?? ''));
            $isQro = in_array($clinicLower, ['queretaro', 'querétaro', 'qro'], true);

            $storageZone = $isQro ? env('BUNNY_STORAGE_ZONE_QRO') : env('BUNNY_STORAGE_ZONE');
            $apiKey      = $isQro ? env('BUNNY_API_KEY_QRO')      : env('BUNNY_API_KEY');

            // Validar URL del CDN correcto
            $cdnPrefix = "https://{$storageZone}.b-cdn.net/";
            if (!str_starts_with($url, $cdnPrefix)) {
                return response()->json(['status' => 'error', 'message' => 'URL inválida'], 422);
            }

            // Extraer path dentro del CDN
            $path = str_replace($cdnPrefix, '', $url);

            // Debe pertenecer a este px_id y treatmentId
            $expectedPrefix = "treatments_new/id/id_{$px_id}/{$treatmentId}/";
            if (!str_starts_with($path, $expectedPrefix)) {
                return response()->json(['status' => 'error', 'message' => 'La imagen no pertenece a este tratamiento'], 403);
            }

            // URL de borrado del original
            $apiUrlOriginal = "https://la.storage.bunnycdn.com/{$storageZone}/{$path}";

            // Resolver thumb:
            // - Si ya viene /thumb/ lo borramos tal cual
            // - Si es HEIC/HEIF: thumb real es slug.jpg (como en tu upload)
            // - Si es jpg/png: thumb puede ser mismo nombre (si así lo subiste) o también slug.jpg
            if (str_contains($path, '/thumb/')) {
                $thumbPath = $path;
                $originalPath = str_replace('/thumb/', '/', $path);
                $apiUrlThumb = "https://la.storage.bunnycdn.com/{$storageZone}/{$thumbPath}";
                $apiUrlOriginal = "https://la.storage.bunnycdn.com/{$storageZone}/{$originalPath}";
            } else {
                $filename = basename($path);
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $baseName = pathinfo($filename, PATHINFO_FILENAME);

                // Thumb name según tu regla: siempre slug(base).jpg
                $thumbFile = \Illuminate\Support\Str::slug($baseName) . '.jpg';

                $thumbPath = dirname($path) . "/thumb/{$thumbFile}";
                $apiUrlThumb = "https://la.storage.bunnycdn.com/{$storageZone}/{$thumbPath}";
            }

            // --- Eliminar original ---
            $deleteOriginal = Http::withHeaders(['AccessKey' => $apiKey])->delete($apiUrlOriginal);

            // --- Eliminar thumb ---
            $deleteThumb = Http::withHeaders(['AccessKey' => $apiKey])->delete($apiUrlThumb);

            // Considera 404 como OK (ya no existe)
            $okOriginal = in_array($deleteOriginal->status(), [200, 201, 202, 204, 404], true);
            $okThumb    = in_array($deleteThumb->status(),    [200, 201, 202, 204, 404], true);

            if ($okOriginal && $okThumb) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Foto y thumb eliminados correctamente.'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'No se pudieron eliminar los archivos.'
            ], 422);

            return response()->json(['status' => 'error', 'message' => 'Valio madres!'], 422);
        } catch (\Throwable $e) {
            return back()->with('error', 'Valio turbo madres: ' . $e->getMessage());
        }
    }


    // CRear tratamiento sin expediente
    public function guardarTreatmentSinExp(Request $request)
    {
        $request->validate([
            'px_id'      => 'required|integer',   // <- este es el "grupo" del paciente sin exp
            //'date'       => 'required|date',
            //'clinic'     => 'required|string',
            'doctor'     => 'required|string',
            'type'       => 'required|string',
            'notes'      => 'nullable|string',
            'created_by' => 'required|integer',
            'origin'     => 'nullable|string',
            'inv_type'   => 'nullable|string',
        ]);

        // Por si quieres defaults:
        $origin  = $request->origin ?? $request->clinic;
        $invType = $request->inv_type ?? 'tratamiento';

        // INSERT en tabla sin expediente
        $id = DB::table('enf_treatments_appointments_ext')->insertGetId([
            'px_id'       => (int) $request->px_id,
            'date'        => $request->date,
            'clinic'      => $request->clinic,
            'doctor'      => $request->doctor,
            'type'        => $request->type,
            'inv_type'    => $invType,
            'notes'       => $request->notes,
            'created_by'  => (int) $request->created_by,
            'origin'      => $origin,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'id'     => $id
        ]);
    }



    /*------------------------------------------------
    -------------- Obtener las pinshis firmas --------
    ------------------------------------------------*/

    public function buscarFirma(Request $request)
    {
        $firma = EnfSign::where('num_med', $request->num_med)
            ->where('step', $request->fase)
            ->where('clinic', $request->clinic)
            ->first();

        if ($firma) {
            return response()->json([
                'success' => true,
                'url' => asset($firma->url) // ya apunta a /public directamente
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'No se encontró firma para los parámetros dados.'
        ]);
    }

    public function guardarFirma(Request $request)
    {
        $request->validate([
            'firma' => 'required',
            'fase' => 'required',
            'num_med' => 'required',
            'clinic' => 'required'
        ]);

        try {
            $imageData = $request->firma;
            $imageData = str_replace('data:image/png;base64,', '', $imageData);
            $imageData = str_replace(' ', '+', $imageData);

            $folder = 'firmas/' . $request->clinic . '/' . $request->num_med . '/' . $request->fase;
            $fileName = uniqid() . '.png';
            $fullPath = public_path($folder);

            // Crear carpeta si no existe
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0777, true);
            }

            // Guardar archivo físico
            $path = $folder . '/' . $fileName;
            file_put_contents(public_path($path), base64_decode($imageData));

            // Guardar registro en BD
            $firma = EnfSign::create([
                'num_med' => $request->num_med,
                'step' => $request->fase,
                'clinic' => $request->clinic,
                'url' => $path,
            ]);

            return response()->json([
                'success' => true,
                'url' => asset($path)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function eliminarFirma(Request $request)
    {
        $firma = EnfSign::where('num_med', $request->num_med)
            ->where('step', $request->fase)
            ->where('clinic', $request->clinic)
            ->first();

        if (!$firma) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la firma.'
            ]);
        }

        $filePath = public_path($firma->url);

        // Borrar archivo físico
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $firma->delete();

        return response()->json([
            'success' => true,
            'message' => 'Firma eliminada correctamente.'
        ]);
    }

    // =============================================================== //
    //  FUNCIONES ORIGINALES ADAPTADAS A LARAVEL                       //
    // =============================================================== //

    //Generar en pinshi sin Exp
    public function generateTreatmentReceipt(Request $request)
    {
        /* ============================================================
        NORMALIZAR TIPO
    ============================================================ */
        if (!$request->has('Tipo') && $request->has('tipo')) {
            $request->merge(['Tipo' => $request->input('tipo')]);
        }

        /* ============================================================
        VALIDACIÓN
    ============================================================ */
        $request->validate([
            'lead_id'      => 'required|integer',
            'patient_name' => 'required|string',
            'clinic'       => 'required|string',
            'receipt_date' => 'required|date',

            'Tipo' => 'required|in:Tratamiento,Producto',

            'treatment_name' => 'required_if:Tipo,Tratamiento|nullable|string',
            'product_name'   => 'required_if:Tipo,Producto|nullable|string',

            'total'        => 'required|numeric|min:0.01',
            'pay_method_1' => 'required|string',
            'pay_amount_1' => 'required|numeric|min:0.01',

            'pay_method_2' => 'nullable|string',
            'pay_amount_2' => 'nullable|numeric|min:0.01',

            'notes' => 'nullable|string',
        ]);

        if ($request->filled('pay_method_2') && !$request->filled('pay_amount_2')) {
            return response()->json([
                'success' => false,
                'message' => 'Falta el monto del pago 2.'
            ], 422);
        }

        /* ============================================================
        VARIABLES
    ============================================================ */
        $lead_id = (int) $request->lead_id;
        $clinic  = $request->clinic;
        $type    = $request->Tipo;
        $date    = \Carbon\Carbon::parse($request->receipt_date)->format('Y-m-d');
        $userId  = auth()->id();
        $total   = (float) $request->total;

        $itemName = $type === 'Producto'
            ? $request->product_name
            : $request->treatment_name;

        $methodCombo = $request->filled('pay_method_2')
            ? "{$request->pay_method_1} + {$request->pay_method_2}"
            : $request->pay_method_1;

        /* ============================================================
        MPDF
    ============================================================ */
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'Letter-L',
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 5,
            'margin_bottom' => 5,
            'tempDir' => storage_path('app/mpdf'),
        ]);

        /* ============================================================
        TEMPLATE
    ============================================================ */
        $template = match ($type) {
            'Producto' => match ($clinic) {
                'Pedregal'  => resource_path('pdf_templates/producto-pedregal.pdf'),
                'Santa Fe'  => resource_path('pdf_templates/producto-Santa Fe.pdf'),
                'Queretaro' => resource_path('pdf_templates/producto-Queretaro.pdf'),
                default     => resource_path('pdf_templates/producto_default_fixed.pdf'),
            },
            default => match ($clinic) {
                'Pedregal'  => resource_path('pdf_templates/tratamiento-pedregal.pdf'),
                'Santa Fe'  => resource_path('pdf_templates/tratamiento-Santa Fe.pdf'),
                'Queretaro' => resource_path('pdf_templates/tratamiento-Queretaro.pdf'),
                default     => resource_path('pdf_templates/tratamiento_default_fixed.pdf'),
            },
        };

        if (!file_exists($template)) {
            return response()->json(['success' => false, 'message' => 'Plantilla PDF no encontrada'], 500);
        }

        $mpdf->SetSourceFile($template);
        $mpdf->UseTemplate($mpdf->ImportPage(1));

        /* ============================================================
        HELPERS ABSOLUTOS
    ============================================================ */
        $left  = fn(float $x, float $y, string $t) => $mpdf->WriteText($x, $y, $t);
        $right = fn(float $x, float $y, string $t) => $mpdf->WriteText($x, $y, $t);

        /* ============================================================
        DATOS GENERALES (NOMBRE / FECHA)
    ============================================================ */
        $left(18.0, 55.5, $request->patient_name);
        $left(80.0, 42.0, \Carbon\Carbon::parse($date)->format('d/m/Y'));

        $right(145.0, 65.5, $request->patient_name);
        $right(203.0, 53.0, \Carbon\Carbon::parse($date)->format('d/m/Y'));

        /* ============================================================
        MONTO TOTAL (SEGÚN TIPO)
    ============================================================ */
        if ($type === 'Producto') {
            $left(20.2, 85.5, number_format($total, 2));
            $right(148.0, 99.0, number_format($total, 2));
        } else {
            $left(41.2, 141.5, number_format($total, 2));
            $right(169.0, 151.5, number_format($total, 2));
        }

        /* ============================================================
        PRODUCTO (CAMPO REAL DEL PDF)
    ============================================================ */
        if ($type === 'Producto') {
            $left(20.0, 72.0, $itemName);
            $right(148.0, 82.0, $itemName);
        }

        /* ============================================================
        MÉTODOS DE PAGO (SEGÚN TIPO)
    ============================================================ */
        if ($type === 'Producto') {
            $payCheckMap = [
                'efectivo' => ['left' => 85.5, 'right' => 95.5],
                'transferencia' => ['left' => 89.5, 'right' => 100.5],
                'tarjeta de débito' => ['left' => 95.5, 'right' => 106.5],
                'tarjeta de credito' => ['left' => 101.5, 'right' => 111.5],
                'tarjeta de crédito' => ['left' => 101.5, 'right' => 111.5],
                'dólares' => ['left' => 106.5, 'right' => 118.0],
                'dolares' => ['left' => 106.5, 'right' => 118.0],
                'deposito' => ['left' => 112.5, 'right' => 124.5],
                'depósito' => ['left' => 112.5, 'right' => 124.5],
            ];
        } else {
            $payCheckMap = [
                'efectivo' => ['left' => 96.0, 'right' => 105.2],
                'transferencia' => ['left' => 101.9, 'right' => 110.2],
                'tarjeta de débito' => ['left' => 107.0, 'right' => 116.2],
                'tarjeta de credito' => ['left' => 112.0, 'right' => 121.2],
                'tarjeta de crédito' => ['left' => 112.0, 'right' => 121.2],
                'dólares' => ['left' => 117.0, 'right' => 127.7],
                'dolares' => ['left' => 117.0, 'right' => 127.7],
            ];
        }

        $xPay = ['left' => 53.0, 'right' => 180.0];

        $markPay = function (?string $method) use ($payCheckMap, $xPay, $left, $right) {
            if (!$method) return;
            $k = strtolower(trim($method));
            if (!isset($payCheckMap[$k])) return;

            $left($xPay['left'],  $payCheckMap[$k]['left'],  'X');
            $right($xPay['right'], $payCheckMap[$k]['right'], 'X');
        };

        $markPay($request->pay_method_1);
        $markPay($request->pay_method_2);

        /* ============================================================
        CHECK TRATAMIENTO
    ============================================================ */
        if ($type === 'Tratamiento') {
            $treatCheckMap = [
                'factores de crecimiento' => ['left' => 70.0, 'right' => 80.0],
                'dutasteride' => ['left' => 75.0, 'right' => 85.0],
                'exosomas' => ['left' => 80.0, 'right' => 90.0],
            ];

            $xLeft = 20.5;
            $xRight = 149.1;

            $key = strtolower(trim($itemName));

            if (isset($treatCheckMap[$key])) {
                $left($xLeft,  $treatCheckMap[$key]['left'],  'X');
                $right($xRight, $treatCheckMap[$key]['right'], 'X');
            }
        }

        /* ============================================================
        NOTAS
    ============================================================ */
        $notes = [
            ($type === 'Producto' ? 'Producto: ' : 'Tratamiento: ') . $itemName,
            "Pago 1: {$request->pay_method_1} $" . number_format($request->pay_amount_1, 2),
        ];

        if ($request->pay_method_2) {
            $notes[] = "Pago 2: {$request->pay_method_2} $" . number_format($request->pay_amount_2, 2);
        }

        if ($request->notes) {
            $notes[] = trim($request->notes);
        }

        $yLeft  = 130.0;
        $yRight = 138.0;

        foreach ($notes as $i => $note) {
            $left(32.0,   $yLeft  + ($i * 6), $note);
            $right(170.0, $yRight + ($i * 6), $note);
        }

        /* ============================================================
        DB
    ============================================================ */
        DB::beginTransaction();
        try {
            $receiptId = DB::table('sa_info_payment_treatments')->insertGetId([
                'px_id' => $lead_id,
                'type' => $type,
                'amount' => $total,
                'method' => $methodCombo,
                'payment_date' => $date,
                'receipt_date' => $date,
                'public_notes' => implode("\n", $notes),
                'clinic' => $clinic,
                'uploaded_by' => $userId,
                'status' => 1,
                'created_at' => now(),
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        /* ============================================================
        GUARDAR PDF
    ============================================================ */
        $dir = public_path("storage/trats/{$lead_id}/receipts");
        if (!file_exists($dir)) mkdir($dir, 0775, true);

        $file = strtolower($type) . "_{$receiptId}.pdf";
        $mpdf->Output("{$dir}/{$file}", 'F');

        return response()->json([
            'success' => true,
            'pdf_url' => asset("storage/trats/{$lead_id}/receipts/{$file}")
        ]);
    }



    public function listTreatmentReceipts($px_id)
    {
        $px_id = (int) $px_id;

        $rows = DB::table('sa_info_payment_treatments')
            ->select('id', 'type', 'amount', 'method', 'receipt_date', 'created_at')
            ->where('px_id', $px_id)
            ->where('status', 1)
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $baseRel = "storage/trats/{$px_id}/receipts";          // para asset()
        $baseDir = public_path("storage/trats/{$px_id}/receipts"); // para file_exists()

        $data = $rows->map(function ($r) use ($baseRel, $baseDir) {

            $id = (int) $r->id;
            $typeSlug = strtolower(trim((string)$r->type)); // tratamiento | producto

            // candidatos (orden de prioridad)
            $candidates = [
                "{$typeSlug}_{$id}.pdf",  // nuevo
                "Recibo_{$id}.pdf",       // viejo (R mayúscula)
                "recibo_{$id}.pdf",       // viejo (minúscula)
            ];

            $found = null;
            foreach ($candidates as $name) {
                if (is_file($baseDir . DIRECTORY_SEPARATOR . $name)) {
                    $found = $name;
                    break;
                }
            }

            $url = $found ? asset("{$baseRel}/{$found}") : null;

            return [
                'id'     => $id,
                'type'   => (string) $r->type,
                'amount' => (float) $r->amount,
                'method' => (string) $r->method,
                'date'   => $r->receipt_date ? \Carbon\Carbon::parse($r->receipt_date)->format('d/m/Y') : '',
                'url'    => $url,       // null si no existe ningún archivo
                'file'   => $found,     // opcional: para debug
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }


    // Recibos con expediente medico
    public function generateTreatmentReceiptExp(Request $request)
    {
        /* ============================================================
        NORMALIZAR TIPO
    ============================================================ */
        if (!$request->has('Tipo') && $request->has('tipo')) {
            $request->merge(['Tipo' => $request->input('tipo')]);
        }

        /* ============================================================
        VALIDACIÓN
    ============================================================ */
        $request->validate([
            'num_med_record' => 'required|string',
            'patient_name'   => 'required|string',
            'clinic'         => 'required|string',
            'receipt_date'   => 'required|date',

            'Tipo' => 'required|in:Tratamiento,Producto',

            'treatment_name' => 'required_if:Tipo,Tratamiento|nullable|string',
            'product_name'   => 'required_if:Tipo,Producto|nullable|string',

            'total'        => 'required|numeric|min:0.01',
            'pay_method_1' => 'required|string',
            'pay_amount_1' => 'required|numeric|min:0.01',

            'pay_method_2' => 'nullable|string',
            'pay_amount_2' => 'nullable|numeric|min:0.01',

            'notes' => 'nullable|string',
        ]);

        if ($request->filled('pay_method_2') && !$request->filled('pay_amount_2')) {
            return response()->json([
                'success' => false,
                'message' => 'Falta el monto del pago 2.'
            ], 422);
        }

        /* ============================================================
        VARIABLES
    ============================================================ */
        $num_med_record = trim($request->num_med_record);
        $clinic  = $request->clinic;
        $type    = $request->Tipo;
        $date    = \Carbon\Carbon::parse($request->receipt_date)->format('Y-m-d');
        $userId  = auth()->id();
        $total   = (float) $request->total;

        $itemName = $type === 'Producto'
            ? $request->product_name
            : $request->treatment_name;

        $methodCombo = $request->filled('pay_method_2')
            ? "{$request->pay_method_1} + {$request->pay_method_2}"
            : $request->pay_method_1;

        /* ============================================================
        MPDF
    ============================================================ */
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'Letter-L',
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 5,
            'margin_bottom' => 5,
            'tempDir' => storage_path('app/mpdf'),
        ]);

        /* ============================================================
        TEMPLATE
    ============================================================ */
        $template = match ($type) {
            'Producto' => match ($clinic) {
                'Pedregal'  => resource_path('pdf_templates/producto-pedregal.pdf'),
                'Santa Fe'  => resource_path('pdf_templates/producto-Santa Fe.pdf'),
                'Queretaro' => resource_path('pdf_templates/producto-Queretaro.pdf'),
                default     => resource_path('pdf_templates/producto_default_fixed.pdf'),
            },
            default => match ($clinic) {
                'Pedregal'  => resource_path('pdf_templates/tratamiento-pedregal.pdf'),
                'Santa Fe'  => resource_path('pdf_templates/tratamiento-Santa Fe.pdf'),
                'Queretaro' => resource_path('pdf_templates/tratamiento-Queretaro.pdf'),
                default     => resource_path('pdf_templates/tratamiento_default_fixed.pdf'),
            },
        };

        if (!file_exists($template)) {
            return response()->json(['success' => false, 'message' => 'Plantilla PDF no encontrada'], 500);
        }

        $mpdf->SetSourceFile($template);
        $mpdf->UseTemplate($mpdf->ImportPage(1));

        /* ============================================================
        HELPERS ABSOLUTOS
    ============================================================ */
        $left  = fn(float $x, float $y, string $t) => $mpdf->WriteText($x, $y, $t);
        $right = fn(float $x, float $y, string $t) => $mpdf->WriteText($x, $y, $t);

        /* ============================================================
        DATOS GENERALES (NOMBRE / FECHA)
    ============================================================ */
        $left(18.0, 55.5, $request->patient_name);
        $left(80.0, 42.0, \Carbon\Carbon::parse($date)->format('d/m/Y'));

        $right(145.0, 65.5, $request->patient_name);
        $right(203.0, 53.0, \Carbon\Carbon::parse($date)->format('d/m/Y'));

        /* ============================================================
        🔴 MONTO TOTAL (DIFERENTE POR TIPO)
    ============================================================ */
        if ($type === 'Producto') {
            $left(20.2, 85.5, number_format($total, 2));
            $right(148.0, 99.0, number_format($total, 2));
        } else {
            $left(41.2, 141.5, number_format($total, 2));
            $right(169.0, 151.5, number_format($total, 2));
        }

        /* ============================================================
        🔴 PRODUCTO (CAMPO REAL DEL PDF)
    ============================================================ */
        if ($type === 'Producto') {
            $left(20.0, 72.0, $itemName);
            $right(148.0, 82.0, $itemName);
        }

        /* ============================================================
        MÉTODOS DE PAGO (SEGÚN TIPO)
    ============================================================ */
        if ($type === 'Producto') {
            $payCheckMap = [
                'efectivo' => ['left' => 85.5, 'right' => 95.5],
                'transferencia' => ['left' => 89.5, 'right' => 100.5],
                'tarjeta de débito' => ['left' => 95.5, 'right' => 106.5],
                'tarjeta de credito' => ['left' => 101.5, 'right' => 111.5],
                'tarjeta de crédito' => ['left' => 101.5, 'right' => 111.5],
                'dólares' => ['left' => 106.5, 'right' => 118.0],
                'dolares' => ['left' => 106.5, 'right' => 118.0],
                'deposito' => ['left' => 112.5, 'right' => 124.5],
                'depósito' => ['left' => 112.5, 'right' => 124.5],
            ];
        } else {
            $payCheckMap = [
                'efectivo' => ['left' => 96.0, 'right' => 105.2],
                'transferencia' => ['left' => 101.9, 'right' => 110.2],
                'tarjeta de débito' => ['left' => 107.0, 'right' => 116.2],
                'tarjeta de credito' => ['left' => 112.0, 'right' => 121.2],
                'tarjeta de crédito' => ['left' => 112.0, 'right' => 121.2],
                'dólares' => ['left' => 117.0, 'right' => 127.7],
                'dolares' => ['left' => 117.0, 'right' => 127.7],
            ];
        }

        $xPay = ['left' => 53.0, 'right' => 180.0];

        $markPay = function (?string $method) use ($payCheckMap, $xPay, $left, $right) {
            if (!$method) return;
            $k = strtolower(trim($method));
            if (!isset($payCheckMap[$k])) return;

            $left($xPay['left'],  $payCheckMap[$k]['left'],  'X');
            $right($xPay['right'], $payCheckMap[$k]['right'], 'X');
        };

        $markPay($request->pay_method_1);
        $markPay($request->pay_method_2);

        /* ============================================================
        CHECK TRATAMIENTO
    ============================================================ */
        if ($type === 'Tratamiento') {
            $treatCheckMap = [
                'factores de crecimiento' => ['left' => 70.0, 'right' => 80.0],
                'dutasteride' => ['left' => 75.0, 'right' => 85.0],
                'exosomas' => ['left' => 80.0, 'right' => 90.0],
            ];

            $xLeft = 20.5;
            $xRight = 149.1;

            $key = strtolower(trim($itemName));

            if (isset($treatCheckMap[$key])) {
                $left($xLeft,  $treatCheckMap[$key]['left'],  'X');
                $right($xRight, $treatCheckMap[$key]['right'], 'X');
            }
        }

        /* ============================================================
        NOTAS
    ============================================================ */
        $notes = [
            ($type === 'Producto' ? 'Producto: ' : 'Tratamiento: ') . $itemName,
            "Pago 1: {$request->pay_method_1} $" . number_format($request->pay_amount_1, 2),
        ];

        if ($request->pay_method_2) {
            $notes[] = "Pago 2: {$request->pay_method_2} $" . number_format($request->pay_amount_2, 2);
        }

        if ($request->notes) {
            $notes[] = trim($request->notes);
        }

        $yLeft = 130.0;
        $yRight = 138.0;

        foreach ($notes as $i => $note) {
            $left(32.0,   $yLeft  + ($i * 6), $note);
            $right(170.0, $yRight + ($i * 6), $note);
        }

        /* ============================================================
        DB
    ============================================================ */
        DB::beginTransaction();
        try {
            $receiptId = DB::table('sa_info_payment_treatments')->insertGetId([
                'num_med_record' => $num_med_record,
                'px_id' => null,
                'type' => $type,
                'amount' => $total,
                'conversion' => 0,
                'amount_conversion' => 0,
                'method' => $methodCombo,
                'payment_date' => $date,
                'receipt_date' => $date,
                'public_notes' => implode("\n", $notes),
                'clinic' => $clinic,
                'uploaded_by' => $userId,
                'created_at' => now(),
                'status' => 1,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        /* ============================================================
        GUARDAR PDF
    ============================================================ */
        $dir = public_path("storage/trats/exp/{$num_med_record}/receipts");
        if (!file_exists($dir)) mkdir($dir, 0775, true);

        $file = strtolower($type) . "_{$receiptId}.pdf";
        $mpdf->Output("{$dir}/{$file}", 'F');

        return response()->json([
            'success' => true,
            'pdf_url' => asset("storage/trats/exp/{$num_med_record}/receipts/{$file}")
        ]);
    }


    public function listTreatmentReceiptsExp($num_med_record)
    {
        $num_med_record = trim((string) $num_med_record);

        $rows = DB::table('sa_info_payment_treatments')
            ->select('id', 'type', 'amount', 'method', 'receipt_date', 'created_at')
            ->where('num_med_record', $num_med_record)
            ->where('status', 1)
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        // paths base
        $baseRel = "storage/trats/exp/{$num_med_record}/receipts";          // para asset()
        $baseDir = public_path("storage/trats/exp/{$num_med_record}/receipts"); // para file_exists()

        $data = $rows->map(function ($r) use ($baseRel, $baseDir) {

            $id = (int) $r->id;
            $typeSlug = strtolower(trim((string) $r->type)); // tratamiento | producto

            // nombres posibles (orden importa)
            $candidates = [
                "{$typeSlug}_{$id}.pdf", // nuevo
                "Recibo_{$id}.pdf",      // viejo
                "recibo_{$id}.pdf",      // viejo lowercase
            ];

            $found = null;
            foreach ($candidates as $name) {
                if (is_file($baseDir . DIRECTORY_SEPARATOR . $name)) {
                    $found = $name;
                    break;
                }
            }

            return [
                'id'     => $id,
                'type'   => (string) $r->type,
                'amount' => (float) $r->amount,
                'method' => (string) $r->method,
                'date'   => $r->receipt_date
                    ? \Carbon\Carbon::parse($r->receipt_date)->format('d/m/Y')
                    : '',
                'url'    => $found ? asset("{$baseRel}/{$found}") : null,
                'file'   => $found, // opcional (debug)
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }



    ///////////////////// NOTIFICACIONES ///////////////////////


    public function getNotification($id)
    {
        $notification = DB::table('notifications')->where('id', $id)->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la notificación.'
            ]);
        }

        // Formatear datetime para <input type="datetime-local">
        $notificationData = (array) $notification;
        $notificationData['datetime'] = date('Y-m-d\TH:i', strtotime($notification->datetime));

        return response()->json([
            'success' => true,
            'data' => $notificationData
        ]);
    }
    public function getPatientNotifications(Request $request)
    {
        $px_sales_id = $request->input('id', 0);
        $user_id = $request->input('user_id', 0);
        $procedure_type = $request->input('procedure_type', 1);

        // Buscar paciente
        $procedure = DB::table('enf_procedures as ep')
            ->join('sa_leads_assessment as sla', 'ep.lead_id', '=', 'sla.lead_id')
            ->select(
                'sla.lead_id',
                'ep.num_med_record',
                'ep.room',
                'ep.specialist',
                'ep.touchup',  // Agregar esta línea
                DB::raw("CONCAT(sla.first_name, ' ', sla.last_name) AS name"),
                'sla.procedure_type'
            )
            ->where('ep.lead_id', $px_sales_id)
            ->first();

        if (!$procedure) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información del paciente.',
                'data' => []
            ]);
        }

        // Obtener notificaciones
        $notifications = DB::table('notifications')
            ->where('lead_id', $px_sales_id)
            ->where('procedure_type', $procedure_type)
            ->orderBy('datetime', 'desc')
            ->get();

        $activityList = [];

        foreach ($notifications as $notif) {

            $activityList[] = [
                'id'       => $notif->id,          // 👈 CLAVE

                'title'      => $notif->message,
                'datetime'   => \Carbon\Carbon::parse($notif->datetime)->format('d/m/Y h:i A'),
                'image'      => $notif->ext_image, // 👈 IMAGEN
                'process'    => $notif->process,
                'uploadedBy' => $notif->uploaded_by,
            ];
        }

        $nextNotif = "Siguiente notificación del procedimiento {$procedure_type}";

        return response()->json([
            'success' => true,
            'message' => 'Datos encontrados',
            'data' => [
                'patient_name' => $procedure->name,
                'num_med' => $procedure->num_med_record,  // Agregar
                'touchup' => $procedure->touchup,         // Agregar
                'specialist' => $procedure->specialist,   // Agregar
                'room' => $procedure->room,               // Agregar
                'activity' => $activityList,
                'next_notification' => $nextNotif
            ]
        ]);
    }

    public function viewNotifications(Request $request)
    {
        $lead_id = $request->query('id');  // <-- AQUÍ CAMBIA

        if (!$lead_id) {
            abort(404, "Lead ID no proporcionado.");
        }

        $procedure = DB::table('enf_procedures')
            ->where('lead_id', $lead_id)
            ->first();

        $notifications = DB::table('notifications')
            ->where('lead_id', $lead_id)
            ->orderBy('datetime', 'desc')
            ->get()
            ->map(function ($item) {
                $item->datetime_formatted = date('Y-m-d\TH:i', strtotime($item->datetime));
                return $item;
            });

        return view('crm.procedimientos.notificaciones', [
            'lead_id' => $lead_id,
            'procedure' => $procedure,
            'notifications' => $notifications
        ]);
    }

    public function createNotification(Request $request)
    {
        /* =====================================================
       VALIDACIÓN
    ===================================================== */
        $request->validate([
            'lead_id'        => 'required|integer',
            'process'        => 'required',
            'px_data'        => 'required|string',
            'notif_type'     => 'nullable|string|max:50',
            'specialist'     => 'nullable|string|max:255',
            'room'           => 'nullable|integer',
            'hour'           => 'nullable|string',
            'uf'             => 'nullable|integer',
            'hair_follicles' => 'nullable|integer',
            'goal'           => 'nullable|integer',
            'file'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'comments'       => 'nullable|string'
        ]);

        /* =====================================================
       DATOS BASE
    ===================================================== */
        $lead_id        = (int) $request->lead_id;
        $process        = (string) $request->process;
        $px_data        = trim($request->px_data);
        $specialist     = $request->specialist ?? '';
        $hour           = $request->hour ?? 'N/A';
        $comments       = $request->comments ?? '';
        $procedure_type = 1;

        /* =====================================================
       VARIABLES DE IMAGEN (BLINDADAS)
    ===================================================== */
        $img_url        = null;
        $localImagePath = null;

        /* =====================================================
       PX DATA
    ===================================================== */
        $px_name = $px_data;
        $num_med = 0;
        $room = $request->room ? "🏥 Sala: {$request->room}\n" : '';

        if (str_contains($px_data, '|')) {
            [$px_name, $num_med] = array_pad(explode('|', $px_data), 2, 0);
            $num_med = (int) $num_med;
        }

        /* =====================================================
       CATÁLOGO DE PROCESOS
    ===================================================== */
        $available_process = [
            0  => "Px firmó documentos",
            1  => "Inicio de infiltración",
            2  => "Término de infiltración",
            3  => "Inicio de extracción",
            4  => "Término de extracción",
            5  => "Inicio de infiltración",
            6  => "Término de infiltración",
            7  => "Inicio de incisiones",
            8  => "Término de incisiones",
            9  => "Inicio de implantación",
            10 => "Término de implantación y procedimiento"
        ];

        $message = '';

        try {

            /* ================= FIRMA DOCUMENTOS ================= */
            if ($process === '0') {

                DB::table('enf_procedures_targets')->insert([
                    'lead_id'         => $lead_id,
                    'target_fixed'    => (int) ($request->goal ?? 0),
                    'target_achieved' => 0
                ]);

                $message =
                    "📄 <b>Px firmó documentos</b>\n" .
                    "⏰ Hora: {$hour}\n" .
                    $room .

                    "👨‍⚕️ Especialista: {$specialist}";
            }

            /* ================= INCIDENCIA / FOTO ================= */ elseif (in_array($process, ['-1', '3.1', '9.1'], true)) {

                if ($request->hasFile('file')) {

                    $file = $request->file('file');
                    $filename = now()->format('Ymd_His') . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();

                    $folder = public_path("temporal_storage/rdi-enf-cdmx/{$num_med}/proced");

                    if (!File::exists($folder)) {
                        File::makeDirectory($folder, 0755, true);
                    }

                    $file->move($folder, $filename);

                    // ✔️ LOCAL (para localhost)
                    $localImagePath = "{$folder}/{$filename}";

                    // ✔️ URL pública (producción)
                    $img_url = url("temporal_storage/rdi-enf-cdmx/{$num_med}/proced/{$filename}");
                }

                $photoType = $request->input('photo_type', 'Incidencia');
                $message = "⚠️ <b>{$px_name}</b>\n{$photoType} a las {$hour}";
            }

            /* ================= PROCESOS NORMALES ================= */ elseif (is_numeric($process) && (int)$process >= 1 && (int)$process <= 9) {

                $index = (int) $process;
                $message = "🧑‍⚕️ <b>{$px_name}</b>\n{$available_process[$index]} a las {$hour}";
            }

            /* ================= PROCESO FINAL ================= */ elseif ($process === '10') {

                $message =
                    "✅ <b>{$px_name}</b>\n" .
                    "{$available_process[10]} a las {$hour}\n" .
                    "UF: {$request->uf}\n" .
                    "Folículos: {$request->hair_follicles}\n" .
                    "Especialista: {$specialist}";
            }

            if ($comments) {
                $message .= "\n📝 Notas: {$comments}";
            }

            // 🔐 Limitar caption para Telegram
            $message = Str::limit(trim($message), 1000);

            /* ================= GUARDAR LOCAL ================= */
            DB::table('notifications')->insert([
                'lead_id'           => $lead_id,
                'process'           => $process,
                'datetime'          => now(),
                'message'           => strip_tags($message),
                'uploaded_by'       => auth()->id() ?? 1,
                'uploaded_datetime' => now(),
                'ext_image'         => $img_url,
                'telegram_msg_id'   => 0,
                'procedure_type'    => $procedure_type,
                'clinic'            => null
            ]);

            /* ================= TELEGRAM (NO BLOQUEANTE) ================= */
            $this->sendTelegramMessage(
                $message,
                $procedure_type,
                $img_url,
                $localImagePath
            );

            return response()->json([
                'success' => true,
                'message' => 'Notificación creada correctamente'
            ]);
        } catch (\Throwable $e) {

            Log::error('createNotification ERROR', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al crear la notificación'
            ], 500);
        }
    }




    public function nextNotification(Request $request)
    {
        $lead_id = $request->input('lead_id');
        $procedure_type = $request->input('procedure_type', 1);

        $available_process = [
            0  => "Px firmó documentos",
            1  => "Inicio de infiltración",
            2  => "Término de infiltración",
            3  => "Inicio de extracción",
            '3.1' => "Hora de extracción",
            4  => "Término de extracción",
            5  => "Inicio de incisiones",
            6  => "Término de incisiones",
            7  => "Inicio de implantación",
            8  => "Hora de implantación",
            9  => "Término de implantación",
            10 => "Término de procedimiento",
        ];

        try {
            $current = DB::table('notifications')
                ->where('lead_id', $lead_id)
                ->where('procedure_type', $procedure_type)
                ->orderByDesc('process')
                ->first();

            $options = '';

            if ($current) {
                $current_process = $current->process;
                $next_process = is_numeric($current_process) ? floor($current_process) + 1 : 0;

                // Opción siguiente normal
                if (isset($available_process[$next_process])) {
                    $options .= "<option value='{$next_process}' selected>{$available_process[$next_process]}</option>";
                }

                // Hora de extracción si el proceso anterior fue 3
                if ($current_process == 3 && isset($available_process['3.1'])) {
                    $options .= "<option value='3.1'>{$available_process['3.1']}</option>";
                }

                // Inicio de implantación si no existe el proceso 7
                if ($current_process >= 6) {
                    $implantation_done = DB::table('notifications')
                        ->where('lead_id', $lead_id)
                        ->where('procedure_type', $procedure_type)
                        ->where('process', 7)
                        ->exists();

                    if (!$implantation_done) {
                        $options .= "<option value='7'>{$available_process[7]}</option>";
                    }
                }
            } else {
                // Primera notificación
                $options .= "<option value='0' selected>{$available_process[0]}</option>";
            }

            $select = "
            <div class='col-span-2'>
                <label class='block text-sm font-semibold text-gray-700 mb-1'>
                    Selecciona la notificación
                </label>
                <select id='select_next_process'
                        class='w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-300'>
                    {$options}
                </select>
            </div>
        ";

            return response()->json([
                'success' => true,
                'next_notif' => $select,
                'message' => 'Done'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function sendTelegramMessage(
        string $message,
        int $procedure_type,
        ?string $imageUrl = null,
        ?string $localImagePath = null
    ): bool {

        /**
         * =====================================================
         * 🏥 RESOLVER SALA DESDE API / PROCEDURE
         * =====================================================
         */
        $room = match ($procedure_type) {
            1 => 1,
            2 => 2,
            3 => 3,
            default => null,
        };

        $bots = config('services.telegram.bots');

        if (!isset($bots[$procedure_type])) {
            Log::warning('Telegram: bot no configurado', [
                'procedure_type' => $procedure_type
            ]);
            return false;
        }

        $bot = $bots[$procedure_type];

        if (empty($bot['api']) || empty($bot['chat_id'])) {
            Log::error('Telegram: API o chat_id vacío', $bot);
            return false;
        }

        /**
         * =====================================================
         * 📝 PREPEND SALA AL MENSAJE
         * =====================================================
         */
        if ($room) {
            $message = "🏥 <b>Sala {$room}</b>\n" . $message;
        }

        try {

            $response = null;

            /**
             * =====================================================
             * 📸 FOTO (LOCAL > URL)
             * =====================================================
             */
            if (
                ($localImagePath && file_exists($localImagePath)) ||
                ($imageUrl && str_starts_with($imageUrl, 'http'))
            ) {

                $url = "https://api.telegram.org/bot{$bot['api']}/sendPhoto";

                $postFields = [
                    'chat_id'    => $bot['chat_id'],
                    'caption'    => $message,
                    'parse_mode' => 'HTML',
                ];

                // 🖥️ Local
                if ($localImagePath && file_exists($localImagePath)) {
                    $postFields['photo'] = new \CURLFile($localImagePath);
                }
                // 🌐 Producción
                else {
                    $postFields['photo'] = $imageUrl;
                }

                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => $postFields,
                    CURLOPT_TIMEOUT        => 15,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);

                $response = curl_exec($ch);

                if (curl_errno($ch)) {
                    Log::error('Telegram cURL error', [
                        'error' => curl_error($ch)
                    ]);
                }

                curl_close($ch);
            }

            /**
             * =====================================================
             * 📝 SOLO TEXTO
             * =====================================================
             */
            else {

                $url = "https://api.telegram.org/bot{$bot['api']}/sendMessage";

                $response = file_get_contents(
                    $url . '?' . http_build_query([
                        'chat_id'    => $bot['chat_id'],
                        'text'       => $message,
                        'parse_mode' => 'HTML',
                    ])
                );
            }

            if (!$response) {
                Log::warning('Telegram: respuesta vacía');
                return false;
            }

            $result = json_decode($response, true);

            Log::info('Telegram enviado correctamente', [
                'procedure_type' => $procedure_type,
                'sala' => $room,
                'chat_id' => $bot['chat_id'],
            ]);

            return isset($result['ok']) && $result['ok'] === true;
        } catch (\Throwable $e) {

            Log::error('Telegram no disponible', [
                'error' => $e->getMessage(),
                'procedure_type' => $procedure_type,
                'sala' => $room,
            ]);

            return false;
        }
    }



    // =============================================================== //
    //  FUNCIONES ORIGINALES ADAPTADAS A LARAVEL                       //
    // =============================================================== //

    private function parseProcessNumber($process_number)
    {
        return (strpos($process_number, '.') === false)
            ? intval($process_number)
            : floatval($process_number);
    }

    private function getPatientData($px_data)
    {
        $px = explode("-", $px_data);
        return [trim($px[0]), trim($px[1])];
    }

    private function getNotifDatetime($hour)
    {
        $today = date('Y-m-d');
        return (new DateTime("$today $hour"))->format('Y-m-d H:i:s');
    }

    private function parseDbMessage($telegram_message)
    {
        return $this->convertBold(str_replace("\n", "<br>", $telegram_message));
    }

    private function convertBold($msg)
    {
        $toggle = true;
        return preg_replace_callback('/\*/', function () use (&$toggle) {
            $tag = $toggle ? '<b>' : '</b>';
            $toggle = !$toggle;
            return $tag;
        }, $msg);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        try {

            // 🔎 1. Buscar la notificación
            $notif = DB::table('notifications')
                ->where('id', $request->id)
                ->first();

            if (!$notif) {
                return response()->json([
                    'success' => false,
                    'message' => 'La notificación no existe.',
                ]);
            }

            // 🗑️ 2. Eliminar en Telegram (si existe)
            if (!empty($notif->telegram_msg_id)) {

                $deletedTelegram = $this->deleteTelegramMessage(
                    (int) $notif->procedure_type,
                    (int) $notif->telegram_msg_id
                );

                Log::info('Eliminar Telegram', [
                    'notification_id' => $notif->id,
                    'telegram_msg_id' => $notif->telegram_msg_id,
                    'deleted' => $deletedTelegram
                ]);
            }

            // 🗑️ 3. Eliminar local
            DB::table('notifications')
                ->where('id', $request->id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notificación eliminada correctamente.',
            ]);
        } catch (\Throwable $e) {

            Log::error('Error al eliminar notificación', [
                'id' => $request->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la notificación.',
            ], 500);
        }
    }
    private function deleteTelegramMessage(
        int $procedure_type,
        int $telegramMsgId
    ): bool {

        if (!$telegramMsgId) {
            return false;
        }

        $bot = config("services.telegram.bots.$procedure_type");

        if (!$bot || empty($bot['api']) || empty($bot['chat_id'])) {
            Log::warning('Telegram delete: bot no configurado', [
                'procedure_type' => $procedure_type
            ]);
            return false;
        }

        try {

            $url = "https://api.telegram.org/bot{$bot['api']}/deleteMessage";

            $response = file_get_contents(
                $url . '?' . http_build_query([
                    'chat_id'    => $bot['chat_id'],
                    'message_id' => $telegramMsgId,
                ])
            );

            $result = json_decode($response, true);

            return $result['ok'] ?? false;
        } catch (\Throwable $e) {

            Log::error('Telegram delete error', [
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
