<section id="galeria" class="py-10 px-6 bg-gradient-to-r from-gray-100 via-white to-gray-100">
    <main class="flex-1 overflow-y-auto p-6 bg-gray-50" x-data="{ openCreate: false, editId: null }">

        <!-- Título y botón Crear -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-[#1C6C73]">Galería</h1>
            <button @click="openCreate = true"
                class="bg-[#1C6C73] text-white px-4 py-2 rounded-lg hover:bg-[#14565c] transition">
                📸 Agregar Imagen
            </button>
        </div>

        @if ($galerias->isEmpty())
            <div class="text-center py-12 bg-white rounded-lg shadow">
                <div class="text-6xl mb-4">🖼️</div>
                <p class="text-gray-600 text-lg">No hay imágenes en la galería.</p>
                <p class="text-gray-500">Agrega la primera imagen para comenzar.</p>
            </div>
        @else
            <!-- Contenedor del carrusel -->
            <div x-data="carousel()" class="relative">
                <!-- Botón Izquierda -->
                <button @click="scrollLeft"
                    class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-4 bg-[#1C6C73] text-white p-3 rounded-full shadow hover:bg-[#14565c] z-10">
                    ‹
                </button>

                <!-- Scroll horizontal -->
                <div x-ref="container"
                    class="flex overflow-x-auto space-x-6 scrollbar-hide scroll-smooth cursor-grab active:cursor-grabbing select-none"
                    @mousedown="startDrag($event)" @mouseup="endDrag" @mouseleave="endDrag" @mousemove="drag($event)"
                    @touchstart="startTouch($event)" @touchmove="dragTouch($event)">
                    
                    @foreach ($galerias as $galeria)
                        <div class="flex-shrink-0 w-72">
                            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow h-full">
                                <div class="relative group">
                                    @if($galeria->tipo == 'video')
                                        <video class="w-full h-48 object-cover" controls>
                                            <source src="{{ asset($galeria->imagen) }}" type="video/mp4">
                                        </video>
                                    @else
                                        <img src="{{ asset($galeria->imagen) }}" alt="Imagen de galería" class="w-full h-48 object-cover">
                                    @endif

                                    <!-- Overlay con botones -->
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-opacity flex items-center justify-center opacity-0 group-hover:opacity-100">
                                        <button @click="editId = {{ $galeria->id }}"
                                                class="bg-blue-500 text-white p-2 rounded-full mx-1 hover:bg-blue-600">
                                            ✏️
                                        </button>
                                        <form action="{{ route('panel.drsantana.galeria.destroy', $galeria->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta imagen?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-500 text-white p-2 rounded-full mx-1 hover:bg-red-600">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="p-4">
                                    <h3 class="font-semibold text-[#1C6C73]">{{ $galeria->titulo }}</h3>
                                    @if ($galeria->descripcion)
                                        <p class="text-gray-600 text-sm mt-1 line-clamp-2">{{ $galeria->descripcion }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Botón Derecha -->
                <button @click="scrollRight"
                    class="absolute right-0 top-1/2 transform -translate-y-1/2 translate-x-4 bg-[#1C6C73] text-white p-3 rounded-full shadow hover:bg-[#14565c] z-10">
                    ›
                </button>
            </div>
        @endif

        <!-- Modal Crear -->
        <div x-show="openCreate" x-cloak @click.self="openCreate = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded-lg w-96 p-6 relative">
                <button @click="openCreate = false" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
                <h2 class="text-xl font-bold mb-4">Agregar Imagen a la Galería</h2>
                <form action="{{ route('panel.drsantana.galeria.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <label class="block mb-2">Título</label>
                    <input type="text" name="titulo" class="w-full border rounded p-2 mb-4" required>
                    <label class="block mb-2">Descripción</label>
                    <textarea name="descripcion" class="w-full border rounded p-2 mb-4"></textarea>
                    <label class="block mb-2">Tipo de archivo</label>
                    <select name="tipo" class="w-full border rounded p-2 mb-4" required>
                        <option value="imagen">Imagen</option>
                        <option value="video">Video</option>
                    </select>
                    <label class="block mb-2">Archivo</label>
                    <input type="file" name="imagen" accept="image/*,video/*" required class="mb-4">
                    <button type="submit" class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealOscuro">Agregar</button>
                </form>
            </div>
        </div>

        <!-- Modal Editar -->
        @foreach ($galerias as $galeria)
            <div x-show="editId === {{ $galeria->id }}" x-cloak @click.self="editId = null"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="bg-white rounded-lg w-96 p-6 relative">
                    <button @click="editId = null" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
                    <h2 class="text-xl font-bold mb-4">Editar Imagen</h2>
                    <form action="{{ route('panel.drsantana.galeria.update', $galeria->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <label class="block mb-2">Título</label>
                        <input type="text" name="titulo" value="{{ $galeria->titulo }}" class="w-full border rounded p-2 mb-4">
                        <label class="block mb-2">Descripción</label>
                        <textarea name="descripcion" class="w-full border rounded p-2 mb-4">{{ $galeria->descripcion }}</textarea>
                        <label class="block mb-2">Tipo de archivo</label>
                        <select name="tipo" class="w-full border rounded p-2 mb-4">
                            <option value="imagen" @if($galeria->tipo == 'imagen') selected @endif>Imagen</option>
                            <option value="video" @if($galeria->tipo == 'video') selected @endif>Video</option>
                        </select>
                        <label class="block mb-2">Archivo</label>
                        <input type="file" name="imagen" accept="image/*,video/*">
                        <br><br>
                        <button type="submit" class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealOscuro">Guardar Cambios</button>
                    </form>
                    <form action="{{ route('panel.drsantana.galeria.destroy', $galeria->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta imagen?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-[#ff1616] text-white px-3 py-1 rounded hover:bg-tealOscuro text-sm mt-4">Borrar</button>
                    </form>
                </div>
            </div>
        @endforeach

    </main>
</section>

<!-- Alpine Component para el carrusel -->
<script>
function carousel() {
    return {
        isDown: false,
        startX: 0,
        scrollLeftStart: 0,
        scrollLeft() { this.$refs.container.scrollBy({ left: -300, behavior: 'smooth' }); },
        scrollRight() { this.$refs.container.scrollBy({ left: 300, behavior: 'smooth' }); },
        startDrag(e) {
            this.isDown = true;
            this.startX = e.pageX - this.$refs.container.offsetLeft;
            this.scrollLeftStart = this.$refs.container.scrollLeft;
        },
        endDrag() { this.isDown = false; },
        drag(e) {
            if (!this.isDown) return;
            e.preventDefault();
            const x = e.pageX - this.$refs.container.offsetLeft;
            this.$refs.container.scrollLeft = this.scrollLeftStart - (x - this.startX) * 2;
        },
        startTouch(e) { this.startX = e.touches[0].pageX; this.scrollLeftStart = this.$refs.container.scrollLeft; },
        dragTouch(e) {
            const x = e.touches[0].pageX;
            this.$refs.container.scrollLeft = this.scrollLeftStart - (x - this.startX) * 2;
        }
    }
}
</script>
