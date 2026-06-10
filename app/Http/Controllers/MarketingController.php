<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class MarketingController extends Controller
{

    public function index()
    {
        return view('crm.marketing.indexMarketing');
    }


    public function trackingPxData(Request $request)
    {
        $date   = $request->query('date');
        $clinic = $request->query('clinic');

        if (!$date || !$clinic) {
            return response()->json([
                'success' => 'false',
                'message' => 'Faltan parámetros: clinic o date.',
            ], 422);
        }

        // Guardamos el valor original (tal como viene del UI)
        $clinicUi = $clinic;
        
        // DB: "Santafe" en lugar de "Santa Fe"
        if ($clinic === 'Santa Fe') {
            $clinic = 'Santafe';
        }

        // Query DB
        $events = DB::table('sa_events')
            ->select(
                'id',
                'event_type',
                'attendance_type',
                'title',
                'start',
                'end',
                'description',
                'clinic',
                'qualy',
                'status',
                'review_time',
                'uploaded_by'
            )
            ->whereIn('event_type', ['revision', 'tratamiento'])
            ->whereDate('start', $date)
            ->where('clinic', $clinic)
            ->orderBy('start', 'asc')
            ->get()
            ->map(fn($row) => (array) $row)
            ->values()
            ->all();

        if (!$events) {
            return response()->json([
                'success' => 'true',
                'data'    => [],
                'message' => 'No se encontraron eventos para la fecha y clínica especificadas.',
            ]);
        }

        // Selección de Bunny según clínica (QRO vs CDMX)
        // OJO: tu UI manda "Queretaro" (sin acento). Si un día mandan "Querétaro", lo contemplamos.
        $isQro = in_array($clinicUi, ['Queretaro', 'Querétaro'], true);

        $storageZone = $isQro ? env('BUNNY_STORAGE_ZONE_QRO') : env('BUNNY_STORAGE_ZONE');
        $apiKey      = $isQro ? env('BUNNY_API_KEY_QRO')      : env('BUNNY_API_KEY');

        $region = env('BUNNY_REGION', 'la');

        $client = new Client([
            'base_uri' => "https://{$region}.storage.bunnycdn.com/",
            'headers'  => [
                'AccessKey' => $apiKey,
                'Accept'    => 'application/json',
            ],
            'timeout'         => 15,
            'connect_timeout' => 10,
        ]);

        $pattern = '/-\s*(\d+)$/';
        $bunnyCache = [];

        foreach ($events as &$event) {
            $expedienteNumber = 0;

            if (!empty($event['title']) && preg_match($pattern, $event['title'], $matches)) {
                $expedienteNumber = (int) $matches[1];
            }

            $event['expedienteNumber'] = $expedienteNumber ?: 0;
            $event['files'] = [];

            if (!$expedienteNumber) {
                continue;
            }

            if (array_key_exists($expedienteNumber, $bunnyCache)) {
                $cached = $bunnyCache[$expedienteNumber];
                $event['files'] = isset($cached['error'])
                    ? ['error' => $cached['error']]
                    : $cached['files'];
                continue;
            }

            try {
                $response = $client->request('GET', "{$storageZone}/{$expedienteNumber}/");
                $payload  = json_decode($response->getBody()->getContents(), true);

                $filesList = [];
                if (is_array($payload)) {
                    foreach ($payload as $file) {
                        if (!empty($file['ObjectName'])) {
                            $filesList[] = $file['ObjectName'];
                        }
                    }
                }

                $bunnyCache[$expedienteNumber] = ['files' => $filesList];
                $event['files'] = $filesList;
            } catch (RequestException $e) {
                $msg = 'No se pudo acceder a la carpeta: ' . $e->getMessage();
                $bunnyCache[$expedienteNumber] = ['error' => $msg];
                $event['files'] = ['error' => $msg];
            }
        }
        unset($event);

        return response()->json([
            'success' => 'true',
            'data'    => $events,
        ]);
    }
}
