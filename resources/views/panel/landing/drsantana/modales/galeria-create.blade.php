<div id="createGaleriaModal" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50" onclick="closeModal('createGaleriaModal')"></div>

    <div class="modal-container bg-white rounded-2xl w-full max-w-md p-6 relative mx-4 max-h-[90vh] overflow-y-auto
                fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50">
        <button onclick="closeModal('createGaleriaModal')"
            class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-xl">×</button>

        <h2 class="text-2xl font-bold text-[#1C6C73] mb-6">Agregar elemento a Galería</h2>

        <form id="createGaleriaForm" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="block font-medium text-gray-700 mb-2">Título</label>
                <input type="text" name="titulo"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#1C6C73] focus:border-transparent"
                       placeholder="Título de la imagen">
            </div>

            <div class="mb-4">
                <label class="block font-medium text-gray-700 mb-2">Descripción</label>
                <textarea name="descripcion" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#1C6C73] focus:border-transparent resize-vertical"
                          placeholder="Descripción opcional"></textarea>
            </div>

             <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2">Tipo *</label>
                    <select name="tipo" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        <option value="imagen">Imagen</option>
                        <option value="video">Video</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2">Archivo *</label>
                    <input type="file" name="archivo" accept="image/*,video/*" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <p class="text-sm text-gray-500 mt-1">Formatos: JPG, PNG, GIF, MP4, MOV, AVI (Máx. 10MB)</p>
                </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeModal('createGaleriaModal')"
                        class="bg-gray-300 text-gray-800 px-5 py-2 rounded-lg hover:bg-gray-400 transition">
                    Cancelar
                </button>
                <button type="submit"
                        class="bg-[#1C6C73] text-white px-5 py-2 rounded-lg hover:bg-[#14565c] transition">
                    📸 Agregar Imagen
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('createGaleriaForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;

    submitBtn.disabled = true;
    submitBtn.textContent = 'Subiendo...';

    try {
        const response = await fetch('{{ route("panel.drsantana.galeria.store") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Imagen agregada correctamente', 'success');
            closeModal('createGaleriaModal');
            this.reset();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(result.message || 'Error al agregar imagen');
        }
    } catch (error) {
        showNotification('Error: ' + error.message, 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
});
</script>
