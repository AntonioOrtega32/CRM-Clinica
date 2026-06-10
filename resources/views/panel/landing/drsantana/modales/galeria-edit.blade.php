<<<<<<< HEAD
<!-- Modal Editar Galería -->
<div id="editGaleriaModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <!-- Overlay -->
    <div class="modal-overlay absolute inset-0 bg-black opacity-50" onclick="closeModal('editGaleriaModal')"></div>

    <!-- Contenedor -->
    <div class="modal-container bg-white rounded-2xl w-full max-w-md p-6 relative mx-4 max-h-[90vh] overflow-y-auto
                fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50">
        <button onclick="closeModal('editGaleriaModal')"
            class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-xl">×</button>

        <h2 class="text-2xl font-bold text-[#1C6C73] mb-6">Editar elemento de Galería</h2>

<form action="{{ route('panel.drsantana.galeria.update', $galeria->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- ID oculto -->
            <input type="hidden" id="editGaleriaId" name="id" value="">

            <div class="mb-4">
                <label class="block font-medium text-gray-700 mb-2">Título</label>
                <input type="text" id="editGaleriaTitulo" name="titulo"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#1C6C73] focus:border-transparent"
                       placeholder="Título de la imagen">
            </div>

            <div class="mb-4">
                <label class="block font-medium text-gray-700 mb-2">Descripción</label>
                <textarea id="editGaleriaDescripcion" name="descripcion" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#1C6C73] focus:border-transparent resize-vertical"
                          placeholder="Descripción opcional"></textarea>
            </div>

            <div class="mb-4">
                <label class="block font-medium text-gray-700 mb-2">Archivo</label>
                <input type="file" name="archivo" accept="image/*,video/*"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2">
                <p class="text-sm text-gray-500 mt-1">Si subes un nuevo archivo, reemplazará al anterior.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeModal('editGaleriaModal')"
                        class="bg-gray-300 text-gray-800 px-5 py-2 rounded-lg hover:bg-gray-400 transition">
                    Cancelar
                </button>
                <button type="submit"
                        class="bg-[#1C6C73] text-white px-5 py-2 rounded-lg hover:bg-[#14565c] transition">
                    💾 Guardar Cambios
                </button>
            </div>
        </form>
=======
<div id="editGaleriaModal" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50" onclick="closeModal('editGaleriaModal')"></div>

    <div class="modal-container bg-white rounded-2xl w-full max-w-md p-6 relative mx-4 max-h-[90vh] overflow-y-auto
                fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50">
        <button onclick="closeModal('editGaleriaModal')"
            class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-xl">×</button>

        <h2 class="text-2xl font-bold text-[#1C6C73] mb-6">Editar multimedia de Galería</h2>

        <form id="editGaleriaForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" id="editGaleriaId" name="id">

            <div class="mb-4">
                <label class="block font-medium text-gray-700 mb-2">Título</label>
                <input type="text" id="editGaleriaTitulo" name="titulo"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#1C6C73] focus:border-transparent">
            </div>

            <div class="mb-4">
                <label class="block font-medium text-gray-700 mb-2">Descripción</label>
                <textarea id="editGaleriaDescripcion" name="descripcion" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#1C6C73] focus:border-transparent resize-vertical"></textarea>
            </div>

            <div class="mb-4">
                <label class="block font-medium text-gray-700 mb-2">Imagen</label>
                <input type="file" name="imagen" accept="image/*"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#1C6C73] focus:border-transparent">
                <p class="text-sm text-gray-500 mt-1">Dejar vacío para mantener la imagen actual</p>
                <!--
                <div id="editGaleriaImagenContainer" class="mt-2">
                    <img id="editGaleriaImagen" src="" alt="Imagen actual"
                         class="w-32 h-32 object-cover rounded-lg border mx-auto">
                    <p class="text-sm text-gray-500 text-center mt-1">Imagen actual</p>
                </div>
            </div>
            -->
            <div class="flex justify-between items-center pt-4">

                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('editGaleriaModal')"
                            class="bg-gray-300 text-gray-800 px-5 py-2 rounded-lg hover:bg-gray-400 transition">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="bg-[#1C6C73] text-white px-5 py-2 rounded-lg hover:bg-[#14565c] transition">
                        💾 Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
        <div class="flex justify-end pt-4">
            <button type="button" id="deleteGaleriaBtn"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                    🗑️ Eliminar
                </button>
    </div>
</div>

<script>
function openEditGaleriaModal(id, titulo, descripcion, imagen) {
    document.getElementById('editGaleriaId').value = id;
    document.getElementById('editGaleriaTitulo').value = titulo || '';
    document.getElementById('editGaleriaDescripcion').value = descripcion || '';
    openModal('editGaleriaModal');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}


// Editar galería
document.getElementById('editGaleriaForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const galeriaId = document.getElementById('editGaleriaId').value;
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;

    submitBtn.disabled = true;
    submitBtn.textContent = 'Guardando...';

    try {
        const response = await fetch(`/panel/doctor-santana/galeria/${galeriaId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Imagen actualizada correctamente', 'success');
            closeModal('editGaleriaModal');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(result.message || 'Error al actualizar');
        }
    } catch (error) {
        showNotification('Error: ' + error.message, 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
});

// Eliminar galería
document.getElementById('deleteGaleriaBtn').addEventListener('click', function() {
    const galeriaId = document.getElementById('editGaleriaId').value;

    if (!confirm('¿Estás seguro de que deseas eliminar esta imagen?')) {
        return false;
    }

    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('_method', 'DELETE');

    fetch(`/panel/doctor-santana/galeria/${galeriaId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification('Imagen eliminada correctamente', 'success');
            closeModal('editGaleriaModal');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(result.message || 'Error al eliminar');
        }
    })
    .catch(error => {
        showNotification('Error: ' + error.message, 'error');
    });
});
</script>
