<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
class BunnyController extends Controller
{
    protected $apiKey;
    protected $storageZone;

    public function __construct()
    {
        $this->apiKey = env('BUNNY_API_KEY');
        $this->storageZone = env('BUNNY_STORAGE_ZONE');
    }

    // Vista principal
    public function index($lead_id)
    {
        return view('crm.Bunny.index', compact('lead_id'));
    }

    // Listar archivos
    public function listar($lead_id, $step = 'pre')
{
    $folder = "https://la.storage.bunnycdn.com/{$this->storageZone}/{$lead_id}/{$step}/";

        try {
            $response = Http::withHeaders([
                'AccessKey' => $this->apiKey,
                'accept' => '*/*',
            ])->get($folder);

            $files = $response->json();

            if (empty($files)) {
                return response()->json(['error' => "No hay archivos en la carpeta: $folder"]);
            }

            $data = [];
            foreach ($files as $file) {
                $data[] = [
                    'ObjectName' => $file['ObjectName'],
                    'Length'     => $file['Length'],
                    'LastChanged'=> $file['LastChanged'],
                    'url'        => "https://{$this->storageZone}.b-cdn.net/{$lead_id}/{$step}/{$file['ObjectName']}"
                ];
            }

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    // Mostrar imagen
    public function mostrar($lead_id, $file_name, $step = 'pre')
    {
        $url = "https://{$this->storageZone}.b-cdn.net/{$lead_id}/{$step}/{$file_name}";
        return redirect($url);
    }
}