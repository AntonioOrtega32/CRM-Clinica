<div class="space-y-6">

    <div class="bg-gray-50 p-4 rounded border">
        <h3 class="text-lg font-semibold text-[#1C6C73] mb-3">
            Identificación del Paciente
        </h3>

        {{-- INPUT FILE --}}
        <input type="file" id="fileid" multiple accept="image/*,.pdf"
            class="block w-full text-sm text-gray-600
                file:mr-4 file:py-2 file:px-4
                file:rounded file:border-0
                file:text-sm file:font-semibold
                file:bg-[#1C6C73] file:text-white
                hover:file:bg-[#155b61]">

        {{-- PREVIEW --}}
        <div id="id-preview" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
            {{-- Bunny preview --}}
        </div>
    </div>

</div>

<script>
    function loadIDFiles() {
        fetch("{{ route('panel.leads.id') }}", {
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
                const container = document.getElementById('id-preview');
                container.innerHTML = '';

                if (!data.initialPreview.length) {
                    container.innerHTML = '<p class="text-gray-500">No hay archivos</p>';
                    return;
                }

                data.initialPreview.forEach(src => {
                    const ext = src.split('.').pop().toLowerCase();
                    const filename = src.split('/').pop();

                    if (['jpg', 'jpeg', 'png'].includes(ext)) {
                        container.innerHTML += `
                <div class="relative group">
                    <a href="${src}" target="_blank">
                        <img src="${src}" class="rounded shadow object-cover h-32 w-full">
                    </a>
                    <button onclick="deleteIDFile('${filename}')"
                        class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-7 h-7 text-xs hidden group-hover:flex items-center justify-center">
                        ✕
                    </button>
                </div>`;
                    } else {
                        container.innerHTML += `
                <div class="relative group">
                    <a href="${src}" target="_blank"
                       class="p-3 border rounded text-sm text-center bg-white hover:bg-gray-100 block">
                        Ver documento
                    </a>
                    <button onclick="deleteIDFile('${filename}')"
                        class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-7 h-7 text-xs hidden group-hover:flex items-center justify-center">
                        ✕
                    </button>
                </div>`;
                    }
                });
            });
    }

    loadIDFiles();
</script>

<script>
    const idInput = document.getElementById('fileid');

    idInput.addEventListener('change', () => {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('lead_id', '{{ $lead->id }}');

        for (let file of idInput.files) {
            formData.append('fileid[]', file);
        }

        fetch("{{ route('panel.leads.id.upload') }}", {
                method: 'POST',
                body: formData
            })
            .then(() => loadIDFiles());
    });
</script>
<script>
    function deleteIDFile(filename) {

        if (!confirm('¿Eliminar este archivo?')) return;

        fetch("{{ route('panel.leads.id.delete', $lead->id) }}", {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    filename
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadIDFiles();
                } else {
                    alert(data.message);
                }
            });
    }
</script>
