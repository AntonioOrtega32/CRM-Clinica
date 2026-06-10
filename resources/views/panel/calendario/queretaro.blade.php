@extends('panel.layouts.panel')
@section('title', 'Queretaro')

@section('content')
    <div @class(['min-h-screen', 'bg-gray-100', 'p-6', 'flex', 'gap-6'])>

        {{-- Sidebar --}}
        <div @class(['w-1/4', 'hidden', 'md:flex', 'flex-col', 'space-y-6'])>

            <!-- Mini Calendar -->
            <div @class(['bg-white', 'rounded-xl', 'shadow-md', 'p-4'])>
                <h3 @class(['font-semibold', 'text-center', 'mb-3', 'text-gray-700'])>Mini Calendario</h3>
                <div @class(['calendar-container'])>
                    <div @class(['flex', 'justify-between', 'items-center', 'mb-2'])>
                        <select @class(['calendar-years', 'border', 'rounded', 'px-2', 'py-1'])></select>
                        <select @class(['calendar-months', 'border', 'rounded', 'px-2', 'py-1'])></select>
                    </div>
                    <div @class(['flex', 'justify-between', 'mt-2'])>
                        <button @class([
                            'calendar-left-arrow',
                            'bg-indigo-100',
                            'hover:bg-indigo-200',
                            'rounded',
                            'px-2',
                            'py-1',
                        ])>←</button>
                        <button @class([
                            'calendar-today-button',
                            'bg-green-100',
                            'hover:bg-green-200',
                            'rounded',
                            'px-2',
                            'py-1',
                        ])>Hoy</button>
                        <button @class([
                            'calendar-right-arrow',
                            'bg-indigo-100',
                            'hover:bg-indigo-200',
                            'rounded',
                            'px-2',
                            'py-1',
                        ])>→</button>
                    </div>
                    <ul @class([
                        'calendar-week',
                        'flex',
                        'justify-between',
                        'text-center',
                        'font-semibold',
                        'mt-3',
                        'text-gray-600',
                    ])></ul>
                    <ul @class(['calendar-days', 'grid', 'grid-cols-7', 'gap-1', 'mt-2'])></ul>
                </div>
            </div>

            <!-- Buscar citas -->
            <div @class(['bg-white', 'rounded-xl', 'shadow-md', 'p-4'])>
                <h4 @class(['text-center', 'text-gray-700', 'font-semibold', 'mb-3'])>Buscar citas</h4>
                <form method="POST" id="search_appointments">
                    @csrf
                    <div @class(['flex', 'gap-2'])>
                        <input type="text" placeholder="Nombre o Expediente ..." @class([
                            'flex-1',
                            'border',
                            'rounded',
                            'px-3',
                            'py-2',
                            'focus:ring-2',
                            'focus:ring-indigo-400',
                        ])
                            name="search" id="search" minlength="3" required>
                        <button type="submit" @class([
                            'bg-indigo-600',
                            'hover:bg-indigo-700',
                            'text-white',
                            'px-4',
                            'py-2',
                            'rounded',
                        ])>Buscar</button>
                    </div>
                </form>
            </div>

            <div id="searchResultsModal" @class([
                'fixed',
                'inset-0',
                'bg-black',
                'bg-opacity-50',
                'flex',
                'items-center',
                'justify-center',
                'hidden',
                'z-50',
            ])>
                <div @class([
                    'bg-white',
                    'rounded-xl',
                    'shadow-lg',
                    'w-full',
                    'max-w-2xl',
                    'p-6',
                    'relative',
                ])>
                    <button onclick="closeModal()" @class([
                        'absolute',
                        'top-3',
                        'right-3',
                        'text-gray-500',
                        'hover:text-gray-700',
                        'text-2xl',
                    ])>&times;</button>
                    <h3 @class(['text-lg', 'font-semibold', 'mb-4'])>Resultados de la búsqueda</h3>
                    <div id="searchResults" @class(['space-y-2', 'max-h-80', 'overflow-y-auto'])></div>
                    <div @class(['mt-4', 'text-right'])>
                        <button onclick="closeModal()" @class([
                            'bg-gray-300',
                            'hover:bg-gray-400',
                            'text-gray-700',
                            'px-4',
                            'py-2',
                            'rounded',
                        ])>Cerrar</button>
                    </div>
                </div>
            </div>
            <div @class([
                'bg-white',
                'rounded-xl',
                'shadow-md',
                'p-4',
                'flex',
                'flex-col',
                'gap-2',
            ])>
                <h4 @class(['text-gray-700', 'font-semibold'])>Copiar agenda</h4>

                <form id="copyAgendaForm" method="POST" action="{{ route('panel.calendar.copyAgenda') }}">
                    @csrf

                    <input type="text" name="clinic" value="Queretaro">

                    <input type="date" id="target_date" name="target_date"
                        value="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}">

                    <button type="submit">Copiar Agenda</button>
                </form>
            </div>



            <!-- Filtros -->
            <div @class(['bg-white', 'rounded-xl', 'shadow-md', 'p-4', 'space-y-2'])>
                <h4 @class(['text-gray-700', 'font-semibold', 'mb-2'])>Viendo:</h4>

                @foreach ([
            'revision' => 'Revisiones',
            'valoracion' => 'Valoraciones',
            'tratamiento' => 'Tratamientos',
            'evento' => 'Eventos',
            'holidays' => 'Vacaciones',
        ] as $key => $label)
                    <div @class(['flex', 'items-center'])>
                        <input @class(['cb_event_type', 'mr-2']) type="checkbox" id="check{{ ucfirst($key) }}"
                            value="{{ $key }}" checked>
                        <label @class(['text-gray-600', 'font-medium']) for="check{{ ucfirst($key) }}">
                            {{ $label }}
                        </label>
                    </div>
                @endforeach
            </div>


        </div>

        {{-- Calendario principal --}}
        <div @class(['flex-1', 'bg-white', 'rounded-xl', 'shadow-xl', 'p-6'])>
            <!-- ========================== BOTÓN PARA ABRIR MODAL ========================== -->
            <button onclick="window.dispatchEvent(new CustomEvent('open-event-modal', { detail: { mode: 'create' } }))"
                @class(['px-4', 'py-2', 'text-white', 'bg-blue-600', 'rounded-lg'])>
                Crear evento
            </button>

            <div id="calendar" @class(['w-full'])></div>
        </div>

    </div>

    @include('panel.calendario.event-modal')
@endsection

@section('scripts')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@5.11.3/main.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="//cdn.jsdelivr.net/npm/toastr/build/toastr.min.js"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <script>
        /* ==========================================================================
                                                                                                                                   CONFIG GLOBAL
                                                                                                                                   ========================================================================== */
        window.calendarConfig = {
            indexRoute: "{{ route('calendar.index') }}",
            csrfToken: "{{ csrf_token() }}",
            searchRoute: "{{ route('calendar.search') }}"
        };

        /* ==========================================================================
           MODAL BUSCADOR
           ========================================================================== */
        function openModal() {
            const modal = document.getElementById('searchResultsModal');
            if (!modal) {
                console.error('❌ searchResultsModal no existe');
                return;
            }
            modal.classList.remove('hidden');
        }

        function closeModal() {
            const modal = document.getElementById('searchResultsModal');
            if (!modal) return;
            modal.classList.add('hidden');
        }


        /* ==========================================================================
           BUSCAR EVENTOS (Versión moderna estilo PHP viejo)
           ========================================================================== */
        $("#search_appointments").submit(function(e) {
            e.preventDefault();

            const search = $("#search").val().trim();
            if (search.length < 3) return;

            $.ajax({
                    url: window.calendarConfig.searchRoute,
                    method: "POST",
                    dataType: "json",
                    data: {
                        search: search,
                        clinic: $("#clinic").val()
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: "Buscando...",
                            text: "Por favor, espera",
                            showConfirmButton: false,
                            allowOutsideClick: false
                        });
                    }
                })
                .done(function(response) {
                    Swal.close();

                    const resultsDiv = $("#searchResults");
                    resultsDiv.html("");

                    // ❌ No hay coincidencias
                    if (!response.success || response.coincidences.length === 0) {
                        resultsDiv.html('<p @class(['text-gray-500'])>No se encontraron resultados.</p>');
                        openModal();
                        return;
                    }

                    // ✅ Mostrar coincidencias
                    response.coincidences.forEach(event => {

                        const item = $(`
                    <div @class(['cursor-pointer', 'p-2', 'border-b', 'hover:bg-gray-100'])>
                        <strong>${event.title}</strong><br>
                        <small>${event.date}</small>
                    </div>
                `);

                        // ==========================================
                        // 📌 Clic → cambiar a vista por día y cargar fecha
                        // ==========================================
                        item.on("click", function() {

                            // Convertir dd/mm/yyyy → yyyy-mm-dd
                            let [day, month, year] = event.date.split("/");
                            const finalDate = `${year}-${month}-${day}T00:00:00`;

                            // Ir a vista por día SIEMPRE
                            calendar.changeView("timeGridDay");
                            calendar.gotoDate(finalDate);

                            closeModal();
                        });

                        resultsDiv.append(item);
                    });

                    openModal();
                })
                .fail(function(response) {
                    console.log(response);
                    Swal.close();
                    Swal.fire("Error", "Hubo un problema en la búsqueda", "error");
                });
        });

        /* ==========================================================================
           FULLCALENDAR
           ========================================================================== */
        $(document).ready(function() {

            let chosenClinic = "Queretaro";
            $("#chosen_clinic").val(chosenClinic);

            const calendarEl = document.getElementById('calendar');

            window.calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridDay',
                locale: 'es',
                height: '80vh',
                selectable: true,


                eventContent(arg) {

                    const title = arg.event.title;
                    const timeText = arg.timeText;

                    return {
                        html: `
            <div class="fc-custom-event">
                <div class="fc-custom-title">${title}</div>
            </div>
        `
                    };
                },

                /* 🔥🔥🔥 ESTO ES LO QUE FALTA 🔥🔥🔥 */
                slotEventOverlap: true,
                eventOverlap: true,
                dayMaxEvents: false,
                dayMaxEventRows: false,
                eventDisplay: 'block',

                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridDay,timeGridWeek,listWeek'
                },

                slotMinTime: "07:00:00",
                slotMaxTime: "19:00:00",

                slotLabelFormat: {
                    hour: "numeric",
                    minute: "2-digit",
                    hour12: true
                },

                eventTimeFormat: {
                    hour: "numeric",
                    minute: "2-digit",
                    hour12: true,
                    meridiem: "short"
                },

                /* =========================================================
                   CARGAR EVENTOS DESDE SERVIDOR (CON COLORES DINÁMICOS)
                   ========================================================= */
                events: async function(fetchInfo, successCallback, failureCallback) {
                    try {
                        const y = fetchInfo.start.getFullYear();
                        const m = String(fetchInfo.start.getMonth() + 1).padStart(2, '0');
                        const d = String(fetchInfo.start.getDate()).padStart(2, '0');

                        const event_date = `${y}-${m}-${d}`;

                        const response = await $.ajax({
                            url: window.calendarConfig.indexRoute,
                            method: "GET",
                            data: {
                                clinic: chosenClinic,
                                event_date
                            },
                            dataType: "json"
                        });

                        if (!response.success) {
                            successCallback([]);
                            return;
                        }

                        /* Normalizador */
                        const normalize = (txt) => {
                            return txt ?
                                txt.toString().toLowerCase()
                                .normalize("NFD")
                                .replace(/[\u0300-\u036f]/g, "") :
                                "";
                        };


                        const normalizeKey = (txt) =>
                            (txt ?? '')
                            .toString()
                            .trim()
                            .toLowerCase()
                            .normalize("NFD")
                            .replace(/[\u0300-\u036f]/g, '')
                            .replace(/[^a-z0-9]/g, '');

                        const typeAlias = {
                            proc: 'procedimiento',
                            procedimiento: 'procedimiento',
                            revision: 'revision',
                            valoracion: 'valoracion',
                            tratamiento: 'tratamiento',
                            evento: 'evento',
                            holidays: 'holidays'
                        };

                    const events = response.events
    .map(e => {

        const tipoRaw = normalizeKey(e.extendedProps?.event_type);

        const typeAlias = {
            proc: 'procedimiento',
            procedimiento: 'procedimiento',
            revision: 'revision',
            valoracion: 'valoracion',
            tratamiento: 'tratamiento',
            evento: 'evento'
        };

        const tipo_final = typeAlias[tipoRaw] ?? 'evento';

        return {
            id: e.id,
            title: e.title,
            start: e.start,
            end: e.end,

            backgroundColor: e.backgroundColor,
            borderColor: e.borderColor,

            classNames: [
                tipo_final === 'procedimiento' ? 'evento-procedimiento' : null,
                e.extendedProps?.protocolo_alejandro ? 'protocolo-alejandro' : null
            ].filter(Boolean),

            extendedProps: {
                ...e.extendedProps,
                tipo_final // 🔥 VUELVE A EXISTIR
            }
        };
    })
    .filter(ev =>
        window.activeEventTypes.includes(ev.extendedProps.tipo_final)
    );


                        successCallback(events);






                    } catch (err) {
                        console.error(err);
                        failureCallback(err);
                    }
                },

                /* =========================================================
                   CLICK EN DÍA → CREAR
                   ========================================================= */
                dateClick(info) {
                    window.dispatchEvent(new CustomEvent('open-event-modal', {
                        detail: {
                            mode: 'create',
                            date: info.date
                        }
                    }));
                },

                eventClick(info) {
                    window.dispatchEvent(new CustomEvent('open-event-modal', {
                        detail: {
                            mode: 'edit',
                            info
                        }
                    }));
                }



            });

            calendar.render();

            /* =========================================================
               CAMBIAR CLÍNICA
               ========================================================= */
            $('#chosen_clinic').on('change', function() {
                chosenClinic = $(this).val();
                calendar.refetchEvents();
            });

            /* ==========================================================================
               EDITAR EVENTO — COMPLETO
               ========================================================================== */


        });
        /* =========================================================
           MINI CALENDARIO — CONFIGURACIÓN INICIAL
           ========================================================= */

        function buildMiniCalendar(year, month) {
            const daysContainer = $(".calendar-days");
            const weekContainer = $(".calendar-week");

            daysContainer.html("");
            weekContainer.html("");

            const weekDays = ["D", "L", "M", "M", "J", "V", "S"];

            weekDays.forEach(d => {
                weekContainer.append(`<li @class(['w-full', 'text-center'])>${d}</li>`);
            });

            const firstDay = new Date(year, month, 1).getDay();
            const totalDays = new Date(year, month + 1, 0).getDate();

            // Espacios antes del día 1  
            for (let i = 0; i < firstDay; i++) {
                daysContainer.append(`<li></li>`);
            }

            // Días del mes  
            for (let d = 1; d <= totalDays; d++) {
                daysContainer.append(`
            <li @class([
                'calendar-day',
                'cursor-pointer',
                'text-center',
                'py-1',
                'rounded',
            ]) 
                data-day="${d}">
                ${d}
            </li>
        `);
            }

            highlightToday(year, month);
        }

        /* =========================================================
           MARCAR DÍA ACTUAL
           ========================================================= */
        function highlightToday(year, month) {
            const today = new Date();

            if (today.getFullYear() === year && today.getMonth() === month) {
                $(`.calendar-day[data-day='${today.getDate()}']`).css({
                    background: "#e0ac44",
                    color: "white",
                    "border-radius": "6px"
                });
            }
        }

        /* =========================================================
           CARGAR SELECTS AÑO / MES
           ========================================================= */

        function loadYearMonthSelectors() {
            const yearSelect = $(".calendar-years");
            const monthSelect = $(".calendar-months");

            const currentYear = new Date().getFullYear();
            const currentMonth = new Date().getMonth();

            // Años (5 atrás, 5 adelante)
            for (let y = currentYear - 5; y <= currentYear + 5; y++) {
                yearSelect.append(`<option value="${y}">${y}</option>`);
            }

            // Meses
            const monthNames = [
                "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
                "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
            ];

            monthNames.forEach((name, i) => {
                monthSelect.append(`<option value="${i}">${name}</option>`);
            });

            yearSelect.val(currentYear);
            monthSelect.val(currentMonth);

            buildMiniCalendar(currentYear, currentMonth);
        }

        /* =========================================================
           EVENTOS: CAMBIAR MES / AÑO
           ========================================================= */

        $(document).on("change", ".calendar-years, .calendar-months", function() {
            const year = parseInt($(".calendar-years").val());
            const month = parseInt($(".calendar-months").val());

            buildMiniCalendar(year, month);
        });

        /* =========================================================
           FLECHAS ← →
           ========================================================= */

        $(".calendar-left-arrow").on("click", function() {
            let year = parseInt($(".calendar-years").val());
            let month = parseInt($(".calendar-months").val());

            month--;
            if (month < 0) {
                month = 11;
                year--;
            }

            $(".calendar-years").val(year);
            $(".calendar-months").val(month);

            buildMiniCalendar(year, month);
        });

        $(".calendar-right-arrow").on("click", function() {
            let year = parseInt($(".calendar-years").val());
            let month = parseInt($(".calendar-months").val());

            month++;
            if (month > 11) {
                month = 0;
                year++;
            }

            $(".calendar-years").val(year);
            $(".calendar-months").val(month);

            buildMiniCalendar(year, month);
        });

        /* =========================================================
           BOTÓN HOY
           ========================================================= */

        $(".calendar-today-button").on("click", function() {
            const today = new Date();
            const year = today.getFullYear();
            const month = today.getMonth();

            $(".calendar-years").val(year);
            $(".calendar-months").val(month);

            buildMiniCalendar(year, month);

            calendar.changeView("timeGridDay");
            calendar.gotoDate(today);
        });

        /* =========================================================
           CLICK EN DÍA → CAMBIAR FULLCALENDAR
           ========================================================= */

        $(document).on("click", ".calendar-day", function() {
            const year = parseInt($(".calendar-years").val());
            const month = parseInt($(".calendar-months").val());
            const day = parseInt($(this).data("day"));

            // Limpia estilos
            $(".calendar-day").css({
                background: "white",
                color: "black"
            });

            // Marca selección
            $(this).css({
                background: "#e0ac44",
                color: "white",
                "border-radius": "6px"
            });

            const date = new Date(year, month, day);

            calendar.changeView("timeGridDay");
            calendar.gotoDate(date);
        });

        /* =========================================================
           INICIAR MINI CALENDARIO
           ========================================================= */
        loadYearMonthSelectors();

        // Copiar agenda de hoy
        $("#copy_agenda").click(function() {

            $.ajax({
                    url: "{{ route('panel.calendar.agendaByClinic') }}",
                    method: "POST",
                    data: {
                        clinic: $("#clinic").val(),
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: "json",
                })
                .done(function(response) {

                    if (response.success) {

                        toastr.success("Agenda copiada al portapapeles", "Listo!", {
                            positionClass: "toast-top-left",
                        });

                        let messageCopy =
                            "Hola buen día, les confirmo la agenda del día de hoy:\n\n" +
                            response.agenda;

                        copyToClipboard(messageCopy);
                    } else {
                        showSweetAlert("😟", response.message, "error");
                    }
                })

                .fail(function(xhr) {
                    console.log(xhr.responseText);
                    showSweetAlert("Error", "No se pudo obtener la agenda");
                });
        });


        // Copiar agenda de mañana (plantilla)
        const copyAgendaForm = document.getElementById('copyAgendaForm');

        if (copyAgendaForm) {
            copyAgendaForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                fetch("{{ route('panel.calendar.copyAgenda') }}", {
                        method: "POST",
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            navigator.clipboard.writeText(data.clipboard);
                            alert("Agenda copiada ✔\n\nYa la tienes en tu portapapeles.");
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => console.error(error));
            });
        }

        // Filtros de tipos activos
window.activeEventTypes = [
    'revision',
    'valoracion',
    'tratamiento',
    'procedimiento', // 👈 ESTE
    'evento',
    'holidays'
];

        // Detectar cambios en los checkboxes
        $(document).on('change', '.cb_event_type', function() {

            // Guardar tipos seleccionados
            window.activeEventTypes = [];
            $('.cb_event_type:checked').each(function() {
                window.activeEventTypes.push($(this).val());
            });

            // Recargar eventos del calendario
            if (window.calendar) {
                window.calendar.refetchEvents();
            }
        });

        // Mostrar alertas de sesión con SweetAlert
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ session('error') }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif
        });
    </script>

   <style>
        /* =========================================================
                                   FULLCALENDAR – AGENDA CLÍNICA (FINAL)
                                ========================================================= */

        #calendar {
            width: 100%;
        }

        /* ---------------------------
                                   CONTENEDOR GENERAL
                                --------------------------- */
        .fc {
            min-height: calc(100vh - 180px) !important;
            font-family: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f9fafb;
        }

        /* ---------------------------
                                   OCULTAR ALL-DAY
                                --------------------------- */
        .fc-timegrid-all-day {
            display: none !important;
        }

        /* ---------------------------
                                   ALTURA DE SLOTS (COMPACTO)
                                --------------------------- */
        .fc-timegrid-slot {
            height: 43px !important;
            /* 👈 MÁS COMPACTO */
            border-top: 1px dashed rgba(0, 0, 0, 0.05) !important;
        }

        /* ---------------------------
                                   EVENTO BASE
                                --------------------------- */
        .fc-event {
            border-radius: 10px !important;
            padding: 2px 6px !important;
            font-size: 13px;
        }

        /* ==============================
                                   PROCEDIMIENTOS (VERDE)
                                ============================== */

        /* Texto negro en procedimientos */


        /* Hover */


        /* ---------------------------
                                   CONTENIDO CUSTOM (eventContent)
                                --------------------------- */
        .fc-custom-event {
            display: flex;
            flex-direction: column;
            justify-content: center;
            /* 👈 CENTRADO VERTICAL */
            height: 100%;
        }

        /* Hora */
        /* Hora (más fina) */
        .fc-custom-time {
            font-size: 11px;
            font-weight: 400;
            /* normal */
            opacity: 0.85;
        }

        /* Título del evento (más fino) */
        .fc-custom-title {
            font-size: 14px;
            font-weight: 400;
            /* 👈 CLAVE */
            line-height: 1.25;
        }

        /* ---------------------------
                                   TEXTO NORMAL (NEGRO)
                                --------------------------- */
        .fc-timegrid-event .fc-custom-time,
        .fc-timegrid-event .fc-custom-title {
            color: #000000 !important;
        }

        /* ---------------------------
                                   PROCEDIMIENTOS (VERDE)
                                --------------------------- */
        .fc-event.evento-procedimiento {
            background-color: #0f8a1f !important;
            border-color: #0f8a1f !important;
        }

        /* ---------------------------
                                   PROTOCOLO ALEJANDRO
                                --------------------------- */
        .fc-event.protocolo-alejandro {
            background-color: #111827 !important;
            border-color: #111827 !important;
        }

        .fc-event.protocolo-alejandro .fc-custom-time,
        .fc-event.protocolo-alejandro .fc-custom-title {
            color: #ffffff !important;
        }

        /* ---------------------------
                                   EVENTOS SUPERPUESTOS
                                --------------------------- */
        .fc-timegrid-event-harness {
            margin-right: 6px;
        }

        /* ---------------------------
                                   BOTONES
                                --------------------------- */
        .fc-button {
            background: linear-gradient(135deg, #4f46e5, #6366f1) !important;
            border: none !important;
            border-radius: 10px !important;
            padding: 7px 16px !important;
            font-weight: 600;
            color: #fff !important;
        }

        .fc-button-active {
            background: linear-gradient(135deg, #312e81, #4338ca) !important;
        }

        /* ---------------------------
                                   TÍTULO FECHA
                                --------------------------- */
        .fc-toolbar-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
        }

        /* ---------------------------
                                   RESPONSIVE
                                --------------------------- */
        @media (max-width: 768px) {
            .fc-timegrid-slot {
                height: 30px !important;
            }

            .fc-custom-title {
                font-size: 13px;
            }

            .fc-custom-time {
                font-size: 10px;
            }
        }
    </style>
@endsection
