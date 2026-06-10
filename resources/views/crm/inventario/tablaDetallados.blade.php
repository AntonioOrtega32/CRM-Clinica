<h2 class="text-2xl text-center font-[Poppins] mb-4">Movimientos de inventario</h2>
<br>
@if ($movimientos->isEmpty())
    <p>No hay movimientos disponibles.</p>
@else
<table id="TablaDetallados" style="width:100%" class="table table-striped table-bordered display nowrap">
    <thead class="bg-gray-dark color-palette text-white">
        <tr style="background-color: #4298a7">
            <th>Id</th>
            <th>Nombre</th>
            <th>Tipo de Movimiento</th>
            <th>Cantidad</th>
            <th>Fecha de Movimiento</th>
            <th>Entregado  a: </th>
            <th>Ubicacion</th>
        </tr>
    </thead>
        <tbody>
    @foreach ($movimientos as $movi)
        <tr>
            <td>{{ $movi->id }}</td>
            <td>{{ $movi->nombreProducto }}</td>
            <td>{{ $movi->movement_type }}</td>
            <td>{{ $movi->quantity }} Piezas</td>
            <td>{{ $movi->movement_date }}</td>
            @if ($movi->received_by==NULL)
            <td>No aplica</td>
            @else
            <td>{{ $movi->received_by }}</td>
            @endif
            <td>{{ $movi->location }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

@endif
<!--- Inicia script de DataTable --->

<script>
    $(document).ready(function() {
        $('#TablaDetallados').DataTable({
            responsive: true,
            scrollX: true,
            autoWidth: false,
            order: [
                    [0, 'desc']
                ],
            buttons: [
                {
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
            dom: 'Bfrtip',
            // Otras opciones de configuración si es necesario
            //idioma
            language: {
                info:           "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty:      "Mostrando 0 a 0 de 0 registros",
                lengthMenu:     "Mostrar _MENU_ registros",
                search:         "Buscar:",
                loadingRecords: "Loading...",
                processing:     "Procesando...",
                zeroRecords:    "No hay registros aún",
                paginate: {
                    // previous: "Anterior",
                    // next: "Siguiente"
                    next: '→',
                    previous: '←',
                    first:'Inicio',
                    last:'Ultimo'
                },
            },
        });
    });
</script>
