@extends('panel.layouts.panel')
@section('title', 'Ver Lead')
@section('content')

    <section class="py-10 px-6 bg-white">
 
        <h1 class="text-4xl text-center font-[Poppins] mb-8 text-[#1C6C73]">
            Leads
            <span class="text-sm font-[Cinzel] text-gray-500"></span>
        </h1>
        <div class="mb-6">
            <div class="bg-white border rounded-xl shadow-sm p-4">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">

                    {{-- Fecha inicial --}}
                    <div class="md:col-span-1">
                        <label for="minLeads" class="block text-sm font-semibold text-gray-700 mb-1">
                            Fecha inicial
                        </label>
                        <input type="date" id="minLeads"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1C6C73] focus:ring-[#1C6C73]">
                    </div>

                    {{-- Fecha final --}}
                    <div class="md:col-span-1">
                        <label for="maxLeads" class="block text-sm font-semibold text-gray-700 mb-1">
                            Fecha final
                        </label>
                        <input type="date" id="maxLeads"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1C6C73] focus:ring-[#1C6C73]">
                    </div>

                    {{-- Sucursal --}}
                    <div class="md:col-span-1">
                        <label for="filterClinic" class="block text-sm font-semibold text-gray-700 mb-1">
                            Sucursal
                        </label>
                        <select id="filterClinic"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1C6C73] focus:ring-[#1C6C73]">
                            <option value="">Todas</option>
                            <option value="Santa Fe">Santa Fe</option>
                            <option value="Pedregal">Pedregal</option>
                            <option value="Queretaro">Querétaro</option>
                        </select>
                    </div>

                    {{-- Tipo (si lo vas a dejar) --}}
                    <div class="md:col-span-1">
                        <label for="filterType" class="block text-sm font-semibold text-gray-700 mb-1">
                            Tipo
                        </label>
                        <select id="filterType"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1C6C73] focus:ring-[#1C6C73]">
                            <option value="">Todos</option>
                            <option value="Capilar">Capilar</option>
                            <option value="Barba">Barba</option>
                            <option value="Ambos">Ambos</option>
                        </select>
                    </div>

                    {{-- Semáforo --}}
                    <div class="md:col-span-1">
                        <label for="filterSem" class="block text-sm font-semibold text-gray-700 mb-1">
                            Semáforo
                        </label>
                        <select id="filterSem"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1C6C73] focus:ring-[#1C6C73]">
                            <option value="">Todos</option>
                            <option value="Interesado">Interesado</option>
                            <option value="Valoración">Valoración</option>
                            <option value="Tratamiento">Tratamiento</option>
                            <option value="Cierre">Cierre</option>
                        </select>
                    </div>

                    {{-- Propietario --}}
                    <div class="md:col-span-1">
                        <label for="filterSeller" class="block text-sm font-semibold text-gray-700 mb-1">
                            Propietario
                        </label>
                        <select id="filterSeller"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[#1C6C73] focus:ring-[#1C6C73]">
                            <option value="">Todos</option>
                            <option value="Janeth Ruíz">Janeth Ruíz</option>
                            <option value="Marisol Olmos">Marisol Olmos</option>
                            <option value="Paola Segura">Paola Segura</option>
                        </select>
                    </div>

                </div>

                {{-- Botones opcionales --}}
                <div class="flex flex-wrap gap-2 justify-end mt-4">
                    <button type="button" id="clearFilters"
                        class="px-4 py-2 rounded-lg border text-gray-700 hover:bg-gray-50">
                        Limpiar filtros
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de leads -->
        <br><br>
        <table id="TablaLeads" style="width:100%" class="table table-striped table-bordered display nowrap">
            <thead class="bg-gray-dark color-palette text-white">
                <tr style="background-color: #4298a7">
                    <th>ID</th>
                    <th>Nombre completo</th>
                    <th>Clínica</th>
                    <th>Telefono</th>
                    <th>Interés en:</th>
                    <th>Semáforo</th>
                    <!--<th>Etapa</th>-->
                    <th>Propietarios</th>
                    <th>Fecha de Valoracion</th>
                    <!--<th>Actividad proxima</th>
                        <th>Valoración</th>
                        <th>Status</th>
                        <th>Opt.</th>-->
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

        <script>
            // Flatpickr
            flatpickr("#minLeads", {
                dateFormat: "Y-m-d",
                locale: "es"
            });
            flatpickr("#maxLeads", {
                dateFormat: "Y-m-d",
                locale: "es"
            });

            $(document).ready(function() {

                // Colores semáforo (solo 4 opciones)
                const SEMAFORO_COLORS = {
                    'Cierre': '#009346',
                    'Interesado': '#14db73',
                    'Valoración': '#35a0ea',
                    'Tratamiento': '#e174f5',
                };

                function semaforoSelectHtml(leadId, current) {
                    const options = ['Interesado', 'Valoración', 'Tratamiento', 'Cierre'];

                    const bg = SEMAFORO_COLORS[current] || '#e5e7eb';
                    const textColor = (current === 'Interesado') ? '#000' : '#fff';

                    const optsHtml = options.map(o =>
                        `<option value="${o}" ${o === current ? 'selected' : ''}>${o}</option>`
                    ).join('');

                    return `
        <select
          class="semaforo-select rounded-md px-2 py-1 text-sm"
          data-lead-id="${leadId}"
          style="background:${bg};color:${textColor};border:1px solid rgba(0,0,0,.15)"
        >
          <option value="">Semáforo</option>
          ${optsHtml}
        </select>
      `;
                }

                const table = $('#TablaLeads').DataTable({
                    serverSide: false,
                    processing: true,
                    responsive: true,
                    scrollX: true,
                    autoWidth: true,
                    ajax: "{{ route('panel.leads.reporte.data') }}",
                    columns: [{
                            data: 'id'
                        },
                        {
                            data: 'name',
                            render: function(data, type, row) {
                                if (type !== 'display') return data;

                                return `
            <a href="{{ url('/panel/leads') }}/${row.id}" target="_blank"
                class="text-[#1C6C73] font-semibold hover:underline">
                ${data}
            </a>
            `;
                            }
                        },
                        {
                            data: 'clinic'
                        },
                        {
                            data: 'phone'
                        },
                        {
                            data: 'interested_in'
                        },

                        // Semáforo editable (col 5)
                        {
                            data: 'semaforo',
                            orderable: false,
                            render: function(data, type, row) {
                                if (type !== 'display') return data || '';
                                return semaforoSelectHtml(row.id, data || '');
                            }
                        },

                        {
                            data: 'seller'
                        },
                        {
                            data: 'created_at'
                        }
                    ],

                    dom: 'Bfrtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: 'Excel',
                            className: 'bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    search: 'applied',
                                    order: 'applied'
                                },
                                format: {
                                    body: function(data, row, column, node) {
                                        const $select = $('select', node);
                                        if ($select.length) return $select.val() || '';
                                        return $(node).text().trim() || data;
                                    }
                                }
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: 'PDF',
                            className: 'bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg',
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    search: 'applied',
                                    order: 'applied'
                                },
                                format: {
                                    body: function(data, row, column, node) {
                                        const $select = $('select', node);
                                        if ($select.length) return $select.val() || '';
                                        return $(node).text().trim() || data;
                                    }
                                }
                            }
                        }
                    ],

                    order: [
                        [0, 'desc']
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

                // Filtro personalizado (fecha + clínica + semáforo + propietario)
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    const minStr = $('#minLeads').val().trim();
                    const maxStr = $('#maxLeads').val().trim();

                    // Fecha de la fila (col 7)
                    const rowStr = data[7];
                    const row = moment(rowStr, ['YYYY-MM-DD', 'DD/MM/YYYY'], true);
                    if (!row.isValid()) return true;

                    let dateOk = true;

                    if (minStr) {
                        const min = moment(minStr, ['YYYY-MM-DD', 'DD/MM/YYYY'], true);
                        if (min.isValid() && row.isBefore(min, 'day')) dateOk = false;
                    }
                    if (maxStr) {
                        const max = moment(maxStr, ['YYYY-MM-DD', 'DD/MM/YYYY'], true);
                        if (max.isValid() && row.isAfter(max, 'day')) dateOk = false;
                    }

                    const clinicFilter = $('#filterClinic').val();
                    const semFilter = $('#filterSem').val();
                    const sellerFilter = $('#filterSeller').val();

                    // Ojo: data[5] ahora contiene HTML (select), hay que leer el valor actual del select si existe
                    let semValue = data[5];
                    // intentar sacar el valor del select en el DOM si está renderizado
                    const $rowNode = $(table.row(dataIndex).node());
                    const $sel = $rowNode.find('select.semaforo-select');
                    if ($sel.length) semValue = $sel.val() || '';

                    const clinic = data[2];
                    const seller = data[6];

                    return dateOk &&
                        (!clinicFilter || clinic === clinicFilter) &&
                        (!semFilter || semValue === semFilter) &&
                        (!sellerFilter || seller === sellerFilter);
                });

                $('#minLeads, #maxLeads, #filterClinic, #filterSem, #filterSeller').on('change', function() {
                    table.draw();
                });

                // ✅ Update semáforo al cambiar (delegación)
                $(document).on('change', '.semaforo-select', async function() {
                    const $sel = $(this);
                    const leadId = $sel.data('lead-id');
                    const newVal = $sel.val();

                    // guardar valor previo para revertir si falla
                    const row = table.row($sel.closest('tr'));
                    const rowData = row.data();
                    const oldVal = rowData?.semaforo || '';

                    $sel.prop('disabled', true);

                    try {
                        const res = await fetch(`{{ url('/panel/leads') }}/${leadId}/semaforo`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                semaforo: newVal
                            })
                        });

                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        const json = await res.json();
                        if (!json.success) throw new Error('no success');

                        // UI: cambiar color
                        const bg = SEMAFORO_COLORS[newVal] || '#e5e7eb';
                        const textColor = (newVal === 'Interesado') ? '#000' : '#fff';
                        $sel.css({
                            background: bg,
                            color: textColor
                        });

                        // actualizar data interno para filtros/export
                        rowData.semaforo = newVal;
                        row.data(rowData).invalidate();

                        // si hay filtro activo por semáforo, redibujar
                        table.draw();

                    } catch (e) {
                        // revertir
                        $sel.val(oldVal).trigger(
                        'change.select2'); // por si usan select2 (si no, no pasa nada)
                        alert('Error al actualizar semáforo');
                    } finally {
                        $sel.prop('disabled', false);
                    }
                });

            });
        </script>

        <script>
            $('#clearFilters').on('click', function() {
                $('#minLeads').val('');
                $('#maxLeads').val('');
                $('#filterClinic').val('');
                $('#filterType').val('');
                $('#filterSem').val('');
                $('#filterSeller').val('');
                $('#TablaLeads').DataTable().draw();
            });
        </script>

        </div>
    @endsection
