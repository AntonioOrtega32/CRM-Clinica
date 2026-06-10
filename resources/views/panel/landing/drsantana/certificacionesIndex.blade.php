<section id="certificaciones" class="py-10 px-6 bg-gray-50 mb-12"
    x-data="{ openCreate: false, editId: null }">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Título y botón Crear -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-[#1C6C73]">Certificaciones Dr. Santana</h1>
            <button @click="openCreate = true"
                class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealClaro transition">
                Crear Certificación
            </button>
        </div>

        @if ($certificaciones->isEmpty())
            <p>No hay certificaciones disponibles.</p>
        @else
            <!-- Contenedor del carrusel -->
            <div class="relative">
                <!-- Botón Izquierda -->
                <button onclick="CerscrollLeft()"
                    class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-[#1C6C73] text-white p-2 rounded-full shadow hover:bg-tealOscuro z-10">
                    ‹
                </button>

                <!-- Scroll horizontal -->
                <div id="CerscrollContainer"
                    class="flex overflow-x-auto space-x-6 scrollbar-hide scroll-smooth cursor-grab active:cursor-grabbing select-none">
                    @foreach ($certificaciones as $cert)
                        <div
                            class="min-w-[300px] bg-beigeNeutro shadow-lg rounded-lg p-6 flex justify-between items-center hover:shadow-xl transition-shadow">
                            <div>
                                <h2 class="text-xl font-bold text-tealOscuro">{{ $cert->titulo }}</h2>
                                <p class="text-tealOscuro mt-2">{{ $cert->descripcion }}</p>
                            </div>
                            <div class="flex flex-col items-end space-y-2">
                                <div class="w-20 h-20 rounded-lg flex items-center justify-center bg-white shadow-lg">
                                    @if ($cert->imagen)
                                        <img src="{{ asset($cert->imagen) }}" alt="imagen"
                                            class="w-12 h-12 object-contain">
                                    @else
                                        <span class="text-gray-400">Sin imagen</span>
                                    @endif
                                </div>
                                <button @click="editId = {{ $cert->id }}"
                                    class="bg-[#1C6C73] text-white px-3 py-1 rounded hover:bg-tealOscuro text-sm">
                                    Editar
                                </button>
                            </div>
                        </div>

                        <!-- Modal Editar -->
                        <div x-show="editId === {{ $cert->id }}" x-cloak @click.self="editId = null"
                            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                            <div class="bg-white rounded-lg w-96 p-6 relative">
                                <button @click="editId = null"
                                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>

                                <h2 class="text-xl font-bold mb-4">Editar Certificación</h2>
                                <form action="{{ route('panel.certificaciones.update', $cert->id) }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <label class="block mb-2">Título</label>
                                    <input type="text" name="titulo" value="{{ $cert->titulo }}"
                                        class="w-full border rounded p-2 mb-4" required>

                                    <label class="block mb-2">Descripción</label>
                                    <textarea name="descripcion" class="w-full border rounded p-2 mb-4" required>{{ $cert->descripcion }}</textarea>

                                    <label class="block mb-2">Imagen</label>
                                    <input type="file" name="imagen" accept="image/*" class="mt-1">

                                    <button type="submit"
                                        class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealOscuro mt-4">Guardar
                                        cambios</button>
                                </form>

                                <form action="{{ route('panel.certificaciones.destroy', $cert->id) }}" method="POST"
                                    onsubmit="return confirm('¿Estás seguro de eliminar esta certificación?');">
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
                <button onclick="CerscrollRight()"
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

                <h2 class="text-xl font-bold mb-4">Crear Certificación</h2>
                <form action="{{ route('panel.certificaciones.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <label class="block mb-2">Título</label>
                    <input type="text" name="titulo" class="w-full border rounded p-2 mb-4" required>

                    <label class="block mb-2">Descripción</label>
                    <textarea name="descripcion" class="w-full border rounded p-2 mb-4" required></textarea>

                    <label class="block mb-2">Imagen</label>
                    <input type="file" name="imagen" accept="image/*" class="mt-1">

                    <button type="submit"
                        class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealClaro mt-4">Agregar</button>
                </form>
            </div>
        </div>
    </div>

    <!-- JS para scroll y arrastre -->
    <script>
        const cerContainer = document.getElementById('CerscrollContainer');

        // Botones
        function CerscrollLeft() {
            cerContainer.scrollBy({ left: -350, behavior: 'smooth' });
        }
        function CerscrollRight() {
            cerContainer.scrollBy({ left: 350, behavior: 'smooth' });
        }

        // Drag-to-scroll (mouse y touch)
        let isDown = false;
        let startX;
        let scrollLeft;

        cerContainer.addEventListener('mousedown', (e) => {
            isDown = true;
            cerContainer.classList.add('active');
            startX = e.pageX - cerContainer.offsetLeft;
            scrollLeft = cerContainer.scrollLeft;
        });
        cerContainer.addEventListener('mouseleave', () => {
            isDown = false;
        });
        cerContainer.addEventListener('mouseup', () => {
            isDown = false;
        });
        cerContainer.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - cerContainer.offsetLeft;
            const walk = (x - startX) * 1.2; // sensibilidad
            cerContainer.scrollLeft = scrollLeft - walk;
        });

        // Soporte táctil
        let touchStartX = 0;
        cerContainer.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].clientX;
            scrollLeft = cerContainer.scrollLeft;
        });
        cerContainer.addEventListener('touchmove', (e) => {
            const touchX = e.touches[0].clientX;
            const walk = (touchX - touchStartX) * 1.2;
            cerContainer.scrollLeft = scrollLeft - walk;
        });
    </script>
</section>
