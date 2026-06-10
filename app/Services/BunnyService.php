<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BunnyService
{
    protected $storageZone;
    protected $apiKey;
    protected $host;

    public function __construct()
    {
        $this->storageZone = config('services.bunny.storage_zone');
        $this->apiKey = config('services.bunny.api_key');
        $this->host = config('services.bunny.host');
    }

    public function upload($file, $path = '')
    {
        $filename = Str::random(10) . '.' . $file->getClientOriginalExtension();
        $uploadPath = trim($path, '/') . '/' . $filename;

        $response = Http::withHeaders([
            'AccessKey' => $this->apiKey,
            'Content-Type' => 'application/octet-stream',
        ])->put("{$this->host}/{$uploadPath}", file_get_contents($file));

        if (!$response->successful()) {
            throw new \Exception("Error al subir archivo a Bunny: {$response->body()}");
        }

        return [
            'name' => $filename,
            'url' => "{$this->host}/{$uploadPath}"
        ];
    }

    public function delete($path)
    {
        $response = Http::withHeaders([
            'AccessKey' => $this->apiKey,
        ])->delete("{$this->host}/" . ltrim($path, '/'));

        return $response->successful();
    }

    public function listFiles($path = '')
    {
        $response = Http::withHeaders([
            'AccessKey' => $this->apiKey,
        ])->get("https://storage.bunnycdn.com/{$this->storageZone}/" . trim($path, '/'));

        if (!$response->successful()) {
            throw new \Exception("Error al listar archivos: {$response->body()}");
        }

        return $response->json();
    }

    public function getFileUrl($path)
    {
        return "{$this->host}/" . ltrim($path, '/');
    }
}


