<div id="editBlogModal" class="fixed inset-0 z-50 items-center justify-center hidden">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50" onclick="closeModal('editBlogModal')"></div>
    
    <div class="modal-container bg-white rounded-2xl w-full max-w-2xl p-6 relative mx-4 max-h-[90vh] overflow-y-auto 
                fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50">
        <button onclick="closeModal('editBlogModal')"
            class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-xl">×</button>
        
        <h2 class="text-2xl font-bold text-[#1C6C73] mb-6">Editar Entrada de Blog</h2>
        
        <form id="editBlogForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" id="editBlogId" name="id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="md:col-span-2">
                    <label class="block font-medium text-gray-700 mb-2">Título *</label>
                    <input type="text" id="editBlogTitulo" name="titulo" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#1C6C73] focus:border-transparent" required>
                </div>
                
                <div>
                    <label class="block font-medium text-gray-700 mb-2">Fecha de Publicación *</label>
                    <input type="date" id="editBlogFecha" name="fecha" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#1C6C73] focus:border-transparent" required>
                </div>
                
                <div>
                    <label class="block font-medium text-gray-700 mb-2">Imagen</label>
                    <input type="file" name="imagen" accept="image/*" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#1C6C73] focus:border-transparent">
                    <div id="editBlogImagenContainer" class="mt-2 hidden">
                        <img id="editBlogImagen" src="" alt="Imagen actual" class="w-20 h-20 object-cover rounded-lg border">
                        <p class="text-sm text-gray-500 mt-1">Imagen actual</p>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block font-medium text-gray-700 mb-2">Contenido *</label>
                <textarea id="editBlogContenido" name="contenido" rows="6" 
                          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#1C6C73] focus:border-transparent resize-vertical" 
                          required></textarea>
            </div>
            
            <div class="flex justify-between items-center pt-4">
                <button type="button" id="deleteBlogBtn"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                    🗑️ Eliminar
                </button>
                
                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('editBlogModal')" 
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
    </div>
</div>

<script>
    // Funciones globales para modales
function openModal(editBlogModal) {
    const modal = document.getElementById(editBlogModal);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(editBlogModal) {
    const modal = document.getElementById(editBlogModal);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Cerrar modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (modal.style.display === 'flex') {
                closeModal(modal.id);
            }
        });
    }
});

// Cerrar modal al hacer clic fuera
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        const modal = e.target.closest('.modal');
        if (modal) {
            closeModal(modal.id);
        }
    }
});
</script>