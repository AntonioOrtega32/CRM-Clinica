@extends('panel.layouts.panel')
@section('title', 'Seguimiento de PX - Marketing')
@section('content')

    <section class="py-10 px-6 bg-white">
        <h1 class="text-4xl text-center font-[Poppins] mb-8 text-[#1C6C73]">
            Seguimiento de PX
            <span class="text-sm font-[Cinzel] text-gray-500"></span>
        </h1>

        <div class="bg-white border rounded-xl shadow-sm p-4 mb-6">
            <div class="flex flex-col md:flex-row md:items-end gap-3 justify-between">
                <div>
                    <h4 class="text-lg font-semibold">
                        Mostrando px del día:
                        <span id="date_title" class="text-[#1C6C73]"></span>
                        -
                        <span id="clinic_show" class="text-[#1C6C73]"></span>
                    </h4>

                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Selecciona fecha</label>
                            <input id="trackingDate" type="text"
                                class="mt-1 w-56 rounded-lg border-gray-300 shadow-sm focus:border-[#1C6C73] focus:ring-[#1C6C73]">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Clínica</label>
                            <select id="trackingClinic"
                                class="mt-1 w-48 rounded-lg border-gray-300 shadow-sm focus:border-[#1C6C73] focus:ring-[#1C6C73]">
                                <option value="Santa Fe">Santa Fe</option>
                                <option value="Pedregal">Pedregal</option>
                                <option value="Queretaro">Queretaro</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button id="reloadTracking" class="px-4 py-2 rounded-lg bg-[#1C6C73] text-white hover:bg-[#155b61]">
                        Actualizar
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white border rounded-xl shadow-sm p-4">
            <table id="trackingTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                <thead>
                    <tr class="bg-[#4298a7] text-white">
                        <th class="w-10"></th>
                        <th>Paciente / Expediente</th>
                        <th>Tipo</th>
                        <th>Revisión</th>
                        <th>Calificación</th>
                        <th>Hora</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>


        <script>
            // carpetas “esperadas” del viejo crm
            const FOLDERS = ["pre", "diseno", "post", "24horas", "10dias", "1mes", "3meses", "6meses", "9meses", "12meses",
                "15meses", "18meses", "21meses", "post_alta"
            ];

            function normalizeClinic(clinic) {
                return clinic === "Santa Fe" ? "Santafe" : clinic;
            }

            function badgeHtml(qualy) {
                const map = {
                    "Asistió": "bg-green-500",
                    "Pendiente": "bg-yellow-500",
                    "No asistió": "bg-red-500",
                };
                const cls = map[qualy] || "bg-gray-400";
                return `<span class="px-2 py-1 rounded-md text-white text-xs font-semibold ${cls}">${qualy || '—'}</span>`;
            }

            function parseExistingFolders(files) {
                // files viene como ["pre/archivo.jpg", "post/archivo.pdf", ...]
                const existing = (files || [])
                    .map(f => String(f).split('/')[0])
                    .filter(Boolean);

                // ordenar según FOLDERS
                existing.sort((a, b) => FOLDERS.indexOf(a) - FOLDERS.indexOf(b));
                return [...new Set(existing)];
            }

            function computeMissingFolders(eventType, expediente, existingFolders) {
                if (eventType === "tratamiento" && Number(expediente) === 0) return ["No aplica"];
                return FOLDERS.filter(f => !existingFolders.includes(f));
            }

            function detailsHtml(row) {
                const existingFolders = parseExistingFolders(row.files);
                const missing = computeMissingFolders(row.event_type, row.expedienteNumber, existingFolders);

                const filesHtml = (row.files && Array.isArray(row.files) && row.files.length) ?
                    `<ul class="list-disc ml-5">${row.files.map(f => `<li class="break-all">${f}</li>`).join('')}</ul>` :
                    `<span class="text-gray-500">No hay archivos</span>`;

                const missingHtml = missing.length ?
                    `<span class="text-gray-700">${missing.join(', ')}</span>` :
                    `<span class="text-gray-500">Ninguna</span>`;

                const attendance = row.attendance_type == 1 ? 'Virtual' : 'Presencial';

                return `
      <div class="p-4 bg-gray-50 border rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div>
            <div><span class="font-semibold">Notas:</span> ${row.description || '—'}</div>
            <div class="mt-1"><span class="font-semibold">Tipo:</span> ${attendance}</div>
            <div class="mt-1"><span class="font-semibold">Status:</span> ${row.status ?? '—'}</div>
          </div>
          <div>
            <div><span class="font-semibold">Archivos:</span><div class="mt-1">${filesHtml}</div></div>
            <div class="mt-2"><span class="font-semibold">Carpetas faltantes:</span> ${missingHtml}</div>
          </div>
        </div>
      </div>
    `;
            }

            async function fetchTracking(date, clinic) {
                const url =
                    `{{ route('panel.marketing.trackingpx.data') }}?date=${encodeURIComponent(date)}&clinic=${encodeURIComponent(normalizeClinic(clinic))}`;
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return await res.json();
            }

            $(document).ready(function() {
                // fecha hoy YYYY-MM-DD
                const today = new Date();
                const yyyy = today.getFullYear();
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                const dd = String(today.getDate()).padStart(2, '0');
                let currentDate = `${yyyy}-${mm}-${dd}`;

                let currentClinic = "Santa Fe";

                $("#date_title").text(currentDate);
                $("#clinic_show").text(currentClinic);

                // flatpickr
                flatpickr("#trackingDate", {
                    dateFormat: "Y-m-d",
                    locale: "es",
                    defaultDate: currentDate,
                    onChange: function(selectedDates, dateStr) {
                        currentDate = dateStr;
                        $("#date_title").text(currentDate);
                        loadTable();
                    }
                });

                $("#trackingClinic").val(currentClinic).on('change', function() {
                    currentClinic = $(this).val();
                    $("#clinic_show").text(currentClinic);
                    loadTable();
                });

                $("#reloadTracking").on('click', function() {
                    loadTable();
                });

                // DataTable
                const table = $("#trackingTable").DataTable({
                    responsive: true,
                    scrollX: true,
                    data: [],
                    columns: [{
                            data: null,
                            orderable: false,
                            className: "dt-control",
                            defaultContent: `<button class="w-8 h-8 rounded-md border hover:bg-gray-100">▾</button>`
                        },
                        {
                            data: null,
                            render: (d, t, row) => {
                                const exp = row.expedienteNumber || 0;
                                return `<span class="font-semibold">${row.title || '—'}</span> <span class="text-gray-500">- ${exp}</span>`;
                            }
                        },
                        {
                            data: 'event_type',
                            render: d => `<span class="capitalize">${d || '—'}</span>`
                        },
                        {
                            data: 'review_time',
                            render: d => d ? `<span class="font-semibold">${d}</span>` : '—'
                        },
                        {
                            data: 'qualy',
                            render: (d) => badgeHtml(d)
                        },
                        {
                            data: 'start',
                            render: (d) => d ? String(d).slice(11, 16) :
                                '—' // HH:MM si viene "YYYY-MM-DD HH:MM:SS"
                        },
                    ],
                    order: [
                        [5, 'asc']
                    ],
                    dom: 'Bfrtip',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: 'Exportar a Excel',
                        className: 'px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700'
                    }, ],
                    language: {
                        url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                    }


                });

                // toggle row details (acordeón)
                $('#trackingTable tbody').on('click', 'td.dt-control button', function() {
                    const tr = $(this).closest('tr');
                    const row = table.row(tr);

                    if (row.child.isShown()) {
                        row.child.hide();
                        tr.removeClass('shown');
                    } else {
                        row.child(detailsHtml(row.data())).show();
                        tr.addClass('shown');
                    }
                });

                async function loadTable() {
                    try {
                        const json = await fetchTracking(currentDate, currentClinic);

                        if (json.success !== "true") {
                            table.clear().draw();
                            return;
                        }

                        table.clear();
                        table.rows.add(json.data || []);
                        table.draw();
                    } catch (e) {
                        console.error(e);
                        table.clear().draw();
                    }
                }

                loadTable();
            });
        </script>




    </section>

@endsection
