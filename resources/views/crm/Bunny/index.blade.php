@extends('panel.layouts.panel')

@section('content')
<div class="container mt-4">
    <h3>Archivos del paciente #{{ $lead_id }}</h3>

    <div class="mb-3">
    <label for="selectStep">Tipo de imagen:</label>
    <select id="selectStep" class="form-select w-auto">
        <option value="pre" selected>Pre</option>
        <option value="post">Post</option>
        <option value="diseno">Diseño</option>
        <option value="proced">Procedimiento</option>
        <option value="1mes">1 mes</option>
    </select>
</div>

<br>

    <table id="tablaArchivos" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Tamaño</th>
                <th>Última modificación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabla = document.querySelector('#tablaArchivos tbody');
    const leadId = "{{ $lead_id }}";
    let step = 'pre'; // valor por defecto

document.getElementById('selectStep').addEventListener('change', function() {
    step = this.value;
    cargarArchivos();
});

function cargarArchivos() {
    fetch(`/bunny/listar/${leadId}/${step}`)
        .then(res => res.json())
        .then(data => {
            tabla.innerHTML = '';
            if (Array.isArray(data)) {
                data.forEach(file => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${file.ObjectName}</td>
                        <td>${(file.Length / 1024).toFixed(2)} KB</td>
                        <td>${new Date(file.LastChanged).toLocaleString()}</td>
                        <td>
                            <a href="${file.url}" target="_blank" class="btn btn-sm btn-success">Ver</a>
                        </td>
                    `;
                    tabla.appendChild(tr);
                });
            } else if (data.error) {
                tabla.innerHTML = `<tr><td colspan="4" class="text-center text-danger">${data.error}</td></tr>`;
            }
        });
}

    cargarArchivos();
});
</script>
@endsection