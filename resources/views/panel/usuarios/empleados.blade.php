@extends('panel.layouts.panel')

@section('title', 'Empleados')

@section('content')
<div
    x-data="{
        editModal: null,
        deleteModal: null,
        viewModal: null,
        fotoPreview: '',
        empleados: {{ $empleados->toJson() }},
        empleadoActual: {}
    }"
    class="p-6 bg-white shadow rounded-lg"
>

    {{-- Encabezado --}}
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-[Poppins] text-[#1C6C73] text-center">Empleados</h1>
        <button @click="editModal = 'create'; empleadoActual={}; fotoPreview=''" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
            Agregar Empleado
        </button>
    </div>

    {{-- Tabla de empleados --}}
    <div class="overflow-x-auto">
<table id="empleadosTable" class="display nowrap min-w-full" x-ignore>
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Foto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Apellido</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Puesto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario CRM</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($empleados as $empleado)
                <tr>
                    <td class="px-6 py-4">
                        @if($empleado->foto)
                            <img src="{{ asset($empleado->foto) }}" class="w-10 h-10 rounded-full">
                        @else
                            <span class="text-gray-400">Sin foto</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">{{ $empleado->nombre }}</td>
                    <td class="px-6 py-4">{{ $empleado->apellido }}</td>
                    <td class="px-6 py-4">{{ $empleado->puesto }}</td>
                    <td class="px-6 py-4">{{ $empleado->user ? $empleado->user->name : 'No asignado' }}</td>
                    <td class="px-6 py-4 flex gap-2">
    <button type="button"
        data-action="edit"
        data-id="{{ $empleado->id }}"
        class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
        Editar
    </button>

    <button type="button"
        data-action="delete"
        data-id="{{ $empleado->id }}"
        class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">
        Eliminar
    </button>

    <button type="button"
        data-action="view"
        data-id="{{ $empleado->id }}"
        class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600">
        Ver
    </button>
</td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- MODALES SEPARADOS --}}
    @include('panel.usuarios.modales.edit')   {{-- Editar / Crear --}}
    @include('panel.usuarios.modales.view')   {{-- Ver --}}
    @include('panel.usuarios.modales.delete') {{-- Eliminar --}}

</div>


<style>
.dataTables_filter input {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.4rem 0.6rem;
    outline: none;
}
.dataTables_filter label {
    font-weight: 500;
}
.dataTables_paginate a {
    padding: 0.25rem 0.5rem;
    margin: 0 2px;
    border-radius: 0.375rem;
}
.dataTables_paginate .current {
    background-color: #2563eb;
    color: white !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

  // Inicializa DataTable (una sola vez)
  if ($.fn.DataTable.isDataTable('#empleadosTable')) {
    $('#empleadosTable').DataTable().destroy();
  }

  const dt = $('#empleadosTable').DataTable({
    responsive: true,
    paging: true,
    searching: true,
    info: true,
    pageLength: 10,
    lengthChange: false,
    autoWidth: false,
    order: [[1, 'asc']],
    columnDefs: [{ orderable: false, targets: [0, 5] }],
    dom: '<"flex justify-between items-center mb-4"f>rt<"flex justify-between items-center mt-4"ip>',
    language: {
      search: "",
      searchPlaceholder: "Buscar empleado...",
      info: "Mostrando _START_ a _END_ de _TOTAL_ empleados",
      zeroRecords: "No se encontraron empleados",
      paginate: { previous: "Anterior", next: "Siguiente" }
    }
  });

  // ✅ Delegación: funciona aunque DataTables re-renderice filas
  document.querySelector('#empleadosTable').addEventListener('click', function (e) {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;

    const action = btn.dataset.action;
    const id = btn.dataset.id;

    // Obtener el componente Alpine raíz (el div que tiene x-data)
    const root = btn.closest('[x-data]');
    const x = Alpine.$data(root);

    // Buscar empleado del JSON ya cargado
    const emp = x.empleados.find(e => String(e.id) === String(id));
    x.empleadoActual = emp || {};

    // preview (si existe)
    x.fotoPreview = (emp && emp.foto) ? ('{{ asset('') }}' + emp.foto) : '';

    if (action === 'edit')  x.editModal = String(id);
    if (action === 'delete') x.deleteModal = String(id);
    if (action === 'view')  x.viewModal = String(id);
  });

});
</script>
@endsection
