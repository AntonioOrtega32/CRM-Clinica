<div id="salidaProductModal" class="modal fixed inset-0 z-50 items-center justify-center hidden flex backdrop-blur-sm">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>

    <div class="modal-container bg-white w-full max-w-md rounded-2xl shadow-lg z-50 overflow-hidden mx-4">
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-4 border-b bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-800">Salida de Producto</h2>
            <button onclick="closeModal('salidaProductModal')"
                class="text-gray-500 hover:text-gray-700 text-xl">✕</button>
        </div>

        <!-- Body -->
        <div class="p-6">
            <form action="{{ route('panel.inventario.salida') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="movement_type" value="salida">
                <input type="hidden" id="salidaProductId" name="product_id">

                <!-- Nombre -->
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2">Producto:</label>
                    <input type="text" id="salidaProductName" readonly
                        class="w-full border rounded px-3 py-2 bg-gray-100">
                </div>

                <!-- Cantidad a salir -->
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2">Cantidad a salir:</label>
                    <input type="number" id="salidaQuantity" name="output_quantity" min="1"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1C6C73]">
                </div>

                <!-- A quién se entrega -->
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2">Se entrega a:</label>
                    <select id="salidaReceivedBy" name="received_by"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1C6C73]">
                        <option value="">Selecciona...</option>
                        <option value="Idania Bastida">Idania Bastida</option>
                        <option value="Dra Oriana">Dra Oriana</option>
                        <option value="Gaby">Gaby</option>
                        <option value="Luis">Luis</option>
                        <option value="Sra Susana">Sra Susana</option>
                        <option value="Sra Liseth">Sra Liseth</option>
                        <option value="Alan">Alan</option>
                        <option value="Xochitl">Xochitl</option>
                        <option value="Janeth">Janeth</option>
                        <option value="Dra Samanta">Dra Samanta</option>
                        <option value="Paola">Paola</option>
                        <option value="Armando">Armando</option>
                        <option value="Monica">Monica</option>
                        <option value="Ana">Ana</option>
                        <option value="Dr Joaquín">Dr Joaquín</option>
                        <option value="Dra Amairani">Dra Amairani</option>
                    </select>
                </div>

                <!-- Fecha -->
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2">Fecha de salida:</label>
                    <input type="date" id="salidaDate" name="output_date"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1C6C73]">
                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeModal('salidaProductModal')"
                        class="bg-gray-300 text-gray-800 px-5 py-2 rounded-lg hover:bg-gray-400 transition-colors font-medium">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="bg-[#1C6C73] text-white px-5 py-2 rounded-lg hover:bg-[#14565c] transition-colors font-medium">
                        Guardar salida
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    //Modal de salida de productos con JS porque Alpine esta todo tonto
    function openSalidaModal(id, nombre) {
        // Rellenar datos
        document.getElementById('salidaProductId').value = id;
        document.getElementById('salidaProductName').value = nombre;

        // Mostrar modal
        document.getElementById('salidaProductModal').classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

     document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function () {
      closeModal('salidaProductModal');
    });
  });
</script>
