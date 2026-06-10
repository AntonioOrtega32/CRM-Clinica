<div id="kitModal" class="modal fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto flex backdrop-blur-sm">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50" onclick="closeModal('kitModal')"></div>

    <div class="modal-container bg-white w-full max-w-3xl rounded-2xl shadow-lg z-50 overflow-hidden max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-4 border-b bg-gray-50">
            <h2 id="kitModalLabel" class="text-lg font-semibold text-gray-800">Editar Kit: Capilar</h2>
            <button onclick="closeModal('kitModal')" class="text-gray-500 hover:text-gray-700 text-xl">✕</button>
        </div>

        <!-- Body -->
        <div class="p-6">
            <form id="kitForm" action="{{ route('panel.guardarKit') }}" method="POST">
    @csrf
    <div class="mb-4">
        <label for="treatment_type">Tipo de Kit:</label>
        <select id="treatment_type" name="treatment_type" class="w-full border rounded px-3 py-2">
            <option value="capilar" {{ old('treatment_type') == 'capilar' ? 'selected' : '' }}>Capilar</option>
            <option value="barba" {{ old('treatment_type') == 'barba' ? 'selected' : '' }}>Barba</option>
        </select>
    </div>

    <div class="mb-4">
        <label for="clinic">Clínica:</label>
        <input type="text" id="clinic" name="clinic" value="{{ old('clinic', 'Santa fe') }}" class="w-full border rounded px-3 py-2">
    </div>

    <table class="w-full border">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="kitBody">
            @foreach($kits as $kit)
                <tr>
                    <td>
                        <select name="productos[]" class="w-full border rounded px-2 py-1">
                            @foreach($inventarios as $prod)
                                <option value="{{ $prod->id }}" {{ $prod->id == $kit->product_id ? 'selected' : '' }}>
                                    {{ $prod->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="cantidades[]" min="1" value="{{ $kit->quantity }}" class="w-20 border rounded px-2 py-1 text-center">
                    </td>
                    <td class="text-center">
                        <button type="button" onclick="eliminarFila(this)" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Eliminar</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <button type="button" onclick="agregarFila()" class="bg-green-600 text-white px-4 py-2 rounded mt-2 hover:bg-green-700">Agregar Producto</button>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded mt-2 hover:bg-blue-700">Guardar Cambios</button>
</form>

        </div>
    </div>
</div>

<style>
.modal {
    transition: opacity 0.25s ease;
}
</style>

<script>
function agregarFila() {
    const tbody = document.getElementById('kitBody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <select name="productos[]" class="w-full border rounded px-2 py-1">
                @foreach($inventarios as $prod)
                    <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="cantidades[]" min="1" value="1" class="w-20 border rounded px-2 py-1 text-center">
        </td>
        <td class="text-center">
            <button type="button" onclick="eliminarFila(this)" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Eliminar</button>
        </td>
    `;
    tbody.appendChild(row);
}

function eliminarFila(button) {
    if (confirm('¿Deseas eliminar este producto del kit?')) {
        button.closest('tr').remove();
    }
}
</script>