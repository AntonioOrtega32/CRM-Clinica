@extends('panel.layouts.panel')

@section('title', 'Vacaciones')

@section('content')

<div x-data="holidayModal()" class="max-w-7xl mx-auto px-4 py-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-700">Vacaciones</h2>

        <a href="{{ route('panel.holidays.create') }}"
     class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow transition flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Agregar
        </a>
    </div>

    {{-- CARD --}}
    <div class="bg-white rounded-xl shadow-md p-6">

        <h3 class="text-xl font-semibold text-gray-600 mb-4">
            Lista de Vacaciones Registradas
        </h3>

        {{-- TABLA --}}
        <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-gray-100 text-left text-gray-600 text-sm uppercase tracking-wide">
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Empleado</th>
                        <th class="px-4 py-3">Inicio</th>
                        <th class="px-4 py-3">Fin</th>
                        <th class="px-4 py-3 text-center">Opciones</th>
                    </tr>
                </thead>

                <tbody class="text-gray-700">
                @foreach ($holidays as $h)

                    @php
                        $holidayData = [
                            'id' => $h->id,
                            'empleado' => trim(
                                (optional($h->employee)->nombre ?? '') . ' ' .
                                (optional($h->employee)->apellido ?? '')
                            ),
                            'inicio' => $h->start,
                            'fin' => $h->end,
                        ];
                    @endphp

                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-semibold text-gray-800">{{ $h->id }}</td>

                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 bg-indigo-500 rounded-full"></span>
                                <span class="font-medium">
                                    {{ optional($h->employee)->nombre ?? 'Sin empleado' }}
                                    {{ optional($h->employee)->apellido }}
                                </span>
                            </div>
                        </td>

                        <td class="px-4 py-3">{{ $h->start }}</td>
                        <td class="px-4 py-3">{{ $h->end }}</td>

                        <td class="px-4 py-3 text-center flex items-center justify-center gap-2">

                            {{-- VER --}}
                            <button
                                type="button"
                                class="text-indigo-600 hover:text-indigo-800 p-2 rounded-lg hover:bg-indigo-50"
                                title="Ver Vacación"
                                data-holiday='@json($holidayData)'
                                @click="openFromDataset($event)"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5
                                             c4.478 0 8.268 2.943 9.542 7
                                             -1.274 4.057-5.064 7-9.542 7
                                             -4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>

                            {{-- ELIMINAR --}}
                            <form action="{{ route('panel.holidays.destroy', $h) }}"
                                  method="POST"
                                  class="delete-form">
                                @csrf
                                @method('DELETE')

                                <button type="button"
                                        class="delete-btn text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50"
                                        title="Eliminar">
                                    🗑
                                </button>
                            </form>

                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="mt-5">
            {{ $holidays->links() }}
        </div>
    </div>

    {{-- MODAL --}}
    <div x-show="show" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

        <div @click.away="show=false"
             class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">

            <h3 class="text-xl font-bold text-gray-700 mb-4">
                Detalle de Vacación
            </h3>

            <div class="space-y-2 text-gray-600">
                <p><strong>ID:</strong> <span x-text="holiday.id"></span></p>
                <p><strong>Empleado:</strong> <span x-text="holiday.empleado"></span></p>
                <p><strong>Inicio:</strong> <span x-text="holiday.inicio"></span></p>
                <p><strong>Fin:</strong> <span x-text="holiday.fin"></span></p>
            </div>

            <div class="mt-6 text-right">
                <button @click="show=false"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

</div> {{-- cierra x-data --}}

{{-- Alpine --}}
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

{{-- SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function holidayModal() {
    return {
        show: false,
        holiday: {},

        openFromDataset(event) {
            this.holiday = JSON.parse(event.currentTarget.dataset.holiday);
            this.show = true;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const form = this.closest('form');
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¡No podrás revertir esto!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then(r => r.isConfirmed && form.submit());
        });
    });
});
</script>

@endsection
