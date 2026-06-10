@extends('panel.layouts.panel')

@section('title', 'Nómina')

@section('content')
    <div x-data="nominaTable()" x-init="init()" class="bg-white rounded-xl shadow p-6">

        {{-- BOTONES --}}
        <div class="flex flex-wrap gap-2 mb-4">
            <button @click="addRow" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                Agregar fila
            </button>

            <button @click="deleteSelected" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                Eliminar fila
            </button>

            <button @click="exportTxt" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg">
                Guardar y generar Layout
            </button>

            {{-- CLÍNICA --}}
            <div class="relative">
                <button @click="openClinic = !openClinic"
                    class="bg-indigo-500 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <span x-text="clinic || 'Clínica'"></span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openClinic" @click.outside="openClinic = false" x-transition
                    class="absolute mt-2 bg-white border rounded-lg shadow w-40 z-10">

                    <button @click="setClinic('Santa Fe')" class="block w-full text-left px-4 py-2 hover:bg-gray-100">
                        Santa Fe
                    </button>

                    <button @click="setClinic('Queretaro')" class="block w-full text-left px-4 py-2 hover:bg-gray-100">
                        Querétaro
                    </button>
                </div>
            </div>
        </div>

        {{-- TABLA --}}
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 rounded-lg">
                <thead class="bg-gray-100">
                    <tr class="text-left text-sm text-gray-600">
                        <th class="p-3">
                            <input type="checkbox" @change="toggleAll($event)">
                        </th>
                        <th class="p-3">ID</th>
                        <th class="p-3">Cuenta</th>
                        <th class="p-3">Importe</th>
                        <th class="p-3">Nombre</th>
                        <th class="p-3">Clínica</th>
                    </tr>
                </thead>

                <tbody>
                    <template x-for="row in rows" :key="row._uid">
                        <tr class="border-t text-sm hover:bg-gray-50">
                            <td class="p-3">
                                <input type="checkbox" x-model="row.selected">
                            </td>
                            <td class="p-3" x-text="row.num_progresivo"></td>

                            <td class="p-3">
                                <input x-model="row.cuenta" class="w-full border rounded px-2 py-1">


                            </td>

                            <td class="p-3">
                             <input x-model="row.importe" class="w-full border rounded px-2 py-1">

                            </td>

                            <td class="p-3">
                            <input x-model="row.nombre"  class="w-full border rounded px-2 py-1">

                            </td>

                            <td class="p-3" x-text="row.clinic"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
function nominaTable() {
    return {
        rows: [],
        clinic: null,
        openClinic: false,
        saving: false,

        init() {
            this.clinic = localStorage.getItem('clinica');
            if (this.clinic) {
                this.loadData();
            }
        },

        async loadData() {
            const response = await fetch(
                `{{ route('panel.nomina.data') }}?clinic=${this.clinic}`
            );
            const json = await response.json();

            this.rows = json.data.map((row, index) => ({
                id: row.id,
                num_progresivo: row.num_progresivo,
                cuenta: row.cuenta,
                importe: row.importe,
                nombre: row.nombre,
                clinic: row.clinic,
                selected: false,
                _uid: index + '_' + Math.random()
            }));
        },

        setClinic(value) {
            this.clinic = value;
            localStorage.setItem('clinica', value);
            this.openClinic = false;
            this.loadData();
        },

        toggleAll(e) {
            this.rows.forEach(r => r.selected = e.target.checked);
        },

        addRow() {
            this.rows.push({
                id: null,
                num_progresivo: this.rows.length + 1,
                cuenta: '',
                importe: '',
                nombre: '',
                clinic: this.clinic,
                selected: false,
                _uid: Date.now()
            });
        },

        async saveRows() {
            if (!this.clinic) {
                Swal.fire('Aviso', 'Selecciona una clínica', 'warning');
                return;
            }

            this.saving = true;

            const res = await fetch("{{ route('panel.nomina.save') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    clinic: this.clinic,
                    rows: this.rows
                })
            });

            const result = await res.json();
            this.saving = false;

            if (result.success) {
                Swal.fire('Guardado', 'La nómina se guardó correctamente', 'success');
                this.loadData(); // sincroniza IDs
            } else {
                Swal.fire('Error', 'No se pudo guardar', 'error');
            }
        },

        async deleteSelected() {
            const ids = this.rows
                .filter(r => r.selected && r.id)
                .map(r => r.id);

            if (!ids.length) {
                Swal.fire('Aviso', 'Selecciona al menos un registro', 'warning');
                return;
            }

            await fetch("{{ route('panel.nomina.delete') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids })
            });

            this.rows = this.rows.filter(r => !r.selected);

            Swal.fire('Correcto', 'Registros eliminados', 'success');
        },

        async exportTxt() {
            await this.saveRows();

            let content = this.rows.map(r =>
                `${r.num_progresivo}/${r.cuenta}/${r.importe}/${r.nombre}/${r.clinic}`
            ).join("\n");

            await fetch("{{ route('panel.nomina.layout') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    content,
                    clinic: this.clinic
                })
            });

            window.open("{{ route('panel.nomina.layout.download') }}");
        }
    }
}
</script>



@endsection
