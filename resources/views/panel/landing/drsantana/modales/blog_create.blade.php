<div id="createBlogModal" class="modal fixed inset-0 z-50 items-center justify-center hidden">
    <div class="modal-overlay absolute inset-0 bg-black opacity-50"></div>
    
   <div class="modal-container bg-white rounded-2xl w-full max-w-2xl p-6 relative mx-4 max-h-[90vh] overflow-y-auto 
                 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50">
        <button onclick="closeModal('createBlogModal')"
            class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-xl">×</button>
        
        <h2 class="text-2xl font-bold text-[#1C6C73] mb-6">Crear Entrada de Blog</h2>
        
        <form id="createBlogForm" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="md:col-span-2">
                    <label class="block font-medium text-gray-700 mb-2">Título *</label>
                    <input type="text" name="titulo" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#1C6C73] focus:border-transparent" required>
                </div>
                
                <div>
                    <label class="block font-medium text-gray-700 mb-2">Fecha de Publicación *</label>
                    <input type="date" name="fecha" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#1C6C73] focus:border-transparent" required>
                </div>
                
                <div>
                    <label class="block font-medium text-gray-700 mb-2">Imagen</label>
                    <input type="file" name="imagen" accept="image/*" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#1C6C73] focus:border-transparent">
                    <p class="text-sm text-gray-500 mt-1">Formatos: JPG, PNG, GIF, WebP (Máx. 2MB)</p>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block font-medium text-gray-700 mb-2">Contenido *</label>
                <textarea name="contenido" rows="6" 
                          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-[#1C6C73] focus:border-transparent resize-vertical" 
                          placeholder="Escribe el contenido de la entrada..." required></textarea>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeModal('createBlogModal')" 
                        class="bg-gray-300 text-gray-800 px-5 py-2 rounded-lg hover:bg-gray-400 transition">
                    Cancelar
                </button>
                <button type="submit" 
                        class="bg-[#1C6C73] text-white px-5 py-2 rounded-lg hover:bg-[#14565c] transition">
                    📝 Crear Entrada
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Funciones globales para modales
function openModal(createBlogModal) {
    const modal = document.getElementById(createBlogModal);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(createBlogModal) {
    const modal = document.getElementById(createBlogModal);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

function showNotification(message, type = 'success') {
    // Implementar notificación toast
    alert(message); // Temporal - puedes implementar un sistema de notificaciones bonito
}

// Cerrar modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (!modal.classList.contains('hidden')) {
                closeModal(modal.id);
            }
        });
    }
});
</script>

<script>
//Funciones para contenido dinamico xd
document.getElementById('createBlogForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creando...';
    
    try {
        const response = await fetch('{{ route("panel.drsantana.blog.store") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Entrada creada correctamente', 'success');
            closeModal('createBlogModal');
            this.reset();
            // Recargar la página o actualizar la lista
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(result.message || 'Error al crear la entrada');
        }
    } catch (error) {
        showNotification('Error: ' + error.message, 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
});

// Establecer fecha actual por defecto
document.addEventListener('DOMContentLoaded', function() {
    const fechaInput = document.querySelector('#createBlogModal input[name="fecha"]');
    if (fechaInput) {
        fechaInput.value = new Date().toISOString().split('T')[0];
    }
});

</script>