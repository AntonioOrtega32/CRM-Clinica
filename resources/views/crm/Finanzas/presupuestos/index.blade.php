@extends('panel.layouts.panel')

@section('title', 'Presupuestos')

@section('content')
    <div class="p-6" x-data="budgetDashboard()">



        {{-- CONTENEDOR BLANCO --}}
        <div class="bg-white rounded-2xl shadow-lg p-8">

            {{-- HEADER --}}
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6 mb-8">

                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        Configuración de Presupuesto
                    </h1>
                    <p class="text-gray-500">
                        {{ $monthLabel }}
                    </p>

                    <div class="flex gap-3 mt-3">
                        <span class="px-4 py-1.5 rounded-full bg-emerald-100 text-emerald-700 font-semibold text-sm">
                            Presupuestado: ${{ number_format($totalBudget, 2) }}
                        </span>
                        <span class="px-4 py-1.5 rounded-full bg-rose-100 text-rose-700 font-semibold text-sm">
                            Gastado: ${{ number_format($totalExpensed, 2) }}
                        </span>
                    </div>
                </div>

                {{-- FILTROS --}}
                <div class="flex flex-wrap gap-3 items-end">

                    {{-- CLÍNICA --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Clínica</label>
                        <select x-model="filters.clinic" @change="applyFilters"
                            class="mt-1 block w-40 rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Todas</option>
                            <option value="Santafe">Santa Fe</option>
                            <option value="Pedregal">Pedregal</option>
                            <option value="CDMX">CDMX</option>
                            <option value="Queretaro">Querétaro</option>
                        </select>
                    </div>

                    {{-- MES --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Mes</label>
                        <select x-model="filters.month" @change="applyFilters"
                            class="mt-1 block w-36 rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500">
                            <option value="01">Enero</option>
                            <option value="02">Febrero</option>
                            <option value="03">Marzo</option>
                            <option value="04">Abril</option>
                            <option value="05">Mayo</option>
                            <option value="06">Junio</option>
                            <option value="07">Julio</option>
                            <option value="08">Agosto</option>
                            <option value="09">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                    </div>

                    {{-- BOTONES --}}
                    <div class="flex gap-2">
                        <button type="button" @click="openCategoryModal"
                            class="px-4 py-2 rounded-lg bg-purple-600 text-white">
                            + Categoría
                        </button>

                        <button type="button" @click="openSubcategoryModal"
                            class="px-4 py-2 rounded-lg bg-emerald-600 text-white">
                            + Subcategoría
                        </button>

                    </div>

                </div>
            </div>
            {{-- GRID --}}
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

                @foreach ($categories as $category)
                    @php
                        $asignado = $category->amount;
                        $gastado = $category->subcategories_total ?? 0;
                        $disponible = $asignado - $gastado;
                        $percent = $asignado > 0 ? min(100, ($gastado / $asignado) * 100) : 0;
                    @endphp

                    <div class="border rounded-2xl p-6 hover:shadow-md transition">

                        {{-- HEADER --}}
                        <div class="flex justify-between mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">
                                    {{ $category->name }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    Disponible:
                                    <span class="font-semibold">
                                        ${{ number_format($disponible, 2) }}
                                    </span>
                                </p>
                                <button @click="openEditCategoryModal(@js($category))"
                                    class="text-sm text-blue-600 hover:text-blue-800">
                                    Editar
                                </button>

                            </div>

                            <div class="text-right text-sm">
                                <p class="text-emerald-600 font-semibold">
                                    Asignado: ${{ number_format($asignado, 2) }}
                                </p>
                                <p class="text-rose-600 font-semibold">
                                    Gastado: ${{ number_format($gastado, 2) }}
                                </p>
                            </div>
                        </div>

                        {{-- PROGRESS --}}
                        <div class="mb-4">
                            <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full transition-all duration-700
                            {{ $percent < 70 ? 'bg-emerald-500' : ($percent < 90 ? 'bg-yellow-400' : 'bg-rose-500') }}"
                                    style="width: {{ $percent }}%">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ number_format($percent, 1) }}% utilizado
                            </p>
                        </div>

                        {{-- SUBCATEGORÍAS --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach ($category->subcategories ?? [] as $sub)
                                <div class="border rounded-xl p-4 bg-gray-50 hover:bg-white transition">
                                    <div class="flex justify-between items-center">
                                        <p class="font-semibold text-gray-700">
                                            {{ $sub->name }}
                                        </p>
                                        <div class="flex items-center gap-2">
                                            <span class="text-rose-600 font-bold">
                                                ${{ number_format($sub->total_expensed, 2) }}
                                            </span>
                                            <button @click="openEditSubcategoryModal(@js($sub))"
                                                class="text-xs text-blue-600 hover:text-blue-800">
                                                Editar
                                            </button>

                                        </div>
                                    </div>
                                    @if ($sub->description)
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $sub->description }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                    </div>
                @endforeach

            </div>
        </div>



        {{-- MODAL CATEGORÍA --}}
        <div x-show="showCategoryModal" x-cloak x-transition
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

            <div class="bg-white rounded-xl p-6 w-full max-w-md" @click.outside="showCategoryModal = false">

                <h3 class="text-xl font-bold mb-4">Nueva Categoría</h3>

                <form @submit.prevent="submitCategory" class="space-y-4">
                    @csrf

                    <input type="text" name="name"
                        class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Nombre" required>
                    <input type="number" name="amount" step="0.01"
                        class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Monto" required>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showCategoryModal = false" class="px-4 py-2 border rounded-lg">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL EDITAR CATEGORÍA --}}
        <div x-show="showEditCategoryModal" x-cloak x-transition
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

            <div class="bg-white rounded-xl p-6 w-full max-w-md" @click.outside="showEditCategoryModal = false">

                <h3 class="text-xl font-bold mb-4">Editar Categoría</h3>

                <form @submit.prevent="submitEditCategory" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <input type="text" name="name" x-model="editCategory.name"
                        class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500" required>
                    <input type="number" name="amount" x-model="editCategory.amount" step="0.01"
                        class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500" required>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showEditCategoryModal = false"
                            class="px-4 py-2 border rounded-lg">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL SUBCATEGORÍA --}}
        {{-- MODAL SUBCATEGORÍA --}}
        <div x-show="showSubcategoryModal" x-cloak x-transition
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

            <div class="bg-white rounded-xl p-6 w-full max-w-md" @click.outside="showSubcategoryModal = false">

                <h3 class="text-xl font-bold mb-4">
                    Agregar Subcategoría
                </h3>
<form @submit.prevent="submitSubcategory" class="space-y-4">


                    @csrf

                    {{-- CATEGORÍA --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Categoría
                        </label>
                        <select name="category_id"
                            class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500"
                            required>
                            <option value="">Selecciona una categoría</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- NOMBRE --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Nombre de la subcategoría
                        </label>
                        <input type="text" name="name"
                            class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500"
                            required>
                    </div>

                    {{-- DESCRIPCIÓN --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Descripción
                        </label>
                        <textarea name="description" rows="3"
                            class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>

                    {{-- MONTO PRESUPUESTADO --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Monto presupuestado
                        </label>
                        <input type="number" name="amount" step="0.01"
                            class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    {{-- MONTO ACTUAL --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Monto actual
                        </label>
                        <input type="number" name="current" step="0.01" value="0"
                            class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    {{-- BOTONES --}}
                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button" @click="showSubcategoryModal = false"
                            class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-100">
                            Cancelar
                        </button>

                        <button type="submit"
                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                            Guardar
                        </button>
                    </div>

                </form>
            </div>
        </div>

        {{-- MODAL EDITAR SUBCATEGORÍA --}}
        <div x-show="showEditSubcategoryModal" x-cloak x-transition
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

            <div class="bg-white rounded-xl p-6 w-full max-w-md" @click.outside="showEditSubcategoryModal = false">

                <h3 class="text-xl font-bold mb-4">
                    Editar Subcategoría
                </h3>
<form @submit.prevent="submitEditSubcategory" class="space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- CATEGORÍA --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Categoría
                        </label>
                        <select name="category_id" x-model="editSubcategory.category_id" :value="editSubcategory.category_id"
                            class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500"
                            required>
                            <option value="">Selecciona una categoría</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- NOMBRE --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Nombre de la subcategoría
                        </label>
                        <input type="text" name="name" x-model="editSubcategory.name" :value="editSubcategory.name"
                            class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500"
                            required>
                    </div>

                    {{-- DESCRIPCIÓN --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Descripción
                        </label>
                        <textarea name="description" x-model="editSubcategory.description" :value="editSubcategory.description" rows="3"
                            class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    </div>

                    {{-- MONTO PRESUPUESTADO --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Monto presupuestado
                        </label>
                        <input type="number" name="amount" x-model="editSubcategory.amount" step="0.01"
                            class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    {{-- MONTO ACTUAL --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Monto actual
                        </label>
                        <input type="number" name="current" x-model="editSubcategory.current" :value="editSubcategory.current" step="0.01"
                            class="w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    {{-- BOTONES --}}
                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button" @click="showEditSubcategoryModal = false"
                            class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-100">
                            Cancelar
                        </button>

                        <button type="submit"
                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                            Guardar
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('budgetDashboard', () => ({

                /* =========================
                   ESTADO
                ========================= */
                showCategoryModal: false,
                showSubcategoryModal: false,
                showEditCategoryModal: false,
                showEditSubcategoryModal: false,
                loading: false,
                editCategory: {},
                editSubcategory: {},

                /* =========================
                   FILTROS
                ========================= */
                filters: {
                    clinic: @js($selectedClinic ?? ''),
                    month: @js($selectedMonth ?? now()->format('m')),
                },

                applyFilters() {
                    const params = new URLSearchParams();
                    if (this.filters.clinic) params.set('clinic', this.filters.clinic);
                    if (this.filters.month) params.set('month', this.filters.month);

                    const url = window.location.pathname;
                    window.location.href = params.toString() ?
                        `${url}?${params}` :
                        url;
                },

                /* =========================
                   MODALES
                ========================= */
                openCategoryModal() {
                    console.log('✅ Modal categoría');
                    this.showCategoryModal = true;
                },

                openSubcategoryModal() {
                    console.log('✅ Modal subcategoría');
                    this.showSubcategoryModal = true;
                },

          openEditCategoryModal(category) {
    console.log('EDIT CATEGORY RAW:', category);
    this.editCategory = { ...category };
    this.showEditCategoryModal = true;
},


                openEditSubcategoryModal(sub) {
                    this.editSubcategory = {
                        ...sub
                    };
                    this.showEditSubcategoryModal = true;
                },

                closeModals() {
                    this.showCategoryModal = false;
                    this.showSubcategoryModal = false;
                    this.showEditCategoryModal = false;
                    this.showEditSubcategoryModal = false;
                },

                submitSubcategory(event) {
                    event.preventDefault();
                    const form = event.target;
                    const formData = new FormData(form);
                    fetch('/panel/finanzas/subcategorias', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(async response => {
                        if (!response.ok) {
                            const text = await response.text();
                            console.error('❌ Respuesta no JSON:', text);
                            throw new Error('Respuesta inválida');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            this.showSubcategoryModal = false;
                            Swal.fire('Éxito', 'Subcategoría creada correctamente', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', 'Error al guardar', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Error al guardar', 'error');
                    });
                },

                submitCategory(event) {
                    event.preventDefault();
                    const form = event.target;
                    const formData = new FormData(form);
                    fetch('/panel/finanzas/categorias', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(async response => {
                        if (!response.ok) {
                            const text = await response.text();
                            console.error('❌ Respuesta no JSON:', text);
                            throw new Error('Respuesta inválida');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            this.showCategoryModal = false;
                            Swal.fire('Éxito', 'Categoría creada correctamente', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', 'Error al guardar', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Error al guardar', 'error');
                    });
                },

                submitEditCategory(event) {
                    event.preventDefault();

                    const form = event.target;
                    const formData = new FormData(form);

                    fetch(`/panel/finanzas/categorias/${this.editCategory.id}`, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document
                                    .querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),

                                // 🔥 ESTOS DOS SON CLAVE
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(async response => {
                            // ❌ Si Laravel responde HTML (405, 419, validación, etc)
                            if (!response.ok) {
                                const text = await response.text();
                                console.error('❌ Respuesta no JSON:', text);
                                throw new Error('Respuesta inválida');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.status === 'success') {
                                this.showEditCategoryModal = false;

                                Swal.fire(
                                    'Éxito',
                                    'Categoría actualizada correctamente',
                                    'success'
                                ).then(() => location.reload());

                            } else {
                                Swal.fire('Error', 'Error al guardar', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error', 'Error al guardar', 'error');
                        });
                },

                submitEditSubcategory(event) {
                    event.preventDefault();
                    const form = event.target;
                    const formData = new FormData(form);
                    console.log('FormData entries:', Array.from(formData.entries()));
                    fetch(`/panel/finanzas/subcategorias/${this.editSubcategory.id}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(async response => {
                        if (!response.ok) {
                            const text = await response.text();
                            console.error('❌ Respuesta no JSON:', text);
                            throw new Error('Respuesta inválida');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            this.showEditSubcategoryModal = false;
                            Swal.fire('Éxito', 'Subcategoría actualizada correctamente', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', 'Error al guardar', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Error al guardar', 'error');
                    });
                }


            }));
        });
    </script>

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endsection
