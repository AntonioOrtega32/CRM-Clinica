<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

class RevistaController extends Controller
{ 
     public function viewer(Request $request, string $token)
{
    // El middleware signed ya validó firma/expiración del viewer
    $expires = (int) $request->query('expires'); // viene en la URL firmada

    // Genera URL firmada para el PDF con el mismo vencimiento
    $expiresAt = Carbon::createFromTimestamp($expires);

    $pdfUrl = URL::temporarySignedRoute(
        'revista.pdf',
        $expiresAt,
        ['token' => $token]
    );

    return view('landing.RevistaDigital', [
        'token'  => $token,
        'pdfUrl' => $pdfUrl,
        'watermark' => 'Clinica Capilar Elite',
        'watermark2' => '© Todos los derechos reservados',
    ]);
}

   public function streamPdf(Request $request, string $token)
{
    $path = storage_path('app/private/revista.pdf');

    abort_unless(file_exists($path), 404);

    return response()->file($path, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="revista.pdf"',
        'X-Content-Type-Options' => 'nosniff',
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        'Pragma' => 'no-cache',
    ]);
}

 public function upload(Request $request)
    {
        $request->validate([
            'revista' => 'required|file|mimes:pdf|max:102400', // 50MB (ajusta)
        ]);

        // Guarda SIEMPRE con el mismo nombre (solo 1 revista)
        $file = $request->file('revista');

        // Esto sobrescribe si ya existe
        Storage::disk('local')->putFileAs('private', $file, 'revista.pdf');

        return back()->with('success', 'Revista actualizada correctamente.');
    }

    public function destroy()
    {
        $path = 'private/revista.pdf';

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }

        return back()->with('success', 'Revista eliminada. Ya puedes subir una nueva.');
    }

}
