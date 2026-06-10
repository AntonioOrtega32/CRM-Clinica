        <script>
            window.eventModal = function() {


                return {
                    open: false,
                    activeTab: 'revision',
                    currentEventId: null,
                    userId: null,
                    sinNumeroTrat: false,



                    searchByExpediente(value, tab) {
                        if (!value || value.length < 2) return;

                        fetch(`/panel/calendar/search-by-expediente?num_med_record=${value}`, {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            })
                            .then(async res => {
                                if (!res.ok) {
                                    const text = await res.text(); // 👈 DEBUG
                                    console.error('Respuesta NO JSON:', text);
                                    throw new Error('Respuesta inválida');
                                }
                                return res.json();
                            })
                            .then(data => {
                                console.log('Resultados:', data);

                                if (data.success && data.results.length > 0) {
                                    const first = data.results[0];

                                 const name = first.patient_name;


                                    const patientNameId = tab === 'revision' ?
                                        'revision_patient_name' :
                                        'tratamiento_patient_name';

                                    document.getElementById(patientNameId).value = name;
                                }
                            })
                            .catch(err => {
                                console.error('Error búsqueda expediente:', err);
                            });

                    },



                    resetAllFormsToCreate() {
    formRevision.action = '{{ route("panel.calendar.store") }}';
    form_method.value = 'POST';

    formValoracion.action = '{{ route("panel.calendar.store") }}';
    form_method_valoracion.value = 'POST';

    formTratamiento.action = '{{ route("panel.calendar.store") }}';
    form_method_tratamiento.value = 'POST';

    formEvento.action = '{{ route("panel.calendar.storeEvento") }}';
    form_method_evento.value = 'POST';
}
,
                    /* ===============================
                    ABRIR CREAR
                    =============================== */
                    openModalForCreate(date = null) {
                        this.open = true;
                        this.activeTab = 'revision';
                        this.currentEventId = null;
                        this.userId = null;
                        this.sinNumeroTrat = false;
    this.resetAllFormsToCreate(); // 🔥 ESTA LÍNEA ES LA CLAVE

                        this.resetAllForms();

                        this.$nextTick(() => {
                            if (!date) return;

                            const d = new Date(date);
                            const y = d.getFullYear();
                            const m = String(d.getMonth() + 1).padStart(2, '0');
                            const day = String(d.getDate()).padStart(2, '0');
                            const hh = String(d.getHours()).padStart(2, '0');
                            const mm = String(d.getMinutes()).padStart(2, '0');

                            const dateStr = `${y}-${m}-${day}`;
                            const startTime = `${hh}:${mm}`;
                            const end = new Date(d.getTime() + 30 * 60 * 1000); // 30 min
                            const endTime =
                                `${String(end.getHours()).padStart(2,'0')}:${String(end.getMinutes()).padStart(2,'0')}`;

                            // Revision
                            document.getElementById('revision_event_date').value = dateStr;
                            document.getElementById('revision_start_date').value = startTime;
                            document.getElementById('revision_end_date').value = endTime;

                            // Valoracion
                            document.querySelector('#formValoracion input[name="event_date"]').value = dateStr;
                            document.getElementById('valoracion_start_date').value = startTime;
                            document.getElementById('valoracion_end_date').value = endTime;

                            // Tratamiento
                            document.getElementById('tratamiento_event_date').value = dateStr;
                            document.getElementById('tratamiento_start_date').value = startTime;
                            document.getElementById('tratamiento_end_date').value = endTime;

                            // Evento
                            document.getElementById('evento_event_date').value = dateStr;
                            document.getElementById('evento_start_date').value = startTime;
                            document.getElementById('evento_end_date').value = endTime;
                        });
                    },

                    /* ===============================
                    ABRIR EDITAR
                    =============================== */
                    openModalWithEvent(info) {
                        const e = info.event;
                        const props = e.extendedProps || {};
                        const type = props.event_type || 'revision';

                        /* =====================================================
                        ABRIR MODAL
                        ===================================================== */
                        this.open = true;
                        this.currentEventId = e.id;
                        this.userId = props.uploaded_by || props.user_id || null;

                        document.getElementById('modalTitle').textContent = 'Editar evento';

                        /* =====================================================
                        FECHAS Y HORAS
                        ===================================================== */
                        const start = new Date(e.start);
                        const end = new Date(e.end || e.start);

                        const startDate =
                            start.getFullYear() + '-' +
                            String(start.getMonth() + 1).padStart(2, '0') + '-' +
                            String(start.getDate()).padStart(2, '0');
                        const startTime = start.toTimeString().slice(0, 5);
                        const endTime = end.toTimeString().slice(0, 5);

                        /* =====================================================
                        🔥 EXTRAER NOMBRE Y EXPEDIENTE DESDE EL TITLE
                        ===================================================== */
                        this.patientName = '';
                        this.expediente = '';

                        if (e.title) {
                            let baseTitle = e.title;
                            if (type === 'revision' || type === 'tratamiento' || type === 'valoracion') {
                                baseTitle = e.title.replace(/ \[.*\]$/, '');
                            }

                            if (type === 'revision' || type === 'tratamiento') {
                                const parts = baseTitle.split('-');
                                this.patientName = parts[0]?.trim() || '';
                                this.expediente = parts[1]?.trim() || '';
                            } else if (type === 'valoracion') {
                                this.patientName = baseTitle;
                            }
                        }

                        /* =====================================================
                        ESPERAR DOM → CARGAR FORMULARIOS
                        ===================================================== */
                        this.$nextTick(() => {

                            // Cambiar action a UPDATE
                            this.updateAllFormsForEdit(e.id);

                            if (type === 'revision') {
                                this.activeTab = 'revision';
                                this.populateRevisionForm(
                                    props,
                                    startDate,
                                    startTime,
                                    endTime
                                );
                            } else if (type === 'valoracion') {
                                this.activeTab = 'valoracion';
                                this.populateValoracionForm(
                                    e,
                                    props,
                                    startDate,
                                    startTime,
                                    endTime
                                );
                            } else if (type === 'tratamiento') {
                                this.activeTab = 'tratamiento';
                                this.populateTratamientoForm(
                                    e,
                                    props,
                                    startDate,
                                    startTime,
                                    endTime
                                );
                            } else {
                                this.activeTab = 'evento';
                                this.populateEventoForm(
                                    e,
                                    startDate,
                                    startTime,
                                    endTime
                                );
                            }
                        });
                    },

                    /* ===============================
                    POPULATE FORMS
                    =============================== */
                    populateRevisionForm(p, d, s, e) {

                        revision_patient_name.value =
                            p.patient_name || this.patientName || '';

                        document.querySelector('#formRevision input[name="expediente"]').value =
                            p.num_med_record || this.expediente || '';

                        revision_event_id.value = this.currentEventId;

                        revision_notes.value = p.description || '';
                        revision_attendance_type.value = p.attendance_type ?? '0';

                        // 🔥 CORREGIDO
                        revision_review_time.value = (p.revision_time ?? '').trim();

                        revision_status.value = p.status || 'Agendada';
                        revision_qualy.value = p.qualy || 'Pendiente';

                        revision_clinic.value = p.clinic || '';

                        chkProtocoloAlejandro.checked = p.protocolo_alejandro == 1;

                        revision_event_date.value = d;
                        revision_start_date.value = s;
                        revision_end_date.value = e;
                    },


                    populateValoracionForm(e, p, d, s, en) {
                        valoracion_event_id.value = this.currentEventId;
                        document.querySelector('#formValoracion input[name="patient_name"]').value = this.patientName || '';
                        document.querySelector('#formValoracion select[name="seller"]').value = p.seller || '';
                        document.querySelector('#formValoracion select[name="attendance_type"]').value = p
                            .attendance_type || '0';
                        document.querySelector('#formValoracion input[name="event_date"]').value = d;
                        valoracion_start_date.value = s;
                        valoracion_end_date.value = en;
                        document.querySelector('#formValoracion select[name="clinic"]').value = p.clinic || '';
                    },

                    populateTratamientoForm(e, p, d, s, en) {
                        this.sinNumeroTrat = p.dif == 1;
                        tratamiento_event_id.value = this.currentEventId;
                        document.querySelector('#formTratamiento input[name="patient_name"]').value = this.patientName ||
                            '';
                        document.querySelector('#formTratamiento input[name="expediente"]').value = p.num_med_record ||
                            this.expediente || '';
                        document.querySelector('#formTratamiento textarea[name="notes"]').value = p.description || '';
                        document.querySelector('#formTratamiento input[name="event_date"]').value = d;
                        tratamiento_start_date.value = s;
                        tratamiento_end_date.value = en;
                        document.querySelector('#formTratamiento select[name="clinic"]').value = p.clinic || '';
                    },

                    populateEventoForm(e, d, s, en) {
                        evento_event_id.value = this.currentEventId;
                        event_name.value = e.title || '';
                        evento_event_date.value = d;
                        evento_start_date.value = s;
                        evento_end_date.value = en;
                    },

                    /* ===============================
                    HELPERS
                    =============================== */
                    resetAllForms() {
                        this.sinNumeroTrat = false;
                        formRevision.reset();
                        formValoracion.reset();
                        formTratamiento.reset();
                        formEvento.reset();
                    },

                    updateAllFormsForEdit(id) {
                        const url = `/panel/calendar/update/${id}`;
                        formRevision.action = url;
                        form_method.value = 'PUT';
                        formValoracion.action = url;
                        form_method_valoracion.value = 'PUT';
                        formTratamiento.action = url;
                        form_method_tratamiento.value = 'PUT';
                        formEvento.action = url;
                        form_method_evento.value = 'PUT';
                    },

                    submitActiveForm() {
                        if (this.isSubmitting) return;

                        const form = {
                            revision: formRevision,
                            valoracion: formValoracion,
                            tratamiento: formTratamiento,
                            evento: formEvento
                        } [this.activeTab];

                        if (!form) return;

                        this.isSubmitting = true;

                        const formData = new FormData(form);

                        Swal.fire({
                            title: 'Procesando...',
                            text: 'Guardando el evento, por favor espera.',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => Swal.showLoading()
                        });

                        fetch(form.action, {
                                method: 'POST',
                                body: formData,

                            })
                            .then(async response => {
                                const data = await response.json();

                                if (!response.ok) {
                                    throw data;
                                }

                                return data;
                            })
                            .then(data => {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Guardado!',
                                    text: data.message || 'Evento guardado correctamente.',
                                    timer: 1800,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.calendar.refetchEvents();
                                    this.closeModal();
                                });
                            })
                            .catch(error => {
                                console.error('Error:', error);

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: error.message || 'Hubo un error al guardar el evento.'
                                });
                            })
                            .finally(() => {
                                this.isSubmitting = false;
                                Swal.close();
                            });
                    },

                    closeModal() {
                        this.open = false;
                    },

                    /* ===============================
                    ACTUALIZAR HORA DE FIN (30 MIN)
                    =============================== */
                    updateEndTime(startInputId, endInputId) {
                        const startInput = document.getElementById(startInputId);
                        const endInput = document.getElementById(endInputId);
                        
                        if (!startInput || !endInput || !startInput.value) return;
                        
                        // Parsear la hora de inicio (formato HH:mm)
                        const [hours, minutes] = startInput.value.split(':').map(Number);
                        
                        // Crear una fecha auxiliar para hacer el cálculo
                        const start = new Date();
                        start.setHours(hours, minutes);
                        
                        // Sumar 30 minutos
                        const end = new Date(start.getTime() + 30 * 60 * 1000);
                        
                        // Formatear como HH:mm
                        const endHours = String(end.getHours()).padStart(2, '0');
                        const endMinutes = String(end.getMinutes()).padStart(2, '0');
                        endInput.value = `${endHours}:${endMinutes}`;
                    },

                    deleteEvent() {
                        if (!this.currentEventId) return;

                        Swal.fire({
                            title: '¿Eliminar evento?',
                            text: 'Esta acción no se puede deshacer',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#dc2626',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: 'Sí, eliminar',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (!result.isConfirmed) return;

                            fetch(`/panel/calendar/destroy/${this.currentEventId}`, {
                                    method: 'POST', // 👈 POST + spoof DELETE (más estable)
                                    headers: {
                                        'X-CSRF-TOKEN': document
                                            .querySelector('meta[name="csrf-token"]')
                                            .getAttribute('content'),
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        _method: 'DELETE'
                                    })
                                })
                                .then(async res => {
                                    const data = await res.json();
                                    if (!res.ok) throw data;
                                    return data;
                                })
                                .then(() => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Eliminado',
                                        text: 'El evento fue eliminado correctamente',
                                        timer: 1800,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.calendar.refetchEvents();
                                        this.closeModal();
                                    });
                                })
                                .catch(err => {
                                    console.error('Error al eliminar:', err);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: err.message || 'No se pudo eliminar el evento'
                                    });
                                });
                        });
                    }

                };
            };
        </script>


        <!-- ===================== ALERTAS ===================== -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <strong>Errores de validación:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- ========================== MODAL BOOTSTRAP + TAILWIND + ALPINE ========================== -->
        <div id="eventModal" x-data="eventModal()" x-show="open" x-transition.opacity x-cloak
            @click.self="closeModal"
            @open-event-modal.window="
                if ($event.detail.mode === 'create') {
                    openModalForCreate($event.detail.date)
                } else {
                    openModalWithEvent($event.detail.info)
                }
            "
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

            <div class="bg-white w-full max-w-5xl rounded-xl shadow-xl overflow-hidden">
                <div class="modal-content">
                    <!-- HEADER -->
                    <div class="modal-header bg-gradient-to-r from-blue-600 to-blue-800 text-white border-0">
                        <h5 class="modal-title font-semibold" id="modalTitle">Crear / Editar Evento</h5>
                        <button type="button" class="btn-close btn-close-white" @click="closeModal()"
                            aria-label="Close"></button>
                    </div>

                    <!-- BODY -->
                    <div class="modal-body">
                        <meta name="csrf-token" content="{{ csrf_token() }}">

                        <!-- ===================== TABS ===================== -->
                        <div class="nav nav-tabs border-b border-gray-200 mb-4" id="eventTabs" role="tablist">
                            <button
                                class="nav-link px-4 py-2 border-0 border-b-2 border-transparent hover:border-blue-600 transition"
                                id="tab-revision" @click="activeTab = 'revision'"
                                :class="activeTab === 'revision' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' :
                                    'text-gray-600'"
                                type="button" role="tab">
                                Revisión
                            </button>
                            <button
                                class="nav-link px-4 py-2 border-0 border-b-2 border-transparent hover:border-blue-600 transition"
                                id="tab-valoracion" @click="activeTab = 'valoracion'"
                                :class="activeTab === 'valoracion' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' :
                                    'text-gray-600'"
                                type="button" role="tab">
                                Valoración
                            </button>
                            <button
                                class="nav-link px-4 py-2 border-0 border-b-2 border-transparent hover:border-blue-600 transition"
                                id="tab-tratamiento" @click="activeTab = 'tratamiento'"
                                :class="activeTab === 'tratamiento' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' :
                                    'text-gray-600'"
                                type="button" role="tab">
                                Tratamiento
                            </button>
                            <button
                                class="nav-link px-4 py-2 border-0 border-b-2 border-transparent hover:border-blue-600 transition"
                                id="tab-evento" @click="activeTab = 'evento'"
                                :class="activeTab === 'evento' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' :
                                    'text-gray-600'"
                                type="button" role="tab">
                                Evento
                            </button>
                        </div>

                        <!-- ===================== CONTENIDO DE TABS ===================== -->
                        <div class="tab-content" id="eventTabContent" x-cloak>

                            <!-- TAB: REVISION -->
                            <div x-show="activeTab === 'revision'" role="tabpanel">

                                <form id="formRevision" method="POST" action="{{ route('panel.calendar.store') }}"
                                    @submit.prevent="submitActiveForm()">

                                    @csrf
                                    <input type="hidden" id="revision_event_id" name="event_id">
                                    <input type="hidden" name="event_type" value="revision">
                                    <input type="hidden" id="form_method" name="_method" value="POST">
                                    <input type="hidden" name="uploaded_by" :value="userId">

                                    <!-- ================= DATOS GENERALES ================= -->
                                    <div class="form-section">
                                        <h6>Datos generales</h6>

                                        <div class="form-grid">

                                            <div class="form-group">
                                                <label>Tipo<span>*</span></label>
                                                <select id="revision_attendance_type" name="attendance_type"
                                                    class="form-control" required>
                                                    <option value="" disabled>Selecciona</option>
                                                    <option value="0">Presencial</option>
                                                    <option value="1">Virtual</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Corresponde a<span>*</span></label>
                                                <select id="revision_review_time" name="review_time"
                                                    class="form-control" required>
                                                    <option value="" disabled>Selecciona</option>
                                                    <option value="24 H">24 horas</option>
                                                    <option value="10 D">10 días</option>
                                                    <option value="1 M">1 mes</option>
                                                    <option value="3 M">3 meses</option>
                                                    <option value="6 M">6 meses</option>
                                                    <option value="9 M">9 meses</option>
                                                    <option value="12 M">12 meses</option>
                                                    <option value="15 M">15 meses</option>
                                                    <option value="18 M">18 meses</option>
                                                    <option value="21 M">21 meses</option>
                                                    <option value="Post Alta">Post Alta</option>
                                                </select>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- ================= PACIENTE ================= -->
                                    <div class="form-section">
                                        <h6>Paciente</h6>

                                        <div class="form-grid">

                                            <div class="form-group">
                                                <label>Expediente<span>*</span></label>
                                                <input id="revision_num_med_record" type="number" name="expediente"
                                                    class="form-control" placeholder="Ej. 1023"
                                                    x-on:input.debounce.400ms="searchByExpediente($event.target.value, 'revision')">

                                            </div>

                                            <div class="form-group">
                                                <label>Nombre del paciente<span>*</span></label>
                                                <input id="revision_patient_name" type="text" name="patient_name"
                                                    class="form-control" placeholder="Ej. Juan Pérez" required>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- ================= FECHA Y HORARIO ================= -->
                                    <div class="form-section">
                                        <h6>Fecha y horario</h6>

                                        <div class="form-grid">

                                            <div class="form-group">
                                                <label>Fecha<span>*</span></label>
                                                <input id="revision_event_date" type="date" name="event_date"
                                                    class="form-control" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Inicio<span>*</span></label>
                                                <input id="revision_start_date" type="time" name="start_date"
                                                    class="form-control" required
                                                    @change="updateEndTime('revision_start_date', 'revision_end_date')">
                                            </div>

                                            <div class="form-group">
                                                <label>Término<span>*</span></label>
                                                <input id="revision_end_date" type="time" name="end_date"
                                                    class="form-control" required>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- ================= OBSERVACIONES ================= -->
                                    <div class="form-section">
                                        <h6>Observaciones</h6>

                                        <div class="form-grid">

                                            <div class="form-group full">
                                                <label>Notas adicionales</label>
                                                <textarea id="revision_notes" name="notes" class="form-control" placeholder="Notas relevantes del seguimiento…"></textarea>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- ================= ESTADO ================= -->
                                    <div class="form-section">
                                        <h6>Estado del seguimiento</h6>

                                        <div class="form-grid">

                                            <div class="form-group">
                                                <label>Status<span>*</span></label>
                                                <select id="revision_status" name="status" class="form-control"
                                                    required>
                                                    <option value="" disabled>Selecciona</option>
                                                    <option value="Agendada">Agendada</option>
                                                    <option value="Confirmada">Confirmada</option>
                                                    <option value="No Confirmada">No Confirmada</option>
                                                    <option value="No contestó">No contestó</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Calificación<span>*</span></label>
                                                <select id="revision_qualy" name="qualy" class="form-control"
                                                    required>
                                                    <option value="" disabled>Selecciona</option>
                                                    <option value="Pendiente">Pendiente</option>
                                                    <option value="Asistió">Asistió</option>
                                                    <option value="No asistió">No asistió</option>
                                                    <option value="Reagendó">Reagendó</option>
                                                </select>
                                            </div>

                                            <div class="form-group full">
                                                <label>Clínica<span>*</span></label>
                                                <select id="revision_clinic" name="clinic" class="form-control"
                                                    required>
                                                    <option value="" disabled>Selecciona</option>
                                                    <option value="Santafe">Santa Fe</option>
                                                    <option value="Pedregal">Pedregal</option>
                                                    <option value="Queretaro">Querétaro</option>
                                                </select>
                                            </div>

                                            <div class="form-group full">
                                                <label>
                                                    <input id="chkProtocoloAlejandro" type="checkbox"
                                                        name="protocolo_alejandro">
                                                    Protocolo Dr. Alejandro
                                                </label>
                                            </div>

                                        </div>
                                    </div>

                                </form>

                            </div>

                            <!-- TAB: VALORACION -->
                            <div x-show="activeTab === 'valoracion'" class="tab-pane fade" role="tabpanel">
                                <form id="formValoracion" method="POST" action="{{ route('panel.calendar.store') }}"
                                    @submit.prevent="submitActiveForm()">

                                    @csrf
                                    <input type="hidden" id="valoracion_event_id" name="event_id">
                                    <input type="hidden" name="event_type" value="valoracion">
                                    <input type="hidden" id="form_method_valoracion" name="_method" value="POST">
                                    <input type="hidden" name="uploaded_by" :value="userId">

                                    <!-- ================= DATOS GENERALES ================= -->
                                    <div class="form-section">
                                        <h6>Datos generales</h6>

                                        <div class="form-grid">

                                            <div class="form-group">
                                                <label>Tipo<span>*</span></label>
                                                <select name="attendance_type" class="form-control" required>
                                                    <option value="" disabled>Selecciona</option>
                                                    <option value="0">Presencial</option>
                                                    <option value="1">Virtual</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Vendedor(a)<span>*</span></label>
                                                <select name="seller" class="form-control" required>
                                                    <option value="" disabled>Selecciona</option>
                                                    <option value="Janeth Ruíz">Janeth Ruíz</option>
                                                    <option value="Marisol Olmos">Marisol Olmos</option>
                                                    <option value="Dr. Alejandro Santana">Dr. Alejandro Santana
                                                    </option>
                                                                                                       <option value="Paola Segura">Paola Segura</option>

                                                    <option value="Dra. Lizbeth Carmona">Dra. Lizbeth Carmona</option>
                                                    <option value="Sin vendedor(a)">Sin vendedor(a) asignado(a)
                                                    </option>
                                                </select>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- ================= CLIENTE ================= -->
                                    <div class="form-section">
                                        <h6>Cliente</h6>

                                        <div class="form-grid">

                                            <div class="form-group full">
                                                <label>Nombre del cliente<span>*</span></label>
                                                <input type="text" name="patient_name" class="form-control"
                                                    placeholder="Ej. Juan Pérez" required>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- ================= FECHA Y HORARIO ================= -->
                                    <div class="form-section">
                                        <h6>Fecha y horario</h6>

                                        <div class="form-grid">

                                            <div class="form-group full">
                                                <label>Fecha<span>*</span></label>
                                                <input type="date" name="event_date" class="form-control"
                                                    required>
                                            </div>

                                            <div class="form-group">
                                                <label>Inicio<span>*</span></label>
                                                <input id="valoracion_start_date" type="time" name="start_date"
                                                    class="form-control" required
                                                    @change="updateEndTime('valoracion_start_date', 'valoracion_end_date')">
                                            </div>

                                            <div class="form-group">
                                                <label>Término<span>*</span></label>
                                                <input id="valoracion_end_date" type="time" name="end_date"
                                                    class="form-control" required>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- ================= OBSERVACIONES ================= -->
                                    <div class="form-section">
                                        <h6>Observaciones</h6>

                                        <div class="form-grid">

                                            <div class="form-group full">
                                                <label>Notas adicionales</label>
                                                <textarea name="notes" class="form-control" placeholder="Notas relevantes de la valoración…"></textarea>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- ================= ESTADO ================= -->
                                    <div class="form-section">
                                        <h6>Estado de la valoración</h6>

                                        <div class="form-grid">

                                            <div class="form-group">
                                                <label>Status<span>*</span></label>
                                                <select name="status" class="form-control" required>
                                                    <option value="" disabled>Selecciona</option>
                                                    <option value="Agendada">Agendada</option>
                                                    <option value="Confirmada">Confirmada</option>
                                                    <option value="No Confirmada">No Confirmada</option>
                                                    <option value="No contestó">No contestó</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Calificación<span>*</span></label>
                                                <select name="qualy" class="form-control" required>
                                                    <option value="" disabled>Selecciona</option>
                                                    <option value="Pendiente">Pendiente</option>
                                                    <option value="Asistió">Asistió</option>
                                                    <option value="No asistió">No asistió</option>
                                                    <option value="Reagendó">Reagendó</option>
                                                </select>
                                            </div>

                                            <div class="form-group full">
                                                <label>Clínica<span>*</span></label>
                                                <select name="clinic" class="form-control" required>
                                                    <option value="" disabled>Selecciona</option>
                                                    <option value="Santafe">Santa Fe</option>
                                                    <option value="Pedregal">Pedregal</option>
                                                    <option value="Queretaro">Querétaro</option>
                                                </select>
                                            </div>

                                        </div>
                                    </div>

                                </form>

                            </div>

                            <!-- TAB: TRATAMIENTO -->
                            <div x-show="activeTab === 'tratamiento'" class="tab-pane fade" role="tabpanel">
                                <form id="formTratamiento" method="POST"
                                    action="{{ route('panel.calendar.store') }}" @submit.prevent="submitActiveForm()">

                                    @csrf
                                    <input type="hidden" id="tratamiento_event_id" name="event_id">
                                    <input type="hidden" name="event_type" value="tratamiento">
                                    <input type="hidden" id="form_method_tratamiento" name="_method"
                                        value="POST">
                                    <input type="hidden" name="attendance_type" value="0">
                                    <input type="hidden" name="uploaded_by" :value="userId">

                                    <!-- ================= PACIENTE ================= -->
                                    <div class="form-section">
                                        <h6>Paciente</h6>

                                        <div class="form-grid">

                                            <div class="form-group full">
                                                <label>Expediente<span>*</span></label>

                                                <div class="input-inline">
                                                    <input type="number" name="expediente"
                                                        id="tratamiento_num_med_record" class="form-control"
                                                        min="0" step="1" max="2000"
                                                        :disabled="sinNumeroTrat"
                                                        x-on:input.debounce.400ms="searchByExpediente($event.target.value, 'tratamiento')">

                                                    <label class="check-pill">
                                                        <input type="checkbox" name="dif" id="dif_trat"
                                                            x-model="sinNumeroTrat">
                                                        <span>Sin número</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="form-group full">
                                                <label>Nombre del paciente<span>*</span></label>
                                                <input type="text" name="patient_name"
                                                    id="tratamiento_patient_name" class="form-control"
                                                    placeholder="Ej. Juan Pérez" required>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- ================= FECHA Y HORARIO ================= -->
                                    <div class="form-section">
                                        <h6>Fecha y horario</h6>

                                        <div class="form-grid">

                                            <div class="form-group full">
                                                <label>Fecha<span>*</span></label>
                                                <input type="date" name="event_date" id="tratamiento_event_date"
                                                    class="form-control" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Inicio<span>*</span></label>
                                                <input id="tratamiento_start_date" type="time" name="start_date"
                                                    class="form-control" required
                                                    @change="updateEndTime('tratamiento_start_date', 'tratamiento_end_date')">
                                            </div>

                                            <div class="form-group">
                                                <label>Término<span>*</span></label>
                                                <input id="tratamiento_end_date" type="time" name="end_date"
                                                    class="form-control" required>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- ================= OBSERVACIONES ================= -->
                                    <div class="form-section">
                                        <h6>Observaciones</h6>

                                        <div class="form-grid">

                                            <div class="form-group full">
                                                <label>Notas</label>
                                                <textarea name="notes" id="tratamiento_notes" class="form-control"
                                                    placeholder="Notas relevantes del tratamiento…"></textarea>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- ================= ESTADO ================= -->
                                    <div class="form-section">
                                        <h6>Estado del tratamiento</h6>

                                        <div class="form-grid">

                                            <div class="form-group">
                                                <label>Status<span>*</span></label>
                                                <select name="status" id="tratamiento_status" class="form-control"
                                                    required>
                                                    <option value="" disabled>Selecciona</option>
                                                    <option value="Agendada">Agendada</option>
                                                    <option value="Confirmada">Confirmada</option>
                                                    <option value="No Confirmada">No Confirmada</option>
                                                    <option value="Reagendó">Reagendó</option>
                                                    <option value="No contestó">No contestó</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Calificación<span>*</span></label>
                                                <select name="qualy" id="tratamiento_qualy" class="form-control"
                                                    required>
                                                    <option value="" disabled>Selecciona</option>
                                                    <option value="Pendiente">Pendiente</option>
                                                    <option value="Asistió">Asistió</option>
                                                    <option value="No asistió">No asistió</option>
                                                    <option value="Reagendó">Reagendó</option>
                                                </select>
                                            </div>

                                            <div class="form-group full">
                                                <label>Clínica<span>*</span></label>
                                                <select name="clinic" id="tratamiento_clinic" class="form-control"
                                                    required>
                                                    <option value="" disabled>Selecciona</option>
                                                    <option value="Santafe">Santa Fe</option>
                                                    <option value="Pedregal">Pedregal</option>
                                                    <option value="Queretaro">Querétaro</option>
                                                </select>
                                            </div>

                                            <div class="form-group full checkbox-group">
                                                <label>
                                                    <input id="chkProtocoloAlejandro_trat" type="checkbox"
                                                        name="protocolo_alejandro">
                                                    Protocolo Dr. Alejandro
                                                </label>
                                            </div>

                                        </div>
                                    </div>

                                </form>

                            </div>

                            <!-- TAB: EVENTO -->
                            <div x-show="activeTab === 'evento'" class="tab-pane fade" role="tabpanel">
                                <form id="formEvento" method="POST"
                                    action="{{ route('panel.calendar.storeEvento') }}"
                                    @submit.prevent="submitActiveForm()">

                                    @csrf
                                    <input type="hidden" id="evento_event_id" name="event_id">
                                    <input type="hidden" name="event_type" value="evento">
                                    <input type="hidden" id="form_method_evento" name="_method" value="POST">
                                    <input type="hidden" name="attendance_type" value="0">
                                    <input type="hidden" name="uploaded_by" :value="userId">

                                    <!-- ================= EVENTO ================= -->
                                    <div class="form-section">
                                        <h6>Información del evento</h6>

                                        <div class="form-grid">

                                            <div class="form-group full">
                                                <label>Nombre del evento<span>*</span></label>
                                                <input type="text" name="event_name" id="event_name"
                                                    class="form-control"
                                                    placeholder="Ej. Junta médica, capacitación, cierre…" required>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- ================= FECHA Y HORARIO ================= -->
                                    <div class="form-section">
                                        <h6>Fecha y horario</h6>

                                        <div class="form-grid">

                                            <div class="form-group full">
                                                <label>Fecha<span>*</span></label>
                                                <input type="date" name="event_date" id="evento_event_date"
                                                    class="form-control" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Inicio<span>*</span></label>
                                                <input id="evento_start_date" type="time" name="start_date"
                                                    class="form-control" required
                                                    @change="updateEndTime('evento_start_date', 'evento_end_date')">
                                            </div>

                                            <div class="form-group">
                                                <label>Término<span>*</span></label>
                                                <input id="evento_end_date" type="time" name="end_date"
                                                    class="form-control" required>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- ================= OBSERVACIONES ================= -->
                                    <div class="form-section">
                                        <h6>Observaciones</h6>

                                        <div class="form-grid">

                                            <div class="form-group full">
                                                <label>Notas</label>
                                                <textarea name="notes" id="evento_notes" class="form-control" placeholder="Notas internas del evento…"></textarea>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- ================= ESTADO ================= -->
                                    <div class="form-section">
                                        <h6>Estado del evento</h6>

                                        <div class="form-grid">

                                            <div class="form-group">
                                                <label>Status<span>*</span></label>
                                                <select name="status" id="evento_status" class="form-control"
                                                    required>
                                                    <option value="" disabled>Selecciona</option>
                                                    <option value="Agendada">Agendada</option>
                                                    <option value="Confirmada">Confirmada</option>
                                                    <option value="No Confirmada">No Confirmada</option>
                                                    <option value="Reagendó">Reagendó</option>
                                                    <option value="No contestó">No contestó</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Calificación<span>*</span></label>
                                                <select name="qualy" id="evento_qualy" class="form-control"
                                                    required>
                                                    <option value="" disabled>Selecciona</option>
                                                    <option value="Pendiente">Pendiente</option>
                                                    <option value="Asistió">Asistió</option>
                                                    <option value="No asistió">No asistió</option>
                                                    <option value="Reagendó">Reagendó</option>
                                                </select>
                                            </div>

                                        </div>
                                        <div class="form-group full">
                                            <label>Clínica<span>*</span></label>
                                            <select name="clinic" id="evento_clinic" class="form-control" required>
                                                <option value="" disabled>Selecciona</option>
                                                <option value="Santafe">Santa Fe</option>
                                                <option value="Pedregal">Pedregal</option>
                                                <option value="Queretaro">Querétaro</option>
                                            </select>
                                        </div>

                                    </div>

                                </form>

                            </div>

                        </div>


                        <!-- FOOTER -->
                        <div class="modal-footer border-t border-gray-200 bg-gray-50">
                            <button type="button" class="btn btn-secondary" @click="closeModal()">Cerrar</button>
                            <button type="button" class="btn btn-danger" @click="deleteEvent()"
                                x-show="currentEventId">Eliminar</button>
                            <button type="button" class="btn btn-primary"
                                @click="submitActiveForm()">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <style>
            /* =====================================================
        ESTRUCTURA BASE (SCROLL FUNCIONAL)
        ===================================================== */
            #eventModal .modal-content {
                display: flex;
                flex-direction: column;
                height: 90vh;
            }

            #eventModal .modal-header,
            #eventModal .modal-footer {
                flex-shrink: 0;
            }

            #eventModal .modal-body {
                flex: 1;
                overflow-y: auto;
                /* ✅ SCROLL AQUÍ */
                padding: 1.25rem 1.5rem;
            }

            /* ❌ modal-inner NO controla scroll */
            #eventModal .modal-inner {
                height: auto;
                overflow: visible;
                padding: 0;
            }

            /* =====================================================
        ALPINE
        ===================================================== */
            [x-cloak] {
                display: none;
            }

            /* =====================================================
        GRADIENTE HEADER
        ===================================================== */
            .bg-gradient-to-r {
                background-image: linear-gradient(to right, var(--tw-gradient-stops));
            }

            .from-blue-600 {
                --tw-gradient-from: #2563eb;
                --tw-gradient-stops: var(--tw-gradient-from), rgba(37, 99, 235, 0);
            }

            .to-blue-800 {
                --tw-gradient-to: #1e3a8a;
            }

            /* =====================================================
        MODAL OVERLAY
        ===================================================== */
            #eventModal {
                backdrop-filter: blur(4px);
            }

            /* =====================================================
        CONTENEDOR PRINCIPAL
        ===================================================== */
            #eventModal>div {
                border-radius: 1rem;
                box-shadow: 0 25px 60px rgba(0, 0, 0, .25);
                background: #fff;
                max-height: 90vh;
                display: flex;
                flex-direction: column;
            }

            /* =====================================================
        HEADER
        ===================================================== */
            #eventModal .modal-header {
                padding: 1rem 1.5rem;
                background: linear-gradient(135deg, #2563eb, #1e40af);
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            #eventModal .modal-title {
                font-size: 1.05rem;
                font-weight: 600;
                letter-spacing: .3px;
            }

            /* =====================================================
        TABS
        ===================================================== */
            #eventTabs {
                display: flex;
                gap: 2rem;
                border-bottom: 1px solid #e5e7eb;
                margin-bottom: 1.25rem;
            }

            #eventTabs button {
                background: none;
                padding: .4rem 0;
                font-size: .8rem;
                font-weight: 600;
                color: #6b7280;
                position: relative;
            }

            #eventTabs button.border-blue-600 {
                color: #111827;
            }

            #eventTabs button.border-blue-600::after {
                content: '';
                position: absolute;
                bottom: -1px;
                left: 0;
                width: 100%;
                height: 2px;
                background: #2563eb;
                border-radius: 2px;
            }

            /* =====================================================
        FORM SECTIONS
        ===================================================== */
            #eventModal .form-section {
                background: #f9fafb;
                border: 1px solid #e5e7eb;
                border-radius: .75rem;
                padding: .9rem 1.1rem;
                margin-bottom: 1.25rem;
            }

            #eventModal .form-section h6 {
                font-size: .8rem;
                font-weight: 700;
                color: #1f2937;
                margin-bottom: .75rem;
            }

            /* =====================================================
        FORM GRID
        ===================================================== */
            #eventModal .form-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem 1.25rem;
            }

            #eventModal .form-group {
                display: flex;
                flex-direction: column;
            }

            #eventModal .form-group.full {
                grid-column: 1 / -1;
            }

            /* =====================================================
        LABELS
        ===================================================== */
            #eventModal .form-group label {
                font-size: .68rem;
                font-weight: 600;
                margin-bottom: .2rem;
                color: #374151;
            }

            #eventModal .form-group label span {
                color: #dc2626;
            }

            /* =====================================================
        INPUTS
        ===================================================== */
            #eventModal input,
            #eventModal select,
            #eventModal textarea {
                height: 36px;
                font-size: .78rem;
                padding: .4rem .6rem;
                border-radius: .5rem;
                border: 1.5px solid #e5e7eb;
            }

            #eventModal textarea {
                min-height: 70px;
            }

            /* Focus */
            #eventModal input:focus,
            #eventModal select:focus,
            #eventModal textarea:focus {
                outline: none;
                border-color: #6366f1;
                box-shadow: 0 0 0 3px rgba(99, 102, 241, .15);
            }

            /* =====================================================
        CHECKBOX
        ===================================================== */
            #eventModal .checkbox-group label {
                display: flex;
                align-items: center;
                gap: .5rem;
                font-size: .8rem;
            }

            /* =====================================================
        FOOTER
        ===================================================== */
            #eventModal .modal-footer {
                padding: .9rem 1.25rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: #f9fafb;
                border-top: 1px solid #e5e7eb;
            }

            #eventModal .modal-footer .btn {
                border-radius: .6rem;
                padding: .4rem 1.15rem;
                font-size: .78rem;
                font-weight: 600;
            }

            #eventModal .btn-primary {
                background: #b08a2e;
                border: none;
                color: #fff;
            }

            #eventModal .btn-primary:hover {
                background: #9c7826;
            }

            #eventModal .btn-danger {
                background: #dc2626;
                border: none;
                color: #fff;
            }

            /* =====================================================
        RESPONSIVE
        ===================================================== */
            @media (max-width: 768px) {
                #eventModal .form-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
