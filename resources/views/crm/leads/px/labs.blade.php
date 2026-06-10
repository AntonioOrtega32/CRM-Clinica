<div class="space-y-6">

    <div class="bg-gray-50 p-4 rounded border">
        <h3 class="text-lg font-semibold text-[#1C6C73] mb-3">
            Laboratorios
        </h3>

        {{-- INPUT FILE --}}
        <input type="file" id="filelabs" multiple accept="image/*,.pdf"
            class="block w-full text-sm text-gray-600
                file:mr-4 file:py-2 file:px-4
                file:rounded file:border-0
                file:text-sm file:font-semibold
                file:bg-[#1C6C73] file:text-white
                hover:file:bg-[#155b61]">


        {{-- PREVIEW --}}
        <div id="labs-preview" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
            {{-- Bunny preview --}}
        </div>
    </div>

</div>

<script>
    function loadLabsFiles() {
        fetch("{{ route('panel.leads.labs') }}", {
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

                const container = document.getElementById('labs-preview');
                container.innerHTML = '';

                if (!data.initialPreview.length) {
                    container.innerHTML = '<p class="text-gray-500">No hay laboratorios</p>';
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
                    <button onclick="deleteLabsFile('${filename}')"
                        class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-7 h-7 text-xs hidden group-hover:flex items-center justify-center">
                        ✕
                    </button>
                </div>`;
                    } else {
                        container.innerHTML += `
                <div class="relative group">
                    <a href="${src}" target="_blank"
                       class="p-3 border rounded text-sm text-center bg-white hover:bg-gray-100 block">
                        Ver laboratorio
                    </a>
                    <button onclick="deleteLabsFile('${filename}')"
                        class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-7 h-7 text-xs hidden group-hover:flex items-center justify-center">
                        ✕
                    </button>
                </div>`;
                    }
                });
            });
    }

    loadLabsFiles();
</script>

<script>
    const labsInput = document.getElementById('filelabs');

    labsInput.addEventListener('change', () => {

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('lead_id', '{{ $lead->id }}');

        for (let file of labsInput.files) {
            formData.append('filelabs[]', file);
        }

        fetch("{{ route('panel.leads.labs.upload') }}", {
                method: 'POST',
                body: formData
            })
            .then(() => loadLabsFiles());
    });
</script>

<script>
    function deleteLabsFile(filename) {

        if (!confirm('¿Eliminar este archivo de laboratorio?')) return;

        fetch("{{ route('panel.leads.labs.delete', $lead->id) }}", {
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
                    loadLabsFiles();
                } else {
                    alert(data.message);
                }
            });
    }
</script>
