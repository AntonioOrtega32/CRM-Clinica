@extends('panel.layouts.panel')
@section('title', 'Gestión de gastos')
@section('content')

    <section class="py-10 px-6 bg-gray-50">
        <h1 class="text-2xl text-center font-[Poppins] mb-8 text-[#1C6C73]">Administración de Gastos</h1>

        <!-- Botón para abrir modal -->
        <div class="flex justify-end mb-6">
            <button onclick="openModal('agregarGastoModal')"
                class="bg-[#1C6C73] hover:bg-[#14565c] text-white px-4 py-2 rounded shadow-md transition flex items-center gap-2">
                <span>Nuevo gasto</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="white"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="block">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
            </button>
        </div>

        <!-- Tabla de gastos -->
        <div class="bg-white p-4 rounded-lg shadow-md">

            {{-- Header filtros estilo CRM viejo --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-2">Ver gastos de</label>
                        <input id="rangeGastos" type="text"
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#1C6C73]/30"
                            value="{{ now()->startOfMonth()->format('d/m/Y') }} - {{ now()->endOfMonth()->format('d/m/Y') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-2">Sucursal</label>
                        <select id="clinicGastos"
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#1C6C73]/30">
                            <option value="Todas">Todas</option>
                            <option value="Santafe">Santa Fe</option>
                            <option value="Pedregal">Pedregal</option>
                            <option value="Queretaro">Querétaro</option>
                        </select>
                    </div>

                    <div class="md:text-right">
                        <div class="text-sm font-semibold text-gray-600 mb-2">Total:</div>
                        <h1 id="totalGastos" class="text-4xl font-[Poppins] text-[#1C6C73] tracking-tight">$0.00</h1>
                    </div>
                </div>
            </div>

            <table id="tablaGastos" style="width:100%" class="table table-striped table-bordered nowrap">
                <thead class="bg-[#1C6C73] text-white">
                    <tr>
                        <th class="px-4 py-2">ID</th>
                        <th class="px-4 py-2">Descripción</th>
                        <th class="px-4 py-2">Categoría</th>
                        <th class="px-4 py-2">Monto</th>
                        <th class="px-4 py-2">Fecha</th>
                        <th class="px-4 py-2">Sucursal</th>
                        <th class="px-4 py-2">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($gastos as $gasto)
                        <tr class="border-b">
                            <td>{{ $gasto->id }}</td>
                            <td style="white-space: pre-line;">{{ $gasto->description }}</td>
                            @if ($gasto->payment_method_id == 1)
                                <td><i class="fa-solid fa-money-bill text-[#0c864f]"></i> Efectivo</td>
                            @elseif ($gasto->payment_method_id == 2)
                                <td><i class="fa-solid fa-credit-card text-[#14565c]"></i> Tarjeta</td>
                            @elseif ($gasto->payment_method_id == 3)
                                <td><i class="fa-solid fa-right-left text-[#18127a]"></i> Transferencia</td>
                            @elseif ($gasto->payment_method_id == 4)
                                <td><i class="fa-solid fa-cash-register text-[#690c91]"></i> Depósito</td>
                            @else
                                <td>{{ $gasto->payment_method_id }}</td>
                            @endif
                            <td class="text-right text-red-600">$ {{ number_format(abs($gasto->amount), 2, '.', ',') }}</td>
                            <td data-order="{{ $gasto->date }}">
                                {{ \Carbon\Carbon::parse($gasto->date)->format('d/m/Y') }}
                            </td>
                            <td>{{ $gasto->clinic }}</td>
                            <td class="px-4 py-2 flex gap-2">
                                <button class="btn-editar bg-[#1c6c73] hover:bg-[#4298a7] text-white px-2 py-1 rounded"
                                    data-id="{{ $gasto->id }}" data-description="{{ $gasto->description }}"
                                    data-store="{{ $gasto->store }}" data-cat-id="{{ $gasto->cat_id }}"
                                    data-subcategory="{{ $gasto->subcategory }}" data-date="{{ $gasto->date }}"
                                    data-payment="{{ $gasto->payment_method_id }}" data-amount="{{ abs($gasto->amount) }}"
                                    data-clinic="{{ $gasto->clinic }}">
                                    Editar
                                </button>
                                <button
                                    class="btn-eliminar bg-red-600 text-white px-2 py-1 rounded hover:bg-red-800 text-xs"
                                    data-id="{{ $gasto->id }}">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </section>

    <!-- Modal de Crear Gasto -->
    <div id="agregarGastoModal" onclick="handleBackdrop(event, 'agregarGastoModal')"
        class="fixed inset-0 bg-black/40 hidden flex items-center justify-center z-50 backdrop-blur-sm">
        <div onclick="event.stopPropagation()"
            class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 relative border border-gray-100">
            <!-- Botón cerrar -->
            <button onclick="closeModal('agregarGastoModal')"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl">
                &times;
            </button>

            <!-- Título -->
            <h2 class="text-lg font-[Poppins] text-[#1C6C73] mb-5">Añadir Gasto</h2>

            <form method="post" action="{{ route('panel.gastos.guardar') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="created_by" value="{{ auth()->id() }}">

                <div class="space-y-4">
                    <!-- Descripción -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Descripción de la transacción</label>
                        <textarea name="description" rows="2"
                            class="w-full border border-gray-200 rounded-lg p-2 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none"></textarea>
                    </div>

                    <!-- Establecimiento -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Establecimiento</label>
                        <input type="text" name="store"
                            class="w-full border border-gray-200 rounded-lg p-2 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none"
                            required>
                    </div>

                    <!-- Fila 1 -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Categoría</label>
                            <select name="cat_id" id="cat_id"
                                class="w-full border border-gray-200 rounded-lg p-2 bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none"
                                required>
                                <option value="">Selecciona...</option>
                                @foreach ($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Subcategoría</label>
                            <select name="subcategory" id="subcategory"
                                class="w-full border border-gray-200 rounded-lg p-2 bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                                <option value="">Selecciona...</option>
                                @foreach ($subcategorias as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Fila 2 -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Fecha</label>
                            <input type="date" name="date"
                                class="w-full border border-gray-200 rounded-lg p-2 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Método de Pago</label>
                            <select name="payment_method_id"
                                class="w-full border border-gray-200 rounded-lg p-2 bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none"
                                required>
                                <option value="">Selecciona...</option>
                                <option value="1">Efectivo</option>
                                <option value="2">Tarjeta</option>
                                <option value="3">Transferencia</option>
                                <option value="4">Depósito</option>
                            </select>
                        </div>
                    </div>

                    <!-- Fila 3 -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Monto de la compra</label>
                            <input type="number" name="amount" step="0.1" required
                                class="w-full border border-gray-200 rounded-lg p-2 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Sucursal</label>
                            <select name="clinic"
                                class="w-full border border-gray-200 rounded-lg p-2 bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none"
                                required>
                                <option value="">Selecciona...</option>
                                <option value="Santafe">Santa Fe</option>
                                <option value="Pedregal">Pedregal</option>
                                <option value="Queretaro">Querétaro</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Botón -->
                <div class="mt-6">
                    <button type="submit"
                        class="w-full bg-[#1C6C73] text-white font-semibold py-2.5 rounded-lg hover:bg-[#1b5e61] transition-all">
                        Añadir gasto
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 🔹 Obtenemos subcategorías agrupadas por categoría desde Blade
            const subcategoriasPorCategoria = @json(
                $subcategorias->groupBy('category_id')->map(function ($items) {
                    return $items->map(fn($sub) => ['id' => $sub->id, 'name' => $sub->name]);
                }));

            const categoriaSelect = document.querySelector('select[name="cat_id"]');
            const subcategoriaSelect = document.querySelector('select[name="subcategory"]');

            if (categoriaSelect && subcategoriaSelect) {
                categoriaSelect.addEventListener('change', function() {
                    const catId = this.value;
                    subcategoriaSelect.innerHTML = '<option value="">Seleccione...</option>';

                    if (catId && subcategoriasPorCategoria[catId]) {
                        subcategoriasPorCategoria[catId].forEach(sub => {
                            const option = document.createElement('option');
                            option.value = sub.id;
                            option.textContent = sub.name;
                            subcategoriaSelect.appendChild(option);
                        });
                    }
                });
            }
        });
    </script>

    <!-- Modal Editar Gasto -->
    <div id="editarGastoModal" onclick="handleBackdrop(event, 'editarGastoModal')"
        class="fixed inset-0 bg-black/40 hidden flex items-center justify-center z-50 backdrop-blur-sm">
        <div onclick="event.stopPropagation()"
            class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 relative border border-gray-100">
            <!-- Cerrar -->
            <button onclick="closeModal('editarGastoModal')"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl">
                &times;
            </button>

            <!-- Título -->
            <h2 class="text-lg font-[Poppins] text-[#1C6C73] mb-5">Editar Gasto</h2>

            <!-- Formulario -->
            <form id="formEditarGasto" method="POST" action="">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_id">

                <div class="space-y-4">
                    <!-- Descripción -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Descripción de la transacción</label>
                        <textarea name="description" id="edit_description" rows="2"
                            class="w-full border border-gray-200 rounded-lg p-2 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none"></textarea>
                    </div>

                    <!-- Establecimiento -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Establecimiento</label>
                        <input type="text" name="store" id="edit_store"
                            class="w-full border border-gray-200 rounded-lg p-2 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                    </div>

                    <!-- Fila 1 -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Categoría</label>
                            <select name="cat_id" id="edit_cat_id"
                                class="w-full border border-gray-200 rounded-lg p-2 bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                                <option value="">Selecciona...</option>
                                @foreach ($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Subcategoría</label>
                            <select name="subcategory" id="edit_subcategory"
                                class="w-full border border-gray-200 rounded-lg p-2 bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                                <option value="">Selecciona...</option>
                                @foreach ($subcategorias as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Fila 2 -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Fecha</label>
                            <input type="date" name="date" id="edit_date"
                                class="w-full border border-gray-200 rounded-lg p-2 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Método de Pago</label>
                            <select name="payment_method_id" id="edit_payment_method_id"
                                class="w-full border border-gray-200 rounded-lg p-2 bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                                <option value="1">Efectivo</option>
                                <option value="2">Tarjeta</option>
                                <option value="3">Transferencia</option>
                                <option value="4">Depósito</option>
                            </select>
                        </div>
                    </div>

                    <!-- Fila 3 -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Monto de la compra</label>
                            <input type="number" step="0.1" name="amount" id="edit_amount"
                                class="w-full border border-gray-200 rounded-lg p-2 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Sucursal</label>
                            <select name="clinic" id="edit_clinic"
                                class="w-full border border-gray-200 rounded-lg p-2 bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                                <option value="">Selecciona...</option>
                                <option value="Santafe">Santa Fe</option>
                                <option value="Pedregal">Pedregal</option>
                                <option value="Queretaro">Querétaro</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Botón -->
                <div class="mt-6">
                    <button type="submit"
                        class="w-full bg-[#1C6C73] text-white font-semibold py-2.5 rounded-lg hover:bg-[#1b5e61] transition-all">
                        Actualizar gasto
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function handleBackdrop(event, modalId) {
            // Solo cerrar si el clic fue en el fondo
            if (event.target.id === modalId) {
                closeModal(modalId);
            }
        }
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 🔹 Subcategorías agrupadas por categoría
            const subcategoriasPorCategoria = @json(
                $subcategorias->groupBy('category_id')->map(function ($items) {
                    return $items->map(fn($sub) => ['id' => $sub->id, 'name' => $sub->name]);
                }));

            // 🔹 Al hacer clic en "Editar"
            $('#tablaGastos').on('click', '.btn-editar', function() {
                const btn = $(this);

                // Obtener los datos desde los atributos data-*
                const data = {
                    id: btn.data('id'),
                    description: btn.data('description'),
                    store: btn.data('store'),
                    cat_id: btn.data('cat-id'),
                    subcategory: btn.data('subcategory'),
                    date: btn.data('date'),
                    payment_method_id: btn.data('payment'),
                    amount: btn.data('amount'),
                    clinic: btn.data('clinic'),
                };

                // Llenar los campos del modal
                $('#edit_id').val(data.id);
                $('#edit_description').val(data.description);
                $('#edit_store').val(data.store);
                $('#edit_cat_id').val(data.cat_id);
                $('#edit_amount').val(data.amount);
                $('#edit_date').val(data.date);
                $('#edit_payment_method_id').val(data.payment_method_id);
                $('#edit_clinic').val(data.clinic);

                // 🔹 Actualizar subcategorías según la categoría seleccionada
                const subSelect = $('#edit_subcategory');
                subSelect.html('<option value="">Seleccione...</option>');

                if (data.cat_id && subcategoriasPorCategoria[data.cat_id]) {
                    subcategoriasPorCategoria[data.cat_id].forEach(sub => {
                        const selected = (sub.id == data.subcategory) ? 'selected' : '';
                        subSelect.append(
                            `<option value="${sub.id}" ${selected}>${sub.name}</option>`);
                    });
                }

                // Establecer acción del formulario
                $('#formEditarGasto').attr('action', `/panel/gastos/${data.id}`);

                // Mostrar el modal
                openModal('editarGastoModal');
            });

            // 🔹 Cuando cambia la categoría dentro del modal, actualizar subcategorías
            $('#edit_cat_id').on('change', function() {
                const catId = $(this).val();
                const subSelect = $('#edit_subcategory');
                subSelect.html('<option value="">Seleccione...</option>');

                if (catId && subcategoriasPorCategoria[catId]) {
                    subcategoriasPorCategoria[catId].forEach(sub => {
                        subSelect.append(`<option value="${sub.id}">${sub.name}</option>`);
                    });
                }
            });
        });
    </script>

    <script>
        //funciones de eliminar
        $(document).ready(function() {
            // Acción del botón eliminar
            $('#tablaGastos').on('click', '.btn-eliminar', function() {
                const id = $(this).data('id');

                if (confirm('¿Estás seguro de eliminar este gasto?')) {
                    $.ajax({
                        url: `/panel/gastos/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                location.reload(); // recarga la tabla
                            } else {
                                alert('Ocurrió un error al eliminar.');
                            }
                        },
                        error: function() {
                            alert('Error en la solicitud.');
                        }
                    });
                }
            });
        });
    </script>


    <!-- Inicia script de DataTable -->
    <script>
        $(document).ready(function() {

            // =========================
            // DATATABLE
            // =========================
            const table = $('#tablaGastos').DataTable({
                responsive: true,
                autoWidth: true,
                scrollX: true,
                processing: true,
                order: [
                    [4, 'desc']
                ],
                dom: 'Bfrtip',
                pageLength: 20,
                buttons: [{
                        extend: 'excel',
                        text: 'Excel',
                        className: 'bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg'
                    },
                    {
                        extend: 'pdf',
                        text: 'PDF',
                        className: 'bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg'
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

            // Para debug en consola
            window.table = table;

            // =========================
            // HELPERS
            // =========================
            function formatMXN(n) {
                return Number(n || 0).toLocaleString('es-MX', {
                    style: 'currency',
                    currency: 'MXN'
                });
            }

            function parseMoney(text) {
                const num = parseFloat(String(text || '').replace(/[^\d.-]/g, ''));
                return isNaN(num) ? 0 : num;
            }

            // quita HTML si DT te devuelve <span> etc
            function stripHtml(s) {
                return String(s || '').replace(/<[^>]*>/g, '').trim();
            }

            function totalFiltradoDT() {
                let total = 0;
                table.rows({
                    search: 'applied'
                }).every(function() {
                    const row = this.data();
                    total += parseMoney(stripHtml(row[3])); // Monto col 3
                });
                $('#totalGastos').text(formatMXN(total));
            }

            // =========================
            // RANGO FECHAS
            // =========================
            let rango = {
                start: moment().startOf('month'),
                end: moment().endOf('month')
            };

            // Soporta "DD/MM/YYYY - DD/MM/YYYY" y "DD/MM/YYYY a DD/MM/YYYY"
            function setRangoDesdeInput(rangeStr) {
                const raw = stripHtml(rangeStr);

                // normaliza " a " => " - "
                const normalized = raw.replace(/\s+a\s+/i, ' - ');

                const parts = normalized.split(' - ').map(s => s.trim());
                if (parts.length !== 2) {
                    rango = {
                        start: null,
                        end: null
                    };
                    return;
                }

                const start = moment(parts[0], 'DD/MM/YYYY', true);
                const end = moment(parts[1], 'DD/MM/YYYY', true);

                rango = (start.isValid() && end.isValid()) ?
                    {
                        start,
                        end
                    } :
                    {
                        start: null,
                        end: null
                    };
            }

            function clinicMatch(value, clinicSelected) {
                if (!clinicSelected || clinicSelected === 'Todas') return true;
                return stripHtml(value) === String(clinicSelected).trim();
            }

            // filtro global de DataTables
            $.fn.dataTable.ext.search.push(function(settings, data) {
                if (settings.nTable.id !== 'tablaGastos') return true;

                const fechaStr = stripHtml(data[4]); // Fecha col 4: "DD/MM/YYYY"
                const clinicTd = stripHtml(data[5]); // Sucursal col 5

                // clínica
                const clinicSelected = $('#clinicGastos').val();
                if (!clinicMatch(clinicTd, clinicSelected)) return false;

                // rango fechas
                if (!rango.start || !rango.end) return true;

                const fecha = moment(fechaStr, 'DD/MM/YYYY', true);
                if (!fecha.isValid()) return true;

                return fecha.isBetween(rango.start, rango.end, 'day', '[]');
            });

            function aplicarFiltros() {
                setRangoDesdeInput($('#rangeGastos').val());
                table.draw();
                totalFiltradoDT();
            }

            // =========================
            // FLATPICKR (CALENDARIO)
            // =========================
            const fp = flatpickr("#rangeGastos", {
                
                mode: "range",
                dateFormat: "d/m/Y",
                locale: "es",
                allowInput: true,
                rangeSeparator: " - ",
                onChange: function(selectedDates) {
                    if (selectedDates.length === 2) {
                        const s = moment(selectedDates[0]).format('DD/MM/YYYY');
                        const e = moment(selectedDates[1]).format('DD/MM/YYYY');

                        const params = new URLSearchParams(window.location.search);
                        params.set('start', s);
                        params.set('end', e);

                        window.location.search = params.toString();
                    }
                },
                onReady: function() {
                    // si el value venía con " a ", lo normalizamos para que el picker lo entienda visualmente
                    const v = $('#rangeGastos').val();
                    if (v && v.includes(' a ')) {
                        $('#rangeGastos').val(v.replace(/\s+a\s+/i, ' - '));
                    }
                    aplicarFiltros();
                }
            });

            // clínica cambia => refiltrar
            $('#clinicGastos').on('change', function() {
                table.draw();
                totalFiltradoDT();
            });

            // recalcula total cada redraw
            table.on('draw', totalFiltradoDT);

            // primera vez
            aplicarFiltros();
        });
    </script>

@endsection
