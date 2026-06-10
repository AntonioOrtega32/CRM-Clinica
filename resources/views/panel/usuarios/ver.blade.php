@extends('panel.layouts.panel')

@section('title', 'Ver Usuario')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="max-w-4xl mx-auto bg-white p-6 rounded-2xl shadow-md">

    <h2 class="text-2xl font-bold mb-6 border-b pb-2">Información del Usuario</h2>

    <!-- Información básica -->
    <div class="mb-6 space-y-2">
        <p><strong>Nombre:</strong> {{ $user->name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Rol:</strong> {{ $user->roles->pluck('name')->first() ?? '-' }}</p>
        <p><strong>Fecha de creación:</strong> {{ $user->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
        <p><strong>Última actualización:</strong> {{ $user->updated_at?->format('d/m/Y H:i') ?? '-' }}</p>
    </div>

    <!-- Historial de Movimientos -->
    <h3 class="text-xl font-semibold mb-2 border-b pb-1">Historial de Movimientos</h3>

    @if($user->movimientos->count())
    <div class="overflow-x-auto">
        <table id="movimientosTable" class="min-w-full divide-y divide-gray-200 display">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($user->movimientos as $mov)
                <tr>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $mov->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $mov->tipo_movimiento }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">{{ $mov->descripcion }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <p class="text-gray-500 mt-2">No hay movimientos registrados.</p>
    @endif

    <!-- Botón regresar -->
    <div class="mt-6">
        <a href="{{ route('panel.usuarios.index') }}"
           class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Regresar</a>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#movimientosTable').DataTable({
               responsive: true,
      scrollX: true,
      autoWidth: true,
      buttons: [
        { extend: 'excel', 
            text: 'Excel',
            className: 'bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg',
            exportOptions: {
              columns: [0,1,2]
            }  
        },
        { extend: 'pdf', 
            text: 'PDF', 
            className: 'bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg', 
            exportOptions: {
              columns: [0,1,2]
            }          
        }
      ],
      dom: 'Bfrtip',
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
@endsection
