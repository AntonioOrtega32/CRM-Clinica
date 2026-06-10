<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DailyExpensesController extends Controller
{
    //FIRMAS
    private function firmaPath(string $clinic, string $fecha): string
    {
        // clinic limpio para path
        $clinicSlug = Str::slug($clinic);
        $date = date('Y-m-d', strtotime($fecha));

        // storage/app/public/cortes/firmas/santa-fe/2026-01-08.png
        return "cortes/firmas/{$clinicSlug}/{$date}.png";
    }

    public function saveDailyFirma(Request $request)
    {
        $request->validate([
            'fecha'  => 'required|date',
            'clinic' => 'required|string',
            'firma'  => 'required|string', // data:image/png;base64,...
        ]);

        try {
            $firma = $request->input('firma');

            if (!Str::startsWith($firma, 'data:image')) {
                return response()->json(['success' => false, 'message' => 'Formato de firma inválido.'], 422);
            }

            // separar "data:image/png;base64," del base64
            [$meta, $content] = explode(',', $firma, 2);
            $bin = base64_decode($content);

            if ($bin === false || strlen($bin) < 50) {
                return response()->json(['success' => false, 'message' => 'Firma vacía o corrupta.'], 422);
            }

            $path = $this->firmaPath($request->clinic, $request->fecha);

            Storage::disk('public')->put($path, $bin);

            return response()->json([
                'success' => true,
                'path' => asset('storage/' . $path),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Error guardando firma', 'error' => $e->getMessage()], 500);
        }
    }

    public function getDailyFirma(Request $request)
    {
        $request->validate([
            'fecha'  => 'required|date',
            'clinic' => 'required|string',
        ]);

        $path = $this->firmaPath($request->clinic, $request->fecha);

        if (Storage::disk('public')->exists($path)) {
            return response()->json(['success' => true, 'dataUrl' => null]);
        }

        $bin = Storage::disk('public')->get($path);
        $b64 = base64_encode($bin);

        return response()->json([
            'success' => true,
            'dataUrl' => 'data:image/png;base64,' . $b64,
        ]);
    }


    public function loadTotals(Request $request)
    {
        // === Equivalente a $_POST ===
        $fechaSeleccionada  = $request->input('fecha');
        $clinicSeleccionada = $request->input('clinic');

        $success = "false";

        if (!$fechaSeleccionada || !$clinicSeleccionada) {
            return response()->json([
                'success' => 'false',
                'totals'  => []
            ]);
        }

        // === Normalizar fecha (igual que PHP original) ===
        $fechaFormato = date('Y-m-d', strtotime($fechaSeleccionada));

        $responseDebug = [
            "fecha_recibida" => $fechaSeleccionada,
            "formato_fecha"  => $fechaFormato
        ];

        $totals = [];

        /* =====================================================
           TOTALES PX  (status = 1)
        ===================================================== */
        $pxTotals = DB::table('sa_info_payment_px as p')
            ->select(
                'p.method as metodo_de_pago',
                DB::raw('SUM(p.amount) as total_importe')
            )
            ->whereDate('p.payment_date', $fechaFormato)
            ->where('p.clinic', $clinicSeleccionada)
            ->where('p.status', 1)
            ->groupBy('p.method')
            ->get();

        foreach ($pxTotals as $row) {
            $metodoPago   = $row->metodo_de_pago ?? 'NA';
            $totalImporte = (float) ($row->total_importe ?? 0);

            if (isset($totals[$metodoPago])) {
                $totals[$metodoPago] += $totalImporte;
            } else {
                $totals[$metodoPago] = $totalImporte;
            }

            $success = "true";
        }

        /* =====================================================
           TOTALES TREATMENTS
        ===================================================== */
        $treatmentTotals = DB::table('sa_info_payment_treatments as t')
            ->select(
                't.method as metodo_de_pago',
                DB::raw('SUM(t.amount) as total_importe')
            )
            ->whereDate('t.payment_date', $fechaFormato)
            ->where('t.clinic', $clinicSeleccionada)
            ->groupBy('t.method')
            ->get();

        foreach ($treatmentTotals as $row) {
            $metodoPago   = $row->metodo_de_pago ?? 'NA';
            $totalImporte = (float) ($row->total_importe ?? 0);

            if (isset($totals[$metodoPago])) {
                $totals[$metodoPago] += $totalImporte;
            } else {
                $totals[$metodoPago] = $totalImporte;
            }

            $success = "true";
        }

        /* =====================================================
           FORMATO FINAL (IGUAL AL PHP ORIGINAL)
        ===================================================== */
        $combinedTotals = [];

        foreach ($totals as $metodoPago => $totalImporte) {
            $combinedTotals[] = [
                "metodo_de_pago" => $metodoPago,
                "total_importe"  => $totalImporte
            ];
        }

        return response()->json([
            "success"       => $success,
            "fecha"         => $responseDebug['fecha_recibida'],
            "formato_fecha" => $responseDebug['formato_fecha'],
            "clinica"       => $clinicSeleccionada,
            "totals"        => $combinedTotals
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function loadDaily(Request $request)
    {
        $fecha    = $request->input('fecha');
        $clinic   = $request->input('clinic');
        $terminal = $request->input('terminal') ?: null; // ✅ normalizado

        if (!$fecha || !$clinic) {
            return response()->json([
                'success' => false,
                'data' => [],
                'totales' => [],
                'total_cierre' => 0
            ]);
        }

        /* ===============================
       TOTALES BASE
    =============================== */
        $totales = [
            'Efectivo' => 0,
            'Dólares' => 0,
            'Tarjeta' => 0,
            'Depósito' => 0,
            'Transferencia' => 0,
            'Otro' => 0,
            'Enlace digital' => 0,
            'TDC' => 0,
            'TDD' => 0,
        ];

        $data = [];
        \Log::info('REQUEST ALL', $request->all());
        \Log::info('HEADERS', $request->headers->all());

        /* =====================================================
       PAGOS PX (CLIENTES)
    ===================================================== */
        $px = DB::table('sa_info_payment_px as p')
            ->leftJoin('sa_leads as l', 'p.lead_id', '=', 'l.id')
            ->whereBetween('p.payment_date', [
                $fecha . ' 00:00:00',
                $fecha . ' 23:59:59'
            ])
            ->where('p.clinic', $clinic)
            ->where('p.status', 1)
            ->when($terminal, fn($q) => $q->where('p.terminal', $terminal))
            ->orderByDesc('p.payment_date')
            ->get([
                'p.id',
                DB::raw("DATE(p.payment_date) as fecha"),
                DB::raw("CONCAT(l.first_name,' ',l.last_name) as nombre"),
                'l.id as lead_id',
                'p.public_notes',
                'p.type',
                'p.amount',
                DB::raw('COALESCE(p.conversion,0) as conversion'),
                DB::raw('COALESCE(p.amount_conversion,0) as amount_conversion'),
                'p.method',
                'p.clinic',
                'p.terminal',
            ]);

        foreach ($px as $row) {

            $metodosNotas = $this->obtenerMetodosDesdeNotas($row->public_notes);
            $cantidad = count($metodosNotas);

            // 🔢 Totales
            if ($cantidad > 0) {
                foreach ($metodosNotas as $m => $v) {
                    $totales[$m] = ($totales[$m] ?? 0) + $v;
                }
            } else {
                $metodo = trim($row->method ?? 'Otro');
                $totales[$metodo] = ($totales[$metodo] ?? 0) + $row->amount;
            }

            // 💵 Importe visible
            if ($cantidad === 0) {
                $importeMostrar = $row->amount;
            } elseif ($cantidad === 1) {
                $importeMostrar = array_values($metodosNotas)[0];
            } else {
                $importeMostrar = null;
            }

            $clienteUrl = url("/panel/leads/{$row->lead_id}");
            $pdfUrl = url("storage/leads/{$row->lead_id}/receipts/{$row->type}{$row->id}.pdf");

            $data[] = [
                'id' => $row->id,
                'fecha' => $row->fecha,
                'nombre' => "
                <a href='{$clienteUrl}' target='_blank'
                   class='font-semibold text-emerald-600 hover:text-emerald-800 transition'>
                    {$row->nombre}
                </a>",
                'concepto' => $row->public_notes,
                'tipo' => $row->type,
                'importe' => $importeMostrar,
                'conversion' => $row->conversion,
                'amount_conversion' => $row->amount_conversion,
                'metodo_de_pago' => $row->method,
                'sucursal' => $row->clinic,
                'terminal' => ucfirst($row->terminal ?? 'N/A'),
                'options' => "
                <div class='flex justify-center gap-2'>
                    <a href='{$pdfUrl}' target='_blank'
                       class='text-red-600 hover:text-red-800 transition'>
                        <i class='fas fa-file-pdf'></i>
                    </a>
                    <button
                        type='button'
                        onclick=\"openEditTerminal({id: {$row->id}, source: 'payment_px', terminal: '" . ($row->terminal ?? '') . "'})\"
                        class='text-blue-600 hover:text-blue-800 transition'>
                        <i class='fas fa-edit'></i>
                    </button>
                </div>",
                'source' => 'payment_px'
            ];
        }

        /* =====================================================
       PAGOS TREATMENTS
    ===================================================== */
        $treatments = DB::table('sa_info_payment_treatments as t')
            ->leftJoin('enf_treatments as e', 't.px_id', '=', 'e.id')
            ->whereBetween('t.payment_date', [
                $fecha . ' 00:00:00',
                $fecha . ' 23:59:59'
            ])
            ->where('t.clinic', $clinic)
            ->when($terminal, fn($q) => $q->where('t.terminal', $terminal))
            ->orderByDesc('t.payment_date')
            ->get([
                't.id',
                DB::raw("DATE(t.payment_date) as fecha"),
                'e.name as nombre',
                't.public_notes',
                't.type',
                't.amount',
                DB::raw('COALESCE(t.conversion,0) as conversion'),
                DB::raw('COALESCE(t.amount_conversion,0) as amount_conversion'),
                't.method',
                't.clinic',
                't.px_id',
                't.terminal',
            ]);

        foreach ($treatments as $row) {

            $metodosNotas = $this->obtenerMetodosDesdeNotas($row->public_notes);
            $cantidad = count($metodosNotas);

            if ($cantidad > 0) {
                foreach ($metodosNotas as $m => $v) {
                    $totales[$m] = ($totales[$m] ?? 0) + $v;
                }
            } else {
                $metodo = trim($row->method ?? 'Otro');
                $totales[$metodo] = ($totales[$metodo] ?? 0) + $row->amount;
            }

            if ($cantidad === 0) {
                $importeMostrar = $row->amount;
            } elseif ($cantidad === 1) {
                $importeMostrar = array_values($metodosNotas)[0];
            } else {
                $importeMostrar = null;
            }

            $tratamientoUrl = url("/panel/tratamientos/sin-expediente/{$row->px_id}");
            $pdfUrl = url("/panel/receipts/treatment/{$row->id}/pdf");

            $data[] = [
                'id' => $row->id,
                'fecha' => $row->fecha,
                'nombre' => "
                <a href='{$tratamientoUrl}' target='_blank'
                   class='font-semibold text-emerald-600 hover:text-emerald-800 transition'>
                    {$row->nombre}
                </a>",
                'concepto' => $row->public_notes,
                'tipo' => $row->type,
                'importe' => $importeMostrar,
                'conversion' => $row->conversion,
                'amount_conversion' => $row->amount_conversion,
                'metodo_de_pago' => $row->method,
                'sucursal' => $row->clinic,
                'terminal' => ucfirst($row->terminal ?? 'N/A'),
                'options' => "
                <div class='flex justify-center gap-2'>
                    <a href='{$pdfUrl}' target='_blank'
                       class='text-red-600 hover:text-red-800 transition'>
                        <i class='fas fa-file-pdf'></i>
                    </a>
                    <button
                        type='button'
                        onclick=\"openEditTerminal({id: {$row->id}, source: 'payment_treatments', terminal: '" . ($row->terminal ?? '') . "'})\"
                        class='text-blue-600 hover:text-blue-800 transition'>
                        <i class='fas fa-edit'></i>
                    </button>
                </div>",
                'source' => 'payment_treatments'
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'totales' => $totales,
            'total_cierre' => array_sum($totales),
        ]);
    }

    private function obtenerMetodosDesdeNotas(?string $notes): array
    {
        if (!$notes) return [];

        $notes = strip_tags($notes);

        preg_match_all(
            '/([A-Za-zÁÉÍÓÚáéíóú\s]+):\s*\$([\d,]+\.\d{2})/',
            $notes,
            $matches,
            PREG_SET_ORDER
        );

        $out = [];

        foreach ($matches as $m) {
            $metodo = trim($m[1]);
            $monto  = (float) str_replace(',', '', $m[2]);

            if ($monto > 0) {
                $out[$metodo] = $monto;
            }
        }

        return $out; // ['Efectivo'=>1000,'TDC'=>1555]
    }


    public function deleteExpense(Request $request)
    {
        $recordId    = $request->input('id');
        $typePayment = $request->input('type'); // 👈 CLAVE CORREGIDA

        if (!$recordId || !$typePayment) {
            return response()->json([
                'success' => false,
                'message' => 'ID o tipo de pago no proporcionados.'
            ], 400);
        }

        try {

            if ($typePayment === 'payment_treatments') {

                $deleted = DB::table('sa_info_payment_treatments')
                    ->where('id', $recordId)
                    ->delete();
            } elseif ($typePayment === 'payment_px') {

                $deleted = DB::table('sa_info_payment_px')
                    ->where('id', $recordId)
                    ->delete();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de pago no válido.'
                ], 400);
            }

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registro eliminado correctamente.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se encontró el registro.'
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el registro.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function updateTerminal(Request $request)
    {
        abort_unless(Auth::user()->hasRole('super_usuario'), 403);

        $request->validate([
            'id' => 'required|integer',
            'source' => 'required|string',
            'terminal' => 'required|in:moral,fisica'
        ]);

        $table = $request->source === 'payment_px'
            ? 'sa_info_payment_px'
            : 'sa_info_payment_treatments';

        DB::table($table)
            ->where('id', $request->id)
            ->update(['terminal' => $request->terminal]);

        return response()->json(['success' => true]);
    }


    public function generateDailyCorte(Request $request)
    {
        $request->validate([
            'tableData' => 'required|string',
            'fecha'     => 'required|date',
            'clinic'    => 'required|string',
            'firma'     => 'required|string',
                'terminal'  => 'nullable|in:fisica,moral',

        ]);

        /* =========================
       USUARIO
    ========================= */
        $user = Auth::user();
        $userName = $user?->name ?? 'Usuario desconocido';
$terminal = $request->input('terminal'); // fisica | moral | null

        /* =========================
       FIRMA: dataURL -> PNG temporal
    ========================= */
        $firmaDataUrl = (string) $request->input('firma');

        if (!$firmaDataUrl || !Str::startsWith($firmaDataUrl, 'data:image')) {
            return response()->json([
                'success' => false,
                'message' => 'Firma inválida o vacía.',
            ], 422);
        }

        // Separar "data:image/png;base64," del contenido
        [$meta, $content] = array_pad(explode(',', $firmaDataUrl, 2), 2, null);

        if (!$content) {
            return response()->json([
                'success' => false,
                'message' => 'Firma inválida (sin contenido base64).',
            ], 422);
        }

        $bin = base64_decode($content, true);
        if ($bin === false) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo decodificar la firma.',
            ], 422);
        }

        $tmpSig = sys_get_temp_dir() . '/firma_' . uniqid() . '.png';
        try {
            file_put_contents($tmpSig, $bin);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo crear el archivo temporal de firma: ' . $e->getMessage(),
            ], 500);
        }

        /* =========================
       DECODIFICAR TOTALES
    ========================= */
        $tableData = json_decode($request->tableData, true);

        if (!is_array($tableData) || !isset($tableData[0]) || count($tableData[0]) < 9) {
            @unlink($tmpSig);
            return response()->json([
                'success' => false,
                'message' => 'Formato de totales inválido',
            ], 422);
        }

        $clean = fn($v) => (float) str_replace(['$', ','], '', (string) $v);

        [
            $efectivo,
            $dolares,
            $tarjeta,        // (si lo mandas, no lo usas directo)
            $deposito,
            $transferencia,
            $otro,
            $enlace,
            $credito,
            $debito
        ] = array_map($clean, $tableData[0]);

        $tarjetaTotal       = $credito + $debito;

        $totalCierre =
            $efectivo +
            $dolares +
            $deposito +
            $transferencia +
            $enlace +
            $otro +
            $tarjetaTotal;

        /* =========================
       GENERAR PDF
    ========================= */
        try {
            $mpdf = new \Mpdf\Mpdf([
                'mode'   => 'utf-8',
                'format' => [215.9, 140]
            ]);
/* =========================
   TEXTO SEGÚN TERMINAL
========================= */
$terminalTexto = 'Corte general (Persona Física y Moral)';

if ($terminal === 'fisica') {
    $terminalTexto = 'Corte correspondiente a Persona Física';
} elseif ($terminal === 'moral') {
    $terminalTexto = 'Corte correspondiente a Persona Moral';
}

            $template = resource_path('pdf_templates/corte_de_caja_diario.pdf');
            $mpdf->SetSourceFile($template);
            $tplId = $mpdf->ImportPage(1);
            $mpdf->UseTemplate($tplId);

            $mpdf->SetFont('Arial', 'B', 11);

            $fechaTexto = date('d/m/Y', strtotime($request->fecha));

            // TEXTOS

            // TEXTOS

// 👇 TEXTO DEL FILTRO
$mpdf->SetFont('Arial', 'B', 11);
$mpdf->WriteText(80, 18, $terminalTexto);

// regresar a fuente normal
$mpdf->SetFont('Arial', 'B', 11);

            $mpdf->WriteText(170, 25.5, $fechaTexto);
            $mpdf->WriteText(28, 132, $request->clinic);

            // ✅ FIRMA (ARRIBA) + NOMBRE (ABAJO)
            // Ajusta X/Y/W/H si quieres moverla
            $mpdf->Image($tmpSig, 138, 91, 55, 18, 'png');
            $mpdf->WriteText(48, 44, $userName);

            // MONTOS
            $mpdf->WriteText(58, 56, number_format($efectivo, 2));
            $mpdf->WriteText(58, 72.7, number_format($dolares, 2));
            $mpdf->WriteText(58, 89.5, number_format($tarjetaTotal, 2));
            $mpdf->WriteText(58, 106.5, number_format($deposito, 2));
            $mpdf->WriteText(58, 122.3, number_format($transferencia, 2));
            $mpdf->WriteText(150, 56.6, number_format($otro, 2));
            $mpdf->WriteText(150, 89.7, number_format($totalCierre, 2));

            // ✅ nombre de archivo seguro
            $clinicSafe = Str::slug($request->clinic, '_');
            $fileName = 'corte_caja_' . $clinicSafe . '_' . date('dmy', strtotime($request->fecha)) . '.pdf';

            $path = public_path('/storage/cortes/' . $fileName);

            if (!File::exists(dirname($path))) {
                File::makeDirectory(dirname($path), 0755, true);
            }

            $mpdf->Output($path, 'F');
        } catch (\Throwable $e) {
            @unlink($tmpSig);
            return response()->json([
                'success' => false,
                'message' => 'Error generando PDF: ' . $e->getMessage(),
            ], 500);
        } finally {
            @unlink($tmpSig); // ✅ limpiar temporal sí o sí
        }

        return response()->json([
            'success'     => true,
            'message'     => 'Corte generado correctamente',
            'path'        => asset('storage/cortes/' . $fileName),
            'total'       => number_format($totalCierre, 2),
            'generatedBy' => $userName,
        ]);
    }

    public function getDailyCorteIfExists(Request $request)
    {
        $request->validate([
            'fecha'  => 'required|date',
            'clinic' => 'required|string',
        ]);

        $fileName = 'corte_caja_' . $request->clinic . '_' . date('dmy', strtotime($request->fecha)) . '.pdf';
        $path = public_path('/storage/cortes/' . $fileName);

        if (!file_exists($path)) {
            return response()->json([
                'exists' => false
            ]);
        }

        return response()->json([
            'exists' => true,
            'path'   => asset('storage/cortes/' . $fileName),
            // si luego lo guardas en BD, aquí puedes regresar el usuario real
            'generatedBy' => 'Usuario registrado'
        ]);
    }
}
