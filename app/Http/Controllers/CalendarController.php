<?php

namespace App\Http\Controllers;

use App\Models\SaAssessmentEvent;
use App\Models\SaEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CalendarController extends Controller
{
    // =========================
    // INDEX: Obtener eventos
    // =========================
    public function index(Request $request)
    {
        $clinic     = $request->input('clinic');
        $filters    = $request->input('filters', []);
        $eventDate  = $request->input('event_date'); // 🔥 NUEVO

        $events = [];

        /* ---------------------------------------------------------
     |  1. EVENTOS PRINCIPALES
     --------------------------------------------------------- */
        $events = $this->getMainEvents($clinic, $filters, $eventDate);

        /* ---------------------------------------------------------
     |  2. LEADS
     --------------------------------------------------------- */
        if ($clinic === "Santafe" || $clinic === "Queretaro") {
            $events = array_merge(
                $events,
                $this->getLeadEvents($clinic, $eventDate) // 🔥 PASAMOS FECHA
            );
        }

        /* ---------------------------------------------------------
     |  3. HOLIDAYS
     --------------------------------------------------------- */
        if (in_array('holidays', $filters)) {
            $events = array_merge(
                $events,
                $this->getHolidays($clinic, $eventDate) // 🔥 PASAMOS FECHA
            );
        }

        return response()->json([
            'success' => true,
            'events'  => $events
        ]);
    }





    private function getMainEvents($clinic, $filters, $eventDate = null)
    {
        $query = SaEvent::with(['assessment', 'uploader'])
            ->select(
                'id',
                'event_type',
                'attendance_type',
                'title',
                'start',
                'end',
                'description',
                'clinic',
                'status',
                'qualy',
                'uploaded_by',
                'review_time',
                'protocolo_alejandro'
            )
            ->where('clinic', $clinic);

        /* 🔥 FILTRO POR FECHA */
        if ($eventDate) {
            $query->whereDate('start', $eventDate);
        }

        /* Filtro de tipos */
        $types = array_filter($filters, fn($v) => $v !== 'holidays');
        if (!empty($types)) {
            $query->whereIn('event_type', $types);
        }

        return $query->get()
            ->map(fn($ev) => $this->formatEvent($ev))
            ->toArray();
    }

    private function getLeadEvents($clinic, $eventDate = null)
    {
        $clinics = $clinic === "Santafe"
            ? ['Santa Fe', 'Pedregal']
            : ['Queretaro'];

        $query = DB::table('sa_leads_assessment')
            ->select(
                'id',
                DB::raw("CONCAT(first_name, ' ', last_name) AS title"),
                'procedure_type',
                'notes AS description',
                'procedure_date'
            )
            ->where('status', 1)
            ->whereIn('clinic', $clinics);

        /* 🔥 FILTRO POR FECHA */
        if ($eventDate) {
            $query->whereDate('procedure_date', $eventDate);
        }

        $leads = $query->get();

        return $leads->map(function ($data) use ($clinic) {
            $procedureDate = $data->procedure_date ?? now()->format('Y-m-d');

            return [
                'id' => $data->id,
                'title' => "{$data->title} [{$data->procedure_type}]",
                'start' => "{$procedureDate}T07:00:00",
                'end'   => "{$procedureDate}T08:00:00",
                'backgroundColor' => 'green',
                'extendedProps' => [
                    'clinic' => $clinic,
                    'description' => '',
                    'attendance_type' => '',
                    'event_type' => 'PROC',
                    'seller' => '',
                    'uploaded_by' => 1,
'review_time' => null,
                    'status' => null,
                    'qualy' => null
                ]
            ];
        })->toArray();
    }

    private function getHolidays($clinic, $eventDate = null)
    {
        $query = DB::table('ad_holidays AS h')
            ->leftJoin('ad_employees AS e', 'h.employee_id', '=', 'e.id')
            ->select('h.id', 'e.name', 'h.start', 'h.end', 'h.notes')
            ->where('h.status', 1);

        /* 🔥 FILTRO POR FECHA (ALL DAY) */
        if ($eventDate) {
            $query->whereDate('h.start', '<=', $eventDate)
                ->whereDate('h.end', '>=', $eventDate);
        }

        $rows = $query->get();

        return $rows->map(function ($data) use ($clinic) {
            return [
                'id' => $data->id,
                'title' => $data->name,
                'start' => $data->start,
                'end' => $data->end,
                'allDay' => true,
                'backgroundColor' => '#ABAFD8',
                'extendedProps' => [
                    'clinic' => $clinic,
                    'description' => $data->notes,
                    'attendance_type' => '',
                    'event_type' => 'HOL',
                    'seller' => '',
                    'uploaded_by' => 1,
'review_time' => null,
                    'status' => null,
                    'qualy' => null
                ]
            ];
        })->toArray();
    }

    private function formatEvent($ev)
    {
        $color = $this->resolveEventColor($ev);

        /* =========================
       NORMALIZAR TIPO
    ========================= */

        $rawType = strtolower(trim($ev->event_type ?? ''));

        $tipoFinal = match ($rawType) {
            'revision'        => 'revision',
            'valoracion'      => 'valoracion',
            'tratamiento'     => 'tratamiento',
            'procedimiento',
            'proc'            => 'procedimiento',
            'evento'          => 'evento',
            default           => 'evento',
        };

        /* =========================
       CONSTRUIR TÍTULO (🔥 AQUÍ ESTABA LO QUE FALTABA)
    ========================= */

        $baseTitle = trim($ev->title ?? '');
        $suffix = '';

        switch ($tipoFinal) {

            case 'revision':
                // 🔥 USAR review_time
                $review = trim($ev->review_time ?? '');
                $suffix = $review
                    ? " [REV {$review}]"
                    : " [REV]";
                break;

            case 'tratamiento':
                $suffix = " [TRAT]";
                break;

            case 'valoracion':
                $seller = $ev->assessment?->seller ?? '';
                $suffix = $seller
                    ? " [VAL ({$seller})]"
                    : " [VAL]";
                break;

            // procedimiento y evento no llevan sufijo
            default:
                $suffix = '';
                break;
        }

        return [
            'id'    => $ev->id,
            'title' => $baseTitle . $suffix, // ✅ YA MUESTRA review_time
            'start' => $ev->start,
            'end'   => $ev->end,

            'backgroundColor' => $color,
            'borderColor'     => $color,

            'extendedProps' => [
                'clinic'       => $ev->clinic,
                'description'  => $ev->description,
                'attendance_type' => $ev->attendance_type,

                // 🔥 SEMÁNTICA REAL
                'event_type'   => $ev->event_type,   // legacy
                'tipo_final'   => $tipoFinal,         // frontend

                'seller'       => $ev->assessment?->seller ?? '',
                'uploaded_by'  => $ev->uploaded_by,
                'uploaded_by_name' => $ev->uploader?->name ?? '',

                'revision_time' => $ev->review_time, // 🔥 SE MANTIENE
                'status'        => $ev->status,
                'qualy'         => $ev->qualy,
                'protocolo_alejandro' => $ev->protocolo_alejandro,
            ]
        ];
    }





    // =========================
    // STORE: Crear evento
    // =========================


public function store(Request $request)
{
    try {

        /* ===============================
           VALIDACIÓN
        =============================== */
        $rules = [
            'event_type' => 'required|string',
            'event_date' => 'required|date',
            'start_date' => 'required|string',
            'end_date'   => 'required|string',
            'clinic'     => 'required|string',
        ];

        if ($request->event_type === 'evento') {
            $rules['event_name'] = 'required|string|max:244';
        } else {
            $rules['patient_name'] = 'required|string|max:244';
        }

        if ($request->event_type === 'valoracion') {
            $rules['seller'] = 'required|string';
        }

        Validator::make($request->all(), $rules)->validate();

        /* ===============================
           USUARIO
        =============================== */
        $userId = auth()->id();

        /* ===============================
           FECHAS
        =============================== */
        $start = $this->parseDateTimeFlexible($request->event_date, $request->start_date);
        $end   = $this->parseDateTimeFlexible($request->event_date, $request->end_date);

        if (!$start || !$end || $end <= $start) {
            return response()->json([
                'success' => false,
                'message' => 'Horario inválido'
            ], 422);
        }

        // ❌ BLOQUEO POR HORARIO ELIMINADO
        // $this->validateBlockedTime($request->clinic, $start, $end);

        /* ===============================
           DESCRIPTION NORMALIZADO
        =============================== */
        $description = $request->input('description')
            ?? $request->input('notes')
            ?? '';

        /* ===============================
           TÍTULO
        =============================== */
        $nombre = trim($request->patient_name ?? '');
        $expediente = trim($request->expediente ?? '');

        if ($request->event_type === 'evento') {
            $title = trim($request->event_name);
        } elseif ($request->event_type === 'valoracion') {
            $title = $nombre;
        } else {
            $title = $expediente
                ? "{$nombre} - {$expediente}"
                : $nombre;
        }

        /* ===============================
           INSERT EVENTO
        =============================== */
        $eventId = DB::table('sa_events')->insertGetId([
            'event_type'          => $request->event_type,
            'attendance_type'     => $request->input('attendance_type', 0),
            'title'               => $title,
            'start'               => $start,
            'end'                 => $end,
            'description'         => $description,
            'clinic'              => $request->clinic,
            'status'              => $request->input('status', 'Agendada'),
            'qualy'               => $request->input('qualy', 'Pendiente'),
            'review_time'         => $request->event_type === 'revision'
                ? $request->review_time
                : null,
            'uploaded_by'         => $userId,
            'protocolo_alejandro' => $request->filled('protocolo_alejandro') ? 1 : 0,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        /* ===============================
           VALORACIÓN
        =============================== */
        if ($request->event_type === 'valoracion') {
            DB::table('sa_assessment_events')->updateOrInsert(
                ['event_id' => $eventId],
                ['seller' => $request->seller]
            );
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Evento creado correctamente',
            'event_id' => $eventId
        ]);

    } catch (\Throwable $e) {

        logger()->error('❌ [STORE EVENT]', [
            'message' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error al guardar: ' . $e->getMessage()
        ], 500);
    }
}




    private function resolveEventColor($ev): string
    {
        $eventType = strtolower(trim($ev->event_type ?? ''));
        $status    = trim($ev->status ?? '');
        $qualy     = trim($ev->qualy ?? '');

        // 1️⃣ Color base por tipo
        switch ($eventType) {
            case 'revision':
                $color = '#ff948e';
                break;
            case 'valoracion':
                $color = '#7dc3ff';
                break;
            case 'tratamiento':
                $color = '#f7d96c';
                break;
            default:
                $color = '#ff0000';
        }

        // 2️⃣ Confirmada + Pendiente
        if ($status === 'Confirmada' && $qualy === 'Pendiente') {
            $color = '#d9a6e2';
        }

        // 3️⃣ Qualy (PRIORIDAD MÁXIMA)
        if ($qualy === 'Asistió') {
            $color = '#87d778';
        } elseif (in_array($qualy, ['No asistió', 'Reagendó'])) {
            $color = '#ff5555';
        }

        return $color;
    }



    /**
     * Intentar parsear una hora que puede venir en 12h (AM/PM) o 24h.
     */
    private function parseDateTimeFlexible(string $date, string $time)
    {
        $time = trim($time);
        // Possibles formatos:
        $formats = [
            'H:i',      // 24h e.g. 13:00
            'H:i:s',
            'g:i A',    // 12h e.g. 1:00 PM or 12:00 AM
            'g:iA',
            'h:i A',
            'h:iA',
        ];

        foreach ($formats as $fmt) {
            $dt = Carbon::createFromFormat("Y-m-d $fmt", "$date $time", config('app.timezone'));
            if ($dt !== false) return $dt;
        }

        // fallback: intentar parse general
        try {
            return new Carbon("$date $time");
        } catch (\Exception $ex) {
            return null;
        }
    }


    // =========================
    // UPDATE: Actualizar evento
    // =========================
public function update(Request $request)
{
    /* ============================================================
       1️⃣ NORMALIZAR CAMPOS ANTES DE VALIDAR
    ============================================================ */
    $request->merge([
        'review_time' => $request->input('review_time')
            ?? $request->input('revision_time'),
    ]);

    /* ============================================================
       2️⃣ VALIDACIÓN
    ============================================================ */
    $rules = [
        'event_id'   => 'required|integer',
        'event_type' => 'required|string',
        'event_date' => 'required|date',
        'start_date' => 'required|string',
        'end_date'   => 'required|string',
        'clinic'     => 'required|string',
        'review_time'=> 'nullable|string',
    ];

    if ($request->event_type === 'evento') {
        $rules['event_name'] = 'required|string|max:244';
    } else {
        $rules['patient_name'] = 'nullable|string|max:244';
    }

    $request->validate($rules);

    Log::info('🟡 [UPDATE EVENT] Request recibido', [
        'event_id'   => $request->event_id,
        'event_type' => $request->event_type,
        'clinic'     => $request->clinic,
        'user_id'    => auth()->id(),
    ]);

    try {

        /* ============================================================
           3️⃣ OBTENER EVENTO
        ============================================================ */
        $event = SaEvent::findOrFail($request->event_id);

        /* ============================================================
           4️⃣ FECHAS
        ============================================================ */
        $start = $this->parseDateTimeFlexible(
            $request->event_date,
            $request->start_date
        );

        $end = $this->parseDateTimeFlexible(
            $request->event_date,
            $request->end_date
        );

        if (!$start || !$end || $end->lessThanOrEqualTo($start)) {
            return response()->json([
                'success' => false,
                'message' => 'Horario inválido'
            ], 422);
        }

        // ❌ BLOQUEO POR HORARIO ELIMINADO
        // $this->validateBlockedTime($request->clinic, $start, $end);

        /* ============================================================
           6️⃣ CAMPOS NORMALIZADOS
        ============================================================ */
        $description = $request->input('description')
            ?? $request->input('notes')
            ?? '';

        $qualy  = $request->input('qualy')  ?: 'Pendiente';
        $status = $request->input('status') ?: 'Agendada';

        /* ============================================================
           7️⃣ TÍTULO
        ============================================================ */
        if ($request->event_type === 'evento') {
            $title = trim($request->event_name ?? '');
        } else {
            $nombre = trim($request->patient_name ?? '');
            $expediente = trim(
                $request->input('num_med_record')
                    ?? $request->input('expediente')
                    ?? ''
            );

            if ($request->event_type === 'valoracion') {
                $title = $nombre;
            } else {
                $title = $expediente ? "{$nombre} - {$expediente}" : $nombre;
            }
        }

        /* ============================================================
           8️⃣ UPDATE EVENTO
        ============================================================ */
        $event->update([
            'event_type'          => $request->event_type,
            'attendance_type'     => $request->input('attendance_type', 0),
            'title'               => $title,
            'start'               => $start->format('Y-m-d H:i:s'),
            'end'                 => $end->format('Y-m-d H:i:s'),
            'description'         => $description,
            'clinic'              => $request->clinic,
            'qualy'               => $qualy,
            'status'              => $status,
            'review_time'         => $request->review_time,
            'uploaded_by'         => auth()->id(),
            'protocolo_alejandro' => $request->filled('protocolo_alejandro') ? 1 : 0,
            'num_med_record'      => $expediente ?? null,
        ]);

        /* ============================================================
           9️⃣ VALORACIÓN
        ============================================================ */
        if ($request->event_type === 'valoracion') {
            SaAssessmentEvent::updateOrCreate(
                ['event_id' => $event->id],
                ['seller' => $request->seller ?? '']
            );
        } else {
            SaAssessmentEvent::where('event_id', $event->id)->delete();
        }

        /* ============================================================
           🔟 RESPUESTA JSON
        ============================================================ */
        return response()->json([
            'success' => true,
            'message' => 'Evento actualizado correctamente',
            'event'   => [
                'id'          => $event->id,
                'title'       => $event->title,
                'start'       => $event->start,
                'end'         => $event->end,
                'status'      => $event->status,
                'qualy'       => $event->qualy,
                'clinic'      => $event->clinic,
                'review_time' => $event->review_time,
                'event_type'  => $event->event_type,
            ]
        ]);

    } catch (\Throwable $e) {

        Log::error('❌ [UPDATE EVENT] Error al actualizar', [
            'event_id' => $request->event_id ?? null,
            'message'  => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar evento'
        ], 500);
    }
}



    // =========================
    // DESTROY: Eliminar evento
    // =========================
    public function destroy($id)
    {
        try {
            $event = SaEvent::find($id);
            if (!$event) throw new Exception("El evento no existe.");

            SaAssessmentEvent::where('event_id', $id)->delete();
            $event->delete();

            return response()->json(['success' => true, 'message' => 'Evento eliminado correctamente.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================
    // AGENDA POR CLÍNICA
    // =========================
    public function agendaByClinic(Request $request)
    {
        try {
            $clinic = trim($request->clinic ?? '');
            $date   = $request->date ?? date('Y-m-d');

            // Cache por clínica y fecha (10 minutos)
            $cacheKey = "agenda::{$clinic}::{$date}";

            $agenda = Cache::remember($cacheKey, 600, function () use ($clinic, $date) {
                $start = "$date 00:00:00";
                $end   = "$date 23:59:59";

                /* --------------------------------------------------------
             * 1) EVENTOS NORMALES (sa_events)
             * -------------------------------------------------------- */
                $events = SaEvent::with('assessment')
                    ->select(
                        'event_type',
                        'attendance_type',
                        'title',
                        'start',
                        'review_time'
                    )
                    ->where('clinic', $clinic)
                    ->whereBetween('start', [$start, $end])
                    ->get();

                $results = [];

                foreach ($events as $event) {
                    $results[] = (object)[
                        'event_type' => $event->event_type,
                        'attendance_type' => $event->attendance_type,
                        'title' => $event->title,
                        'start' => $event->start,
                        'start_hour' => date('l:i A', strtotime($event->start)),
                        'review_time' => $event->review_time,
                        'seller' => $event->assessment?->seller ?? ''
                    ];
                }

                /* --------------------------------------------------------
             * 2) PROCEDIMIENTOS (sa_leads_assessment) - excepto Pedregal
             * -------------------------------------------------------- */
                if ($clinic !== "Pedregal") {
                    $procedures = DB::table('sa_leads_assessment as l')
                        ->select(
                            DB::raw("'Procedimiento' AS event_type"),
                            DB::raw("0 AS attendance_type"),
                            DB::raw("CONCAT(l.first_name, ' ', l.last_name) AS title"),
                            DB::raw("CONCAT(l.procedure_date, ' 07:00:00') AS start"),
                            DB::raw("'07:00 AM' AS start_hour"),
                            'l.procedure_type AS review_time',
                            'l.closer AS seller'
                        )
                        ->whereDate('l.procedure_date', $date)
                        ->where('l.status', 1);

                    if ($clinic === "Queretaro") {
                        $procedures->where('l.clinic', 'Queretaro');
                    } elseif ($clinic === "Santafe") {
                        $procedures->whereIn('l.clinic', ['Santa Fe', 'Pedregal']);
                    }

                    foreach ($procedures->get() as $proc) {
                        $results[] = $proc;
                    }
                }

                // Ordenar por hora
                usort($results, fn($a, $b) => strtotime($a->start) <=> strtotime($b->start));

                return $results;
            });

            if (empty($agenda)) {
                return response()->json([
                    'success' => false,
                    'agenda' => 'No se encontraron eventos.'
                ]);
            }

            /* --------------------------------------------------------
         * 3) FORMATO FINAL DE AGENDA (texto tipo WhatsApp)
         * -------------------------------------------------------- */
            $agendaText = "";

            foreach ($agenda as $data) {
                $start_hour = trim(str_replace(':00', '', $data->start_hour));

                $attendance = ($data->attendance_type == 1)
                    ? 'VIRTUAL'
                    : '';

                $pre = $this->getEventPrefix($data);

                // Eliminar texto después del "-" en el nombre
                $name = trim(explode("-", $data->title)[0]);

                $agendaText .= "{$start_hour} {$name} - {$pre} {$attendance}\n\n";
            }

            return response()->json([
                'success' => true,
                'agenda' => trim($agendaText)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'agenda' => "Error: " . $e->getMessage()
            ]);
        }
    }

    // =========================
    // MÉTODOS PRIVADOS/PROTEGIDOS
    // =========================

    protected function validateRequest(Request $request)
    {
        $event_type = $request->input('event_type');
        $attendance_type = (int)$request->input('attendance_type', 0);
        $clinic = trim($request->input('clinic', ''));
        $uploaded_by = (int)$request->input('user_id', 0);
        $num_med_record = (int)($request->input('num_med_record') ?? 0);
        $patient_name = trim($request->input('patient_name', ''));
        $event_name = trim($request->input('event_name', ''));

        $date  = $request->input('event_date');
        $start = $request->input('start_date');
        $end   = $request->input('end_date');

        if (!$date || !$start || !$end) throw new Exception("Fecha y hora son requeridas.");

        $start_datetime = $this->parseDateTime($date, $start);
        $end_datetime = $this->parseDateTime($date, $end);

        if (!$start_datetime || !$end_datetime) throw new Exception("Formato de fecha/hora inválido.");

        $title = $this->buildTitle($event_type, $patient_name, $num_med_record, $event_name);

        return [
            'event_type' => $event_type,
            'attendance_type' => $attendance_type,
            'clinic' => $clinic,
            'uploaded_by' => $uploaded_by,
            'description' => $request->input('notes', ''),
            'status' => $request->input('status', ''),
            'qualy' => $request->input('qualy', ''),
            'review_time' => $request->input('revision_time', ''),
            'protocolo_alejandro' => !empty($request->input('protocolo_alejandro')) ? 1 : 0,
            'start_datetime' => $start_datetime,
            'end_datetime' => $end_datetime,
            'title' => $title,
            'seller' => $request->input('seller', '')
        ];
    }

    protected function parseDateTime($date, $time)
    {
        $formats = ['Y-m-d h:i A', 'Y-m-d H:i', 'Y-m-d G:i']; // AM/PM y 24h
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, "$date $time");
            } catch (\Exception $e) {
                continue;
            }
        }
        return null; // si no coincide con ningún formato
    }


    protected function buildTitle($event_type, $patient_name, $num_med_record, $event_name)
    {
        return match ($event_type) {
            'revision', 'tratamiento' => "{$patient_name} - {$num_med_record}",
            'valoracion' => $patient_name,
            'evento' => $event_name,
            default => throw new Exception('Tipo de evento desconocido.')
        };
    }

    protected function validateBlockedTime($clinic, $start, $end)
    {
        $clinic_clean = strtolower(trim($clinic));
        $day_of_week = (int) $start->format('N');

        if (in_array($clinic_clean, ['pedregal', 'santafe']) && $day_of_week >= 1 && $day_of_week <= 5) {
            $block_start = Carbon::parse($start->format('Y-m-d') . ' 13:00:00');
            $block_end   = Carbon::parse($start->format('Y-m-d') . ' 14:00:00');

            if ($start < $block_end && $end > $block_start) {
                throw new Exception("No se permite agendar eventos en $clinic de 1:00 PM a 2:00 PM de lunes a viernes.");
            }
        }
    }

    private function getEventPrefix($data)
    {
        switch ($data->event_type) {
            case 'revision':
                return "REV ({$data->review_time})";

            case 'valoracion':
                return "VAL ({$data->seller})";

            case 'tratamiento':
                return "TRAT";

            case 'evento':
                return "EV";

            case 'Procedimiento':
                return "PROC ({$data->review_time})";

            default:
                return "OTRO";
        }
    }


    public function searchEventsByTitle(Request $request)
    {
        try {
            $search = trim($request->input('search', ''));

            if ($search === '') {
                return response()->json([
                    'success' => false,
                    'coincidences' => [],
                    'message' => 'El campo de búsqueda es obligatorio'
                ]);
            }

            $results = SaEvent::select(
                'id',
                'event_type',
                'attendance_type',
                'title',
                'start',
                'clinic',
                'qualy',
                'review_time'
            )
                ->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('clinic', 'LIKE', "%{$search}%");
                })
                ->orderBy('start', 'DESC')
                ->limit(50)
                ->get();

            $events = [];

            foreach ($results as $data) {

                // 🔹 Asegurar Carbon (EVITA ERROR 500)
                $startDate = Carbon::parse($data->start);

                $attendance_type = $data->attendance_type ? 'Virtual' : 'Presencial';

                switch ($data->event_type) {
                    case 'revision':
                        $pre = "Revisión $attendance_type {$data->review_time}";
                        $title = "[$pre] {$data->title}";
                        break;

                    case 'valoracion':
                        $pre = "Valoración $attendance_type";
                        $title = "[$pre] {$data->title}";
                        break;

                    case 'tratamiento':
                        $title = "[Tratamiento] {$data->title}";
                        break;

                    case 'procedimiento':
                        $title = "[Procedimiento] {$data->title}";
                        break;

                    default:
                        $title = "[Evento] {$data->title}";
                }

                $events[] = [
                    'id'     => $data->id,
                    'title'  => $title,
                    'start'  => $startDate->toDateTimeString(), // FullCalendar-friendly
                    'date'   => $startDate->format('d/m/Y'),
                    'clinic' => $data->clinic,
                    'qualy'  => $data->qualy
                ];
            }

            return response()->json([
                'success' => count($events) > 0,
                'coincidences' => $events
            ]);
        } catch (\Throwable $e) {

            // 🔥 Log para depuración real
            \Log::error('Error searchEventsByTitle', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'coincidences' => [],
                'message' => 'Error interno al buscar eventos'
            ], 500);
        }
    }

public function copyAgenda(Request $request)
{
    $request->validate([
        'clinic' => 'required|string',
        'target_date' => 'required|date'
    ]);

    $clinic = $request->clinic;
    $date   = Carbon::parse($request->target_date);
    $ymd    = $date->format('Y-m-d');

    // 1) EVENTOS (AGENDA)
    $events = SaEvent::where('clinic', $clinic)
        ->whereDate('start', $ymd)
        ->orderBy('start')
        ->get();

    // 2) PROCEDIMIENTOS DEL DÍA (sa_leads_assessment)
    $procQuery = DB::table('sa_leads_assessment as sla')
        ->join('sa_leads as sl', 'sl.id', '=', 'sla.lead_id')
        ->leftJoin('enf_procedures as ep', 'ep.lead_id', '=', 'sla.lead_id')
        ->whereDate('sla.procedure_date', $ymd)
        ->where('sla.status', 1) // <- ajusta si tu status es distinto
        ->select(
            'sla.lead_id',
            DB::raw("CONCAT(sla.first_name,' ',sla.last_name) as name"),
            'sla.procedure_type',
            'sla.clinic',
            'ep.num_med_record',
            'ep.room',
            'ep.specialist'
        )
        ->orderByDesc('ep.num_med_record');

    // mismo criterio que tu loadPatients()
    if ($clinic === 'Queretaro') {
        $procQuery->where('sla.clinic', 'Queretaro');
    } else {
        $procQuery->where('sla.clinic', '!=', 'Queretaro');
    }

    // para evitar duplicados por múltiples registros en enf_procedures:
    $procedimientos = $procQuery
        ->groupBy(
            'sla.lead_id',
            'sla.first_name',
            'sla.last_name',
            'sla.procedure_type',
            'sla.clinic',
            'ep.num_med_record',
            'ep.room',
            'ep.specialist'
        )
        ->get();

    // Si no hay nada de nada, regresa error
    if ($events->isEmpty() && $procedimientos->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No hay eventos ni procedimientos para copiar en esa fecha.'
        ], 422);
    }

    // Fecha en español
    $fechaTexto = ucfirst($date->locale('es')->translatedFormat('l d \\d\\e F'));

    $clipboard = "Hola, les informo de las actividades para el {$fechaTexto}:\n\n";

/*
|--------------------------------------------------------------------------
| 1) PROCEDIMIENTOS DEL DÍA (ARRIBA)
|--------------------------------------------------------------------------
*/
$clipboard .= "Procedimientos del día:\n\n";

if ($procedimientos->isNotEmpty()) {
    foreach ($procedimientos as $p) {
        $line = "- {$p->name}";

        if (!empty($p->num_med_record)) {
            $line .= " - {$p->num_med_record}";
        }

        if (!empty($p->procedure_type)) {
            $line .= " | " . strtoupper($p->procedure_type);
        }

        if (!empty($p->room)) {
            $line .= " | Sala: {$p->room}";
        }

        if (!empty($p->specialist)) {
            $line .= " | Esp: {$p->specialist}";
        }

        $clipboard .= $line . "\n";
    }
} else {
    $clipboard .= "— No hay procedimientos registrados.\n";
}

$clipboard .= "\n";

/*
|--------------------------------------------------------------------------
| 2) AGENDA / CITAS (ABAJO)
|--------------------------------------------------------------------------
*/
$clipboard .= " Agenda general:\n\n";

if ($events->isNotEmpty()) {
    foreach ($events as $event) {
        $hora  = Carbon::parse($event->start)->format('g:iA');
        $title = trim((string)$event->title);

        // tipo
        $tipo = null;
        if (!empty($event->event_type)) {
            $tipo = strtoupper(trim($event->event_type));
        } elseif (preg_match('/\[(.*?)\]/', $title, $m)) {
            $tipo = strtoupper(trim($m[1]));
        }

        $titleSinTipo = preg_replace('/\s*\[(.*?)\]\s*/', ' ', $title);
        $titleSinTipo = trim(preg_replace('/\s+/', ' ', $titleSinTipo));

        $line = "{$hora} {$titleSinTipo}";

        if (!empty($event->num_med_record)) {
            $line .= " - {$event->num_med_record}";
        }

        if (!empty($tipo)) {
            $line .= " | {$tipo}";
        }

        if (!empty($event->review_time)) {
            $line .= " {$event->review_time}";
        }

        if (!empty($event->room)) {
            $line .= " | Sala: {$event->room}";
        }

        if (!empty($event->specialist)) {
            $line .= " | Esp: {$event->specialist}";
        }

        $clipboard .= $line . "\n\n";
    }
} else {
    $clipboard .= "— No hay citas confirmadas en agenda.\n";
}


    return response()->json([
        'success'   => true,
        'clipboard' => trim($clipboard)
    ]);
}



public function searchEventsByMedicalRecord(Request $request)
{
    try {

        $num = trim($request->input('num_med_record', ''));

        // 🔒 mínimo 2 caracteres
        if (strlen($num) < 2) {
            return response()->json([
                'success' => false,
                'results' => [],
                'message' => 'Ingrese al menos 2 caracteres'
            ], 422);
        }

        /* ============================================================
           🔎 BÚSQUEDA EXACTA
           num_med_record + nombre completo
        ============================================================ */
        $results = DB::table('enf_procedures as p')
            ->join('sa_leads_assessment as a', 'a.lead_id', '=', 'p.lead_id')
            ->select(
                'p.num_med_record',
                DB::raw("CONCAT(a.first_name, ' ', a.last_name) as patient_name")
            )
            ->where('p.num_med_record', '=', $num) // 🔥 EXACTO
            ->limit(1)
            ->get();

        return response()->json([
            'success' => $results->count() > 0,
            'results' => $results
        ]);

    } catch (\Throwable $e) {

        \Log::error('Error searchEventsByMedicalRecord', [
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'results' => [],
            'message' => 'Error interno al buscar por expediente'
        ], 500);
    }
}


    public function storeEvento(Request $request)
    {
        \Log::info('📌 storeEvento INICIO', [
            'request_all' => $request->all(),
            'user_id'     => auth()->id(),
        ]);

        try {

            /* ================= VALIDACIÓN ================= */
            $request->validate([
                'event_name' => 'required|string|max:244',
                'event_date' => 'required|date',
                'start_date' => 'required',
                'end_date'   => 'required',
                'clinic'     => 'required|string',
                'status'     => 'nullable|string',
                'qualy'      => 'nullable|string',
            ]);

            $userId = auth()->id();
            if (!$userId) {
                throw new \Exception('Usuario no autenticado');
            }

            /* ================= FECHAS ================= */
            $start = Carbon::parse($request->event_date . ' ' . $request->start_date);
            $end   = Carbon::parse($request->event_date . ' ' . $request->end_date);

            if ($end->lte($start)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La hora de término debe ser mayor a la de inicio',
                ], 422);
            }

            /* ================= INSERT ================= */
            $id = DB::table('sa_events')->insertGetId([
                'event_type'          => 'evento',
                'attendance_type'     => 0,
                'title'               => strtoupper(trim($request->event_name)),
                'start'               => $start,
                'end'                 => $end,

                // 🔥 CLAVE: FORZAR STRING (NUNCA NULL)
                'description'         => (string) $request->input('notes', ''),

                'clinic'              => $request->clinic,
                'qualy'               => $request->input('qualy', 'Pendiente'),
                'status'              => $request->input('status', 'Agendada'),
                'review_time'         => '',
                'uploaded_by'         => $userId,
                'protocolo_alejandro' => 0,
            ]);

            \Log::info('✅ Evento creado correctamente', [
                'event_id' => $id,
                'clinic'   => $request->clinic,
            ]);

            return response()->json([
                'success' => true,
                'id'      => $id,
                'message' => 'Evento creado correctamente',
            ]);
        } catch (\Throwable $e) {

            \Log::error('❌ ERROR storeEvento', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
