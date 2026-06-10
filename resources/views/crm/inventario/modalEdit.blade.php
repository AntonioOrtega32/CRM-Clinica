<div id="editProductModal" class="modal fixed inset-0 z-50 items-center justify-center hidden flex backdrop-blur-sm">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>

    <div class="modal-container bg-white w-full max-w-md rounded-2xl shadow-lg z-50 overflow-hidden mx-4">
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-4 border-b bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-800">Editar Producto</h2>
            <button onclick="closeModal('editProductModal')" class="text-gray-500 hover:text-gray-700 text-xl">✕</button>
        </div>

        <!-- Body -->
        <div class="p-6">
            <form action="{{ route('panel.inventario.update') }}" method="POST">
                @csrf
                @method('PUT')

                <input type="hidden" id="editProductId" name="id">

                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2">Nombre:</label>
                    <input type="text" id="editProductName" name="nombre"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1C6C73]">
                </div>

                <div class="mb-4">
                    <label for="itemsCategory" class="block font-medium text-gray-700 mb-2">Categoría:</label>
                    <select id="editItemsCategory" name="category" class="w-full border rounded px-3 py-2">
                        <option value="" disabled>Seleccione una categoría...</option>
                        <option value="Farmacia">Farmacia</option>
                        <option value="Lanceta">Lanceta</option>
                        <option value="La paz">La paz</option>
                        <option value="TIM">TIM</option>
                        <option value="Imprenta">Imprenta</option>
                        <option value="Sams">Sams</option>
                        <option value="Amazon">Amazon</option>
                        <option value="Office">Office</option>
                        <option value="Instituto de tricologia">Instituto de tricologia</option>
                        <option value="Turquia">Turquia</option>
                        <option value="mercado libre">Mercado libre</option>
                        <option value="Kabla">Kabla</option>
                        <option value="Varios">Varios</option>
                        <option value="walmart">Walmart</option>
                    </select>

                </div>

                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2">Stock:</label>
                    <input type="number" id="itemStock" name="stock" min="0"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1C6C73]">
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2">Cantidad mínima requerida:</label>
                    <input type="number" id="itemMinima" name="cantidad_minima" min="0"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1C6C73]">
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2">Pendiente por comprar:</label>
                    <input type="number" id="itemPendiente" name="pending_purchase" min="0"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1C6C73]">
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeModal('editProductModal')" class="bg-gray-300 text-gray-800 px-5 py-2 rounded-lg hover:bg-gray-400 transition-colors font-medium">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-[#1C6C73] text-white px-5 py-2 rounded-lg hover:bg-[#14565c] transition-colors font-medium">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.modal {
    transition: opacity 0.25s ease;
}
.modal-hidden {
    opacity: 0;
    pointer-events: none;
}
.modal-visible {
    opacity: 1;
    pointer-events: auto;
}
</style>

<script>
function openEditModal(id, name, category, stock, minimum_required, pending_purchase) {
    // Llenar inputs del modal
    document.getElementById('editProductId').value = id;
    document.getElementById('editProductName').value = name;
    document.getElementById('editItemsCategory').value = category; // <- aquí estaba el error
    document.getElementById('itemStock').value = stock;
    document.getElementById('itemMinima').value = minimum_required;
    document.getElementById('itemPendiente').value = pending_purchase;

    // Mostrar modal
    document.getElementById('editProductModal').classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

 document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function () {
      closeModal('editProductModal');
    });
  });

</script>
