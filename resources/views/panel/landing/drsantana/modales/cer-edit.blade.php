 @foreach ($certificaciones as $cert)
     <div class="min-w-[300px] bg-beigeNeutro shadow-lg rounded-lg p-6">
         <h2 class="text-xl font-bold text-tealOscuro">{{ $cert->titulo }}</h2>
         <p class="text-tealOscuro mt-2">{{ $cert->descripcion }}</p>

         <div class="mt-3">
             @if ($cert->imagen)
                 <img src="{{ asset($cert->imagen) }}" alt="imagen" class="w-16 h-16 object-cover">
             @endif
         </div>

         <button onclick="openModal('editModal{{ $cert->id }}')"
             class="bg-[#1C6C73] text-white px-3 py-1 rounded hover:bg-tealOscuro text-sm mt-2">
             Editar
         </button>
     </div>

     <!-- Modal Editar -->
     <div id="editModal{{ $cert->id }}"
         class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
         <div class="bg-white rounded-lg w-96 p-6 relative">
             <button onclick="closeModal('editModal{{ $cert->id }}')"
                 class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>

             <h2 class="text-xl font-bold mb-4">Editar Certificación</h2>
             <form action="{{ route('certificaciones.update', $cert->id) }}" method="POST"
                 enctype="multipart/form-data">
                 @csrf
                 @method('PUT')
                 <label class="block mb-2">Título</label>
                 <input type="text" name="titulo" value="{{ $cert->titulo }}"
                     class="w-full border rounded p-2 mb-4">

                 <label class="block mb-2">Descripción</label>
                 <textarea name="descripcion" class="w-full border rounded p-2 mb-4">{{ $cert->descripcion }}</textarea>

                 <label class="block mt-2">Imagen</label>
                 <input type="file" name="imagen" accept="image/*" class="mt-1">

                 <br><br>
                 <button type="submit" class="bg-[#1C6C73] text-white px-4 py-2 rounded hover:bg-tealOscuro">Guardar
                     cambios</button>
             </form>
             <form action="{{ route('certificaciones.destroy', $cert->id) }}" method="POST"
                 onsubmit="return confirm('¿Estás seguro de eliminar esta certificación?');">
                 @csrf
                 @method('DELETE')
                 <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-800 text-sm mt-4">
                     Eliminar
                 </button>
             </form>
         </div>
     </div>
 @endforeach
