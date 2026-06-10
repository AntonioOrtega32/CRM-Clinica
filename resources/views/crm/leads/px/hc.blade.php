 <div class="space-y-6">

     {{-- FORMULARIO DE ENFERMEDADES --}}
     <div class="bg-gray-50 p-4 rounded border">
         <h3 class="text-lg font-semibold text-[#1C6C73] mb-3">
             Historia Clínica
         </h3>

        <form id="healthForm" class="space-y-3" action="{{ route('panel.leads.health.save', $lead->id) }}">
             @csrf
             <input type="hidden" name="lead_id" value="{{ $lead->id }}">

             <div>
                 <label class="block text-sm text-gray-600 mb-1">
                     Enfermedades relevantes
                 </label>

                 <input type="text" name="health_conditions" id="health_conditions"
                     class="w-full border rounded px-3 py-2 text-sm"
                     placeholder="Escriba aquí (si no aplica, escriba 'ninguna')">
             </div>

             <div class="flex gap-2">
                 <button type="submit" class="px-4 py-2 bg-[#1C6C73] text-white rounded text-sm">
                     Guardar
                 </button>

                 <button type="button" id="editButton" class="px-4 py-2 bg-gray-400 text-white rounded text-sm hidden">
                     Editar
                 </button>
             </div>
         </form>
         <script>
document.addEventListener('DOMContentLoaded', () => {
  const leadId = {{ (int)$lead->id }};
  const urlGet  = @json(route('panel.leads.health.get', $lead->id));
  const urlSave = @json(route('panel.leads.health.save', $lead->id));
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  const form = document.getElementById('healthForm');
  const input = document.getElementById('health_conditions');
  const editBtn = document.getElementById('editButton');

  function setLocked(locked) {
    input.readOnly = locked;
    input.classList.toggle('bg-gray-100', locked);
    editBtn.classList.toggle('hidden', !locked); // si está locked, muestra Editar
  }

  async function loadHealth() {
    try {
      const res = await fetch(urlGet, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }});
      const data = await res.json();

      if (!res.ok || !data.success) return;

      input.value = data.data.health_conditions || '';

      // si ya hay algo guardado => bloquea y muestra "Editar"
      if ((input.value || '').trim() !== '') setLocked(true);
      else setLocked(false);

    } catch (e) {
      console.error('loadHealth error', e);
    }
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const val = (input.value || '').trim();
    if (!val) {
      alert("Escribe 'ninguna' si no aplica.");
      return;
    }

    try {
      const res = await fetch(urlSave, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ health_conditions: val })
      });

      const data = await res.json();
      if (!res.ok || !data.success) {
        alert(data.message || 'No se pudo guardar.');
        return;
      }

      // al guardar => bloquear y mostrar editar
      setLocked(true);

    } catch (e) {
      console.error('saveHealth error', e);
      alert('Error guardando historia clínica.');
    }
  });

  editBtn.addEventListener('click', () => {
    setLocked(false);
    input.focus();
  });

  // init
  loadHealth();
});
</script>

     </div>

     {{-- DROPZONE --}}
     <form id="hcDropzone"
         class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer bg-white hover:bg-gray-100 transition">
         @csrf
         <input type="hidden" name="lead_id" value="{{ $lead->id }}">

         <p class="text-gray-600 text-sm">
             Arrastra archivos aquí o haz clic para seleccionar
         </p>

         <p class="text-xs text-gray-400 mt-1">
             Imágenes, PDF, Word
         </p>

         <input type="file" id="hcInput" name="files[]" multiple accept="image/*,.pdf,.doc,.docx" class="hidden">
     </form>

     {{-- GRID --}}
     <div id="hc-preview" class="grid grid-cols-2 md:grid-cols-4 gap-4"></div>
 </div>
 <script>
     function loadHCFiles() {
         fetch("{{ route('panel.leads.hc') }}", {
                 method: "POST",
                 headers: {
                     "X-CSRF-TOKEN": "{{ csrf_token() }}",
                     "Content-Type": "application/json"
                 },
                 body: JSON.stringify({
                     lead_id: {{ $lead->id }}
                 })
             })
             .then(res => res.json())
             .then(data => {
                 const container = document.getElementById('hc-preview');
                 container.innerHTML = '';

                 if (!data.files || !data.files.length) {
                     container.innerHTML = '<p class="text-gray-500 text-sm">No hay archivos</p>';
                     return;
                 }

                 data.files.forEach(file => {
                     const ext = file.url.split('.').pop().toLowerCase();

                     if (['jpg', 'jpeg', 'png'].includes(ext)) {
                         container.innerHTML += `
                    <div class="relative group">
                        <a href="${file.url}" target="_blank">
                            <img src="${file.url}"
                                 class="rounded shadow h-32 w-full object-cover">
                        </a>

                        <button onclick="deleteHCFile('${file.name}')"
                            class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-7 h-7
                                   text-xs hidden group-hover:flex items-center justify-center">
                            ✕
                        </button>
                    </div>
                `;
                     } else {
                         container.innerHTML += `
                    <div class="relative group border rounded p-3 bg-white text-center text-sm">
                        <a href="${file.url}" target="_blank" class="block text-blue-600 truncate">
                            ${file.name}
                        </a>

                        <button onclick="deleteHCFile('${file.name}')"
                            class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-7 h-7
                                   text-xs hidden group-hover:flex items-center justify-center">
                            ✕
                        </button>
                    </div>
                `;
                     }
                 });
             });
     }

     loadHCFiles();
 </script>

 {{-- DROPZONE SCRIPT --}}
 <script>
     const hcDropzone = document.getElementById('hcDropzone');
     const hcInput = document.getElementById('hcInput');

     hcDropzone.addEventListener('click', () => hcInput.click());

     hcDropzone.addEventListener('dragover', e => {
         e.preventDefault();
         hcDropzone.classList.add('bg-gray-100');
     });

     hcDropzone.addEventListener('dragleave', () => {
         hcDropzone.classList.remove('bg-gray-100');
     });

     hcDropzone.addEventListener('drop', e => {
         e.preventDefault();
         hcDropzone.classList.remove('bg-gray-100');
         uploadHCFiles(e.dataTransfer.files);
     });

     hcInput.addEventListener('change', () => {
         uploadHCFiles(hcInput.files);
     });

     function uploadHCFiles(files) {
         const formData = new FormData();
         formData.append('_token', '{{ csrf_token() }}');
         formData.append('lead_id', '{{ $lead->id }}');

         for (let file of files) {
             formData.append('files[]', file);
         }

         fetch("{{ route('panel.leads.hc.upload') }}", {
                 method: 'POST',
                 body: formData
             })
             .then(res => res.json())
             .then(() => loadHCFiles());
     }
 </script>

 <script>
     function deleteHCFile(filename) {
         if (!confirm('¿Eliminar este archivo?')) return;

         fetch("{{ route('panel.leads.hc.delete', $lead->id) }}", {
                 method: 'DELETE',
                 headers: {
                     'X-CSRF-TOKEN': '{{ csrf_token() }}',
                     'Content-Type': 'application/json'
                 },
                 body: JSON.stringify({
                     filename: filename
                 })
             })
             .then(res => res.json())
             .then(() => loadHCFiles());
     }
 </script>
