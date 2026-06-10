<section id="encabezado" class="py-0 px-6 bg-gradient-to-r from-gray-100 via-white to-gray-100">
    <main class="flex-1 overflow-y-auto p-6 bg-gray-50" 
          x-data="{ openCreate: false, editId: null }">

        <!-- Título y botón Crear -->
        <div class="flex justify-between items-center mb-9">
            <h1 class="text-3xl font-bold text-tealOscuro">Conócenos</h1>
              @if ($encabezados->count() < 1)
            <button @click="openCreate = true"
                class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealClaro transition">
                Crear encabezado 
            </button> 
                @else
            <button @click="openCreate = true"
                class="bg-[#4298A7] text-white px-4 py-2 rounded hover:bg-tealClaro transition" disabled>
                Crear encabezado 
            </button>
                @endif
        </div>
        


        @if ($encabezados->isEmpty())
            <p>No hay encabezados disponibles.</p>
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
                    @mousedown="startDrag($event)" 
                    @mouseup="endDrag" 
                    @mouseleave="endDrag" 
                    @mousemove="drag($event)"
                    @touchstart="startTouch($event)" 
                    @touchmove="dragTouch($event)">
                    
                    @foreach ($encabezados as $enca)
                        <div class="min-w-[300px] bg-beigeNeutro shadow-lg rounded-lg p-6 flex justify-between items-center hover:shadow-xl transition-shadow">
                            <div>
                                <!--<h2 class="text-xl font-bold text-tealOscuro">{{ $enca->titulo }}</h2>
                                <p class="text-tealOscuro mt-2">{{ $enca->subtitulo }}</p>-->
                            </div>
                            <div class="flex flex-col items-end space-y-2">
                                <h2 class="text-xl font-bold text-tealOscuro">Imagenes/Videos del encabezado</h2>
                                <div class="w-20 h-20 rounded-lg flex items-center justify-center bg-white shadow-lg">
                                    @if($enca->video_horizontal)
                                        <img src="{{ asset($enca->video_horizontal) }}" alt="video" class="w-12 h-12 object-contain">
                                    @else
                                        <span class="text-gray-400">Sin video</span>
                                    @endif
                                </div>
                                 <div class="w-20 h-20 rounded-lg flex items-center justify-center bg-white shadow-lg">
                                    @if($enca->video_vertical)
                                        <img src="{{ asset($enca->video_vertical) }}" alt="video" class="w-12 h-12 object-contain">
                                    @else
                                        <span class="text-gray-400">Sin video</span>
                                    @endif
                                </div>
                                <button @click="editId = {{ $enca->id }}"
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
            <div class="bg-white rounded-lg w-96 p-6 relative">
                <button @click="openCreate = false"
                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
                <h2 class="text-xl font-bold mb-4">Crear Encabezado</h2>
                <form action="{{ route('panel.landing.encabezado.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <!--<label class="block mb-2">Contenido del texto pequeño superior</label>
                    <input type="text" name="titulo" class="w-full border rounded p-2 mb-4" required>
                    <label class="block mb-2">Contenido del texto resaltado</label>
                    <input type="text" name="contenido" class="w-full border rounded p-2 mb-4" required>
                    <label class="block mb-2">Contenido del texto pequeño inferior</label>
                    <input type="text" name="subtitulo" class="w-full border rounded p-2 mb-4" required>
                    <label class="block mt-2">Imagen</label>
                    <input type="file" name="imagen" accept="image/*" class="mt-1">-->
                    <label class="block mt-2">Video/Imagen Horizontal (PC)</label>
                    <input type="file" name="video_horizontal" class="mt-1">

                    <label class="block mt-2">Video/Imagen Vertical (Móvil)</label>
                    <input type="file" name="video_vertical"  class="mt-1">

                    <br><br>
                    <button type="submit"
                        class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealClaro">Agregar</button>
                </form>
            </div>
        </div>

        <!-- Modal Editar -->
@foreach ($encabezados as $enca)
    <div x-show="editId === {{ $enca->id }}" x-cloak @click.self="editId = null"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-lg w-96 p-6 relative max-h-[90vh] overflow-auto">
            <button @click="editId = null"
                class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
            <h2 class="text-xl font-bold mb-4">Editar Encabezado</h2>

            <form action="{{ route('panel.landing.encabezado.update', $enca->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Título 
                <label class="block mb-2">Título</label>
                <input type="text" name="titulo" value="{{ $enca->titulo }}"
                    class="w-full border rounded p-2 mb-4">-->

                <!-- Subtítulo
                <label class="block mb-2">Subtítulo</label>
                <input type="text" name="subtitulo" value="{{ $enca->subtitulo }}"
                    class="w-full border rounded p-2 mb-4"> -->

                <!-- Contenido (texto medio) 
                <label class="block mb-2">Contenido</label>
                <input type="text" name="contenido" value="{{ $enca->contenido }}"
                    class="w-full border rounded p-2 mb-4">-->

                <!-- Video Horizontal -->
                <label class="block mb-2">Video/imagen Horizontal</label>
                <input type="file" name="video_horizontal" class="w-full mb-4">
                @if($enca->video_horizontal)
                    <img src="{{ asset($enca->video_horizontal) }}" class="w-full mb-4"></img>
                @endif

                <!-- Video Vertical -->
                <label class="block mb-2">Video/imagen Vertical</label>
                <input type="file" name="video_vertical" class="w-full mb-4">
                @if($enca->video_vertical)
                    <img src="{{ asset($enca->video_vertical) }}" class="w-full mb-4"></img>
                @endif

                <!-- Imagen
                <label class="block mb-2">Imagen</label>
                <input type="file" name="imagen" accept="image/*,image/svg+xml" class="w-full mb-4">
                @if($enca->imagen)
                    <img src="{{ asset($enca->imagen) }}" alt="Imagen actual" class="w-full mb-4">
                @endif
                    -->
                <!-- Botón Guardar -->
                <button type="submit"
                    class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealOscuro w-full">Guardar Cambios</button>
            </form>

            <!-- Botón Borrar -->
            <form action="{{ route('panel.landing.encabezado.destroy', $enca->id) }}" method="POST"
                onsubmit="return confirm('¿Estás seguro de que deseas eliminar este encabezado?');" class="mt-4">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-[#ff1616] text-white px-3 py-1 rounded hover:bg-red-700 w-full text-sm">
                    Borrar
                </button>
            </form>
        </div>
    </div>
@endforeach

    </main>
</section>

<!-- Alpine Component -->
<script>
function carousel() {
    return {
        isDown: false,
        startX: 0,
        scrollLeftStart: 0,

        scrollLeft() {
            this.$refs.container.scrollBy({ left: -350, behavior: 'smooth' });
        },
        scrollRight() {
            this.$refs.container.scrollBy({ left: 350, behavior: 'smooth' });
        },
        startDrag(e) {
            this.isDown = true;
            this.startX = e.pageX - this.$refs.container.offsetLeft;
            this.scrollLeftStart = this.$refs.container.scrollLeft;
        },
        endDrag() {
            this.isDown = false;
        },
        drag(e) {
            if (!this.isDown) return;
            e.preventDefault();
            const x = e.pageX - this.$refs.container.offsetLeft;
            const walk = (x - this.startX) * 2;
            this.$refs.container.scrollLeft = this.scrollLeftStart - walk;
        },
        startTouch(e) {
            this.startX = e.touches[0].pageX;
            this.scrollLeftStart = this.$refs.container.scrollLeft;
        },
        dragTouch(e) {
            const x = e.touches[0].pageX;
            const walk = (x - this.startX) * 2;
            this.$refs.container.scrollLeft = this.scrollLeftStart - walk;
        }
    }
}
</script>
