@extends('panel.layouts.panel')
@section('title', 'Tratamientos')
@section('content')

    <section class="py-10 px-6 bg-white">
        <h1 class="text-4xl text-center font-[Poppins] mb-8 text-[#1C6C73]">
            Tratamientos

        </h1>

        <div class="mb-6 flex justify-end">
            <button onclick="openModal()" class="bg-[#1C6C73] hover:bg-[#155357] text-white px-4 py-2 rounded-lg shadow">
                Añadir Tratamiento
            </button>
        </div>

        {{-- Tabla de tratamientos --}}
        <table id="TablaTrat" style="width:100%" class="table table-striped table-bordered display nowrap">
            <thead class="bg-gray-dark color-palette text-white">
                <tr style="background-color: #4298a7">
                    <th>Ultimo Tratamiento</th>
                    <th class="no-export">Opciones</th>
                    <th>No. de Expediente</th>
                    <th>Paciente</th>
                    <th>Tipo de Tratamiento</th>
                    <th>Clinica del Expediente</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($tratamientos as $p)
                    <tr>
                        <td data-date="{{ $p->date ? \Carbon\Carbon::parse($p->date)->format('Y-m-d') : '' }}">
                            @if (!$p->date)
                                Sin fecha asignada.
                            @else
                                {{ \Carbon\Carbon::parse($p->date)->format('d/m/Y') }}
                            @endif
                        </td>

                        <td class="no-export">
                            {{-- Con expediente --}}
                            @if ($p->num_med_record)
                                <a href="{{ route('panel.procedimientos.fotos.treatment', [
                                    'num_med_record' => $p->num_med_record,
                                    'treatmentId' => $p->treatment_id,
                                    'trat' => 2,
                                ]) }}"
                                    class="bg-[#1C6C73] hover:bg-[#4298A7] text-white px-3 py-1 rounded-lg">
                                    Ver Fotos
                                </a>
                                {{-- Sin expediente --}}
                            @else
                                <a href="{{ $p->ext_treatment_id
                                    ? route('panel.tratamientos.sin-exp.view', ['px_id' => $p->treatment_id, 'treatmentId' => null])
                                    : route('panel.tratamientos.sin-exp.view', ['px_id' => $p->treatment_id]) }}"
                                    class="bg-[#CDAF95] hover:bg-[#DED5CE] text-white px-3 py-1 rounded-lg">
                                    Ver Fotos
                                </a>
                            @endif
                        </td>
                        <td>
                            @if ($p->num_med_record == '')
                                Sin Exp.
                            @else
                                {{ $p->num_med_record }}
                            @endif
                        </td>
                        <td>{{ $p->name }}</td>
                        <td>{{ $p->type }}</td>
                        <td>
                            @if ($p->clinic == '')
                                Sin clínica asignada.
                            @else
                                {{ $p->clinic }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{-- Fin tabla de tratamientos --}}

        <!-- BACKDROP -->
        <div id="modalAdd"
            class="hidden fixed inset-0 bg-black backdrop-blur-sm bg-opacity-50 flex items-center justify-center z-50">

            <!-- MODAL CARD -->
            <div
                class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 relative
            max-h-[90vh] overflow-y-auto">

                <!-- Cerrar -->
                <button onclick="closeModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-xl">
                    &times;
                </button>

                <h2 class="text-2xl font-[Poppins] text-[#1C6C73] mb-4">Añadir Tratamiento</h2>

                <!-- FORM -->
                <form id="formAddPx" onsubmit="event.preventDefault(); agregarTratamiento();">
                    @csrf

                    {{-- Forzamos modo sin expediente --}}
                    <input type="hidden" name="no_record" value="1">
                    <input type="hidden" name="num_med_record" value="">
                    <input type="hidden" name="clinic_num_med" value="">

                    <!-- Nombre manual -->
                    <label class="block mb-1 text-sm font-medium">Nombre del paciente</label>
                    <input id="name_manual" name="name_manual" type="text"
                        class="w-full border rounded-lg px-3 py-2 mb-4" placeholder="Ejemplo: Juan Pérez">

                    <!-- Clínica -->
                    <label class="block mb-1 text-sm font-medium">Clínica</label>
                    <select id="clinic_selecto" name="clinic_exp" class="w-full border rounded-lg px-3 py-2 mb-4">
                        <option value="">Seleccionar...</option>
                        <option value="Santa Fe">Santa Fe</option>
                        <option value="Pedregal">Pedregal</option>
                        <option value="Queretaro">Querétaro</option>
                    </select>

                    <button type="submit" class="w-full bg-[#1C6C73] hover:bg-[#155357] text-white py-2 rounded-lg">
                        Guardar Tratamiento
                    </button>
                </form>
                <!-- FIN FORM -->

            </div>
        </div>

        <script>
            // 1. FUNCIONES GLOBALES (Accesibles desde HTML)
            function handleBackdropClick(e) {
                // solo se ejecuta si se dio clic fuera del modal
                closeModal();
            }

            /* --- Control del Modal --- */
            function openModal() {
                document.getElementById("modalAdd").classList.remove("hidden");
            }

            function closeModal() {
                document.getElementById("modalAdd").classList.add("hidden");
            }


            /* --- Agregar Tratamiento (AJAX/Fetch) --- */
            // Guardar tratamiento (solo sin expediente)
            async function agregarTratamiento() {
                const form = document.getElementById("formAddPx");
                const fd = new FormData(form);

                // Seguridad extra: forzar sin expediente siempre
                fd.set("no_record", 1);
                fd.set("num_med_record", "");
                fd.set("clinic_num_med", "");

                try {
                    const res = await fetch("{{ route('panel.add.treatment') }}", {
                        method: "POST",
                        body: fd,
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        }
                    });

                    const data = await res.json();

                    if (data.success) {
                        alert(data.message || "Guardado.");
                        closeModal();
                        location.reload();
                    } else {
                        alert(data.message || "No se pudo guardar.");
                    }

                } catch (error) {
                    console.error("Error agregando tratamiento:", error);
                    alert("Ocurrió un error al procesar la solicitud.");
                }
            }

            // 2. INICIALIZACIÓN JQUERY (Document Ready)
            $(document).ready(function() {

                // --- Configuración de DataTable ---
                $('#TablaTrat').DataTable({
                    responsive: false,
                    scrollX: true,
                    autoWidth: true,
                    dom: 'Bfrtip',
                    order: [
                        [0, 'desc']
                    ], //más reciente arriba
                    columnDefs: [{
                        targets: 0,
                        render: function(data, type, row, meta) {
                            const cell = meta.settings.aoData[meta.row].anCells[0];
                            const raw = cell?.getAttribute('data-date') || '';

                            // 👇 Sin fecha: para ordenar ponlo MUY chico para que en DESC se vaya al final
                            if (!raw) {
                                if (type === 'sort' || type === 'type') return '0000-00-00';
                                return data; // “Sin fecha asignada.”
                            }

                            // ordenar con YYYY-MM-DD
                            if (type === 'sort' || type === 'type') return raw;

                            return data; // ya se imprime bonito desde Blade
                        }
                    }],
                    buttons: [{
                            extend: 'excel',
                            text: 'Excel',
                            className: 'bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg',
                            exportOptions: {
                                columns: ':not(.no-export)'
                            }
                        },
                        {
                            extend: 'pdf',
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
            });
        </script>
    </section>

@endsection
