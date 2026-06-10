@extends('panel.layouts.panel')
@section('title', 'Procedimientos')
@section('content')

    <section class="py-10 px-6 bg-white">
        <h1 class="text-4xl text-center font-[Poppins] mb-8 text-[#1C6C73]">
            Procedimientos
            
        </h1>

        <div class="mb-6">
  <div class="bg-white border rounded-xl shadow-sm p-4">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

      <div>
        <label class="block text-sm font-semibold mb-1">Fecha Inicial:</label>
        <input type="text" id="minProcedimientos"
               class="w-full border rounded-lg px-3 py-2 text-sm">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Fecha Final:</label>
        <input type="text" id="maxProcedimientos"
               class="w-full border rounded-lg px-3 py-2 text-sm">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Sucursal:</label>
        <select id="filterClinic"
                class="w-full border rounded-lg px-3 py-2 text-sm bg-white">
          <option value="">Todas</option>
          <option value="Santa Fe">Santa Fe</option>
          <option value="Pedregal">Pedregal</option>
          <option value="Queretaro">Querétaro</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Tipo:</label>
        <select id="filterType"
                class="w-full border rounded-lg px-3 py-2 text-sm bg-white">
          <option value="">Todos</option>
          <option value="Capilar">Capilar</option>
          <option value="Barba">Barba</option>
          <option value="Ambos">Ambos</option>
        </select>
      </div>

    </div>
  </div>
</div>

        <br><br>

        <table id="TablaProc" style="width:100%" class="table table-striped table-bordered display nowrap">
            <thead class="bg-gray-dark color-palette text-white">
                <tr style="background-color: #4298a7">
                    <th>Fecha</th>
                    <th>Num. Médico</th>
                    <th>Paciente</th>
                    <th>Tipo</th>
                    <th>Clínica</th>
                    <th>Cuarto</th>
                    <th>Especialista</th>
                    <th>Conformidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($procedimientos as $p)
                    <tr>
                        <td data-order="{{ $p->procedure_date_raw ?? '' }}">
                            {{ $p->procedure_date }}
                        </td>
                        <td>{{ $p->num_med_record }}</td>
                        <td>
                            <a href="#" class="text-[#CDAF95] hover:text-[#C8BAAF] font-semibold single_procedure"
                                data-id="{{ $p->lead_id }}" data-clinic="{{ $p->clinic }}"
                                data-exp="{{ $p->num_med_record }}" data-procedure_type="{{ $p->procedure_type }}">
                                {{ $p->name }}
                            </a>
                        </td>
                        <td>{{ $p->procedure_type }}</td>
                        <td>{{ $p->clinic }}</td>
                        @if ($p->room == 4)
                            <td>Queretaro</td>
                        @elseif ($p->room == 0)
                            <td>Sin asignar</td>
                        @else
                            <td>{{ $p->room }}</td>
                        @endif
                        @if ($p->specialist == null)
                            <td>Sin asignar</td>
                        @else
                            <td>{{ $p->specialist }}</td>
                        @endif

                        <td>{{ $p->suma_conformidad }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Modal de Opciones -->
        <div id="optionsModal" class="fixed inset-0 hidden bg-black/40 backdrop-blur-sm items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6 relative animate-fadeIn">

                <!-- Botón cerrar -->
                <button id="closeModal" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl">
                    &times;
                </button>

                <h2 class="text-xl font-[Poppins] text-center text-[#1C6C73] mb-6">
                    Opciones del procedimiento
                </h2>

                <div class="grid grid-cols-2 gap-6 text-center">
                    <button id="btnInfo"
                        class="bg-slate-200 hover:bg-slate-300 text-gray-800 font-medium py-3 rounded-lg transition">
                        <i class="fa-solid fa-info-circle text-[#1C6C73] mr-2"></i> Información
                    </button>

                    <button id="btnPhoto"
                        class="bg-slate-200 hover:bg-slate-300 text-gray-800 font-medium py-3 rounded-lg transition">
                        <i class="fa-solid fa-camera text-[#1C6C73] mr-2"></i> Procedimiento
                    </button>

                    <button id="btnNotif"
                        class="bg-slate-200 hover:bg-slate-300 text-gray-800 font-medium py-3 rounded-lg transition">
                        <i class="fa-solid fa-bell text-[#1C6C73] mr-2"></i> Notificaciones
                    </button>

                    <button id="btnMicro"
                        class="bg-slate-200 hover:bg-slate-300 text-gray-800 font-medium py-3 rounded-lg transition">
                        <i class="fa-solid fa-vial text-[#1C6C73] mr-2"></i> Micro
                    </button>

                    <button id="btnTrat"
                        class="bg-slate-200 hover:bg-slate-300 text-gray-800 font-medium py-3 rounded-lg transition">
                        <i class="fa-solid fa-tablets text-[#1C6C73] mr-2"></i> Tratamientos
                    </button>

                    <button id="btnPhotoDos"
                        class="bg-slate-200 hover:bg-slate-300 text-gray-800 font-medium py-3 rounded-lg transition">
                        <i class="fa-solid fa-star-of-life text-[#1C6C73] mr-2"></i> 2do Procedimiento
                    </button>
                </div>
            </div>
        </div>
        <!-- Fin Modal de Opciones -->
        <!-- Modal de Información / Edición de Procedimiento -->
        <div id="editModal" class="fixed inset-0 hidden bg-black/40 backdrop-blur-sm items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6 relative animate-fadeIn">

                <!-- Botón cerrar -->
                <button id="closeEditModal" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl">
                    &times;
                </button>

                <h2 class="text-xl font-[Poppins] text-center text-[#1C6C73] mb-6">
                    Información del Procedimiento
                </h2>

                <form id="formUpdateProcedure" action="{{ route('panel.procedimientos.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="procedure_id" id="procedure_id">

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold mb-1">Fecha de procedimiento</label>
                            <input type="text" id="procedure_date" class="w-full border rounded px-2 py-1 bg-gray-100"
                                disabled>
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Num. de expediente</label>
                            <input type="text" id="num_med_record" class="w-full border rounded px-2 py-1 bg-gray-100"
                                disabled>
                        </div>

                        <div class="col-span-2">
                            <label class="block font-semibold mb-1">Nombre del paciente</label>
                            <input type="text" id="name" class="w-full border rounded px-2 py-1 bg-gray-100"
                                disabled>
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Tipo de Injerto</label>
                            <input type="text" id="procedure_type" class="w-full border rounded px-2 py-1 bg-gray-100"
                                disabled>
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Sala</label>
                            <select name="room" id="room" class="w-full border rounded px-2 py-1">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">Queretaro</option>
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label class="block font-semibold mb-1">Especialista</label>
                            <select name="specialist" id="specialist" class="w-full border rounded px-2 py-1">
                                <option value="Dr Alejandro Santana">Dr Alejandro Santana</option>
                                <option value="Esp Luis Moreno">Esp Luis Moreno</option>
                                <option value="Esp Xóchitl Lagunas">Esp Xóchitl Lagunas</option>
                                <option value="Esp Héctor Carmona">Esp Héctor Carmona</option>
                                <option value="Dra Oriana Aguilar">Dra Oriana Aguilar</option>
                                <option value="Dra Samanta Soto">Dra Samanta Soto</option>
                                <option value="Esp Fernanda Chavez">Esp Fernanda Chavez</option>
                                <option value="Esp Ivan Mora">Esp Ivan Mora</option>
                                <!-- Agrega los demás manualmente -->
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label class="block font-semibold mb-1">Observaciones</label>
                            <textarea name="notes" id="notes" rows="3" class="w-full border rounded px-2 py-1"></textarea>
                        </div>
                    </div>

                    <div class="mt-4 text-right">
                        <button type="submit"
                            class="bg-[#1C6C73] hover:bg-[#4298A7] text-white font-[Poppins] px-4 py-2 rounded-lg transition">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Fin Modal de Información / Edición de Procedimiento -->

    </section>

    <script>
        $(document).ready(function() {
          
            // === Inicializar DatePickers ===
            let minDate = new DateTime($('#minProcedimientos'), {
                format: 'YYYY-MM-DD'
            });
            let maxDate = new DateTime($('#maxProcedimientos'), {
                format: 'YYYY-MM-DD'
            });

            // === Filtro personalizado ===
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'TablaProc') return true;

            const min = minDate.val(); // Date o null
            const max = maxDate.val(); // Date o null

            const dateStr = data[0]; // puede ser "Por definir" o "DD/MM/YY"
            const date = moment(dateStr, 'DD/MM/YY', true);

            // si es "Por definir" (o inválida), NO pasa filtro de rango si hay min/max
            if (!date.isValid()) {
                return (min === null && max === null);
            }

            const minM = min ? moment(min).startOf('day') : null;
            const maxM = max ? moment(max).endOf('day') : null;

            const clinicFilter = $('#filterClinic').val();
            const typeFilter   = $('#filterType').val();
            const clinic = data[4];
            const type   = data[3];

            const dateOk =
                (!minM && !maxM) ||
                (!minM && date.isSameOrBefore(maxM, 'day')) ||
                (!maxM && date.isSameOrAfter(minM, 'day')) ||
                (date.isSameOrAfter(minM, 'day') && date.isSameOrBefore(maxM, 'day'));

            const clinicOk = (clinicFilter === "" || clinic === clinicFilter);
            const typeOk   = (typeFilter === "" || type === typeFilter);

            return dateOk && clinicOk && typeOk;
            });

            // === Inicializar DataTable ===
            let table = $('#TablaProc').DataTable({
                responsive: true,
                scrollX: true,
                autoWidth: true,
                order: [
                    [0, 'desc']
                ],
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excel',
                        text: 'Excel',
                        className: 'bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg',
                        exportOptions: {
                            columns: [0,1,2,3,4,5,6]
                            } 
                    },
                    {
                        extend: 'pdf',
                        text: 'PDF',
                        className: 'bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg',
                        exportOptions: {
                            columns: [0,1,2,3,4,5,6]
                            } 
                    }
                ],
                language: {
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 a 0 de 0 registros",
                lengthMenu: "Mostrar _MENU_ registros",
                search: "Buscar:",
                loadingRecords: "Loading...",
                processing: "Procesando...",
                zeroRecords: "No hay registros aún",
                paginate: {
                next: '→',
                previous: '←',
                first: 'Inicio',
                last: 'Ultimo'
                },
            },
                columnDefs: [{
                    targets: 7,
                    className: "text-center",
                    render: function(data) {
                        const suma = parseFloat(data) || 0;
                        const val = Math.min((suma / 25) * 5, 5);
                        const fullStars = Math.floor(val);
                        const halfStar = (val - fullStars >= 0.5) ? 1 : 0;
                        const emptyStars = 5 - fullStars - halfStar;
                        let starsHtml = "";
                        for (let i = 0; i < fullStars; i++) starsHtml +=
                            '<i class="fa-solid fa-star text-yellow-500 glow"></i>';
                        if (halfStar) starsHtml +=
                            '<i class="fa-solid fa-star-half-stroke text-yellow-500 glow"></i>';
                        for (let i = 0; i < emptyStars; i++) starsHtml +=
                            '<i class="fa-regular fa-star text-gray-400"></i>';
                        return `<div class="stars">${starsHtml}</div>`;
                    }
                }],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, 200, -1],[10, 25, 50, 100, 200, "Todos"]],

            });

            $('#minProcedimientos, #maxProcedimientos, #filterClinic, #filterType').on('change', function() {
                table.draw();
            });

            // === Variables globales de modales ===
            const modal = document.getElementById("optionsModal");
            const closeModal = document.getElementById("closeModal");
            const editModal = document.getElementById("editModal");
            const closeEditModal = document.getElementById("closeEditModal");

            // === Mostrar modal de opciones ===
            $(document).on("click", ".single_procedure", function(e) {
                e.preventDefault();
                const procedureId = $(this).data("id");
                const numMed = $(this).data("exp");
                const clinic = $(this).data("clinic");
                const procedureType = $(this).data("procedure_type");

                // Asignar datos a todos los botones
                $("#btnInfo, #btnPhoto, #btnNotif, #btnMicro, #btnPhotoDos,#btnTrat").data({
                    procedureid: procedureId,
                    clinic: clinic,
                    numMed: numMed,
                    leadid: procedureId,
                    procedure_type: procedureType
                });

                // Mostrar modal
                modal.classList.remove("hidden");
                modal.classList.add("flex");
            });

            // === Cerrar modal de opciones ===
            closeModal.addEventListener("click", () => modal.classList.add("hidden"));
            modal.addEventListener("click", (e) => {
                if (e.target === modal) modal.classList.add("hidden");
            });

            // === Botón “Información” ===
            $("#btnInfo").on("click", function() {
                const procedureId = $(this).data("procedureid");
                modal.classList.add("hidden");

                $.ajax({
                    type: "GET",
                    url: `/panel/procedimientos/info/${procedureId}`,
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            const p = response.procedimiento;
                            $("#procedure_id").val(p.lead_id);
                            $("#procedure_date").val(p.procedure_date);
                            $("#num_med_record").val(p.num_med_record);
                            $("#name").val(p.name);
                            $("#procedure_type").val(p.procedure_type);
                            $("#room").val(p.room);
                            $("#specialist").val(p.specialist);
                            $("#notes").val(p.notes || "");

                            editModal.classList.remove("hidden");
                            editModal.classList.add("flex");
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(err) {
                        console.log(err);
                        alert("No se pudo cargar la información.");
                    }
                });
            });

            // === Cerrar modal de edición ===
            closeEditModal.addEventListener("click", () => editModal.classList.add("hidden"));
            editModal.addEventListener("click", (e) => {
                if (e.target === editModal) editModal.classList.add("hidden");
            });

            // === Guardar cambios con AJAX y recargar ===
            $("#formUpdateProcedure").submit(function(e) {
                e.preventDefault();
                $.ajax({
                    type: "POST",
                    url: $(this).attr("action"),
                    data: $(this).serialize(),
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            editModal.classList.add("hidden");
                            location.reload();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(err) {
                        console.log(err);
                        alert("Algo salió mal.");
                    }
                });
            });

            // === Botón “Procedimiento / Fotos” ===
            $("#btnPhoto").on("click", function() {
                const numMed = $(this).data("numMed");

                if (!numMed) {
                    alert("No se encontró número médico para este procedimiento.");
                    return;
                }

                // Puedes agregar un selector para elegir el paso si quieres:
                // const step = prompt("Ingrese paso: pre, post, diseño", "pre");
                const step = "pre"; // por defecto 'pre'

                // Cierra el modal
                modal.classList.add("hidden");

                // Abre en nueva pestaña
                window.open(`/panel/procedimientos/fotos/${numMed}/${step}`, '_blank'); '_blank';
            });
            // === Botón “Procedimiento / Fotos” ===
            $("#btnPhotoDos").on("click", function() {
                const numMed = $(this).data("numMed");
                if (!numMed) {
                    alert("No se encontró número médico para este procedimiento.");
                    return;
                }
                // Puedes agregar un selector para elegir el paso si quieres:
                // const step = prompt("Ingrese paso: pre, post, diseño", "pre");
                const step = "pre"; // por defecto 'pre'

                // Cierra el modal
                modal.classList.add("hidden");

                // Redirige a la vista de fotos
                window.open(`/panel/procedimientos/fotos/touchup/${numMed}/${step}`, '_blank');
            });

            // === Botón MIcros / Fotos” ===
            $("#btnMicro").on("click", function() {
                const numMed = $(this).data("numMed");

                if (!numMed) {
                    alert("No se encontró número médico para este procedimiento.");
                    return;
                }

                // Puedes agregar un selector para elegir el paso si quieres:
                // const step = prompt("Ingrese paso: pre, post, diseño", "pre");
                const step = "pre"; // por defecto 'pre'

                // Cierra el modal
                modal.classList.add("hidden");

                // Redirige a la vista de fotos
                window.open(`/panel/procedimientos/fotos/micro/${numMed}/${step}`, '_blank');
            });
            $(document).on("click", "#btnNotif", function(e) {
                e.preventDefault();

                const leadId = $(this).data("leadid"); // lead_id
                const procedureType = $(this).data("procedure_type"); // procedure_type
                const userId = window.USER_ID_GLOBAL || (window.Laravel && window.Laravel.userId) ||
                ''; // obtener user_id

                if (!leadId) {
                    alert("No se encontró LEAD para este procedimiento.");
                    return;
                }

                if (!procedureType) {
                    alert("No se encontró tipo de procedimiento.");
                    return;
                }

                // Redirigir a la vista de notificaciones (no a la API JSON)
                window.open(`/panel/notifications?id=${leadId}&procedure_type=${procedureType}&user_id=${userId}`, '_blank');
                    `/panel/notifications?id=${leadId}&procedure_type=${procedureType}&user_id=${userId}`, '_blank';
            });



            $("#btnTrat").on("click", function() {
                const numMed = $(this).data("numMed");

                if (!numMed) {
                    alert("No se encontró número médico para este procedimiento.");
                    return;
                }

                // Cierra el modal
                modal.classList.add("hidden");
                const trat = 1;

                // Redirige a la vista de fotos
                window.open(`/panel/procedimientos/fotos/treatment/${numMed}/0/${trat}`, '_blank');
            });
        });
    </script>
    <style>

        .stars i {
            font-size: 1.2rem;
            margin: 0 1px;
            transition: transform 0.2s ease-in-out, text-shadow 0.3s;
        }

        .stars i.glow:hover {
            transform: scale(1.2);
            text-shadow: 0 0 8px rgba(255, 215, 0, 0.7);
        }
    </style>
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.2s ease-out;
        }
    </style>
@endsection
