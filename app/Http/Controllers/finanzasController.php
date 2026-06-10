<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\adTransactions;
use App\Models\invCategories;
use App\Models\adCategory;
use App\Models\adSubcategory;
use App\Models\adsubSubcategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Exception;

 
class finanzasController extends Controller
{

   /*---------------------------------------------
   ------------ Seccion de gastos ----------------
   ---------------------------------------------*/
public function indexGastos(Request $request)
{
    // ✅ Default: mes actual
    $start = Carbon::now()->startOfMonth();
    $end   = Carbon::now()->endOfMonth();

    // ✅ Si viene rango desde el filtro (DD/MM/YYYY)
    if ($request->filled('start') && $request->filled('end')) {
        try {
            $start = Carbon::createFromFormat('d/m/Y', $request->start)->startOfDay();
            $end   = Carbon::createFromFormat('d/m/Y', $request->end)->endOfDay();
        } catch (\Exception $e) {
            // Si llega mal el formato, regresa a default sin romper
            $start = Carbon::now()->startOfMonth();
            $end   = Carbon::now()->endOfMonth();
        }
    }

    $gastos = adTransactions::whereBetween('date', [$start, $end])
        ->orderBy('date', 'desc')
        ->get();

    $categorias = adCategory::all();
    $subcategorias = adSubcategory::all(['id', 'name', 'category_id']);

    return view('crm.Finanzas.gastos', compact('gastos', 'categorias', 'subcategorias'));
}

       public function guardarGasto(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'payment_method_id' => 'required',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'store' => 'required|string',
            'cat_id' => 'required|integer',
            'subcategory' => 'nullable|integer',
            'sub_subcategory' => 'nullable|integer',
            'clinic' => 'required|string',
            'created_by' => 'required|integer',
        ]);

        try {
            // Si subcategoria no es un número entero, asigna 73
            $subcat = is_numeric($request->subcategory) ? (int)$request->subcategory : 73;

            // Actualizar la tabla de subcategorías
            DB::table('ad_subcategories')
                ->where('id', $subcat)
                ->increment('current', $request->amount);

            // Guardar el gasto (amount negativo)
            adTransactions::create([
                'description' => $request->description,
                'payment_method_id' => $request->payment_method_id,
                'amount' => $request->amount * -1,
                'date' => $request->date,
                'store' => $request->store,
                'cat_id' => $request->cat_id,
                'current_status' => 1,
                'subcategory' => $subcat,
                'sub_subcategory' => $request->sub_subcategory ?? null,
                'clinic' => $request->clinic,
                'created_by' => $request->created_by
            ]);

            //dd($request->all());

            return redirect()->back()->with('success', 'Gasto agregado correctamente.');

        } catch (Exception $e) {

         dd($e->getMessage());
         //return redirect()->back()->with('error', 'Error al agregar el gasto: ' . $e->getMessage());
        }
    }

    public function actualizarGasto(Request $request, $id)
{
    try {
        $gasto = adTransactions::findOrFail($id);

        $gasto->update([
            'description' => $request->description,
            'store' => $request->store,
            'cat_id' => $request->cat_id,
            'subcategory' => $request->subcategory,
            'payment_method_id' => $request->payment_method_id,
            'amount' => $request->amount,
            'date' => $request->date,
            'clinic' => $request->clinic,
        ]);

        return redirect()->back()->with('success', 'Gasto actualizado correctamente.');
    } catch (Exception $e) {
        return redirect()->back()->with('error', 'Error al actualizar el gasto.');
    }
}

public function eliminarGasto($id)
{
    try {
        $gasto = adTransactions::findOrFail($id);

        // (Opcional) Si quieres devolver el monto al saldo de la subcategoría:
        if ($gasto->subcategory) {
            DB::table('ad_subcategories')
                ->where('id', $gasto->subcategory)
                ->decrement('current', abs($gasto->amount));
        }

        $gasto->delete();

        return response()->json(['success' => true, 'message' => 'Gasto eliminado correctamente.']);
    } catch (Exception $e) {
        return response()->json(['success' => false, 'message' => 'Error al eliminar el gasto.']);
    }
}


public function getGastosPorFechas(Request $request)
{
    $start_date = $request->input('start_date');
    $end_date = $request->input('end_date');

    // Consulta los gastos dentro del rango de fechas
    $gastos = adTransactions::whereBetween('date', [$start_date, $end_date])
        ->orderBy('date', 'desc')
        ->get();

    // Calcula el total
    $total = $gastos->sum('amount');

    // Retorna JSON con los datos
    return response()->json([
    'success' => true,
    'total' => abs($gastos->sum('amount')),
        ]);

}

public function totalGastos(Request $request)
{
    $start  = $request->input('start_date'); // YYYY-MM-DD
    $end    = $request->input('end_date');   // YYYY-MM-DD
    $clinic = $request->input('clinic');     // Santafe|Pedregal|Queretaro|Todas

    $q = adTransactions::query()->whereBetween('date', [$start, $end]);

    if ($clinic && $clinic !== 'Todas') {
        $q->where('clinic', $clinic);
    }

    $total = $q->sum('amount');

    return response()->json([
        'success' => true,
        'total'   => abs($total),
    ]);
}


/*---------------------------------------------
   ------------ Seccion de Ingresos -------------
   ---------------------------------------------*/

     public function indexIngresos(){

        return view('crm.Finanzas.ingresos');
     }


        public function getTransacciones(Request $request)
{
    $filterMode = $request->input('filter_mode', 'day');
    $clinic = $request->input('clinic', 'Ambas');
    $method = $request->input('method', 'Ambos');
    $movement = $request->input('movement', 'Ambos');
    $product_type = $request->input('type', 'Todos');

    $start = $end = null;

    switch ($filterMode) {
        case 'day':
            $start = $end = $request->input('fecha');
            break;
        case 'week':
            $start = $request->input('week_start');
            $end = $request->input('week_end');
            break;
        case 'month':
            $year = $request->input('year');
            $month = $request->input('month');
            $start = "$year-$month-01";
            $end = date("Y-m-t", strtotime($start));
            break;
    }

    if (!$start || !$end) {
        return response()->json(['success' => false, 'error' => 'Fechas inválidas']);
    }
 
    $query = collect();

    // --- INGRESOS ---
    if ($movement === 'Ingreso' || $movement === 'Ambos') {
        $ingresos = DB::table('sa_info_payment_px as p')
            ->leftJoin('sa_leads as l', 'p.lead_id', '=', 'l.id')
            ->select(
                'p.id',
                DB::raw("DATE_FORMAT(p.payment_date, '%d/%m/%Y') as fecha"),
                DB::raw("CONCAT(l.first_name, ' ', l.last_name) as nombre"),
                'p.public_notes as concepto',
                'p.type as tipo',
                'p.amount as importe',
                'p.method as metodo',
                'p.clinic',
                DB::raw("'Ingreso' as movimiento"),
                DB::raw("p.type as tipo_producto") // Campo tipo de producto
            )
            ->whereBetween(DB::raw('DATE(p.payment_date)'), [$start, $end])
            ->where('p.status', 1);

        if ($clinic !== 'Ambas') {
            $ingresos->whereIn('p.clinic', $clinic === 'Santa Fe' ? ['Santa Fe', 'Santafe'] : [$clinic]);
        }

        if ($method !== 'Ambos') {
            $ingresos->where('p.method', $method);
        }

        if ($product_type !== 'Todos') {
            $ingresos->where('p.type', $product_type);
        }

        $query = $query->merge($ingresos->get());
    }

    // --- EGRESOS ---
    if ($movement === 'Egreso' || $movement === 'Ambos') {
        $egresos = DB::table('ad_transactions as a')
            ->select(
                'a.id',
                DB::raw("DATE_FORMAT(a.date, '%d/%m/%Y') as fecha"),
                'a.description as nombre',
                DB::raw("'' as concepto"),
                DB::raw("a.cat_id as tipo"), // si tienes tipo/categoría de gasto
                DB::raw("a.amount * -1 as importe"),
                'a.payment_method_id as metodo',
                'a.clinic',
                DB::raw("'Egreso' as movimiento"),
                DB::raw("'' as tipo_producto") // 🆕 para mantener estructura
            )
            ->whereBetween(DB::raw('DATE(a.date)'), [$start, $end]);

        if ($clinic !== 'Ambas') {
            $egresos->whereIn('a.clinic', $clinic === 'Santa Fe' ? ['Santa Fe', 'Santafe'] : [$clinic]);
        }

        if ($method !== 'Ambos') {
            $egresos->where('a.payment_method_id', $method);
        }

        $query = $query->merge($egresos->get());
    }

    // --- Totales ---
    $totalIngresos = $query->where('movimiento', 'Ingreso')->sum('importe');
    $totalEgresos = abs($query->where('movimiento', 'Egreso')->sum('importe'));
    $total = $totalIngresos - $totalEgresos;

    // --- Formateo ---
    $data = $query->map(function ($item) {
        $item->importe = '$' . number_format(abs($item->importe), 2, '.', ',');
        return $item;
    })->sortByDesc('fecha')->values();

    return response()->json([
        'success' => true,
        'data' => $data,
        'ingresos' => '$' . number_format($totalIngresos, 2, '.', ','),
        'egresos' => '$' . number_format($totalEgresos, 2, '.', ','),
        'total' => '$' . number_format($total, 2, '.', ','),
    ]);
}



/*--------------------------------------------
   --------- Seccion de cortes diarios --------
   --------------------------------------------*/

   public function indexCortesDiarios(){


    return view('crm.Finanzas.cortesDiarios');

   }

   // =========================================================
    // Método: Cargar todos los pagos diarios (px + treatments)
    // =========================================================
    public function loadAllDaily(Request $request)
    {
        $fecha = $request->input('fecha');
        $clinic = $request->input('clinic');

        if (!$fecha || !$clinic) {
            return response()->json([
                'success' => false,
                'message' => 'Fecha y clínica son obligatorias.'
            ]);
        }

        // Pagos px
        $pxPayments = DB::table('sa_info_payment_px as p')
            ->join('sa_leads as l', 'p.lead_id', '=', 'l.id')
            ->select(
                'p.id',
                DB::raw("DATE_FORMAT(p.payment_date, '%Y-%m-%d') as fecha"),
                DB::raw("CONCAT(l.first_name, ' ', l.last_name) as nombre"),
                'l.id as lead_id',
                'p.public_notes as concepto',
                'p.type as tipo',
                'p.amount as importe',
                DB::raw('COALESCE(p.conversion, 0) as conversion'),
                DB::raw('COALESCE(p.amount_conversion, 0) as amount_conversion'),
                'p.method as metodo_de_pago',
                'p.clinic as sucursal',
                DB::raw("'payment_px' as source"),
                'p.public_notes',
                'p.private_notes'
            )
            ->whereDate('p.payment_date', $fecha)
            ->where('p.clinic', $clinic)
            ->where('status', 1)
            ->orderByDesc('p.payment_date')
            ->get();

        // Pagos treatments
        $treatmentsPayments = DB::table('sa_info_payment_treatments as t')
            ->join('enf_treatments as e', 't.px_id', '=', 'e.id')
            ->select(
                't.id',
                't.px_id',
                DB::raw("DATE_FORMAT(t.payment_date, '%Y-%m-%d') as fecha"),
                'e.name as nombre',
                'e.num_med_record',
                't.public_notes as concepto',
                't.type as tipo',
                't.amount as importe',
                DB::raw('COALESCE(t.conversion, 0) as conversion'),
                DB::raw('COALESCE(t.amount_conversion, 0) as amount_conversion'),
                't.method as metodo_de_pago',
                't.clinic as sucursal',
                DB::raw("'payment_treatments' as source"),
                't.public_notes',
                't.private_notes'
            )
            ->whereDate('t.payment_date', $fecha)
            ->where('t.clinic', $clinic)
            ->orderByDesc('t.payment_date')
            ->get();

        // Combinar ambos
        $data = $pxPayments->concat($treatmentsPayments);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    // =========================================================
    // Método: Totales diarios por método de pago
    // =========================================================
    public function loadTotalDaily(Request $request)
    {
        $fecha = $request->input('fecha');
        $clinic = $request->input('clinic'); 

        if (!$fecha || !$clinic) {
            return response()->json([
                'success' => false,
                'message' => 'Fecha y clínica son obligatorias.'
            ]);
        }

        $totalsPx = DB::table('sa_info_payment_px')
            ->select('method as metodo_de_pago', DB::raw('SUM(amount) as total_importe'))
            ->whereDate('payment_date', $fecha)
            ->where('clinic', $clinic)
            ->where('status', 1)
            ->groupBy('method')
            ->get();

        $totalsTreatments = DB::table('sa_info_payment_treatments')
            ->select('method as metodo_de_pago', DB::raw('SUM(amount) as total_importe'))
            ->whereDate('payment_date', $fecha)
            ->where('clinic', $clinic)
            ->groupBy('method')
            ->get();

        // Combinar resultados
        $combinedTotals = collect($totalsPx)
            ->merge($totalsTreatments)
            ->groupBy('metodo_de_pago')
            ->map(function ($group) {
                return [
                    'metodo_de_pago' => $group[0]->metodo_de_pago,
                    'total_importe' => collect($group)->sum('total_importe')
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'fecha' => $fecha,
            'clinica' => $clinic,
            'totals' => $combinedTotals
        ]);
    }

    // =========================================================
    // Método: Guardar firma
    // =========================================================
    public function addSignByDay(Request $request)
    {
        $dia = $request->input('dia');
        $clinic = $request->input('clinic');
        $firma = $request->input('firma');

        if (!$dia || !$clinic || !$firma) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan parámetros obligatorios.'
            ]);
        }

        DB::table('daily_cortes')
            ->updateOrInsert(
                ['dia' => $dia, 'clinic' => $clinic],
                ['firma' => $firma]
            );

        return response()->json(['success' => true]);
    }

    // =========================================================
    // Método: Eliminar firma
    // =========================================================
    public function deleteSignByDay(Request $request)
    {
        $dia = $request->input('dia');
        $clinic = $request->input('clinic');

        if (!$dia || !$clinic) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan parámetros obligatorios.'
            ]);
        }

        DB::table('daily_cortes')
            ->where('dia', $dia)
            ->where('clinic', $clinic)
            ->delete();

        return response()->json(['success' => true]);
    }

    // =========================================================
    // Método: Generar PDF de corte diario
    // =========================================================
    public function generateCashClosingDaily(Request $request)
    {
        $request->validate([
            'tableData' => 'required|array',
            'fecha' => 'required|date',
            'clinic' => 'required|string',
            'user_id' => 'required|integer',
        ]);

        $tableData = $request->input('tableData');
        $fecha = $request->input('fecha');
        $clinic = $request->input('clinic');
        $userId = $request->input('user_id');
        $firmaBase64 = $request->input('firma');

        // Obtener nombre del usuario
        $nombreUsuario = DB::table('users')
            ->where('id', $userId)
            ->value('name');

        if (!$nombreUsuario) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el usuario.'
            ]);
        }

        try {
            $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => [215.9, 140]]);
            $templateFile = public_path('storage/corte-caja/Plantillacorte_de_caja_diario.pdf');
            $pagecount = $mpdf->SetSourceFile($templateFile);
            $tplId = $mpdf->ImportPage($pagecount);
            $mpdf->UseTemplate($tplId);
            $mpdf->SetFont('leagues', 'B', 14);

            $dataRow = $tableData[0] ?? [];

            // Función para limpiar valores
            $limpiar = fn($valor) => (float) str_replace(['$', ','], '', $valor);

            $efectivo = $limpiar($dataRow[0] ?? 0);
            $dolares = $limpiar($dataRow[1] ?? 0);
            $tarjeta = $limpiar($dataRow[2] ?? 0);
            $deposito = $limpiar($dataRow[3] ?? 0);
            $transferencia = $limpiar($dataRow[4] ?? 0);
            $otro = $limpiar($dataRow[5] ?? 0);
            $enlace = $limpiar($dataRow[6] ?? 0);
            $credito = $limpiar($dataRow[7] ?? 0);
            $debito = $limpiar($dataRow[8] ?? 0);

            $transferenciaTotal = $transferencia + $enlace;
            $totalesTarjeta = $credito + $debito;
            $total = $efectivo + $dolares + $tarjeta + $deposito + $transferenciaTotal + $otro + $totalesTarjeta;

            // Formatear números
            $format = fn($v) => number_format($v, 2, '.', ',');
            $efectivo = $format($efectivo);
            $dolares = $format($dolares);
            $tarjeta = $format($tarjeta);
            $deposito = $format($deposito);
            $transferenciaTotal = $format($transferenciaTotal);
            $totalesTarjeta = $format($totalesTarjeta);
            $otro = $format($otro);
            $credito = $format($credito);
            $debito = $format($debito);
            $total = $format($total);

            // Escribir en el PDF
            $dateFormatted = date('d/m/Y', strtotime($fecha));
            $mpdf->WriteText(28, 43, $nombreUsuario);
            $mpdf->WriteText(170, 25.5, $dateFormatted);
            $mpdf->WriteText(58, 56, $efectivo);
            $mpdf->WriteText(58, 72.7, $dolares);
            $mpdf->WriteText(58, 89.5, $totalesTarjeta);
            $mpdf->WriteText(58, 106.5, $deposito);
            $mpdf->WriteText(58, 122.3, $transferenciaTotal);
            $mpdf->WriteText(150, 56.6, $otro);
            $mpdf->WriteText(150, 72.7, $total);

            // Añadir firma si existe
            if ($firmaBase64) {
                $firmaData = base64_decode(str_replace('data:image/png;base64,', '', $firmaBase64));
                $tempPath = storage_path('app/public/temp_signature.png');
                file_put_contents($tempPath, $firmaData);
                $mpdf->Image($tempPath, 160, 92.7, 50, 30, 'png');
                unlink($tempPath);
            }

            $mpdf->WriteText(28, 132, $clinic);

            // Guardar PDF
            $filename = "corte_caja_{$clinic}_" . date('dmy', strtotime($fecha)) . ".pdf";
            $filePath = storage_path("app/public/corte-caja/$filename");
            if (!file_exists(dirname($filePath))) mkdir(dirname($filePath), 0777, true);
            $mpdf->Output($filePath, 'F');

            // Actualizar DB
            DB::table('daily_cortes')->updateOrInsert(
                ['dia' => $fecha, 'clinic' => $clinic],
                ['pdf' => $filename]
            );

            return response()->json([
                'success' => true,
                'path' => "storage/corte-caja/$filename",
                'total' => $total
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }


}



