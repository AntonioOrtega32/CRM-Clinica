@extends('panel.layouts.panel')
@section('title', 'Clientes')
@section('content')
 
<section class="py-10 px-6 bg-white">
        <h1 class="text-4xl text-center font-[Poppins] mb-8 text-[#1C6C73]">
            Clientes
            <span class="text-sm font-[Cinzel] text-gray-500"></span>
        </h1>

        <div class="mb-6 flex justify-end">
            <button onclick="openModal()" class="bg-[#1C6C73] hover:bg-[#155357] text-white px-4 py-2 rounded-lg shadow">
                Cambiar numero de exp
            </button>
        </div>

       <!-- evolver la tabla de filtros en un div responsive -->
<div class="filters-wrapper mb-6 overflow-x-auto">
    <table class="date-table">
        <tbody>
            <tr>
                <td><strong>Fecha Inicial:</strong></td>
                <td><input type="text" id="minCliente" class="date-input"></td>

                <td><strong>Fecha Final:</strong></td>
                <td><input type="text" id="maxCliente" class="date-input"></td>
                <tr></tr>
                <td><strong>Sucursal:</strong></td>
                <td>
                    <select id="filterClinic" class="date-input">
                        <option value="">Todas</option>
                        <option value="Santa Fe">Santa Fe</option>
                        <option value="Pedregal">Pedregal</option>
                        <option value="Queretaro">Querétaro</option>
                    </select>
                </td>

                <td><strong>Propietario:</strong></td>
                <td>
                    <select id="filterProp" class="date-input">
                        <option value="">Todo(a)s</option>
                        <option value="Janeth Ruiz">Janeth Ruiz</option>
                        <option value="Marisol Olmos">Marisol Olmos</option>
                        <option value="Paola Segura">Paola Segura</option>
                    </select>
                </td>

                <td><strong>Status:</strong></td>
                <td>
                    <select id="filterStatus" class="date-input">
                        <option value="">Todos</option>
                        <option value="1">Próximo</option>
                        <option value="2">Expediente Asignado</option>
                        <option value="0">Cancelado</option>
                    </select>
                </td>

                <td><strong>Tipo:</strong></td>
                <td>
                    <select id="filterType" class="date-input">
                        <option value="">Todos</option>
                        <option value="Capilar">Capilar</option>
                        <option value="Barba">Barba</option>
                        <option value="Ambos">Ambos</option>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>
</div>
        
<br><br><br>

        <table id="TablaClientes" style="width:100%" class="table table-striped table-bordered display nowrap">
            <thead class="bg-gray-dark color-palette text-white">
                <tr style="background-color: #4298a7">
                    <th>Nombre del Px</th>
                    <th>Tipo de procedimineto</th>
                    <th>Fecha del Procedimiento</th>
                    <th>Clinica</th>
                    <th>Vendedor(a)</th>
                    <th>Telefono</th>
                    <th class="no-export">Opciones</th>
                    <th>Num. Medico</th>
                </tr>
            </thead>
            <tbody>
        @foreach ($clientes as $c)
            <tr>
                <td>
                    <a href="{{ route('panel.leads.show', ['id' => $c->lead_id, 'client' => 'yes']) }}"
                    class="text-[#CDAF95] hover:text-[#DED5CE] font-semibold hover:underline"
                    target="_blank">
                        {{ $c->name }}
                    </a>
                </td>
                <td>{{ $c->procedure_type }}</td>
                @if ($c->procedure_date_raw == '2030-01-01')
                    <td>Fecha Abierta</td>
                @else
                <td data-order="{{ $c->procedure_date_raw }}" data-raw="{{ $c->procedure_date_raw }}">
                    {{ $c->procedure_date }}
                </td>
                @endif
                <td>{{ $c->clinic }}</td>
                <td>{{ $c->seller }}</td>
                <td>{{ $c->phone }}</td>
                <td class="relative flex flex-col items-center justify-center no-export"> <!-- importante: relative para posicionar el menu -->
                    @if ($c->status == 1)
                        <p class="text-[#4298A7] font-[Poppins]">Próximo</p>
                    @elseif ($c->status == 2)
                        <p class="text-[#1C6C73] font-[Poppins]">Asignar Expediente</p>
                    @elseif ($c->status == 0)
                        <p class="text-[#FA2323] font-[Poppins]">Cancelado</p>
                    @else
                        <p class="text-[#585858] font-[Poppins]"> Desconocido </p>
                    @endif
                    <br><br>

                    <button type="button"
                            class="status-toggle bg-[#1C6C73] text-white px-2 py-1 rounded focus:outline-none"
                            aria-expanded="false">
                        Cambiar
                    </button>

                    <!-- menu oculto -->
                    <div class="status-menu hidden absolute right-0 mt-2 w-48 bg-white border rounded shadow-lg z-50">
                        <a href="#" class="block px-4 py-2 hover:bg-gray-100 change-status" data-value="1" data-label="Próximo">Próximo</a>
                        <a href="#" class="block px-4 py-2 hover:bg-gray-100 change-status" data-value="2" data-label="Asignar Exped.">Asignar Expediente</a>
                        <a href="#" class="block px-4 py-2 hover:bg-gray-100 change-status" data-value="0" data-label="Cancelado">Cancelado</a>

                        <!-- Datos en atributos del menú para leer later -->
                        <div class="sr-only menu-meta" data-lead="{{ $c->lead_id }}" data-current="{{ $c->status }}"></div>
                    </div>
                </td>
                <td>{{ $c->num_med_record }}</td>
            </tr>
        @endforeach
    </tbody>
        </table>

        <!-- BACKDROP -->
        <div id="modalPX"
            class="hidden fixed inset-0 bg-black backdrop-blur-sm bg-opacity-50 flex items-center justify-center z-50">

            <!-- MODAL CARD -->
            <div
                class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 relative
            max-h-[90vh] overflow-y-auto">

                <!-- Cerrar -->
                <button onclick="closeModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-xl">
                    &times;
                </button>

                <h2 class="text-2xl font-[Poppins] text-[#1C6C73] mb-4">Cambiar Num de Expediente</h2>

                <!-- FORM -->
                <form id="formAddPx" onsubmit="event.preventDefault(); cambiarEXP();">

                    <!-- Número de expediente -->
                    <label class="block mb-1 text-sm font-medium">No. de expediente</label>
                    <input id="num_med_record" name="num_med_record" type="text"
                        class="w-full border rounded-lg px-3 py-2 mb-3" placeholder="Ejemplo: 12345">

                    <!-- Buscar paciente -->
                    <button type="button" onclick="buscarPaciente()"
                        class="bg-[#4298a7] hover:bg-[#367f8c] text-white px-3 py-2 rounded-lg mb-4">
                        Buscar Paciente
                    </button>

                    <!-- Nombre (cuando hay expediente) -->
                    <label class="block mb-1 text-sm font-medium">Paciente encontrado</label>
                    <div id="fullname" class="w-full border rounded-lg px-3 py-2 bg-gray-100 mb-4"></div>

                    <!-- Nombre del tratamiento -->
                    <label class="block mb-1 text-sm font-medium">Nuevo numero de expediente</label>
                    <input name="numero" type="text" class="w-full border rounded-lg px-3 py-2 mb-4"
                        placeholder="Ejemplo:  2XXX">

                    <!-- Botón enviar -->
                    <button type="submit" class="w-full bg-[#1C6C73] hover:bg-[#155357] text-white py-2 rounded-lg">
                        Guardar Cambios
                    </button>

                </form>

            </div>
        </div>

        
        {{-- Modal de los fokin estatus --}}
            <div id="statusModal" class="fixed inset-0 hidden items-center justify-center z-50">
            <div class="fixed inset-0 bg-black/50"></div>
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 z-60 p-6 relative">
                <button id="statusModalClose" class="absolute top-3 right-3 text-gray-500 hover:text-gray-800">&times;</button>

                <h3 class="text-xl font-semibold mb-3">Cambiar Estatus</h3>

                <div class="mb-3">
                <p><strong>Actual:</strong> <span id="currentStatusTxt"></span></p>
                <p><strong>Nuevo:</strong> <span id="selectedStatusTxt"></span></p>
                </div>

                <div id="statusDynamicInputs" class="mt-3"></div>

                <!-- hidden inputs -->
                <input type="hidden" id="lead_id">
                <input type="hidden" id="current_status_val">
                <input type="hidden" id="chosen_status_lbl">

                <div class="mt-6 flex justify-end gap-2">
                <button id="statusModalCancel" class="px-4 py-2 rounded border">Cerrar</button>
                <button id="statusSaveBtn" class="px-4 py-2 rounded bg-[#1C6C73] text-white">Guardar</button>
                </div>
            </div>
            </div>
        <script>
            $(function() {

                // ---------- DROPDOWN + MODAL (Tailwind-friendly) ----------
                $(document).on('click', '.status-toggle', function(e) {
                    e.stopPropagation();
                    $('.status-menu').not($(this).next('.status-menu')).addClass('hidden');
                    $(this).next('.status-menu').toggleClass('hidden');
                });

                $(document).on('click', function() {
                    $('.status-menu').addClass('hidden');
                });

                $(document).on('click', '.status-menu', function(e) {
                    e.stopPropagation();
                });

                // click en opción -> abrir modal
                $(document).on('click', '.change-status', function(e) {
                    e.preventDefault();

                    const $opt = $(this);
                    const newVal = $opt.data('value');  // 1 | 2 | 0
                    const newLabel = $opt.data('label');

                    // leer meta
                    const $menu = $opt.closest('.status-menu');
                    const $meta = $menu.find('.menu-meta');
                    const leadId = $meta.data('lead');
                    const current = $meta.data('current');

                    // textos
                    const currentTxt =
                        (current == 1) ? 'Próximo' :
                        (current == 2) ? 'Expediente Asignado' : 'Cancelado';

                    $('#currentStatusTxt').text(currentTxt);
                    $('#selectedStatusTxt').text(newLabel);

                    // hidden inputs
                    $('#lead_id').val(leadId);
                    $('#current_status_val').val(current);
                    $('#chosen_status_lbl').val(newLabel);

                    // limpiar inputs
                    $('#statusDynamicInputs').empty();

                    // generar inputs según acción
                    if (newVal == 0) {
                        // cancelado
                        $('#statusDynamicInputs').append(`
                            <label class="block mb-1">Motivo de cancelación</label>
                            <textarea id="cancel_reason" class="w-full border rounded p-2" rows="4" required></textarea>
                        `);

                    } else if (newVal == 2) {
                        // asignar expediente
                        $('#statusDynamicInputs').append(`
                            <label class="block mb-1">Número de expediente</label>
                            <input id="status_num_med_record" name="status_num_med_record" type="number"
                                class="w-full border rounded p-2" required />
                        `);

                    } else {
                        $('#statusDynamicInputs').append(`
                            <p class="text-sm text-gray-600">No se requiere información adicional.</p>
                        `);
                    }

                    // cerrar dropdown
                    $menu.addClass('hidden');

                    // abrir modal
                    $('#statusModal').removeClass('hidden').addClass('flex');
                    $('body').addClass('overflow-hidden');
                });

                // cerrar modal
                $('#statusModalClose, #statusModalCancel').on('click', function() {
                    $('#statusModal').addClass('hidden').removeClass('flex');
                    $('body').removeClass('overflow-hidden');
                });

                // submit AJAX
                $('#statusSaveBtn').on('click', function() {

                    const data = {
                        lead_id: $('#lead_id').val(),
                        current_status_val: $('#current_status_val').val(),
                        chosen_status_lbl: $('#chosen_status_lbl').val(),

                        // AJUSTADO: usamos el nuevo ID único
                        num_med_record: $('#status_num_med_record').val() || null,
                        cancel_reason: $('#cancel_reason').val() || null,

                        _token: "{{ csrf_token() }}"
                    };

                    // validar cancelación
                    if (data.chosen_status_lbl === 'Cancelado') {
                        if (!data.cancel_reason || data.cancel_reason.trim() === '') {
                            alert('Escribe el motivo de la cancelación.');
                            return;
                        }
                    }

                    // desactivar botón
                    $('#statusSaveBtn').prop('disabled', true).text('Guardando...');

                    $.ajax({
                        url: "{{ route('panel.clientes.update-status') }}",
                        method: 'POST',
                        data: data,
                        dataType: 'json'
                    })
                    .done(function(res) {
                        if (res.success) {
                            location.reload();
                        } else {
                            alert(res.message || 'Error al actualizar');
                        }
                    })
                    .fail(function(xhr) {
                        console.error(xhr.responseText);
                        alert('Error en la petición.');
                    })
                    .always(function() {
                        $('#statusSaveBtn').prop('disabled', false).text('Guardar');
                        $('#statusModal').addClass('hidden').removeClass('flex');
                        $('body').removeClass('overflow-hidden');
                    });
                });

            });
        </script>
        <script>
             /* --- Control del Modal --- */
            function openModal() {
                document.getElementById("modalPX").classList.remove("hidden");
            }

            function closeModal() {
                document.getElementById("modalPX").classList.add("hidden");
            }
             $(document).ready(function() {
                // === Inicializar DatePickers para rango de fechas ===
                let minDate = new DateTime($('#minCliente'), { format: 'YYYY-MM-DD' });
                let maxDate = new DateTime($('#maxCliente'), { format: 'YYYY-MM-DD' });

                // === Inicializar DataTable ===
                var table = $('#TablaClientes').DataTable({
                    responsive: true,
                    scrollX: true,
                    autoWidth: true,
                    processing: true,
                    order: [[2, 'desc']], // ordena por fecha
                    dom: 'Bfrtip',
                    buttons: [
                        { extend: 'excel', 
                        text: 'Excel', 
                        className: 'bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg',
                        exportOptions: {
                            columns: ':not(.no-export)'
                        }
                    },
                        { extend: 'pdf', 
                        text: 'PDF', 
                        className: 'bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg',
                        exportOptions: {
                            columns: ':not(.no-export)'
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
                });

                // === Filtros personalizados ===
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    let min = minDate.val();
                    let max = maxDate.val();
                    // Columna 2 = Fecha del Procedimiento
                    const rowNode = table.row(dataIndex).node();
                    const raw = $(rowNode).find('td').eq(2).attr('data-raw'); // YYYY-MM-DD

                    let date = raw ? moment(raw, 'YYYY-MM-DD') : moment.invalid();

                    let clinicFilter = $('#filterClinic').val();
                    let sellerFilter = $('#filterProp').val();
                    let statusFilter = $('#filterStatus').val();
                    let typeFilter = $('#filterType').val();

                    let clinic = data[3];
                    let seller = data[4];
                    let type = data[1];

                    // Status: se puede agregar data-status en tu td si quieres manejarlo mejor
                    let statusCell = $(table.row(dataIndex).node()).find('td').eq(5).data('status');

                    let dateOk =
                        (min === null && max === null) ||
                        (min === null && date.isSameOrBefore(max)) ||
                        (max === null && date.isSameOrAfter(min)) ||
                        (date.isSameOrAfter(min) && date.isSameOrBefore(max));

                    let clinicOk = (clinicFilter === "" || clinic === clinicFilter);
                    let sellerOk = (sellerFilter === "" || seller === sellerFilter);
                    let statusOk = (statusFilter === "" || statusCell == statusFilter);
                    let typeOk = (typeFilter === "" || type === typeFilter);

                    return dateOk && clinicOk && sellerOk && statusOk && typeOk;
                });

                // === Activar filtros al cambiar valores ===
                $('#minCliente, #maxCliente, #filterClinic, #filterProp, #filterStatus, #filterType').on('change', function() {
                    table.draw();
                });
            });
        </script>

        <script>
        function buscarPaciente() {

            let num = $("#num_med_record").val();

            if (!num || isNaN(num)) {
                $("#fullname").html(`<span class="text-red-600">Número inválido.</span>`);
                return;
            }

            $.ajax({
                url: "{{ route('panel.clientes.get-patient') }}",
                method: "POST",
                data: {
                    num_med_record: num,
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function () {
                    $("#fullname").html("Buscando...");
                },
                success: function (res) {

                    if (!res.success) {
                        $("#fullname").html(`<span class="text-red-600">${res.message}</span>`);
                        return;
                    }

                    $("#fullname").html(`
                        <div class="p-2 bg-green-100 border border-green-300 rounded">
                            <strong>${res.fullname}</strong><br>
                            <span class="text-sm text-gray-600">${res.clinic}</span>
                        </div>
                    `);
                },
                error: function () {
                    $("#fullname").html(`<span class="text-red-600">Error en la petición</span>`);
                }
            });
        }
        function cambiarEXP() {

            const oldNum = $("#num_med_record").val();
            const newNum = $("input[name='numero']").val();

            if (!oldNum || !newNum) {
                alert("Debes ingresar ambos números.");
                return;
            }

            $.ajax({
                url: "{{ route('panel.clientes.update-med-record') }}",
                method: "POST",
                data: {
                    num_med_record: oldNum,
                    numero: newNum,
                    _token: "{{ csrf_token() }}"
                },
                beforeSend() {
                    console.log("Enviando...");
                },
                success(res) {
                    if (!res.success) {
                        alert(res.message);
                        return;
                    }

                    alert(res.message);
                    location.reload();
                },
                error() {
                    alert("Error en la petición.");
                }
            });
        }
        </script>

<style>
        .filters-wrapper {
            width: 100%;
            overflow-x: auto; /* Permite scroll horizontal si es necesario */
        }

        .date-table {
            min-width: 800px; /* Ajusta según la cantidad de filtros */
            border-collapse: collapse;
        }

        .date-table td {
            padding: 5px 10px;
            white-space: nowrap; /* Evita que los textos se rompan */
        }

        .date-input {
            width: 140px; /* ancho fijo para inputs y selects */
            max-width: 100%;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
</style>

</section>
@endsection