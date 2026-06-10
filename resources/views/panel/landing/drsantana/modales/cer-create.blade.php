<!-- Modal Crear -->
<div id="createModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg w-96 p-6 relative shadow-lg">
        <!-- Botón cerrar -->
        <button onclick="closeModal('createModal')"
            class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>

        <h2 class="text-xl font-bold mb-6 text-[#1C6C73]">Crear Certificación</h2>

        <form action="{{ route('certificaciones.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Campo título -->
            <div class="mb-4">
                <label class="block mb-2 font-medium">Título</label>
                <input type="text" name="titulo" class="w-full border rounded p-2 focus:ring focus:ring-[#1C6C73]"
                    required>
            </div>

            <!-- Campo descripción -->
            <div class="mb-4">
                <label class="block mb-2 font-medium">Descripción</label>
                <textarea name="descripcion" class="w-full border rounded p-2 focus:ring focus:ring-[#1C6C73]" required></textarea>
            </div>

            <!-- Campo imagen -->
            <div class="mb-6">
                <label class="block mb-2 font-medium">Imagen</label>
                <input type="file" name="imagen" accept="image/*"
                    class="block w-full text-sm text-gray-600 border rounded cursor-pointer">
            </div>

            <!-- Botón enviar -->
            <button type="submit"
                class="w-full bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealClaro transition">
                Agregar
            </button>
        </form>
    </div>
</div>
