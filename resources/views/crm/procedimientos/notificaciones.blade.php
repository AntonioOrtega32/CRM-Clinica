@extends('panel.layouts.panel')

@section('title', 'Notificaciones de Procedimientos')

@section('content')

    {{-- 
x-data (Alpine.js):
showSelectNotif: Muestra/oculta el modal de selección de notificación.
showForm: Muestra/oculta el formulario dinámico (hora, archivo, etc.)
selectedProcess: Almacena el ID del proceso de notificación (1, 2, 3... o -1 para incidencia)
selectedProcedure: Almacena el tipo de procedimiento (1, 2, 3)
--}}
    <div class="w-full min-h-screen bg-gray-100 p-6">

        <div class="w-full max-w-6xl mx-auto bg-white shadow-lg rounded-xl p-8">

            {{-- BREADCRUMBS --}}
            <nav class="text-sm text-gray-600 mb-6">
                <ol class="flex space-x-2">
                    <li>Dashboard</li>
                    <li>/</li>
                    <li>Procedimientos</li>
                    <li>/</li>
                    <li class="text-blue-600 font-semibold">Notificaciones</li>
                </ol>
            </nav>

            <h2 class="text-2xl font-semibold mb-4 text-[#1C6C73]">Notificaciones del Procedimiento</h2>

            <p class="text-gray-700 leading-relaxed">
                En esta sección enviarás notificaciones del
                <span class="text-red-600 font-semibold">procedimiento en curso.</span>
            </p>

            <p class="mt-4 font-semibold text-gray-700">
                Selecciona el tipo de procedimiento:
            </p>

            {{-- TABS PROCEDIMIENTO --}}
            <div class="flex justify-end space-x-6 mt-2 border-b border-gray-200" id="types_procedures">
                <button data-procedure="1"
                    class="nav-link pb-2 transition border-b-2 border-blue-600 text-blue-700 font-semibold">
                    Proced.
                </button>
                <button data-procedure="2" class="nav-link pb-2 transition">2do Proced</button>
                <button data-procedure="3" class="nav-link pb-2 transition">Micro</button>
            </div>

            {{-- PACIENTE Y BOTÓN DE NUEVA NOTIFICACIÓN --}}
            <div class="text-center mt-6">
                <h3 class="text-xl font-semibold">
                    Mostrando px:
                    <span id="patient_name" class="text-blue-600">Cargando...</span>
                </h3>

                <button onclick="$('#modalSelectNotif').removeClass('hidden');"
                    class="mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg shadow hover:bg-blue-700 transition"
                    data-process="-1">
                    Nueva notificación
                </button>

                {{-- Inputs ocultos para AJAX --}}
                <input type="hidden" id="lead_id" value="{{ request()->id }}">
                <input type="hidden" id="num_med" value="">
                <input type="hidden" id="touchup" value="">
                <input type="hidden" id="specialist" value="">
                <input type="hidden" id="room" value="">
            </div>

            {{-- FORMULARIO DINÁMICO --}}
            <div id="form_notification" class="mt-10 p-6 bg-yellow-50 border border-yellow-200 rounded-lg hidden">
                <h4 class="text-lg font-bold mb-4 text-yellow-800" id="form_title">Formulario de Notificación Detallada</h4>

                <form method="POST" id="form_notif" action="{{ route('panel.notifications.create') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="lead_id" value="{{ request()->id }}">
                    <input type="hidden" name="process" id="process">
                    <input type="hidden" name="specialist" id="form_specialist">
                    <input type="hidden" name="room" id="form_room">
                    <input type="hidden" name="notif_type" id="notif_type">
                    <input type="hidden" name="num_med" id="form_num_med">
                    <input type="hidden" name="touchup" id="form_touchup">
                    <input type="text" name="px_data" id="px_data" value="" class="hidden">
                    <input type="hidden" name="hour_24" id="hour_24">
                    <input type="hidden" name="hour_ampm" id="hour_ampm">
                    <input type="hidden" name="ampm" id="ampm">

                    <input type="hidden" name="final_text" id="final_text">

                    {{-- Notificaciones sugeridas --}}

                    {{-- Inputs dinámicos generados por JS --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="dynamics_inputs"></div>

                    {{-- Área de errores --}}
                    <div id="form_errors" class="text-red-600 mt-2"></div>

                    {{-- Botones de envío --}}
                    <div class="mt-6">
                        <button type="submit"
                            class="bg-green-600 text-white px-6 py-2 rounded-lg shadow hover:bg-green-700 transition submit-form">
                            Enviar Notificación Detallada
                        </button>
                        <button type="button" onclick="$('#form_notification').addClass('hidden');"
                            class="ml-3 bg-gray-300 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>

        </div>

        {{-- MODAL DE SELECCIÓN --}}
        <div id="modalSelectNotif"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white w-full max-w-xl rounded-xl shadow-2xl p-8 relative menu_modal">

                <button onclick="$('#modalSelectNotif').addClass('hidden')"
                    class="absolute top-4 right-4 text-2xl text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-times"></i>
                </button>

                <h2 class="text-2xl font-bold text-center text-[#1C6C73] mb-8">
                    Selecciona Tipo de Notificación
                </h2>

                {{-- Notificaciones sugeridas --}}
                {{-- Notificaciones sugeridas estáticas --}}
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Selecciona la notificación
                    </label>
                    <select id="select_next_process"
                        class="w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-300">
                        <option value="0">Px firmó documentos</option>
                        <option value="1">Inicio de infiltración</option>
                        <option value="2">Término de infiltración</option>
                        <option value="3">Inicio de extracción</option>
                        <option value="3.1">Hora de extracción</option>
                        <option value="4">Término de extracción</option>
                        <option value="5">Inicio de incisiones</option>
                        <option value="6">Término de incisiones</option>
                        <option value="7">Inicio de implantación</option>
                        <option value="8">Hora de implantación</option>
                        <option value="9">Término de implantación</option>
                        <option value="10">Término de procedimiento</option>
                        <option value="-1">Incidencia / Mensaje Manual</option>
                    </select>
                </div>


                <hr class="my-6">

                {{-- Incidencia / Mensaje Manual --}}
                <div>
                    <button
                        onclick="
                    window.setDynamicFormInputs(-1, 'Incidencia / Mensaje Manual', 'manual');
                    $('#modalSelectNotif').addClass('hidden'); 
                    $('#form_notification').removeClass('hidden');
                "
                        class="w-full mt-4 bg-red-600 hover:bg-red-700 text-white font-bold px-5 py-3 rounded-lg transition duration-300 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i> Enviar Incidencia / Mensaje Manual
                    </button>
                </div>

            </div>
        </div>

        <hr class="my-6">

        <h3 class="text-lg font-semibold text-gray-700 mb-4">
            Historial de notificaciones
        </h3>

        <div id="notifications_list" class="space-y-3">
            <p class="text-center text-gray-400">Cargando notificaciones...</p>
        </div>

    </div>


    <script>
        $(document).on('change', '#select_next_process', function() {

            const process = $(this).val();
            const title = $(this).find('option:selected').text();

            // 🔥 ACTUALIZAR process ANTES DE TODO
            $('#process').val(process);

            window.setDynamicFormInputs(process, title, 'auto');
        });



        /* =====================================================
        LEAD ID GLOBAL (FUENTE ÚNICA DE VERDAD)
        ===================================================== */
        window.LEAD_ID = new URLSearchParams(window.location.search).get('id');

        if (!window.LEAD_ID) {
            console.error('LEAD_ID no encontrado en la URL');
            Swal.fire('Error crítico', 'No se encontró el Lead ID', 'error');
        }

        /* =====================================================
        ESTADO GLOBAL
        ===================================================== */
        window.alpineData = {
            selectedProcedure: 1,
            selectedProcess: null
        };

        $(document).on('change', 'input[type="time"]', function() {

            $('#hour_24').val(this.value);

            const converted = convertToAmPm(this.value);
            if (converted) {
                $('#hour_ampm').val(converted.hour12);
                $('#ampm').val(converted.ampm);
            }

            $('#final_text').val(
                buildFinalNotificationText($('#form_title').text())
            );
        });



        /* =====================================================
        CARGAR DATOS DEL PACIENTE
        ===================================================== */
        window.loadPatientNotifications = function() {

            if (!window.LEAD_ID) return;

            $.ajax({
                url: "{{ url('/panel/notifications/patient') }}",
                data: {
                    id: window.LEAD_ID,
                    procedure_type: window.alpineData.selectedProcedure
                },
                success: function(response) {
                    if (!response.success) return;

                    $('#patient_name').text(response.data.patient_name);

                    $('#num_med, #form_num_med').val(response.data.num_med ?? '');
                    $('#touchup, #form_touchup').val(response.data.touchup ?? '');
                    $('#specialist, #form_specialist').val(response.data.specialist ?? '');
                    $('#room, #form_room').val(response.data.room ?? '');

                    // Render historial
                    window.renderNotifications(response.data.activity);

                    // Cargar siguiente notificación
                    window.loadNextNotification();
                }
            });
        };

        /* =====================================================
        SELECT → FORMULARIO DINÁMICO
        ===================================================== */
        $(document).on('change', '#select_next_process, #select_next_process_modal', function() {
            const process = $(this).val();
            const label = $(this).find('option:selected').text();
            window.setDynamicFormInputs(process, label, 'auto');
        });

        /* =====================================================
        SIGUIENTE NOTIFICACIÓN
        ===================================================== */
        window.loadNextNotification = function() {
            if (!window.LEAD_ID) return;

            $.ajax({
                url: "{{ url('/panel/notifications/nextNotification') }}",
                data: {
                    lead_id: window.LEAD_ID,
                    procedure_type: window.alpineData.selectedProcedure
                },
                success: function(res) {
                    $('#next_notification').html(res.next_notif ?? '');
                    $('#next_notification_modal').html(res.next_notif ?? '');
                }
            });
        };
        $(document).on('click', '#types_procedures .nav-link', function(e) {
            e.preventDefault();

            $('#types_procedures .nav-link')
                .removeClass('border-blue-600 text-blue-700 font-semibold');

            $(this).addClass('border-blue-600 text-blue-700 font-semibold');

            window.alpineData.selectedProcedure = $(this).data('procedure');
            window.loadPatientNotifications();
        });

        /* =====================================================
           FORMULARIO DINÁMICO
        ===================================================== */
        function buildFinalNotificationText(title) {

            const px = $('#patient_name').text().trim();
            const process = $('#process').val();

            if (!process) {
                return `${px}. ${title}`;
            }

            // 🟥 Incidencia
            if (process.toString() === '-1') {
                return `${px}. ${title}`;
            }

            const hour24 = $('#hour_24').val();

            // 🚫 Si no hay hora, NO pongas "a las"
            if (!hour24) {
                return `${px}. ${title}`;
            }

            const converted = convertToAmPm(hour24);
            if (!converted) {
                return `${px}. ${title}`;
            }

            return `${px}. ${title} a las ${converted.hour12}`;
        }

        function getCurrentTime() {
            const now = new Date();
            return now.toTimeString().slice(0, 5); // HH:mm
        }

        function convertToAmPm(time24) {
            if (!time24 || !time24.includes(':')) return null;

            let [hours, minutes] = time24.split(':');
            hours = parseInt(hours, 10);

            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;

            return {
                hour12: `${hours}:${minutes} ${ampm}`,
                ampm
            };
        }

        /* =====================================================
           SINCRONIZAR HORA + TEXTO FINAL (BLINDADO)
        ===================================================== */
        function syncHourAndFinalText() {

            const timeInput = $('#dynamics_inputs').find('input[type="time"]').first();

            // 👉 Si el proceso NO tiene hora, limpiar valores
            if (!timeInput.length) {
                $('#hour_24').val('');
                $('#hour_ampm').val('');
                $('#ampm').val('');
                $('#final_text').val(
                    buildFinalNotificationText($('#form_title').text())
                );
                return;
            }

            if (!timeInput.val()) return;

            const hour24 = timeInput.val();
            $('#hour_24').val(hour24);

            const converted = convertToAmPm(hour24);
            if (converted) {
                $('#hour_ampm').val(converted.hour12);
                $('#ampm').val(converted.ampm);
            }

            $('#final_text').val(
                buildFinalNotificationText($('#form_title').text())
            );
        }

        /* =====================================================
           ESCUCHAR CAMBIOS EN INPUTS DINÁMICOS
        ===================================================== */
        $(document).on(
            'change keyup',
            '#dynamics_inputs input, #dynamics_inputs select, #dynamics_inputs textarea',
            function() {
                syncHourAndFinalText();
            }
        );

        /* =====================================================
           SET FORM DINÁMICO
        ===================================================== */
        window.setDynamicFormInputs = function(process, title, type = 'manual') {

            $('#process').val(process);
            $('#form_title').text(title);

            if (!$('#px_data').val()) {
                $('#px_data').val($('#patient_name').text());
            }
            const notifType =
                process.toString() === '-1' ?
                'manual' :
                'auto';

            $('#notif_type').val(notifType);

            /* ===============================
               PLANTILLAS REUTILIZABLES
            =============================== */

            const time_input = `
        <div class="col-span-1">
            <label>Hora *</label>
            <input type="time" name="hour" required class="w-full time-input">
        </div>`;

            const notes_input = `
        <div class="col-span-full">
            <label>Notas</label>
            <textarea name="comments" class="w-full"></textarea>
        </div>`;

            const upload_photo = `
        <div class="col-span-1">
            <label>Foto *</label>
            <input type="file" name="file" accept="image/*" class="input-file">
        </div>`;

            /* ===============================
               MAPA DE NOTIFICACIONES
               (SOLO LO ADICIONAL, LA HORA VA SIEMPRE)
            =============================== */

            const notifications = {

                "-1": {
                    inputs: upload_photo + notes_input,
                    enctype: true,
                    fileRequired: false
                },

                "0": {
                    inputs: `
                <div class="col-span-1">
                    <label>Especialista a cargo *</label>
                    <select name="specialist" class="w-full select-specialist" required>
                        <option value="">Selecciona</option>
                        <option value="${$('#form_specialist').val()}">
                            ${$('#form_specialist').val()}
                        </option>
                    </select>
                </div>
            ` + notes_input,
                    enctype: false,
                    fileRequired: false
                },

                "1": {
                    inputs: notes_input,
                    enctype: false,
                    fileRequired: false
                },
                "2": {
                    inputs: notes_input,
                    enctype: false,
                    fileRequired: false
                },
                "3": {
                    inputs: notes_input,
                    enctype: false,
                    fileRequired: false
                },
                "4": {
                    inputs: notes_input,
                    enctype: false,
                    fileRequired: false
                },
                "9": {
                    inputs: notes_input,
                    enctype: false,
                    fileRequired: false
                },

                "3.1": {
                    inputs: upload_photo + `
                <div class="col-span-1">
                    <label>La foto corresponde a *</label>
                    <select name="photo_type" class="w-full select-photo-type" required>
                        <option value="">Selecciona</option>
                        <option value="1er hora extracción">Primera hora de extracción</option>
                        <option value="2da hora extracción">Segunda hora de extracción</option>
                        <option value="Conteo final extracción">Conteo final</option>
                    </select>
                </div>
            ` + notes_input,
                    enctype: true,
                    fileRequired: true
                },

                "9.1": {
                    inputs: upload_photo + `
                <div class="col-span-1">
                    <label>La foto corresponde a *</label>
                    <select name="photo_type" class="w-full select-photo-type" required>
                        <option value="">Selecciona</option>
                        <option value="1er hora implantación">1er hora implantación</option>
                        <option value="Conteo final implantación">Conteo final</option>
                    </select>
                </div>
            ` + notes_input,
                    enctype: true,
                    fileRequired: true
                },

                "10": {
                    inputs: `
                <div class="col-span-1">
                    <label>Unidades Foliculares *</label>
                    <input type="number" name="uf" class="w-full" required>
                </div>
                <div class="col-span-1">
                    <label>Folículos *</label>
                    <input type="number" name="hair_follicles" class="w-full" required>
                </div>
            ` + notes_input,
                    enctype: false,
                    fileRequired: false
                }
            };

            /* ===============================
               RENDER (HORA SIEMPRE ARRIBA)
            =============================== */

            const node = notifications[process.toString()] ?? {
                inputs: notes_input,
                enctype: false,
                fileRequired: false
            };

            $('#dynamics_inputs').html(`
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            ${time_input}
            ${node.inputs}
        </div>
    `);

            /* ===============================
               AUTOCOMPLETAR HORA
            =============================== */

            $('#dynamics_inputs').find('input[type="time"]').each(function() {
                if (!this.value) {
                    this.value = getCurrentTime();
                }
            });

            syncHourAndFinalText();

            /* ===============================
               ENCTYPE / FILE REQUIRED
            =============================== */

            node.enctype ?
                $('#form_notif').attr('enctype', 'multipart/form-data') :
                $('#form_notif').removeAttr('enctype');

            const $file = $('#dynamics_inputs').find('input[type=file].input-file');
            if ($file.length) {
                node.fileRequired ?
                    $file.attr('required', 'required') :
                    $file.removeAttr('required');
            }

            $('#form_notification').removeClass('hidden');
            $('#modalSelectNotif').addClass('hidden');
        };

        /* =====================================================
        SUBMIT DEL FORMULARIO (BLINDADO)
        ===================================================== */
        $(document).on('submit', '#form_notif', function(e) {
            e.preventDefault();

            $('#form_errors').html('');

            if (!window.LEAD_ID) {
                Swal.fire('Error', 'Lead ID inválido', 'error');
                return;
            }

            if (!$('#process').val()) {
                Swal.fire('Error', 'Selecciona una notificación', 'error');
                return;
            }

            const process = $('#process').val();

            $('#notif_type').val(
                process && process !== '-1' ? 'auto' : 'manual'
            );


            if (!$('#px_data').val()) {
                $('#px_data').val($('#patient_name').text());
            }

            const formData = new FormData(this);
            formData.set('lead_id', window.LEAD_ID);

            $.ajax({
                url: this.action,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,

                success: function(res) {
                    Swal.fire('Éxito', res.message, 'success');
                    $('#form_notification').addClass('hidden');
                    $('#form_notif')[0].reset();
                    window.loadPatientNotifications();
                },

                error: function(xhr) {
                    if (xhr.status === 422) {
                        let html = '';
                        Object.values(xhr.responseJSON.errors).forEach(err => {
                            html += err.join('<br>') + '<br>';
                        });
                        $('#form_errors').html(html);
                    } else {
                        console.error(xhr.responseText);
                        Swal.fire('Error', 'Error inesperado del servidor', 'error');
                    }
                }
            });
        });

        /* =====================================================
        TABS DE PROCEDIMIENTO
        ===================================================== */
        $(document).on('click', '.nav-link', function() {
            $('.nav-link').removeClass('border-blue-600 text-blue-700 font-semibold');
            $(this).addClass('border-blue-600 text-blue-700 font-semibold');

            window.alpineData.selectedProcedure = $(this).data('procedure');
            window.loadPatientNotifications();
        });

        /* =====================================================
        INIT ÚNICO
        ===================================================== */
        $(document).ready(function() {
            console.log('LEAD ID:', window.LEAD_ID);
            window.loadPatientNotifications();
        });

        /* =====================================================
        RENDER HISTORIAL DE NOTIFICACIONES
        ===================================================== */
        window.renderNotifications = function(notifications) {

            let html = '';

            if (!notifications || !notifications.length) {
                $('#notifications_list').html(
                    '<p class="text-center text-gray-400">Sin notificaciones</p>'
                );
                return;
            }
            notifications.forEach(item => {

                html += `
       
                <div class="bg-white border rounded-lg p-4 shadow-sm relative">

               <button
                type="button"
                class="absolute top-2 right-2 text-red-600 hover:text-red-800"
                onclick="confirmDeleteNotification(${item.id})"
                title="Eliminar"
            >
                🗑
            </button>

            <p class="font-semibold text-gray-800">
                ${item.title}
            </p>

            <p class="text-sm text-gray-500 mt-1">
                ${item.datetime}
            </p>
    `;

                if (item.image) {
                    html += `
            <div class="mt-3">
                <img
                    src="${item.image}"
                    class="max-w-xs rounded-lg border cursor-pointer"
                    onclick="window.open('${item.image}', '_blank')"
                >
            </div>
        `;
                }

                html += `</div>`;
            });

            $('#notifications_list').html(html);
        };


        window.confirmDeleteNotification = function(id) {

            Swal.fire({
                title: '¿Eliminar notificación?',
                text: 'También se eliminará del grupo de Telegram',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {

                if (result.isConfirmed) {
                    deleteNotification(id);
                }
            });
        };

        window.deleteNotification = function (id) {

    fetch('{{ route("panel.notifications.destroy") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.CSRF_TOKEN,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ id })
    })
    .then(res => res.json())
    .then(res => {

        if (res.success) {

            Swal.fire({
                icon: 'success',
                title: 'Eliminada',
                text: res.message,
                timer: 1500,
                showConfirmButton: false
            });

            window.loadPatientNotifications();

        } else {

            Swal.fire('Error', res.message, 'error');
        }
    })
    .catch(() => {
        Swal.fire('Error', 'Error inesperado del servidor', 'error');
    });
};

    </script>

    <script>
        window.CSRF_TOKEN = "{{ csrf_token() }}";
    </script>

    <style>
        /* Tu CSS de Tailwind modificado */
        .menu_modal {
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.5);
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
        }

        .btn-suggested-notif {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform, box-shadow;
            /* Estilos de botón Tailwind */
            @apply bg-gray-100 text-gray-800 p-3 rounded-lg shadow-sm hover:bg-blue-100 hover:text-blue-700 font-medium text-sm;
        }

        .btn-suggested-notif:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .btn-suggested-notif:active {
            transform: scale(0.98);
        }

        /* Nota: Se necesita incluir la librería SweetAlert2 para que las funciones AJAX funcionen. */
        /* <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> */
    </style>

@endsection
