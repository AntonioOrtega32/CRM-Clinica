@extends('panel.layouts.panel')

@section('title', 'Gestión de Ingresos/Transacciones')

@section('content')
<section class="py-10 px-6 bg-gray-50 min-h-screen">
    <h1 class="text-3xl font-[Poppins] text-center text-[#1C6C73] mb-8">
        Administración de Ingresos / Transacciones
        <span class="text-sm text-gray-500"></span>
    </h1>

    {{-- FILTROS --}}
    <div class="bg-white p-6 rounded-xl shadow-md mb-8 border border-gray-100">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            {{-- Fecha inicio --}}
            <div>
                <label for="fechaInicio" class="text-gray-700 text-sm font-semibold">Fecha inicio</label>
                <input type="date" id="fechaInicio" class="w-full border-gray-300 rounded-lg">
            </div>

            {{-- Fecha fin --}}
            <div>
                <label for="fechaFin" class="text-gray-700 text-sm font-semibold">Fecha fin</label>
                <input type="date" id="fechaFin" class="w-full border-gray-300 rounded-lg">
            </div>

            {{-- Movimiento --}}
            <div>
                <label for="movimiento" class="text-gray-700 text-sm font-semibold">Movimiento</label>
                <select id="movimiento" class="w-full border-gray-300 rounded-lg">
                    <option value="Ambos">Ambos</option>
                    <option value="Ingreso">Ingreso</option>
                    <option value="Egreso">Egreso</option>
                </select>
            </div>

            {{-- Clínica --}}
            <div>
                <label for="clinic" class="text-gray-700 text-sm font-semibold">Clínica</label>
                <select id="clinic" class="w-full border-gray-300 rounded-lg">
                    <option value="Ambas">Ambas</option>
                    <option value="Santa Fe">Santa Fe</option>
                    <option value="Pedregal">Pedregal</option>
                    <option value="Queretaro">Queretaro</option>
                </select>
            </div>

            {{-- Método --}}
            <div>
                <label for="method" class="text-gray-700 text-sm font-semibold">Método de Pago</label>
                <select id="method" class="w-full border-gray-300 rounded-lg">
                    <option value="Ambos">Ambos</option>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta">Tarjeta</option>
                    <option value="Transferencia">Transferencia</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>

            {{-- Tipo --}}
            <div>
                <label for="product_type" class="text-gray-700 text-sm font-semibold">Tipo de Producto</label>
                <select id="product_type" class="w-full border-gray-300 rounded-lg">
                    <option value="Todos">Todos</option>
                    <option value="tratamiento">Tratamiento</option>
                    <option value="producto">Producto</option>
                    <option value="anticipo">Anticipo</option>
                    <option value="liquidacion">Liquidación</option>
                </select>
            </div>
        </div>

        {{-- Botón --}}
        <div class="flex justify-end mt-5">
            <button id="btnFiltrar"
                class="bg-[#1C6C73] hover:bg-[#15595F] text-white font-semibold py-2 px-6 rounded-lg transition-all duration-200">
                Aplicar filtros
            </button>
        </div>
    </div>

    {{-- RESUMEN --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 text-center">
        <div class="bg-green-50 text-green-800 p-5 rounded-lg shadow">
            <h3 class="text-lg font-[Poppins]">Total Ingresos</h3>
            <p id="totalIngresos" class="text-2xl font-[Cinzel] mt-2">$0.00</p>
        </div>
        <div class="bg-red-50 text-red-800 p-5 rounded-lg shadow">
            <h3 class="text-lg font-[Poppins]">Total Egresos</h3>
            <p id="totalEgresos" class="text-2xl font-[Cinzel] mt-2">$0.00</p>
        </div>
        <div class="bg-blue-50 text-blue-800 p-5 rounded-lg shadow">
            <h3 class="text-lg font-[Poppins]">Balance</h3>
            <p id="totalBalance" class="text-3xl font-[Cinzel] mt-2">$0.00</p>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="bg-white p-4 rounded-xl shadow-lg overflow-hidden">
        <table id="tablaIngresos" class="w-full table table-striped table-bordered nowrap text-sm" width="100%">
            <thead class="bg-[#1C6C73] text-white">
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Nombre</th>
                    <th>Concepto</th>
                    <th>Movimiento</th>
                    <th>Tipo</th>
                    <th>Importe</th>
                    <th>Método</th>
                    <th>Sucursal</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</section>

{{-- Script --}}
<script>
$(document).ready(function () {
    let tabla = $('#tablaIngresos').DataTable({
        responsive: true,
        scrollX: true,
        autoWidth: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: 'Exportar a Excel',
                className: 'bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg'
            },
            {
                extend: 'pdf',
                text: 'Exportar a PDF',
                className: 'bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg'
            }
        ],
        language: {
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            lengthMenu: "Mostrar _MENU_ registros",
            search: "Buscar:",
            zeroRecords: "No hay registros aún",
            paginate: {
                next: '→',
                previous: '←',
                first: 'Inicio',
                last: 'Último'
            },
        },
        columns: [
            { data: 'id' },
            { data: 'fecha' },
            { data: 'nombre' },
            { data: 'concepto' },
            { data: 'movimiento' },
            { data: 'tipo' },
            { data: 'importe' },
            { data: 'metodo' },
            { data: 'clinic' }
        ]
    });

    function cargarDatos(fechaInicio, fechaFin) {
        let movimiento = $('#movimiento').val() || 'Ambos';
        let clinic = $('#clinic').val() || 'Ambas';
        let metodo = $('#method').val() || 'Ambos';
        let tipoProducto = $('#product_type').val() || 'Todos';
        let modoFiltro = (fechaInicio === fechaFin) ? 'day' : 'week';

        $.ajax({
            url: "{{ route('panel.ingresosTransacciones.data') }}",
            method: "GET",
            data: {
                filter_mode: modoFiltro,
                fecha: fechaInicio,
                week_start: fechaInicio,
                week_end: fechaFin,
                movement: movimiento,
                clinic: clinic,
                method: metodo,
                product_type: tipoProducto
            },
            beforeSend: function() {
                $('#btnFiltrar').prop('disabled', true).text('Cargando...');
            },
            success: function (response) {
                if (response.success) {
                    tabla.clear().rows.add(response.data).draw();
                    $('#totalIngresos').text(response.ingresos);
                    $('#totalEgresos').text(response.egresos);
                    $('#totalBalance').text(response.total);
                } else {
                    alert("Error: " + response.error);
                }
            },
            error: function (xhr) {
                alert("Error al cargar los datos");
                console.error(xhr.responseText);
            },
            complete: function() {
                $('#btnFiltrar').prop('disabled', false).text('Aplicar filtros');
            }
        });
    }

    let hoy = new Date().toISOString().split('T')[0];
    $('#fechaInicio').val(hoy);
    $('#fechaFin').val(hoy);
    cargarDatos(hoy, hoy);

    $('#btnFiltrar').click(function() {
        let fechaInicio = $('#fechaInicio').val();
        let fechaFin = $('#fechaFin').val();
        cargarDatos(fechaInicio, fechaFin);
    });
});
</script>
@endsection
