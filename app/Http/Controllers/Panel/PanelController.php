<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PanelController extends Controller
{
    public function index()
    { 
        return view('panel.index');
    }

    /* CARGAR PACIENTES (NO SE MODIFICA) */
    public function loadPatients(Request $request)
    {
        try {
            $clinic = $request->input('clinic', 'Santa Fe');
            $now = now(config('app.timezone'));

            $date = $now->toDateString(); // Y-m-d
            $string_date = $now->locale('es')->translatedFormat('d F');

            $query = DB::table('sa_leads AS leads')
                ->select(
                    'leads.id AS lead_id',
                    DB::raw("CONCAT(sla.first_name, ' ', sla.last_name) AS name"),
                    'sla.procedure_date',
                    'sla.procedure_type',
                    'ep.id AS procedure_id',
                    'ep.num_med_record',
                    'ep.room',
                    'ep.specialist',
                    'sla.status',
                    'sla.enfermedades'
                )
                ->join('sa_leads_assessment AS sla', 'leads.id', '=', 'sla.lead_id')
                ->leftJoin('enf_procedures AS ep', 'leads.id', '=', 'ep.lead_id')
                ->where('sla.procedure_date', $date)
                ->where('sla.status', 1);

            if ($clinic === 'Queretaro') {
                $query->where('sla.clinic', $clinic);
            } else {
                $query->where('sla.clinic', '!=', 'Queretaro');
            }

            $rows = $query->get();

            if ($rows->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay procedimientos programados para hoy en la clínica seleccionada'
                ]);
            }

            $cards = [];
            foreach ($rows as $row) {
                $cards[] = $this->createCard($string_date, $row);
            }

            return response()->json(['success' => true, 'cards' => $cards]);

            return response()->json([
                'app_tz' => config('app.timezone'),
                'php_tz' => date_default_timezone_get(),
                'now' => now()->toDateTimeString(),
                'date_used' => $date,
                ]);


        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function createCard($string_date, $row)
    {
        $actions = "
            <div class='mt-4 flex gap-2'>
                <a href='/panel/procedimientos/fotos/{$row->lead_id}/pre'
                   class='px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 transition'>
                   Enviar Fotos
                </a>
                <a href='/panel/notifications?id={$row->lead_id}'
                   class='px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition'>
                   Notificar
                </a>
                <a href='/edit_lead/{$row->lead_id}'
                   class='px-3 py-1 bg-orange-500 text-white rounded hover:bg-orange-600 transition'>
                   Editar
                </a>
            </div>
        ";

        $num_med_record_display = $row->num_med_record
            ? "#{$row->num_med_record}"
            : '<br> No asignado';

        return "
            <div class='blog-slider__item swiper-slide'>
                <div class='blog-slider__img'>
        <img src='" . asset('images/galeria/1758225524_logo.png') . "' alt='Perfil' />
                </div>
                <div class='blog-slider__content'>
                    <span class='blog-slider__code'>Procedimientos del : {$string_date}</span>
                    <div class='blog-slider__title'>{$row->name} {$num_med_record_display}</div>
                    <div class='blog-slider__text'>
                        Tipo: {$row->procedure_type}<br>
                        Enfermedades: {$row->enfermedades}<br>
                        Sala: {$row->room}<br>
                        Especialista: {$row->specialist}<br>
                    </div>
                    {$actions}
                </div>
            </div>
        ";
    }

    /* ============================================================
        VACACIONES — STORE CORREGIDO
    ============================================================ */
    public function store(Request $request)
    {
        $emp = Empleado::find($request->employee);

        // Fechas
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        // Días solicitados
        $requested_days = $start->diffInDays($end) + 1; // incluye el último día

        // Guardar fin real
        $fixed_end = $end->copy()->format('Y-m-d');

        DB::beginTransaction();

        try {
            Holiday::create([
                'employee_id' => $request->employee,
                'start'       => $request->start,
                'end'         => $fixed_end,
                'notes'       => $request->notes,
                'approved_by' => $request->approved_by
            ]);

            // SUMAR días usados
            $emp->increment('used_days', $requested_days);

            DB::commit();

            return redirect()->route('panel.holidays.index')
                ->with('success', 'Vacación registrada correctamente.');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Error al registrar vacaciones: ' . $e->getMessage());
        }
    }
public function vacacionesCard(Request $request)
{
    $clinic = trim(strtolower($request->clinic));
    $hoy = Carbon::today();

    $empleados = Empleado::whereRaw('LOWER(TRIM(clinica)) = ?', [$clinic])
        ->whereHas('holidays', function ($query) use ($hoy) {
            $query->whereDate('start', '<=', $hoy)
                  ->whereDate('end', '>=', $hoy);
        })
        ->with(['holidays' => function ($query) use ($hoy) {
            $query->whereDate('start', '<=', $hoy)
                  ->whereDate('end', '>=', $hoy)
                  ->latest('id');
        }])
        ->get();

    $empleadosData = [];

    foreach ($empleados as $e) {
        $holiday = $e->holidays->first();

        $fechaIngreso = Carbon::parse($e->fecha_ingreso);
        $cumpleAnio = $fechaIngreso->copy()->addYear()->isSameDay($hoy);

        $empleadosData[] = [
            'nombre' => $e->nombre,
            'apellido' => $e->apellido,
            'puesto' => $e->puesto,
            'vacaciones_hasta' => $holiday
                ? Carbon::parse($holiday->end)->locale('es')->translatedFormat('d F Y')
                : null,
            'cumple_un_anio' => $cumpleAnio
        ];
    }

    return response()->json([
        'success' => true,
        'empleados' => $empleadosData
    ]);
}

public function empleadosAniversario(Request $request)
{
    $clinic = $request->clinic;
    $hoy = Carbon::today();

    $empleados = Empleado::where('clinica', $clinic)->get();

    $empleadosData = [];

    foreach ($empleados as $e) {
        if (!$e->fecha_ingreso) continue;

        $años = Carbon::parse($e->fecha_ingreso)->diffInYears($hoy);
        if ($años >= 1) {
            $empleadosData[] = [
                'nombre' => $e->nombre,
                'apellido' => $e->apellido,
                'puesto' => $e->puesto,
                'fecha_ingreso' => Carbon::parse($e->fecha_ingreso)->format('d/m/Y'),
                'años_en_empresa' => $años
            ];
        }
    }

    return response()->json([
        'success' => true,
        'empleados' => $empleadosData
    ]);
}



}
