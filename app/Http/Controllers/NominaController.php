<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NominaController extends Controller
{
    public function index()
    {
        return view('crm.Finanzas.nomina.index');
    }

    public function data(Request $request)
    {
        $clinic = $request->clinic;

        $rows = DB::table('ad_nomina')
            ->when($clinic, fn ($q) => $q->where('clinic', $clinic))
            ->get();

        return response()->json([
            'data' => $rows
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array'
        ]);

        DB::table('ad_nomina')
            ->whereIn('id', $request->ids)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registros eliminados correctamente'
        ]);
    }

    public function generateLayout(Request $request)
    {
        $content = $request->content;
        $clinic  = $request->clinic ?? 'general';

        $filename = "layout_nomina_{$clinic}.txt";

        Storage::disk('local')->put($filename, $content);

        session(['layout_file' => $filename]);

        return response("Layout generado correctamente");
    }

    public function downloadLayout()
    {
        $filename = session('layout_file');

        abort_if(!$filename, 404);

        return Storage::download($filename);
    }


public function save(Request $request)
{
    try {
        $request->validate([
            'rows'   => 'required|array',
            'clinic' => 'required|string'
        ]);

        DB::beginTransaction();

        foreach ($request->rows as $row) {

            $data = [
                'num_progresivo' => $row['num_progresivo'] ?? null,
                'cuenta'         => $row['cuenta'] ?? '',
                'importe'        => ($row['importe'] !== '' && $row['importe'] !== null)
                                    ? $row['importe']
                                    : 0,
                'nombre'         => $row['nombre'] ?? '',
                'clinic'         => $row['clinic'] ?? $request->clinic,
            ];

            if (!empty($row['id'])) {
                DB::table('ad_nomina')
                    ->where('id', $row['id'])
                    ->update($data);
            } else {
                DB::table('ad_nomina')->insert($data);
            }
        }

        DB::commit();

        return response()->json(['success' => true]);

    } catch (\Throwable $e) {
        DB::rollBack();

        \Log::error('Error al guardar nómina', [
            'error' => $e->getMessage(),
            'payload' => $request->all(),
        ]);

        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

}
