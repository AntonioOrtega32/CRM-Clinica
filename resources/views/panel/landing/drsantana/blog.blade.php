<section id="blog" class="py-0 px-6 bg-gradient-to-r from-gray-100 via-white to-gray-100">

<main class="flex-1 overflow-y-auto p-6 bg-gray-50" x-data="{ openCreate: false, editId: null }">

    <div class="max-w-6xl mx-auto px-6">

        <!-- Título y botón Crear -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-tealOscuro">Blog - Dr. Santana</h1>
            <button @click="openCreate = true"
                class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealClaro transition">
                Crear Artículo
            </button>
        </div>

        @if ($blogs->isEmpty())
            <p>No hay artículos disponibles.</p>
        @else
            <!-- Contenedor del carrusel -->
            <div class="relative">

                <!-- Botón Izquierda -->
                <button onclick="scrollLeftBlog()"
                    class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-[#1C6C73] text-white p-2 rounded-full shadow hover:bg-tealOscuro z-10">
                    ‹
                </button>

                <!-- Scroll horizontal -->
                <div id="blogScrollContainer"
                    class="flex overflow-x-auto space-x-6 scrollbar-hide scroll-smooth cursor-grab active:cursor-grabbing select-none">

                    @foreach ($blogs as $blog)
                        <div class="min-w-[350px] bg-beigeNeutro shadow-lg rounded-lg p-6 hover:shadow-xl transition-shadow">
                            <h2 class="text-xl font-bold text-tealOscuro">{{ $blog->titulo }}</h2>
                            <p class="text-gray-500 text-sm mt-1">Publicado: {{ $blog->fecha }}</p>
                            <p class="text-gray-700 mt-2 line-clamp-3">{{ $blog->contenido }}</p>
                            <div class="text-right mt-4">
                                <div class="w-20 h-20 rounded-lg flex items-center justify-center bg-white shadow-lg mb-2">
                                    @if($blog->imagen)
                                        <img src="{{ asset($blog->imagen) }}" alt="imagen" class="w-12 h-12 object-contain">
                                    @else
                                        <span class="text-gray-400">Sin imagen</span>
                                    @endif
                                </div>
                                <button @click="editId = {{ $blog->id }}"
                                    class="bg-[#1C6C73] text-white px-3 py-1 rounded hover:bg-tealOscuro text-sm">
                                    Editar
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Botón Derecha -->
                <button onclick="scrollRightBlog()"
                    class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-[#1C6C73] text-white p-2 rounded-full shadow hover:bg-tealOscuro z-10">
                    ›
                </button>
            </div>
        @endif

        <!-- Modal Crear -->
        <div x-show="openCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="relative bg-white rounded-lg w-96 max-h-[80vh] p-6 overflow-y-auto">
                <button @click="openCreate = false"
                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
                <h2 class="text-xl font-bold mb-4">Crear Artículo</h2>
                <form action="{{ route('panel.drsantana.blog.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <label class="block mb-2">Título</label>
                    <input type="text" name="titulo" class="w-full border rounded p-2 mb-4" required>
                    <label class="block mb-2">Contenido</label>
                    <textarea name="contenido" class="w-full border rounded p-2 mb-4 resize-y" required></textarea>
                    <label class="block mt-2">Imagen</label>
                    <input type="file" name="imagen" accept="image/*" class="mt-1 mb-4">
                    <button type="submit"
                        class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealClaro">Agregar</button>
                </form>
            </div>
        </div>

        <!-- Modal Editar -->
        @foreach ($blogs as $blog)
            <div x-show="editId === {{ $blog->id }}" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="relative bg-white rounded-lg w-96 max-h-[80vh] p-6 overflow-y-auto">
                    <button @click="editId = null"
                        class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
                    <h2 class="text-xl font-bold mb-4">Editar Artículo</h2>
                    <form action="{{ route('panel.drsantana.blog.update', $blog->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <label class="block mb-2">Título</label>
                        <input type="text" name="titulo" value="{{ $blog->titulo }}" class="w-full border rounded p-2 mb-4">
                        <label class="block mb-2">Contenido</label>
                        <textarea name="contenido" class="w-full h-40 border rounded p-2 mb-4 resize-y">{{ $blog->contenido }}</textarea>
                        <label class="block mt-2">Imagen</label>
                        <input type="file" name="imagen" accept="image/*" class="mt-1 mb-4">
                        <button type="submit"
                            class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealOscuro">Guardar Cambios</button>
                    </form>
                    <form action="{{ route('panel.drsantana.blog.destroy', $blog->id) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar este artículo?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-[#ff1616] text-white px-3 py-1 rounded hover:bg-tealOscuro text-sm mt-4">Borrar</button>
                    </form>
                </div>
            </div>
        @endforeach

    </div>

    <!-- JS para scroll horizontal + arrastre -->
    <script>
        const blogContainer = document.getElementById('blogScrollContainer');

        function scrollLeftBlog() { blogContainer.scrollBy({ left: -350, behavior: 'smooth' }); }
        function scrollRightBlog() { blogContainer.scrollBy({ left: 350, behavior: 'smooth' }); }

        let isDown = false;
        let startX;
        let scrollLeftStart;

        blogContainer.addEventListener('mousedown', e => {
            isDown = true;
            startX = e.pageX - blogContainer.offsetLeft;
            scrollLeftStart = blogContainer.scrollLeft;
        });

        blogContainer.addEventListener('mouseleave', () => isDown = false);
        blogContainer.addEventListener('mouseup', () => isDown = false);
        blogContainer.addEventListener('mousemove', e => {
            if(!isDown) return;
            e.preventDefault();
            const x = e.pageX - blogContainer.offsetLeft;
            const walk = (x - startX) * 2;
            blogContainer.scrollLeft = scrollLeftStart - walk;
        });

        blogContainer.addEventListener('touchstart', e => { startX = e.touches[0].pageX; scrollLeftStart = blogContainer.scrollLeft; });
        blogContainer.addEventListener('touchmove', e => { const x = e.touches[0].pageX; const walk = (x - startX) * 2; blogContainer.scrollLeft = scrollLeftStart - walk; });
    </script>

</main>
</section>
