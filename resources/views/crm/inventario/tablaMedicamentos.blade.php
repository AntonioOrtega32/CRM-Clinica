<h2 class="text-2xl text-center font-[Poppins] mb-4">Inventario de Medicamentos</h2>
<br>
@if ($medicamentos->isEmpty())
<p>No hay medicamentos disponibles.</p>
@else
<table id="TablaMedicamentos" style="width:100%" class="table table-striped table-bordered display nowrap">
    <thead class="bg-gray-dark color-palette text-white">
        <tr style="background-color: #4298a7">
            <th class="py-3 px-4 border-b font-semibold text-left">ID</th>
            <th class="py-3 px-4 border-b font-semibold text-left">Nombre</th>
            <th class="py-3 px-4 border-b font-semibold text-left">Stock</th>
            <th class="py-3 px-4 border-b font-semibold text-left">Caduca</th>
        </tr>
    </thead>
         <tbody>
    @foreach ($medicamentos as $med)
        <tr>
            <td>{{ $med->id }}</td>
            <td>{{ $med->name }}</td>
            <td>{{ $med->stock }} Piezas</td>
            @if ($med->expiry_date == null)
            <td> No aplica </td>
            @else
            <td>{{ $med->expiry_date }}</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<!--- Inicia script de DataTable --->

<script>
    $(document).ready(function() {
        $('#TablaMedicamentos').DataTable({
            responsive: true,
            scrollX: true,
            autoWidth: false,
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
