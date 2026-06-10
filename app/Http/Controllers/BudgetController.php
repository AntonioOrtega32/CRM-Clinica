<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BudgetController extends Controller
{
 public function index(Request $request)
{
    // ===========================
    // MES (por defecto: mes actual)
    // ===========================
    $month = $request->filled('month')
        ? str_pad($request->month, 2, '0', STR_PAD_LEFT)
        : now()->format('m');

    $year = now()->year;

    // ===========================
    // CLÍNICA
    // ===========================
    $clinic = $request->filled('clinic') ? $request->clinic : null;

    // ===========================
    // FECHAS
    // ===========================
    $start = Carbon::createFromDate($year, (int)$month, 1)->startOfDay();
    $end   = (clone $start)->endOfMonth()->endOfDay();

    // ===========================
    // FILTRO CLÍNICA
    // ===========================
    $clinicFilter = null;
    if ($clinic === 'CDMX') {
        $clinicFilter = ['Santafe', 'Pedregal'];
    } elseif ($clinic) {
        $clinicFilter = [$clinic];
    }

    // ===========================
    // CATEGORÍAS
    // ===========================
    $categories = DB::table('ad_categories as c')
        ->leftJoin('ad_transactions as t', function ($join) use ($start, $end, $clinicFilter) {
            $join->on('c.id', '=', 't.cat_id')
                ->whereBetween('t.date', [$start, $end]);

            if ($clinicFilter) {
                $join->whereIn('t.clinic', $clinicFilter);
            }
        })
        ->where('c.visible', 1)
        ->groupBy('c.id', 'c.name', 'c.amount')
        ->select(
            'c.id',
            'c.name',
            'c.amount',
            DB::raw('IFNULL(SUM(t.amount),0) as total_expensed')
        )
        ->get()
        ->keyBy('id');

    // ===========================
    // SUBCATEGORÍAS
    // ===========================
    $subcategories = DB::table('ad_subcategories as s')
        ->leftJoin('ad_transactions as t', function ($join) use ($start, $end, $clinicFilter) {
            $join->on('s.id', '=', 't.subcategory')
                ->whereBetween('t.date', [$start, $end]);

            if ($clinicFilter) {
                $join->whereIn('t.clinic', $clinicFilter);
            }
        })
        ->where('s.status', 1)
        ->groupBy('s.id', 's.category_id', 's.name', 's.description')
        ->select(
            's.id',
            's.category_id',
            's.name',
            's.description',
            DB::raw('ABS(IFNULL(SUM(t.amount),0)) as total_expensed')
        )
        ->get();

    foreach ($subcategories as $sub) {
        if (isset($categories[$sub->category_id])) {
            $categories[$sub->category_id]->subcategories[] = $sub;
            $categories[$sub->category_id]->subcategories_total =
                ($categories[$sub->category_id]->subcategories_total ?? 0) + $sub->total_expensed;
        }
    }

    // ===========================
    // TOTALES
    // ===========================
    $totalBudget = $categories->sum('amount');

    $totalExpensed = DB::table('ad_transactions')
        ->where('amount', '<', 0)
        ->whereBetween('date', [$start, $end])
        ->when($clinicFilter, fn ($q) => $q->whereIn('clinic', $clinicFilter))
        ->sum('amount') * -1;

    // ===========================
    // VISTA
    // ===========================
    return view('crm.Finanzas.presupuestos.index', [
        'categories'      => $categories,
        'totalBudget'     => $totalBudget,
        'totalExpensed'   => $totalExpensed,
        'monthLabel'      => $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y'),
        'selectedMonth'   => $month,   // 🔥 CLAVE
        'selectedClinic'  => $clinic,  // 🔥 CLAVE
    ]);
}

    // ================= AJAX =================
public function storeCategory(Request $request)
{

    $request->validate([
        'name'   => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
    ]);

    DB::table('ad_categories')->insert([
        'name'    => $request->name,
        'amount'  => $request->amount,
        'current' => 0,
        'visible' => 1,
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Categoría creada correctamente'
    ]);
}

public function storeSubcategory(Request $request)
{
    DB::table('ad_subcategories')->insert([
        'category_id' => $request->category_id,
        'name'        => $request->name,
        'description' => $request->description,
        'amount_'      => $request->amount,
        'current'     => $request->current ?? 0,
        'status'      => 1,
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Subcategoría creada'
    ]);
}



public function updateSubcategory(Request $request, $id)
{
    $subcategory = DB::table('ad_subcategories')->where('id', $id)->first();

    if (!$subcategory) {
        return response()->json([
            'status' => 'error',
            'message' => 'Subcategoría no encontrada'
        ], 404);
    }

    $validated = $request->validate([
        'name'        => 'sometimes|required|string|max:255',
        'description' => 'nullable|string',
        'amount'      => 'sometimes|required|numeric',
        'current'     => 'nullable|numeric',
        'category_id' => 'sometimes|required|integer|exists:ad_categories,id',
    ]);

    DB::table('ad_subcategories')
        ->where('id', $id)
        ->update([
            'name'        => $validated['name']        ?? $subcategory->name,
            'description' => $validated['description'] ?? $subcategory->description,
            'amount_'     => $validated['amount']      ?? $subcategory->amount_,
            'current'     => $validated['current']     ?? $subcategory->current,
            'category_id' => $validated['category_id'] ?? $subcategory->category_id,
        ]);

    return response()->json([
        'status' => 'success'
    ]);
}


public function updateCategory(Request $request, $id)
{
        \Log::info('CATEGORY REQUEST', context: $request->all());

    $request->validate([
        'name'   => 'required|string|max:255',
        'amount' => 'required|numeric',
    ]);

    DB::table('ad_categories')
        ->where('id', $id)
        ->update([
            'name'   => $request->name,
            'amount' => $request->amount,
        ]);

    return response()->json([
        'status' => 'success'
    ]);
}

        public function deleteSubcategory($id)
        {
            DB::table('ad_subcategories')->where('id', $id)->update(['status' => 0]);
            return response()->json(['status' => 'success']);
        }


    
}
