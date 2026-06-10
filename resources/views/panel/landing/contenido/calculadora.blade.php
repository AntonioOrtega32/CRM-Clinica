<section id="calculadora" class="py-10 px-2 bg-gradient-to-r from-gray-100 via-white to-gray-100">

<main class="flex-1 overflow-y-auto p-6 bg-gray-50" x-data="{ openCreate: false, editId: null }">

        <div class="max-w-6xl mx-auto px-6">

            <!-- Título y botón Crear -->
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-tealOscuro">Nuestros Resultados</h1>
                <button @click="openCreate = true"
                    class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealClaro transition">
                    Crear Resultado
                </button>
            </div>

            <!-- Grid de resultados -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($resultados as $resultado)
                    <div
                        class="bg-beigeNeutro shadow-lg rounded-lg p-6 flex justify-between items-center hover:shadow-xl transition-shadow">
                        <div>
                            <h2 class="text-xl font-bold text-tealOscuro">{{ $resultado->titulo }}</h2>
                            <p class="text-tealOscuro mt-2">Número: {{ number_format($resultado->numero) }}</p>
                            <p class="mt-1 text-sm">Color: <span class="inline-block w-4 h-4 rounded"
                                    style="background-color: {{ $resultado->color }}"></span></p>
                        </div>
                        <div class="flex flex-col items-end space-y-2">
                             <div class="w-20 h-20 rounded-lg flex items-center justify-center bg-white shadow-lg">
          @if($resultado->icono_svg)
            <img src="{{ asset($resultado->icono_svg) }}" alt="Icono" class="w-12 h-12 object-contain">
          @else
            <span class="text-gray-400">Sin imagen</span>
          @endif
        </div>
                            <button @click="editId = {{ $resultado->id }}"
                                class="bg-[#1C6C73] text-white px-3 py-1 rounded hover:bg-tealOscuro text-sm">
                                Editar
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Modal Crear -->
            <div x-show="openCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="bg-white rounded-lg w-96 p-6 relative">
                    <button @click="openCreate = false"
                        class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
                    <h2 class="text-xl font-bold mb-4">Crear Resultado</h2>
                    <form action="{{ route('panel.landing.resultado.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <label class="block mb-2">Título</label>
                        <input type="text" name="titulo" class="w-full border rounded p-2 mb-4" required>
                        <label class="block mb-2">Número</label>
                        <input type="number" name="numero" class="w-full border rounded p-2 mb-4" required>
                        <label class="block mb-2">Color Representativo</label>
                        <input type="color" name="color" class="w-full h-10 mb-4" required>
                        <!-- Subir imagen -->
                        <label class="block mt-2">Imagen / Icono</label>
                        <input type="file" name="icono_svg" accept="image/*,image/svg+xml" class="mt-1">

                        <button type="submit"
                            class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealClaro">Crear</button>
                    </form>
                </div>
            </div>

            <!-- Modal Editar -->
            @foreach ($resultados as $resultado)
                <div x-show="editId === {{ $resultado->id }}" x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div class="bg-white rounded-lg w-96 p-6 relative">
                        <button @click="editId = null"
                            class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
                        <h2 class="text-xl font-bold mb-4">Editar Resultado</h2>
                        <form action="{{ route('panel.landing.resultado.update', $resultado->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <label class="block mb-2">Título</label>
                            <input type="text" name="titulo" value="{{ $resultado->titulo }}"
                                class="w-full border rounded p-2 mb-4">
                            <label class="block mb-2">Número</label>
                            <input type="number" name="numero" value="{{ $resultado->numero }}"
                                class="w-full border rounded p-2 mb-4">
                            <label class="block mb-2">Color Representativo</label>
                            <input type="color" name="color" value="{{ $resultado->color }}" class="w-full h-10 mb-4">
                            <!-- Subir imagen -->
                            <!-- Subir imagen -->
                            <label class="block mt-2">Imagen / Icono</label>
                            <input type="file" name="icono_svg" accept="image/*,image/svg+xml" class="mt-1">

                            <button type="submit"
                                class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealOscuro">Guardar
                                Cambios</button>
                        </form>
                    </div>
                </div>
            @endforeach

        </div>
    </main>
</section>
