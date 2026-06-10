<section id="blogdr" class="py-10 px-6 bg-gray-50 mb-12"
    x-data="{ openCreate: false, editId: null }">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Título y botón Crear -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-[#1C6C73]">Blog Dr. Santana</h1>
            <button @click="openCreate = true"
                class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealClaro transition">
                Crear Entrada
            </button>
        </div>

        @if ($blogs->isEmpty())
            <p>No hay entradas disponibles.</p>
        @else
            <!-- Contenedor del carrusel -->
            <div class="relative">
                <!-- Botón Izquierda -->
                <button onclick="BlogScrollLeft()"
                    class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-[#1C6C73] text-white p-2 rounded-full shadow hover:bg-tealOscuro z-10">
                    ‹
                </button>

                <!-- Scroll horizontal -->
                <div id="BlogScrollContainer"
                    class="flex overflow-x-auto space-x-6 scrollbar-hide scroll-smooth cursor-grab active:cursor-grabbing select-none">
                    @foreach ($blogs as $blog)
                        <div
                            class="min-w-[300px] bg-white shadow-lg rounded-lg p-6 hover:shadow-xl transition-shadow border border-gray-100">
                            
                            <!-- Imagen -->
                            <div class="w-20 h-20 rounded-lg flex items-center justify-center bg-gray-50 mb-3">
                                @if ($blog->imagen)
                                    <img src="{{ asset($blog->imagen) }}" alt="imagen"
                                        class="w-16 h-16 object-contain rounded">
                                @else
                                    <span class="text-gray-400">Sin imagen</span>
                                @endif
                            </div>

                            <h2 class="text-xl font-bold text-[#1C6C73]">{{ $blog->titulo }}</h2>
                            <p class="text-gray-600 text-sm mt-1">📅 {{ $blog->fecha }}</p>
                            <p class="text-gray-700 text-sm mt-3 line-clamp-4">
                                {{ strip_tags($blog->contenido) }}
                            </p>

                            <div class="text-right mt-4">
                                <button @click="editId = {{ $blog->id }}"
                                    class="bg-[#1C6C73] text-white px-3 py-1 rounded hover:bg-tealOscuro text-sm">
                                    Editar
                                </button>
                            </div>
                        </div>

                        <!-- Modal Editar -->
                        <div x-show="editId === {{ $blog->id }}" x-cloak @click.self="editId = null"
                            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                            <div class="bg-white rounded-lg w-96 p-6 relative">
                                <button @click="editId = null"
                                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>

                                <h2 class="text-xl font-bold mb-4">Editar Entrada</h2>
                                <form action="{{ route('panel.drsantana.blog.update', $blog->id) }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <label class="block mb-2">Título</label>
                                    <input type="text" name="titulo" value="{{ $blog->titulo }}"
                                        class="w-full border rounded p-2 mb-4" required>

                                    <label class="block mb-2">Fecha</label>
                                    <input type="date" name="fecha" value="{{ $blog->fecha }}"
                                        class="w-full border rounded p-2 mb-4" required>

                                    <label class="block mb-2">Contenido</label>
                                    <textarea name="contenido" class="w-full border rounded p-2 mb-4" required>{{ $blog->contenido }}</textarea>

                                    <label class="block mb-2">Enlace (YouTube, TikTok, Instagram)</label>
                                    <input type="url" name="link" value="{{ $blog->link ?? '' }}"
                                        class="w-full border rounded p-2 mb-4" placeholder="https://">


                                    <label class="block mb-2">Imagen</label>
                                    <input type="file" name="imagen" accept="image/*" class="mt-1">

                                    <button type="submit"
                                        class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealOscuro mt-4 w-full">
                                        Guardar cambios
                                    </button>
                                </form>

                                <form action="{{ route('panel.drsantana.blog.destroy', $blog->id) }}" method="POST"
                                    onsubmit="return confirm('¿Seguro que deseas eliminar esta entrada?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-800 mt-4 w-full">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Botón Derecha -->
                <button onclick="BlogScrollRight()"
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

                <h2 class="text-xl font-bold mb-4">Crear Entrada</h2>
                <form action="{{ route('panel.drsantana.blog.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <label class="block mb-2">Título</label>
                    <input type="text" name="titulo" class="w-full border rounded p-2 mb-4" required>

                    <label class="block mb-2">Fecha</label>
                    <input type="date" name="fecha" class="w-full border rounded p-2 mb-4" required>

                    <label class="block mb-2">Contenido</label>
                    <textarea name="contenido" class="w-full border rounded p-2 mb-4" required></textarea>

                    <label class="block mb-2">Enlace (YouTube, TikTok, Instagram)</label>
                    <input type="url" name="link" 
                        class="w-full border rounded p-2 mb-4" placeholder="https://">

                    
                    <label class="block mb-2">Imagen</label>
                    <input type="file" name="imagen" accept="image/*" class="mt-1">

                    <button type="submit"
                        class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealClaro mt-4 w-full">Agregar</button>
                </form>
            </div>
        </div>
    </div>

    <!-- JS para scroll y arrastre -->
    <script>
        const blogContainer = document.getElementById('BlogScrollContainer');

        function BlogScrollLeft() {
            blogContainer.scrollBy({ left: -350, behavior: 'smooth' });
        }
        function BlogScrollRight() {
            blogContainer.scrollBy({ left: 350, behavior: 'smooth' });
        }

        let isDownBlog = false;
        let startXBlog;
        let scrollLeftBlog;

        blogContainer.addEventListener('mousedown', (e) => {
            isDownBlog = true;
            startXBlog = e.pageX - blogContainer.offsetLeft;
            scrollLeftBlog = blogContainer.scrollLeft;
        });
        blogContainer.addEventListener('mouseleave', () => isDownBlog = false);
        blogContainer.addEventListener('mouseup', () => isDownBlog = false);
        blogContainer.addEventListener('mousemove', (e) => {
            if (!isDownBlog) return;
            e.preventDefault();
            const x = e.pageX - blogContainer.offsetLeft;
            const walk = (x - startXBlog) * 1.2;
            blogContainer.scrollLeft = scrollLeftBlog - walk;
        });

        let touchStartXBlog = 0;
        blogContainer.addEventListener('touchstart', (e) => {
            touchStartXBlog = e.touches[0].clientX;
            scrollLeftBlog = blogContainer.scrollLeft;
        });
        blogContainer.addEventListener('touchmove', (e) => {
            const x = e.touches[0].clientX;
            const walk = (x - touchStartXBlog) * 1.2;
            blogContainer.scrollLeft = scrollLeftBlog - walk;
        });
    </script>
</section>

<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    .line-clamp-4 {
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
