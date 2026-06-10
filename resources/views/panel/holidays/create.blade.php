@extends('panel.layouts.panel')

@section('content')

<div class="max-w-3xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-700">Registrar Vacaciones</h2>

        <a href="{{ route('panel.holidays.index') }}"
           class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium px-4 py-2 rounded-lg shadow transition">
            ← Volver
        </a>
    </div>

    <div class="bg-white shadow-md rounded-xl p-6">

        <form action="{{ route('panel.holidays.store') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Empleado --}}
            <div>
                <label class="block text-gray-600 font-medium mb-1">Empleado:</label>
                <select name="employee" required
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">

                    @foreach($employees as $e)
                        <option value="{{ $e->id }}">
                            {{ $e->nombre }} {{ $e->apellido }}
                        </option>
                    @endforeach

                </select>
            </div>

            {{-- Inicio --}}
            <div>
                <label class="block text-gray-600 font-medium mb-1">Inicio:</label>
                <input type="date" name="start" required
                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            {{-- Fin --}}
            <div>
                <label class="block text-gray-600 font-medium mb-1">Fin:</label>
                <input type="date" name="end" required
                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            {{-- Notas --}}
            <div>
                <label class="block text-gray-600 font-medium mb-1">Notas:</label>
                <textarea name="notes" rows="3"
                          class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>

            {{-- Aprobado por --}}
            <div>
                <label class="block text-gray-600 font-medium mb-1">Aprobado por:</label>
                <input type="text" name="approved_by"
                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            {{-- Guardar --}}
            <div class="pt-3">
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2 rounded-lg shadow transition">
                    Guardar
                </button>
            </div>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: '¡Éxito!',
    text: '{{ session('success') }}',
    confirmButtonText: 'Aceptar'
}).then(() => {
    window.location.href = "{{ route('panel.holidays.index') }}";
});
</script>
@endif

@if(session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: '{{ session('error') }}',
    confirmButtonText: 'Aceptar'
});
</script>
@endif

@endsection
