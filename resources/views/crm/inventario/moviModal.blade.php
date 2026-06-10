<div id="productMovementModal" class="modal fixed inset-0 z-50 items-center justify-center hidden flex backdrop-blur-sm">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    <div
        class="modal-container bg-white w-full max-w-2xl rounded-2xl shadow-lg z-50 overflow-hidden mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-4 border-b bg-gray-50 sticky top-0">
            <h2 class="text-lg font-semibold text-gray-800">Registrar Entrada de Producto</h2>
            <button onclick="closeModal('productMovementModal')"
                class="text-gray-500 hover:text-gray-700 text-xl">✕</button>
        </div>

        <!-- Body -->
        <div class="p-6">
            <form id="formAddMovement" action="{{ route('panel.inventario.movimiento') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="isNewProduct" name="is_new_product" value="0">

                <!-- Entrada -->
                <div id="entradaFields" class="space-y-4">
                    <div id="productNameContainer">
                        <label for="itemNameSelect" class="block font-medium text-gray-700 mb-2">Nombre del
                            Producto:</label>
                        <select id="itemNameSelect" name="item_name"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1C6C73]">
                            <option value="" disabled selected>Seleccione un producto...</option>
                            @foreach ($inventarios as $producto)
                                <option value="{{ $producto->id }}" data-category="{{ $producto->category }}">
                                    {{ $producto->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Producto nuevo -->
                    <div class="flex items-center">
                        <input type="checkbox" id="newProductCheckbox"
                            class="w-4 h-4 text-[#1C6C73] border-gray-300 rounded focus:ring-[#1C6C73]"
                            onchange="toggleNewProductFields()">
                        <label for="newProductCheckbox" class="ml-2 text-gray-700">¿Es un producto nuevo?</label>
                    </div>

                    <!-- Categoría -->
                    <div>
                        <label for="itemsCategory" class="block font-medium text-gray-700 mb-2">Categoría:</label>
                        <select
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1C6C73]"
                            id="itemsCategory" name="category">
                            <option value="" disabled selected>Seleccione una categoría...</option>
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
                            <option value="mercado libre">mercado libre</option>
                            <option value="Kabla">Kabla</option>
                            <option value="Varios">Varios</option>
                            <option value="walmart">Walmart</option>
                        </select>
                    </div>

                    <!-- Cantidad -->
                    <div>
                        <label for="itemQuantity" class="block font-medium text-gray-700 mb-2">Cantidad:</label>
                        <input type="number"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1C6C73]"
                            id="itemQuantity" name="stock" min="1">
                    </div>

                    <!-- Es medicamento -->
                    <div class="flex items-center">
                        <input type="checkbox" id="isMedicineCheck" name="has_expiry" value="1"
                            class="w-4 h-4 text-[#1C6C73] border-gray-300 rounded focus:ring-[#1C6C73]"
                            onchange="toggleMedicineFields()">
                        <label for="isMedicineCheck" class="ml-2 text-gray-700">Es medicamento</label>
                    </div>

                    <input type="hidden" id="itemLocation" name="ubicacion" value="Bodega">

                    <div id="minimumValueField" class="hidden">
                        <label for="minimumValue" class="block font-medium text-gray-700 mb-2">Valor Mínimo
                            Requerido:</label>
                        <input type="number"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1C6C73]"
                            id="minimumValue" name="minimum_value" min="0">
                    </div>

                    <div id="expirationDateField" class="hidden">
                        <label for="expirationDate" class="block font-medium text-gray-700 mb-2">Fecha de
                            caducidad:</label>
                        <input type="date"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1C6C73]"
                            id="expirationDate" name="expirationDate">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="toggleManualPrice"
                            class="w-4 h-4 text-[#1C6C73] border-gray-300 rounded focus:ring-[#1C6C73]"
                            onchange="togglePriceField()">
                        <label for="toggleManualPrice" class="ml-2 text-gray-700">Venta</label>
                    </div>

                    <div id="manualPriceField" class="hidden">
                        <label for="manualPrice" class="block font-medium text-gray-700 mb-2">Precio:</label>
                        <input type="number"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1C6C73]"
                            id="manualPrice" name="manualPrice" step="0.01" min="0"
                            placeholder="Opcional">
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-3 px-6 py-4 border-t bg-gray-50 sticky bottom-0">
                    <button type="submit"
                        class="bg-[#1C6C73] text-white px-5 py-2 rounded-lg hover:bg-[#14565c] transition-colors font-medium">
                        Guardar
                    </button>
                </div>
            </form>
            <button onclick="closeModal('productMovementModal')"
                class="bg-gray-300 text-gray-800 px-5 py-2 rounded-lg hover:bg-gray-400 transition-colors font-medium">
                Cancelar
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const select = document.getElementById('itemNameSelect');
        const categoryField = document.getElementById('itemsCategory');

        if (select) {
            select.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const category = selectedOption.getAttribute('data-category');
                if (category) categoryField.value = category;
            });
        }
    });

    // Mostrar/ocultar campos para producto nuevo
    function toggleNewProductFields() {
        const isNew = document.getElementById('newProductCheckbox').checked;
        document.getElementById('isNewProduct').value = isNew ? '1' : '0';

        const container = document.getElementById('productNameContainer');
        if (isNew) {
            container.innerHTML = `
            <label for="itemNameInput" class="block font-medium text-gray-700 mb-2">Nombre del Nuevo Producto:</label>
            <input type="text" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1C6C73]"
                   id="itemNameInput" name="item_name">
        `;
        } else {
            container.innerHTML = `
            <label for="itemNameSelect" class="block font-medium text-gray-700 mb-2">Nombre del Producto:</label>
            <select class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1C6C73]"
                    id="itemNameSelect" name="item_name">
                <option value="" disabled selected>Seleccione un producto...</option>
                @foreach ($inventarios as $producto)
                    <option value="{{ $producto->id }}" data-category="{{ $producto->category }}">
                        {{ $producto->name }}
                    </option>
                @endforeach
            </select>
        `;
            document.getElementById('itemNameSelect').addEventListener('change', function() {
                const category = this.options[this.selectedIndex].getAttribute('data-category');
                if (category) document.getElementById('itemsCategory').value = category;
            });
        }
    }

    // Mostrar/ocultar campos de medicamento
    function toggleMedicineFields() {
        const isMedicine = document.getElementById('isMedicineCheck').checked;
        document.getElementById('expirationDateField').classList.toggle('hidden', !isMedicine);
        document.getElementById('minimumValueField').classList.toggle('hidden', isMedicine && document.getElementById(
            'newProductCheckbox').checked);
    }

    // Mostrar/ocultar campo de precio
    function togglePriceField() {
        const showPrice = document.getElementById('toggleManualPrice').checked;
        document.getElementById('manualPriceField').classList.toggle('hidden', !showPrice);
    }

    //Cerrar el pinshi modal dandole clicky afuera
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function () {
      closeModal('productMovementModal');
    });
  });

</script>
