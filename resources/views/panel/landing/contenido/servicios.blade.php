<section id="servicios" class="py-0 px-6 bg-gradient-to-r from-gray-100 via-white to-gray-100">
    <main class="flex-1 overflow-y-auto p-6 bg-gray-50" x-data="{ openCreate: false, editId: null }">

        <div class="max-w-6xl mx-auto px-6">

            <!-- Título y botón Crear -->
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-tealOscuro">Servicios</h1>
                <button @click="openCreate = true"
                    class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealClaro transition">
                    Crear Servicio
                </button>
            </div>

            @if ($servicios->isEmpty())
                <p>No hay servicios disponibles.</p>
            @else
                <!-- Contenedor del carrusel -->
                <div x-data="carousel()" class="relative">

                    <!-- Botón Izquierda -->
                    <button @click="scrollLeft"
                        class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-[#1C6C73] text-white p-2 rounded-full shadow hover:bg-tealOscuro z-10">
                        ‹
                    </button>

                    <!-- Scroll horizontal -->
                    <div x-ref="container"
                        class="flex overflow-x-auto space-x-6 scrollbar-hide scroll-smooth cursor-grab active:cursor-grabbing select-none"
                        @mousedown="startDrag($event)" @mouseup="endDrag" @mouseleave="endDrag" @mousemove="drag($event)"
                        @touchstart="startTouch($event)" @touchmove="dragTouch($event)">
                        
                        @foreach ($servicios as $serv)
                            <div class="min-w-[350px] bg-beigeNeutro shadow-lg rounded-lg p-6 hover:shadow-xl transition-shadow">
                                <h2 class="text-xl font-bold text-tealOscuro">{{ $serv->titulo }}</h2>
                                <p class="text-gray-700 mb-4 text-justify mt-2 line-clamp-4">{{ $serv->detalle }}</p>
                                <p class="text-gray-500 text-justify mt-2 line-clamp-4">{{ $serv->descripcion }}</p>
                                <div class="text-right mt-4 space-y-2">
                                    <div class="w-20 h-20 rounded-lg flex items-center justify-center bg-white shadow-lg mx-auto">
                                        @if($serv->imagen)
                                            <img src="{{ asset($serv->imagen) }}" alt="imagen" class="w-12 h-12 object-contain">
                                        @else
                                            <span class="text-gray-400">Sin imagen</span>
                                        @endif
                                    </div>
                                    <button @click="editId = {{ $serv->id }}"
                                        class="bg-[#1C6C73] text-white px-3 py-1 rounded hover:bg-tealOscuro text-sm">
                                        Editar
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Botón Derecha -->
                    <button @click="scrollRight"
                        class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-[#1C6C73] text-white p-2 rounded-full shadow hover:bg-tealOscuro z-10">
                        ›
                    </button>
                </div>
            @endif

            <!-- Modal Crear -->
            <div x-show="openCreate" x-cloak @click.self="openCreate = false"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="relative bg-white rounded-lg w-96 max-h-[80vh] p-6 overflow-y-auto">
                    <button @click="openCreate = false"
                        class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
                    <h2 class="text-xl font-bold mb-4">Crear servicio</h2>
                    <form action="{{ route('panel.landing.servicios.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label class="block mb-2">Título</label>
                        <input type="text" name="titulo" class="w-full border rounded p-2 mb-4" required>
                        <label class="block mb-2">SubTítulo</label>
                        <input type="text" name="detalle" class="w-full border rounded p-2 mb-4" required>
                        <label class="block mb-2">Contenido</label>
                        <textarea name="descripcion" class="w-full border rounded p-2 mb-4 resize-y" required></textarea>
                        <label class="block mt-2">Imagen</label>
                        <input type="file" name="imagen" accept="image/*" class="mt-1">
                        <br><br>
                        <button type="submit" class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealClaro">Agregar</button>
                    </form>
                </div>
            </div>

            <!-- Modal Editar -->
            @foreach ($servicios as $serv)
                <div x-show="editId === {{ $serv->id }}" x-cloak @click.self="editId = null"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div class="relative bg-white rounded-lg w-96 max-h-[80vh] p-6 overflow-y-auto">
                        <button @click="editId = null" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
                        <h2 class="text-xl font-bold mb-4">Editar servicio</h2>
                        <form action="{{ route('panel.landing.servicios.update', $serv->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <label class="block mb-2">Título</label>
                            <input type="text" name="titulo" value="{{ $serv->titulo }}" class="w-full border rounded p-2 mb-4">
                            <label class="block mb-2">SubTítulo</label>
                            <input type="text" name="detalle" value="{{ $serv->detalle }}" class="w-full border rounded p-2 mb-4">
                            <label class="block mb-2">Contenido</label>
                            <textarea name="descripcion" class="w-full h-40 border rounded p-2 mb-4 resize-y">{{ $serv->descripcion }}</textarea>
                            <label class="block mt-2">Imagen</label>
                            <input type="file" name="imagen" accept="image/*,image/svg+xml" class="mt-1">
                            <br><br>
                            <button type="submit" class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealOscuro">Guardar Cambios</button>
                        </form>
                        <form action="{{ route('panel.landing.servicios.destroy', $serv->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este servicio?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-[#ff1616] text-white px-3 py-1 rounded hover:bg-tealOscuro text-sm mt-4">Borrar</button>
                        </form>
                    </div>
                </div>
            @endforeach

        </div>
    </main>
</section>

<!-- Alpine Component para el carrusel -->
<script>
function carousel() {
    return {
        isDown: false,
        startX: 0,
        scrollLeftStart: 0,
        scrollLeft() { this.$refs.container.scrollBy({ left: -350, behavior: 'smooth' }); },
        scrollRight() { this.$refs.container.scrollBy({ left: 350, behavior: 'smooth' }); },
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

<style>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
