<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">


    {{-- TÍTULO --}}
    <h3 class="text-lg font-semibold text-[#1C6C73]">
        Fotos de Valoración
    </h3>

    <div class="bg-gray-50 rounded-lg border border-dashed border-gray-300 p-6">
        <h4 class="text-sm font-semibold text-[#1C6C73] mb-3">
            Fotografías del expediente
        </h4>

        <form id="photosDropzone"
            class="flex flex-col items-center justify-center gap-2 rounded-lg p-8 cursor-pointer
               transition hover:bg-gray-100 hover:border-[#1C6C73] border-2 border-dashed">

            @csrf
            <input type="hidden" name="lead_id" value="{{ $lead->id }}">

            <svg class="w-10 h-10 text-[#1C6C73]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 16V4m0 0l-3 3m3-3l3 3M17 8v12m0 0l3-3m-3 3l-3-3" />
            </svg>

            <p class="text-sm text-gray-600">
                Arrastra imágenes aquí o <span class="text-[#1C6C73] font-medium">haz clic</span>
            </p>

            <p class="text-xs text-gray-400">
                JPG, PNG · Máx recomendado 5MB
            </p>

            <input type="file" id="photosInput" name="photos[]" multiple accept="image/*" class="hidden">
        </form>
    </div>


    {{-- GRID DE FOTOS --}}
    <div>
        <div id="photosGrid" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <p class="text-gray-500 text-sm">Cargando fotos...</p>
        </div>
    </div>

</div>

<script>
    const dropzone = document.getElementById('photosDropzone');
    const input = document.getElementById('photosInput');

    dropzone.addEventListener('click', () => input.click());

    dropzone.addEventListener('dragover', e => {
        e.preventDefault();
        dropzone.classList.add('bg-gray-100');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('bg-gray-100');
    });

    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        dropzone.classList.remove('bg-gray-100');
        uploadFiles(e.dataTransfer.files);
    });

    input.addEventListener('change', () => {
        uploadFiles(input.files);
    });

    function uploadFiles(files) {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('lead_id', '{{ $lead->id }}');

        for (let file of files) {
            formData.append('photos[]', file);
        }

        fetch("{{ route('panel.leads.photos.upload') }}", {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(() => loadPhotos());
    }
</script>
<script>
    function loadPhotos() {
        fetch("{{ route('panel.leads.photos', $lead->id) }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                }
            })
            .then(res => res.json())
            .then(data => {
                const grid = document.getElementById('photosGrid');
                grid.innerHTML = '';

                if (!data.images || !data.images.length) {
                    grid.innerHTML = '<p class="text-gray-500">No hay fotos disponibles</p>';
                    return;
                }

                data.images.forEach(img => {
                    grid.innerHTML += `
        <div class="relative group rounded-lg overflow-hidden border bg-white shadow-sm">

            <a href="${img}" target="_blank" rel="noopener noreferrer">
                <img src="${img}"
                     class="w-full h-40 object-cover transition-transform duration-300 group-hover:scale-105 cursor-pointer">
            </a>

            <!-- Overlay -->
            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition pointer-events-none"></div>

            <!-- Botón eliminar -->
            <button onclick="deletePhoto('${img}')"
                class="absolute top-2 right-2 bg-red-600 text-white rounded-full w-8 h-8
                       flex items-center justify-center text-sm opacity-0 group-hover:opacity-100
                       transition hover:bg-red-700 z-10">
                ✕
            </button>

        </div>
    `;
                });


            });
    }

    loadPhotos();
</script>
<script>
    function deletePhoto(imageUrl) {

        if (!confirm('¿Eliminar esta imagen?')) return;

        const filename = imageUrl.split('/').pop();

        fetch("{{ route('panel.leads.photos.delete', $lead->id) }}", {
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
            .then(data => {
                if (data.success) {
                    loadPhotos();
                } else {
                    alert(data.message);
                }
            });
    }
</script>
