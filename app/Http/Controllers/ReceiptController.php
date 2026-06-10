<?php

namespace App\Http\Controllers;

use App\Models\ClosedPx;
use App\Models\Movimiento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

use App\Models\Receipt;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReceiptController extends Controller
{

    protected function registrarMovimiento($tipo, $descripcion, $tabla, $registro_id = null)
    {
        Movimiento::create([
            'usuario_id' => Auth::id(),
            'tipo_movimiento' => $tipo,
            'descripcion' => $descripcion,
            'tabla_afectada' => $tabla,
            'registro_id' => $registro_id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // Crear recibo


    public function storerprodu(Request $request)
    {
        $request->validate([
            'lead_id'        => 'required|integer',
            'patient_name'  => 'required|string',
            'product'       => 'required|string',
            'amount'        => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'clinic'        => 'required|string',
            'receipt_date'  => 'required|date',
            'advance_amount_dls' => 'nullable|numeric|min:0',
            'price_dls'     => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string',
        ]);

        /* =============================
       CÁLCULO MONTO (USD)
    ============================= */
        $amount = $request->amount;
        $conversion = null;
        $amount_conversion = null;

        if (
            $request->payment_method === 'Dólares' &&
            $request->price_dls > 0 &&
            $request->advance_amount_dls > 0
        ) {
            $conversion = $request->price_dls;
            $amount_conversion = $request->advance_amount_dls;
            $amount = $conversion * $amount_conversion;
        }

        /* =============================
       INSERT BD
    ============================= */
        $receipt = Receipt::create([
            'lead_id'           => $request->lead_id,
            'type'              => 'producto',
            'amount'            => $amount,
            'conversion'        => $conversion,
            'amount_conversion' => $amount_conversion,
            'method'            => $request->payment_method,
            'payment_date'      => $request->receipt_date,
            'receipt_date'      => $request->receipt_date,
            'public_notes'      => $request->notes,
            'clinic'            => $request->clinic,
            'uploaded_by'       => auth()->id(),
            'status'            => 1,
        ]);

        /* =============================
       REGISTRAR MOVIMIENTO
    ============================= */
        $this->registrarMovimiento(
            'crear',
            'Generó recibo de producto',
            'receipts',
            $receipt->id
        );

        /* =============================
       PLANTILLA PDF
    ============================= */
        $template = resource_path("pdf_templates/producto-{$request->clinic}.pdf");

        if (!file_exists($template)) {
            $template = resource_path('pdf_templates/producto-default.pdf');
        }

        /* =============================
       PDF
    ============================= */
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'Letter',
            'orientation' => 'L'
        ]);

        $tplId = $mpdf->ImportPage($mpdf->SetSourceFile($template));
        $mpdf->UseTemplate($tplId);
        $mpdf->SetFont('dejavusans', '', 10);

        $date = Carbon::parse($request->receipt_date)->format('d/m/Y');
        $amountFormatted = '$' . number_format($amount, 2, '.', ',');

        foreach ([0, 145] as $offset) {
            $mpdf->WriteText(32 + $offset, 71.8, $request->patient_name);
            $mpdf->WriteText(140 + $offset, 49.5, $date);
            $mpdf->WriteText(32 + $offset, 86.5, $request->product);
            $mpdf->WriteText(41.2 + $offset, 122.8, $amountFormatted);

            foreach (explode("\n", $request->notes ?? '') as $i => $line) {
                $mpdf->WriteText(32 + $offset, 187.5 + ($i * 5.5), trim($line));
            }

            $this->markProductoPayment($mpdf, $request->payment_method, $offset);
        }

        /* =============================
       GUARDAR PDF (PUBLIC)
    ============================= */
        $publicDir = public_path("storage/leads/{$request->lead_id}/receipts");

        if (!file_exists($publicDir)) {
            mkdir($publicDir, 0775, true);
        }

        $pdfName = "producto_{$receipt->id}.pdf";
        $pdfPath = $publicDir . DIRECTORY_SEPARATOR . $pdfName;

        $mpdf->Output($pdfPath, 'F');

        /* =============================
       RESPUESTA
    ============================= */
        return response()->file(
            $pdfPath,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $pdfName . '"'
            ]
        );
    }

    public function store(Request $request)
{
    $request->validate([
        'lead_id'            => 'required|integer',
        'clinic'             => 'required|string',
        'patient_name'       => 'required|string',
        'procedure_type'     => 'required|string',
        'receipt_date'       => 'required|date',
        'settlement_date'    => 'required|date',

        // 🔑 TOTALES
        'total_amount'       => 'required|numeric|min:0',
        'settlement_amount'  => 'required|numeric|min:0',

        // 🟢 ANTICIPO
        'advance_amount'          => 'nullable|numeric|min:0',
        'advance_date'            => 'nullable|date',
        'advance_payment_method'  => 'nullable|string',

        // 🟡 MÉTODOS DE PAGO
        'payment_method_1'        => 'required|string',
        'amount_1'                => 'nullable|numeric|min:0',
        'payment_method_2'        => 'nullable|string',
        'amount_2'                => 'nullable|numeric|min:0',
        'settlement_payment_method' => 'nullable|string',

        // 📎 OTROS
        'receipt_file'       => 'required|file|mimes:pdf,jpg,jpeg,png',
        'vendedor'           => 'nullable|string',
        'bank_or_method'     => 'nullable|string',
        'public_notes'       => 'nullable|string',
    ]);

    /* =============================
     1. CREAR CARPETA
    ============================== */
    $publicDir = public_path("storage/leads/{$request->lead_id}/receipts");
    if (!file_exists($publicDir)) {
        mkdir($publicDir, 0755, true);
    }

    /* =============================
     2. GUARDAR COMPROBANTE
    ============================== */
    $receiptFile = $request->file('receipt_file');
    $receiptName = 'receipt_' . time() . '.' . $receiptFile->getClientOriginalExtension();
    $receiptFile->move($publicDir, $receiptName);
    $receiptPath = "storage/leads/{$request->lead_id}/receipts/{$receiptName}";

    /* =============================
     3. MÉTODO FINAL (1 o 2)
    ============================== */
    $finalMethod = $request->settlement_payment_method
        ?? $request->payment_method_1;

    /* =============================
     4. ARMAR NOTAS CON DESGLOSE
    ============================== */
    $methodsNotes = [];

    $methodsNotes[] = "{$request->payment_method_1}: $" .
        number_format($request->amount_1 ?? 0, 2);

    if ($request->payment_method_2 && $request->amount_2 > 0) {
        $methodsNotes[] = "{$request->payment_method_2}: $" .
            number_format($request->amount_2, 2);
    }

    $fullNotes = trim(
        ($request->public_notes ?? '') .
        "\n\nMétodos de pago:\n" .
        implode("\n", $methodsNotes)
    );

    /* =============================
     5. INSERTAR PAGO
    ============================== */
    $paymentId = DB::table('sa_info_payment_px')->insertGetId([
        'lead_id'        => $request->lead_id,
        'type'           => 'liquidacion',
        'amount'         => $request->settlement_amount,
        'method'         => $finalMethod,
        'payment_date'   => $request->settlement_date,
        'receipt_date'   => $request->receipt_date,
        'public_notes'   => nl2br($fullNotes),
        'clinic'         => $request->clinic,
        'uploaded_by'    => auth()->id(),
        'status'         => 1,
        'vendedor'       => $request->vendedor,
        'file_path'      => $receiptPath,
        'bank_or_method' => $request->bank_or_method,
        'created_at'     => now(),
    ]);

    /* =============================
     6. PLANTILLA PDF
    ============================== */
    $template = match ($request->clinic) {
        'Pedregal'  => resource_path('pdf_templates/pago_pedregal.pdf'),
        'Santa Fe'  => resource_path('pdf_templates/pago-santa-fe.pdf'),
        'Queretaro' => resource_path('pdf_templates/pago_queretaro.pdf'),
        default     => resource_path('pdf_templates/pago-default.pdf'),
    };

    $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'Letter']);
    $mpdf->SetTitle("Liquidacion_{$paymentId}");

    $pagecount = $mpdf->SetSourceFile($template);
    $tplId = $mpdf->ImportPage($pagecount);
    $mpdf->UseTemplate($tplId);
    $mpdf->SetFont('Helvetica', 'B', 12);

    /* =============================
     7. PDF – DATOS GENERALES
    ============================== */

    // FECHA
    $mpdf->WriteText(
        155,
        ($request->clinic === 'Queretaro') ? 46.79 : 50.79,
        \Carbon\Carbon::parse($request->receipt_date)->format('d/m/Y')
    );

    // NOMBRE
    $mpdf->WriteText(45.90, 72.34, $request->patient_name);

    // COSTO TOTAL
    $mpdf->WriteText(60.90, 85, '$' . number_format($request->total_amount, 2));

    // TIPO PROCEDIMIENTO
    if ($request->procedure_type === 'Capilar') {
        $mpdf->WriteText(134.3, 83, 'X');
    } elseif ($request->procedure_type === 'Barba') {
        $mpdf->WriteText(181.9, 83, 'X');
    } else {
        $mpdf->WriteText(134.3, 83, 'X');
        $mpdf->WriteText(183.9, 85, 'X');
    }

    /* =============================
     8. ANTICIPO
    ============================== */
    if ($request->advance_amount > 0) {
        $mpdf->WriteText(38.56, 117.74, '$' . number_format($request->advance_amount, 2));
        $mpdf->WriteText(97.80, 117.74, $request->advance_payment_method);
        $mpdf->WriteText(
            150.56,
            117.74,
            \Carbon\Carbon::parse($request->advance_date)->format('d/m/Y')
        );
    }

    /* =============================
     9. LIQUIDACIÓN
    ============================== */
    $mpdf->WriteText(38.56, 147.74, '$' . number_format($request->settlement_amount, 2));
    $mpdf->WriteText(89.80, 147.74, $finalMethod);
    $mpdf->WriteText(
        150.56,
        147.74,
        \Carbon\Carbon::parse($request->settlement_date)->format('d/m/Y')
    );

    /* =============================
     10. NOTAS PDF
    ============================== */
    $notes = explode("\n", strip_tags($fullNotes));
    $mpdf->WriteText(33.85, 181.3, $notes[0] ?? '');
    $mpdf->WriteText(33.85, 187.28, $notes[1] ?? '');
    $mpdf->WriteText(33.85, 194, $notes[2] ?? '');

    // VENDEDOR
    $mpdf->WriteText(100, 202, $request->vendedor);

    /* =============================
     11. GUARDAR PDF
    ============================== */
    $pdfName = "liquidacion_{$paymentId}.pdf";
    $pdfContent = $mpdf->Output('', 'S');
    file_put_contents($publicDir . '/' . $pdfName, $pdfContent);

    /* =============================
     12. RESPUESTA
    ============================== */
    return response()->json([
        'success' => true,
        'pdf_url' => asset("storage/leads/{$request->lead_id}/receipts/{$pdfName}")
    ]);
}


public function storeproduc(Request $request)
{
    /* ============================================================
     VALIDACIÓN
    ============================================================ */
    $request->validate([
        'lead_id'        => 'required|integer',
        'patient_name'   => 'required|string',
        'product'        => 'required|string',
        'amount'         => 'required|numeric|min:0',
        'payment_method' => 'required|string',
        'clinic'         => 'required|string',
        'receipt_date'   => 'required|date',
        'advance_amount_dls' => 'nullable|numeric|min:0',
        'price_dls'      => 'nullable|numeric|min:0',
        'notes'          => 'nullable|string',
    ]);
/* ============================================================
   NORMALIZAR MÉTODO DE PAGO
============================================================ */

$paymentMethod = trim($request->payment_method);

$paymentMethod = match (strtolower($paymentMethod)) {
    'usd', 'dls', 'dolares', 'dólares', 'dollar', 'dollars' => 'Dólares',
    default => $request->payment_method,
};

   /* ============================================================
   CONVERSIÓN DÓLARES → MXN
============================================================ */

$amount = $request->amount;
$conversion = null;
$amount_conversion = null;

if (
    $paymentMethod === 'Dólares' &&
    $request->price_dls > 0 &&
    $request->advance_amount_dls > 0
) {
    $conversion = $request->price_dls;          // tipo de cambio
    $amount_conversion = $request->advance_amount_dls; // dólares recibidos
    $amount = $conversion * $amount_conversion; // MXN final
}

    /* ============================================================
     DB
    ============================================================ */
    $paymentId = DB::table('sa_info_payment_px')->insertGetId([
        'lead_id'           => $request->lead_id,
        'type'              => 'producto',
        'amount'            => $amount,
        'method'            => $request->payment_method,
        'payment_date'      => $request->receipt_date,
        'receipt_date'      => $request->receipt_date,
        'public_notes'      => nl2br($request->notes),
        'clinic'            => $request->clinic,
        'uploaded_by'       => auth()->id(),
        'status'            => 1,
        'conversion'        => $conversion,
        'amount_conversion' => $amount_conversion,
        'created_at'        => now(),
    ]);

    /* ============================================================
     MPDF
    ============================================================ */
    $mpdf = new Mpdf([
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
    $template = resource_path("pdf_templates/producto-{$request->clinic}.pdf");
    if (!file_exists($template)) {
        $template = resource_path('pdf_templates/producto-default.pdf');
    }

    $mpdf->SetSourceFile($template);
    $tpl = $mpdf->ImportPage(1);
    $mpdf->UseTemplate($tpl);

    /* ============================================================
     HELPERS ABSOLUTOS (CLAVE)
    ============================================================ */
    $left  = fn(float $x, float $y, string $t) => $mpdf->WriteText($x, $y, $t);
    $right = fn(float $x, float $y, string $t) => $mpdf->WriteText($x, $y, $t);

    $date = Carbon::parse($request->receipt_date)->format('d/m/Y');
    $amountFormatted = '$' . number_format($amount, 2);

    /* ============================================================
     DATOS GENERALES
    ============================================================ */
    // IZQUIERDA
    $mpdf->SetFont('Helvetica', 'B', 10);
    $left(20.0, 55, $request->patient_name);

    $mpdf->SetFont('Helvetica', '', 9);
    $left(90.0, 40, $date);

    $mpdf->SetFont('Helvetica', '', 10);
    $left(20.0, 63, $request->product);

    $mpdf->SetFont('Helvetica', 'B', 11);
    $left(25, 84.8, $amountFormatted);

    // DERECHA
    $mpdf->SetFont('Helvetica', 'B', 10);
    $right(155.0, 66.0, $request->patient_name);

    $mpdf->SetFont('Helvetica', '', 9);
    $right(205.0, 54, $date);

    $mpdf->SetFont('Helvetica', '', 10);
    $right(160.0, 75.5, $request->product);

    $mpdf->SetFont('Helvetica', 'B', 11);
    $right(153.2, 95.8, $amountFormatted);

    /* ============================================================
     MÉTODO DE PAGO (MAPA LIMPIO)
    ============================================================ */
    $payMap = [
        'efectivo' => ['left' => 85.5, 'right' => 95.5],
            'transferencia' => ['left' => 89.5, 'right' => 100.5],
            'tarjeta de débito' => ['left' => 95.5, 'right' => 106.5],
            'tarjeta de crédito' => ['left' => 101.5, 'right' => 111.5],
            'dólares' => ['left' => 106.5, 'right' => 118.0],
        'dolares' => ['left' => 106.5, 'right' => 118.0],
             'deposito' => ['left' => 112.5, 'right' => 124.5],
            'depósito' => ['left' => 112.5, 'right' => 124.5],
    ];

    $methodKey = strtolower(trim($request->payment_method));

    if (isset($payMap[$methodKey])) {
        $mpdf->SetFont('Helvetica', 'B', 10);
        $left(53,  $payMap[$methodKey]['left'], 'X');
$right(180.0, $payMap[$methodKey]['right'], 'X');

    }

    /* ============================================================
     NOTAS (INDEPENDIENTES)
    ============================================================ */
    $notes = [
        "Producto: {$request->product}",
        "Pago: {$request->payment_method} {$amountFormatted}",
    ];

    if ($request->notes) {
        $notes[] = trim($request->notes);
    }

    foreach ($notes as $i => $note) {
        $left(32.0,  125.5 + ($i * 5.5), $note);
        $right(177.0, 135.5 + ($i * 5.5), $note);
    }

    /* ============================================================
     GUARDAR PDF
    ============================================================ */
    $dir = public_path("storage/leads/{$request->lead_id}/receipts");
    if (!file_exists($dir)) mkdir($dir, 0775, true);

    $file = "producto_{$paymentId}.pdf";
   $mpdf->Output("{$dir}/{$file}", 'F');

// abrir el PDF en el navegador
return response()->file(
    "{$dir}/{$file}",
    ['Content-Type' => 'application/pdf']
);

}
    public function storeAnticipo(Request $request)
    {
        try {

            /* ===============================
           VALIDACIÓN
        =============================== */
            $request->validate([
                'lead_id'            => 'required|integer',
                'clinic'             => 'required|string',
                'receipt_date'       => 'required|date',
                'patient_name'       => 'required|string',
                'procedure_type'     => 'required|string',
                'payment_date'       => 'required|date',
               
                'advance_amount'     => 'required|numeric',
                'total_amount'       => 'required|numeric',
                'pending_amount'     => 'required|numeric',
                'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
                'public_notes'       => 'nullable|string',
                'advance_amount_dls' => 'nullable|numeric',
                'price_dls'          => 'nullable|numeric',
                'vendedor'           => 'nullable|string',
                'ingles'             => 'nullable|boolean',

                  // ✅ métodos (igual que liquidación)
                'payment_method_1' => 'required|string',
                'amount_1'         => 'required|numeric|min:0',
                'payment_method_2' => 'nullable|string',
                'amount_2'         => 'nullable|numeric|min:0',

            ]);

            if (!$request->hasFile('receipt_file')) {
                throw new \Exception('No se recibió el archivo receipt_file');
            }

            /* ===============================
           DATOS BASE
        =============================== */
            $leadId = (int) $request->lead_id;
            $clinic = $request->clinic;
            $userId = auth()->id();

            $procedure_type = $request->procedure_type;
            $receiptDate   = Carbon::parse($request->receipt_date)->format('d/m/Y');
            $advanceDate   = Carbon::parse($request->payment_date)->format('d/m/Y');
            $procedureDate = $request->procedure_date
                ? Carbon::parse($request->procedure_date)->format('d/m/Y')
                : 'Por definir';
            $vededor = $request->vendedor ?? '';
            $advanceAmount = (float) $request->advance_amount;
            $totalAmount   = (float) $request->total_amount;
            $pendingAmount = (float) $request->pending_amount;

           $amount1 = (float) $request->amount_1;
            $amount2 = (float) ($request->amount_2 ?? 0);
$notes = $request->public_notes ?? '';
            $hasSecond = $request->filled('payment_method_2') && $amount2 > 0;

            $advanceAmount = $amount1 + ($hasSecond ? $amount2 : 0);

            $finalMethod = $hasSecond
            ? ($request->payment_method_1 . ' + ' . $request->payment_method_2)
            : $request->payment_method_1;

            $breakdown = $hasSecond
            ? "Desglose: $" . number_format($amount1,2) . " ({$request->payment_method_1}) + $" . number_format($amount2,2) . " ({$request->payment_method_2})"
            : "Método: {$request->payment_method_1}";


            /* =============================
                ARMAR NOTAS CON DESGLOSE
                ============================= */
                $methodsNotes = [];

                // método 1
                $methodsNotes[] = "{$request->payment_method_1}: $" . number_format($amount1, 2);

                // método 2 (si aplica)
                if ($hasSecond) {
                    $methodsNotes[] = "{$request->payment_method_2}: $" . number_format($amount2, 2);
                }

                // ✅ fullNotes = notas del usuario + bloque de métodos
                $fullNotes = trim(
                    ($request->public_notes ?? '') .
                    "\n\nMétodos de pago:\n" .
                    implode("\n", $methodsNotes)
                );

            /* ===============================
           DIRECTORIO PUBLICO
        =============================== */
            $publicDir = public_path("storage/leads/{$leadId}/receipts");

            if (!is_dir($publicDir)) {
                mkdir($publicDir, 0775, true);
            }

            /* ===============================
           GUARDAR ARCHIVO SUBIDO
        =============================== */
            $receiptFile = $request->file('receipt_file');
            $receiptName = 'receipt_' . time() . '.' . $receiptFile->getClientOriginalExtension();
            $receiptFile->move($publicDir, $receiptName);

            /* ===============================
           INSERT BD
        =============================== */
          $recordId = DB::table('sa_info_payment_px')->insertGetId([
            'lead_id'      => $leadId,
            'type'         => 'anticipo',
            'amount'       => $advanceAmount,
            'method'       => $finalMethod,
            'payment_date' => $request->payment_date,
            'receipt_date' => $request->receipt_date,
            'public_notes' => nl2br(string: $fullNotes),   // ✅ aquí
            'clinic'       => $clinic,
            'created_at'   => now(),
            'uploaded_by'  => $userId,
            'vendedor'     => $request->vendedor,
            'status'       => 1,
            'file_path'    => $receiptName,
            ]);



            /* ===============================
           PLANTILLA PDF (CORREGIDA)
        =============================== */
            $template = $request->boolean('ingles')
                ? match ($clinic) {
                    'Pedregal' => resource_path('pdf_templates/anticipo-pedregal_ingles.pdf'),
                    'Santa Fe' => resource_path('pdf_templates/anticipo-santafe_ingles.pdf'),
                    default    => resource_path('pdf_templates/anticipo-default.pdf'),
                }
                : match ($clinic) {
                    'Pedregal'  => resource_path('pdf_templates/AnticipoPED.pdf'),
                    'Santa Fe'  => resource_path('pdf_templates/AnticipoCDMX.pdf'),
                    'Queretaro' => resource_path('pdf_templates/AnticipoQRO.pdf'),
                    default     => resource_path('pdf_templates/anticipo-default.pdf'),
                };

            if (!file_exists($template)) {
                throw new \Exception("Template no encontrada: {$template}");
            }

            /* ===============================
           GENERAR PDF
        =============================== */
            $pdfName = "anticipo_{$recordId}.pdf";
            $pdfPath = $publicDir . DIRECTORY_SEPARATOR . $pdfName;

            $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'Letter']);
            $mpdf->SetSourceFile($template);
            $tplId = $mpdf->ImportPage(1);
            $mpdf->UseTemplate($tplId);

            $mpdf->SetFont('Arial', 'B', 12);
            $mpdf->WriteText(144, 53.59, $receiptDate);
            $mpdf->WriteText(50.9, 74.34, $request->patient_name);
            if ($procedure_type === 'Capilar') {
                $mpdf->WriteText(136.3, 85, 'X');
            } elseif ($procedure_type === 'Barba') {
                $mpdf->WriteText(183.9, 85, 'X');
            } elseif ($procedure_type === 'Ambos') {
                $mpdf->WriteText(136.3, 85, 'X');
                $mpdf->WriteText(183.9, 85, 'X');
            }
            $mpdf->WriteText(64, 83.67, '$' . number_format($totalAmount, 2));
            $mpdf->WriteText(37.56, 113.74, '$' . number_format($advanceAmount, 2));
            $mpdf->WriteText(82.8, 113.74, $finalMethod);
            $mpdf->WriteText(146.05, 113.74, $advanceDate);
            $mpdf->WriteText(37.56, 149.8, '$' . number_format($pendingAmount, 2));
            $mpdf->WriteText(93, 149.8, $procedureDate);
            $mpdf->WriteText(120, 195, $vededor);
$mpdf->SetXY(27, 170);

$mpdf->WriteHTML(
    nl2br(e($notes))
);
            $mpdf->Output($pdfPath, 'F');

           /* =============================
                NOTAS EN PDF
                ============================= */
                $lines = explode("\n", trim(strip_tags($fullNotes)));
                $y = 172;

                foreach ($lines as $i => $line) {
                    if ($i >= 6) break; // límite por espacio (ajusta si quieres)
                    $mpdf->WriteText(27, $y + ($i * 6), trim($line));
                }



            return response()->json([
                'success' => true,
                'message' => 'Anticipo generado correctamente',
                'path'    => asset("storage/leads/{$leadId}/receipts/{$pdfName}")
            ]);
        } catch (\Throwable $e) {

            Log::error('❌ ERROR storeAnticipo', [
                'lead_id' => $request->lead_id ?? null,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el anticipo. Revisa los logs.'
            ], 500);
        }
    }

    private function markProductoPayment($mpdf, $method, $offset = 0)
    {
        $map = [
            'Efectivo'      => 121,
            'Transferencia' => 129.7,
            'TDD'           => 139.33,
            'TDC'           => 148.2,
            'Dólares'       => 158.4,
            'Depósito'      => 168,
        ];

        if (!isset($map[$method])) return;

        $mpdf->WriteText(89.5 + $offset, $map[$method], 'X');
    }


    // Crear abono
    public function storeAbono(Request $request)
    {
        /* =============================
       LOG INICIAL
    ============================= */
        Log::info('storeAbono: PASO 1');

        /* =============================
       VALIDACIÓN
    ============================= */
        $request->validate([
            'lead_id'        => 'required|integer',
            'patient_name'   => 'required|string',
            'procedure_type' => 'required|string',
            'receipt_date'   => 'required|date',
            'partial_date'   => 'required|date',
            'payment_method' => 'required|string',
            'clinic'         => 'required|string',
            'partial_amount' => 'required|numeric|min:0',
            'advance_amount_dls' => 'nullable|numeric|min:0',
            'price_dls'          => 'nullable|numeric|min:0',
            'receipt_file'       => 'required|file|mimes:pdf,jpg,jpeg,png',
            'public_notes'       => 'nullable|string',
        ]);

        Log::info('storeAbono: PASO 2 validación OK');

        /* =============================
       CONVERSIÓN USD
    ============================= */
        $conversion        = null;
        $amount_conversion = null;
        $amount            = (float) $request->partial_amount;

        if (
            $request->payment_method === 'Dólares' &&
            $request->advance_amount_dls > 0 &&
            $request->price_dls > 0
        ) {
            $conversion        = (float) $request->price_dls;
            $amount_conversion = (float) $request->advance_amount_dls;
            $amount            = $amount_conversion * $conversion;
        }

        /* =============================
       FORMATO FECHAS
    ============================= */
        $receiptDate   = Carbon::parse($request->receipt_date)->format('Y-m-d');
        $partialDate   = Carbon::parse($request->partial_date)->format('Y-m-d');
        $formattedDate = Carbon::parse($request->receipt_date)->format('d/m/Y');
        $InjectDate = Carbon::parse($request->procedure_date)->format('d/m/Y');
        $formattedPartialDate = Carbon::parse($request->partial_date)->format('d/m/Y');

        /* =============================
       SUBIR ARCHIVO
    ============================= */
        $filePath = $request->file('receipt_file')
            ->store("leads/{$request->lead_id}/receipts", 'public');

        /* =============================
       CÁLCULO PAGADO / PENDIENTE
    ============================= */
        $paidBefore = DB::table('sa_info_payment_px')
            ->where('lead_id', $request->lead_id)
            ->where('status', 1)
            ->whereIn('type', ['abono', 'anticipo'])
            ->sum('amount');

        $paidTotal = $paidBefore + $amount;

        $quoted = DB::table('sa_closed_px')
            ->where('lead_id', $request->lead_id)
            ->value('quoted_cc_amount') ?? 0;

        $pending = $quoted - $paidTotal;

        /* =============================
       TRANSACCIÓN
    ============================= */
        DB::beginTransaction();

        try {

            /* =============================
           INSERT RECIBO
        ============================= */
            $receiptId = DB::table('sa_info_payment_px')->insertGetId([
                'lead_id'           => $request->lead_id,
                'type'              => 'abono',
                'amount'            => $amount,
                'conversion'        => $conversion,
                'amount_conversion' => $amount_conversion,
                'method'            => $request->payment_method,
                'payment_date'      => $partialDate,
                'receipt_date'      => $receiptDate,
                'public_notes'      => $request->public_notes,
                'clinic'            => $request->clinic,
                'created_at'        => now(),
                'uploaded_by'       => auth()->id(),
                'status'            => 1,
                'file_path'         => $filePath,
            ]);

            /* =============================
           PDF
        ============================= */
            $template = match ($request->clinic) {
                'Pedregal'  => resource_path('pdf_templates/abonoPedregal.pdf'),
                'Santa Fe'  => resource_path('pdf_templates/AbonoCDMX.pdf'),
                'Queretaro' => resource_path('pdf_templates/AbonoQRO.pdf'),
                default     => resource_path('pdf_templates/abono-default.pdf'),
            };

            $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'Letter', 'default_font' => 'dejavusans']);
            $tplId = $mpdf->ImportPage($mpdf->SetSourceFile($template));
            $mpdf->UseTemplate($tplId);

            /* =============================
           TEXTO PDF
        ============================= */
            $mpdf->WriteText(143, 53.6, $formattedDate);
            $mpdf->WriteText(50.9, 74.3, $request->patient_name);

            // Tipo injerto
            match ($request->procedure_type) {
                'Capilar' => $mpdf->WriteText(136.3, 85, 'X'),
                'Barba'   => $mpdf->WriteText(183.9, 85, 'X'),
                'Ambos'   => [
                    $mpdf->WriteText(136.3, 85, 'X'),
                    $mpdf->WriteText(183.9, 85, 'X'),
                ],
                default => null
            };

            $mpdf->WriteText(38, 113.7, '$' . number_format($amount, 2));
            $mpdf->WriteText(97.8, 113.7, $request->payment_method);

            $mpdf->WriteText(97.8, 149.7, $InjectDate);
            $mpdf->WriteText(97.8, 199.7, $request->seller);


            $mpdf->WriteText(64, 83.7, '$' . number_format($quoted, 2));
            $mpdf->WriteText(37.6, 149.8, '$' . number_format($pending, 2));
            $mpdf->WriteText(147.3, 113.7, $formattedPartialDate);
   $mpdf->SetXY(35, 170);

            $mpdf->WriteHTML(
                nl2br(string: e($request->public_notes))
            );
            /* =============================
           GUARDAR PDF
        ============================= */
            $dir = public_path("storage/leads/{$request->lead_id}/receipts");
            if (!file_exists($dir)) mkdir($dir, 0775, true);

            $file = "abono_{$receiptId}.pdf";
            $path = $dir . '/' . $file;
            $mpdf->Output($path, 'F');

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('storeAbono ERROR', ['e' => $e]);
            abort(500, 'Error al generar el recibo');
        }

        /* =============================
       RESPUESTA
    ============================= */
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'pdf_url' => asset("storage/leads/{$request->lead_id}/receipts/{$file}")
            ]);
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $file . '"'
        ]);
    }


    // -------------- Recibos de tratamientos
public function storeTreatment(Request $request)
{
    /* ============================================================
        NORMALIZAR TIPO
    ============================================================ */
    if (!$request->has('Tipo')) {
        $request->merge([
            'Tipo' => collect(json_decode($request->products, true))
                ->contains(fn($i) => $i['tipo'] === 'Tratamiento')
                ? 'Tratamiento'
                : 'Producto'
        ]);
    }

    /* ============================================================
        VALIDACIÓN
    ============================================================ */
    $request->validate([
        'lead_id'      => 'required|integer',
        'patient_name'=> 'required|string',
        'clinic'       => 'required|string',
        'receipt_date'=> 'required|date',
        'products'     => 'required|string',

        'total_amount' => 'required|numeric|min:0.01',
        'paid_total'   => 'required|numeric|min:0.01',
        'pay_method_1' => 'required|string',
        'total_usd'    => 'nullable|numeric|min:0.01',
    ]);

    /* ============================================================
        VARIABLES
    ============================================================ */
    $leadId = $request->lead_id;
    $clinic = $request->clinic;
    $type   = $request->Tipo; // Producto | Tratamiento
    $dateDB = \Carbon\Carbon::parse($request->receipt_date)->format('Y-m-d');
    $date   = \Carbon\Carbon::parse($request->receipt_date)->format('d/m/Y');
    $userId = auth()->id();

    $cart = json_decode($request->products, true);
    $itemName = collect($cart)->pluck('nombre')->implode(', ');
    $total = (float) $request->total_amount;

    /* ============================================================
        MPDF
    ============================================================ */
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'Letter-L',
        'margin_left' => 5,
        'margin_right'=> 5,
        'margin_top'  => 5,
        'margin_bottom'=>5,
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
        DATOS GENERALES
    ============================================================ */
    $left(18.0, 50.5, $request->patient_name);
    $left(80.0, 42.0, $date);

    $right(145.0, 60.5, $request->patient_name);
    $right(203.0, 53.0, $date);

    /* ============================================================
        TOTAL
    ============================================================ */
    if ($type === 'Producto') {
        $left(20.2, 85.5, number_format($total, 2));
        $right(148.0, 99.0, number_format($total, 2));
    } else {
        $left(41.2, 141.5, number_format($total, 2));
        $right(169.0, 151.5, number_format($total, 2));
    }

    /* ============================================================
        PRODUCTO
    ============================================================ */
    if ($type === 'Producto') {
        $left(20.0, 72.0, $itemName);
        $right(148.0, 82.0, $itemName);
    }

    /* ============================================================
        MÉTODOS DE PAGO
    ============================================================ */
    $payCheckMap = $type === 'Producto'
        ? [
            'efectivo' => ['left' => 85.5, 'right' => 95.5],
            'transferencia' => ['left' => 89.5, 'right' => 100.5],
            'tarjeta de débito' => ['left' => 95.5, 'right' => 106.5],
            'tarjeta de crédito' => ['left' => 101.5, 'right' => 111.5],
            'dólares' => ['left' => 106.5, 'right' => 118.0],
        'dolares' => ['left' => 106.5, 'right' => 118.0],
             'deposito' => ['left' => 112.5, 'right' => 124.5],
            'depósito' => ['left' => 112.5, 'right' => 124.5],
        ]
        : [
            'efectivo' => ['left' => 96.0, 'right' => 105.2],
            'transferencia' => ['left' => 101.9, 'right' => 110.2],
            'tarjeta de débito' => ['left' => 107.0, 'right' => 116.2],
            'tarjeta de crédito' => ['left' => 112.0, 'right' => 121.2],
      'dólares' => ['left' => 117.0, 'right' => 127.7],
            'dolares' => ['left' => 117.0, 'right' => 127.7],
        ];

    $methodKey = strtolower(trim($request->pay_method_1));

    if (isset($payCheckMap[$methodKey])) {
        $left(53.0,  $payCheckMap[$methodKey]['left'],  'X');
        $right(180.0, $payCheckMap[$methodKey]['right'], 'X');
    }

    /* ============================================================
        CHECK TRATAMIENTO
    ============================================================ */
    if ($type === 'Tratamiento') {
        $treatCheckMap = [
            'factor' => ['left' => 70.0, 'right' => 80.0],
            'dutasteride' => ['left' => 75.0, 'right' => 85.0],
            'exosoma' => ['left' => 80.0, 'right' => 90.0],
        ];

        foreach ($cart as $item) {
            $key = strtolower($item['nombre']);
            foreach ($treatCheckMap as $needle => $coords) {
                if (str_contains($key, $needle)) {
                    $left(20.5,  $coords['left'],  'X');
                    $right(149.1, $coords['right'], 'X');
                }
            }
        }
    }

    /* ============================================================
        NOTAS
    ============================================================ */
    $notes = [
        ($type === 'Producto' ? 'Producto: ' : 'Tratamiento: ') . $itemName,
        "Pago: {$request->pay_method_1} $" . number_format($total, 2),
    ];

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

        $receiptId = DB::table('sa_info_payment_px')->insertGetId([
            'lead_id'      => $leadId,
            'type'         => strtolower($type),
            'amount'       => $total,
            'method'       => $request->pay_method_1,
            'payment_date' => $dateDB,
            'receipt_date' => $dateDB,
            'public_notes' => implode("\n", $notes),
            'clinic'       => $clinic,
            'uploaded_by'  => $userId,
            'created_at'   => now(),
            'status'       => 1,
        ]);

        DB::commit();

    } catch (\Throwable $e) {
        DB::rollBack();
        throw $e;
    }

/* ============================================================
   GUARDAR PDF
============================================================ */
$dir = public_path("storage/leads/{$leadId}/receipts");
if (!file_exists($dir)) mkdir($dir, 0775, true);

$file = strtolower($type) . "_{$receiptId}.pdf";
$fullPath = "{$dir}/{$file}";

$mpdf->Output($fullPath, 'F');

// (Opcional pero recomendado) guardar ruta en BD
DB::table('sa_info_payment_px')
    ->where('id', $receiptId)
    ->update([
        'file_path' => "storage/leads/{$leadId}/receipts/{$file}",
    ]);

/* ============================================================
   RESPUESTA
============================================================ */
$pdfUrl = asset("storage/leads/{$leadId}/receipts/{$file}");

if ($request->ajax() || $request->wantsJson()) {
    return response()->json([
        'success' => true,
        'pdf_url' => $pdfUrl,
    ]);
}

// 👇 SI NO ES AJAX: ABRE EL PDF EN EL NAVEGADOR
return response()->file($fullPath, [
    'Content-Type' => 'application/pdf',
    'Content-Disposition' => 'inline; filename="'.$file.'"',
]);

}





    //consultar pacientes para autocompletar
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $patients = ClosedPx::with('lead', 'procedure')
            ->whereHas('lead', function ($q) use ($query) {
                $q->whereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ["%{$query}%"]);
            })
            ->orWhereHas('procedure', function ($q) use ($query) {
                $q->where('num_med_record', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get();

        $patients = $patients->map(function ($px) {
            $costo = $px->costo ?? 0;
            $precioTotal = $px->precio_total ?? 0;

            return [
                'id' => $px->lead_id,
                'name' => $px->lead ? $px->lead->first_name . ' ' . $px->lead->last_name : '',
                'num_med_record' => $px->procedure->num_med_record ?? 'NA',
                'procedure_date' => $px->procedure->procedure_date ?? null,
                'costo' => $costo,
                'restante' => $precioTotal - $costo, // cálculo nativo directo
            ];
        });

        return response()->json($patients);
    }


    public function show($id)
    {
        $receipt = Receipt::find($id);

        if (!$receipt) {
            return response()->json(['error' => 'Recibo no encontrado'], 404);
        }

        return response()->json($receipt);
    }
}
