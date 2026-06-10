<?php

namespace App\Http\Controllers;

use App\Models\Empleado;   // ← MODELO CORRECTO
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::with('empleado')->orderBy('id', 'DESC')->paginate(10);
        return view('panel.holidays.index', compact('holidays'));
    }

    public function create()
    {
        // ANTES: Employee::all();
        $employees = Empleado::all();   // ← CORRECTO
        return view('panel.holidays.create', compact('employees'));
    }
public function store(Request $request)
{
    $request->validate([
        'employee' => 'required|exists:empleados,id',
        'start'    => 'required|date',
        'end'      => 'required|date|after_or_equal:start',
    ]);

    $start = Carbon::parse($request->start);
    $end = Carbon::parse($request->end);

    // Si necesitas que el fin sea inclusivo
    $fixed_end = $end->copy()->addDay()->format('Y-m-d');

    DB::beginTransaction();

    try {

        Holiday::create([
            'employee_id' => $request->employee,
            'start'       => $request->start,
            'end'         => $fixed_end,
            'notes'       => $request->notes,
            'approved_by' => $request->approved_by
        ]);

        DB::commit();

        return redirect()
            ->route('panel.holidays.index')
            ->with('success', 'Vacación registrada correctamente.');

    } catch (\Exception $e) {

        DB::rollBack();

        return back()->with(
            'error',
            'Error al registrar vacaciones: ' . $e->getMessage()
        );
    }
}
public function show(Holiday $holiday)
{
    $holiday->load('employee');

    return response()->json([
        'id'       => $holiday->id,
        'empleado' => optional($holiday->employee)->nombre . ' ' . optional($holiday->employee)->apellido,
        'inicio'   => $holiday->start,
        'fin'      => $holiday->end,
        'dias'     => \Carbon\Carbon::parse($holiday->start)
                        ->diffInDays(\Carbon\Carbon::parse($holiday->end)) + 1
    ]);
}


    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return redirect()->route('panel.holidays.index')
            ->with('success', 'Vacación eliminada.');
    }
}
    