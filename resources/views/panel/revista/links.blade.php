@extends('panel.layouts.panel')
@section('title','Links Revista')

@section('content')
<section class="p-6 space-y-4 bg-white rounded shadow ">
  <h1 class="text-2xl font-[Poppins] text-[#1C6C73]">Generar link de Revista Digital</h1>

  <form method="POST" action="{{ route('panel.revista.links.generate') }}" class="flex gap-3 items-end">
    @csrf
    <div>
      <label class="block text-sm font-medium">Duración (horas)</label>
      <input type="number" name="hours" value="24" min="1" max="720"
            class="border rounded px-3 py-2 w-40">
    </div>

    <button class="bg-[#1C6C73] text-white px-4 py-2 rounded">
      Generar link
    </button>
  </form>

  @if(session('link'))
    <div class="bg-gray-50 border rounded p-4">
      <p class="text-sm text-gray-600 mb-2">Copia y manda este link:</p>

      <div class="flex gap-2">
        <input id="revistaLink"
              class="border rounded px-3 py-2 w-full"
              readonly
              value="{{ session('link') }}">

        <button type="button"
                id="btnCopyLink"
                class="border rounded px-3 py-2 bg-[#CDAF95] text-white">
          Copiar
        </button>
      </div>

      <p id="copyMsg" class="text-sm text-green-700 mt-2 hidden">
        <i class="fa fa-check text-[#1C6C73]" aria-hidden="true"></i> Copiado</p>
    </div>
  @endif


  <br>

<h4 class="text-2xl font-[Poppins] mt-6 text-[#1C6C73]">Revista Actual: </h4>

@if(session('success'))
<script>
Swal.fire({
  icon: 'success',
  title: 'Listo',
  text: @json(session('success')),
  timer: 1800,
  showConfirmButton: false
});
</script>
@endif


@if($exists)
  <div class="bg-gray-50 border rounded p-4 space-y-3">
    <p class="text-gray-700">
      <i class="fa fa-check-circle text-[#1C6C73]" aria-hidden="true"></i> Hay una revista cargada.
      @if($updatedAt)
        <span class="text-sm text-gray-500">(Actualizada: {{ $updatedAt->format('d/m/Y H:i') }})</span>
      @endif
    </p>

    {{-- Reemplazar --}}
    <form method="POST" action="{{ route('panel.revista.upload') }}" enctype="multipart/form-data" class="flex gap-3 items-end flex-wrap">
      @csrf
      <div>
        <label class="block text-sm font-medium">Reemplazar PDF</label>
        <input type="file" name="revista" accept="application/pdf" class="border rounded px-3 py-2">
        @error('revista')
          <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
        @enderror
      </div>

      <button class="bg-[#4298A7] hover:bg-[#84bec9] text-white px-4 py-2 rounded" disabled>
        Subir
      </button>
    </form>

    {{-- Eliminar --}}
    <form method="POST" action="{{ route('panel.revista.destroy') }}"
          onsubmit="return confirm('¿Eliminar la revista actual?')">
      @csrf
      @method('DELETE')
      <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
        Eliminar revista
      </button>
    </form>
  </div>
@else
  <div class="bg-yellow-50 border rounded p-4 space-y-3">
    <p class="text-gray-700">  
    <i class="fa fa-exclamation-triangle text-[#FF7014]" aria-hidden="true"></i> No hay revista cargada. Sube el PDF para habilitar el visor, el archivo no debe ser mayor a 20MB!.</p>

    <form method="POST" action="{{ route('panel.revista.upload') }}" enctype="multipart/form-data" class="flex gap-3 items-end flex-wrap">
      @csrf
      <div>
        <label class="block text-sm font-medium">Subir PDF</label>
        <input type="file" name="revista" accept="application/pdf" class="border rounded px-3 py-2">
        @error('revista')
          <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
        @enderror
      </div>

      <button class="bg-[#1C6C73] text-white px-4 py-2 rounded">
        Subir revista
      </button>
    </form>
  </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('revistaLink');
  const btn = document.getElementById('btnCopyLink');
  const msg = document.getElementById('copyMsg');

  if (!input || !btn) return;

  btn.addEventListener('click', async () => {
    const text = input.value;

    try {
      // Moderno (requiere https / permisos)
      await navigator.clipboard.writeText(text);
    } catch (e) {
      // Fallback (funciona casi siempre)
      input.focus();
      input.select();
      input.setSelectionRange(0, 999999);
      document.execCommand('copy');
      window.getSelection?.().removeAllRanges?.();
    }

    if (msg) {
      msg.classList.remove('hidden');
      setTimeout(() => msg.classList.add('hidden'), 1200);
    }
  });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const uploadForms = document.querySelectorAll('.revista-upload-form');

  uploadForms.forEach(form => {
    form.addEventListener('submit', () => {
      Swal.fire({
        title: 'Subiendo revista',
        text: 'Por favor espera, no cierres esta ventana…',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
    });
  });
});
</script>


</section>
@endsection
