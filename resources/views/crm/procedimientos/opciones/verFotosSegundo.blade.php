@extends('panel.layouts.panel')
@section('title', 'Fotos del procedimiento')

@section('content')
    <section class="py-10 px-6 bg-white">
        <div class="p-6 space-y-6">
            <div>
                <h1 class="text-3xl font-[Poppins] text-[#1C6C73] mb-2">Fotos y notas de 2do procedimiento</h1>
                <p class="text-gray-600">Gestiona las fotos del procedimiento del paciente.</p>
                <br>
               <button
                    type="button"
                    onclick="closeOrBack()"
                    class="bg-[#1C6C73] hover:bg-[#4298a7] text-white px-4 py-2 rounded">
                    <i class="fa-solid fa-square-caret-left"></i> Volver atras 
                </button>

                <script>
                function closeOrBack() {
                    if (window.opener) {
                        window.close();
                    } else {
                        window.location.href = "{{ route('panel.procedimientos.index') }}";
                    }
                }
                </script>
            </div>
            {{-- info de px --}}
            <div class="bg-white shadow-md rounded-xl p-6 border border-gray-200">
                <h1 class="text-2xl font-[Poppins] text-[#1C6C73] mb-3">Datos del paciente</h1>
                <div class="grid md:grid-cols-2 gap-3 text-gray-700">
                    <p><strong>Nombre:</strong> {{ $paciente->name ?? 'No disponible' }}</p>
                    <p><strong># Expediente:</strong> {{ $num_med_record }}</p>
                    <p><strong>Procedimiento:</strong> {{ $paciente->procedure_type ?? '—' }}</p>
                    <p><strong>Clínica:</strong> {{ $paciente->clinic ?? '—' }}</p>
                    <p><strong>Especialista:</strong> {{ $paciente->specialist ?? '—' }}</p>
                    <p><strong>Fecha del Procedimiento:</strong> {{ $paciente->procedure_date ?? '—' }}</p>
                    <p><strong>Enfermedades:</strong> {{ $paciente->enfermedades ?? 'Ninguna' }}</p>
                    <p><strong>2do Procedimiento:</strong> {{ $paciente->touchup ?? 'No' }}</p>
                    <p><strong>Estatus:</strong>
                        <span class="px-2 py-1 rounded text-white bg-[#CDAF95] text-sm">
                            {{ ucfirst($paciente->status ?? 'Desconocido') }}
                        </span>
                    </p>
                </div>
            </div>

             @if ($paciente->status == 'alta')
                <p class="text-red-500 text-center w-full text-lg">
                    Paciente dado de alta, solo se podran subir fotos y notas en post alta!
                </p>
            @endif

            <p class="text-red-500 text-center w-full text-lg">Selecciona una fase para ver/subir las fotos, acciones y notas.</p>
            <p class="text-red-500 text-center w-full text-xl">
               <i class="fa-solid fa-arrow-down-long"></i> CARRUSEL DE FASES DEL SEGUIMIENTO <i class="fa-solid fa-arrow-down-long"></i>
            </p>

            {{-- Carrusel con Swiper --}}
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    @php
                        // aqui se declara que carpetas de mostraran y cuales no dependiendo
                        // de la fecha del alta ni chatgpt pudo hacer esto gracias sackoverflow
                        $disableMap = [
                            '12meses' => ['15meses', '18meses', '21meses'],
                            '15meses' => ['18meses', '21meses'],
                            '18meses' => ['21meses'],
                            '21meses' => [],
                        ];
                    @endphp

                    @foreach ($folderNames as $key => $label)
                        @php
                            $bloquear = false;

                            if ($lastAltaStep && isset($disableMap[$lastAltaStep])) {
                                $bloquear = in_array($key, $disableMap[$lastAltaStep]);
                            }
                        @endphp

                        <div class="swiper-slide text-center {{ $bloquear ? 'opacity-50 pointer-events-none' : '' }}"
                            data-step="{{ $key }}">
                            <div class="flex flex-col items-center">
                                <a href="{{ route('panel.procedimientos.fotos.touchup', ['num_med_record' => $num_med_record, 'step' => $key]) }}"
                                    class="view_imgs inline-block px-5 py-2 rounded-full border transition-all duration-200
                        {{ $step === $key ? 'bg-[#1C6C73] text-white' : 'bg-gray-100 hover:bg-[#1C6C73]/10' }}">
                                    {{ $label }}
                                </a>

                                {{-- botones de dar de alta --}}
                                @if (in_array($key, ['12meses', '15meses', '18meses', '21meses']))
                                    @if ($paciente->status == 'alta')
                                        <button
                                            class="btn-dar-alta disabled:bg-gray-400 text-white text-sm px-4 py-1 rounded mt-2"
                                            disabled>
                                            Paciente dado de alta
                                        </button>
                                    @elseif ($key === $lastAltaStep)
                                        <button class="btn-dar-alta bg-gray-400 text-white text-sm px-4 py-1 rounded mt-2"
                                            disabled>
                                            Paciente dado de alta
                                        </button>
                                    @else
                                        <button
                                            class="btn-dar-alta bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-1 rounded mt-2"
                                            data-step="{{ $key }}">
                                            <i class="fa-solid fa-laptop-medical"></i> Dar de alta
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>

            {{-- Swiper ese ya que estamos --}}
            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const numMedRecord = "{{ $num_med_record }}";
                    const lastAltaStep = @json($lastAltaStep);
                    const folders = @json(array_keys($folderNames));
                    // Inicializa Swiper
                    const swiper = new Swiper(".mySwiper", {
                        slidesPerView: "auto",
                        spaceBetween: 15,
                        navigation: {
                            nextEl: ".swiper-button-next",
                            prevEl: ".swiper-button-prev",
                        },
                        freeMode: true,
                        mousewheel: true,
                    });

                    // Deshabilita slides posteriores al último alta (excepto post_alta)
                    if (lastAltaStep && folders.includes(lastAltaStep)) {
                        document.querySelectorAll(".swiper-slide").forEach((slide) => {
                            const step = slide.dataset.step;
                            const btnAlta = slide.querySelector(".btn-dar-alta");

                            // Deshabilitar el botón de la fase que ya fue dada de alta
                            if (step === lastAltaStep && btnAlta) {
                                btnAlta.disabled = true;
                                btnAlta.textContent = "Paciente dado de alta";
                                btnAlta.classList.replace("bg-green-600", "bg-gray-400");
                            }

                            // Lógica de deshabilitación según la fase
                            const disableMap = {
                                '12meses': ['15meses', '18meses', '21meses'],
                                '15meses': ['18meses', '21meses'],
                                '18meses': ['21meses'],
                                '21meses': []
                            };

                            if (disableMap[lastAltaStep]?.includes(step)) {
                                slide.classList.add("opacity-50", "pointer-events-none");
                            }
                        });
                    }


                    // Evento: Dar de alta
                    document.addEventListener("click", async (e) => {
                        if (!e.target.classList.contains("btn-dar-alta")) return;

                        const btn = e.target;
                        const step = btn.dataset.step;
                        const indexActual = folders.indexOf(step);

                        if (!confirm(`¿Deseas dar de alta al paciente en la etapa ${step}?`)) return;

                        try {
                            const res = await fetch("{{ route('panel.procedimientos.actualizarAlta') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                },
                                body: JSON.stringify({
                                    num_med_record: numMedRecord,
                                    phase: step
                                })
                            });

                            const data = await res.json();

                            if (data.success) {
                                alert("Estado actualizado correctamente.");

                                btn.disabled = true;
                                btn.textContent = "Paciente dado de alta";
                                btn.classList.replace("bg-green-600", "bg-gray-400");

                                // Oculta o bloquea las carpetas posteriores (excepto post_alta)
                                document.querySelectorAll(".swiper-slide").forEach((slide, idx) => {
                                    const stepName = slide.dataset.step;
                                    if (idx > indexActual && stepName !== "post_alta") {
                                        slide.classList.add("opacity-50", "pointer-events-none");
                                    }
                                });
                            } else {
                                alert("Error: " + (data.error ?? "Desconocido"));
                            }
                        } catch (err) {
                            console.error("Error:", err);
                            alert("Error de conexión con el servidor.");
                        }
                    });
                });
            </script>

            <style>
                .swiper {
                    padding: 10px 40px;
                }

                .swiper-slide {
                    width: auto !important;
                }

                .swiper-button-next,
                .swiper-button-prev {
                    color: #1C6C73 !important;
                }
            </style>

            {{-- Subida de fotos (Drag & Drop + botón) --}}
            <form id="uploadForm" disabled
                action="{{ route('panel.procedimientos.subirFotoSegundo', ['num_med_record' => $num_med_record, 'step' => $step]) }}"
                method="POST" enctype="multipart/form-data"
                class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center bg-gray-50 hover:bg-gray-100 transition">
                @csrf

                <p class="text-gray-600 mb-2">Arrastra tus fotos aquí o haz clic para seleccionarlas</p>

                {{-- name="foto[]" + multiple --}}
                <input type="file" id="fotoInput" name="foto[]" class="hidden" accept="image/*" multiple>

                @if ($paciente->status == 'alta')
                    <button type="button" disabled 
                    class="disabled:bg-[#4298A7] hover:bg-[#C8BAAF] text-white px-4 py-2 rounded">
                    Seleccionar archivo
                    </button>
                @else
                    <button type="button" id="btnPickFiles" disabled
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Seleccionar archivo
                    </button>
                @endif
                </form>

            {{-- Galería de imágenes --}}
            <div class="mt-6">
                @if (count($imagenes) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach ($imagenes as $img)
                            <div class="border p-3 rounded-lg shadow hover:shadow-lg transition bg-white">
                                <a href="{{ $img['url'] }}" target="_blank">
                                    <img src="{{ $img['thumb'] }}" alt="{{ $img['name'] }}"
                                        onerror="this.onerror=null;this.src='{{ $img['url'] }}';"
                                        class="rounded-lg h-48 w-full object-cover mb-2">
                                </a>
                                <p class="text-sm text-center truncate mb-2">{{ $img['name'] }}</p>
                                @role('super_usuario|administrador|Médicos')
                                <form action="{{ route('panel.procedimientos.eliminarFotoSegundo') }}" method="POST"
                                    onsubmit="return confirm('¿Eliminar esta foto?');" class="text-center">
                                    @csrf
                                    @method('DELETE')
                                    @if ($paciente->status == 'alta')
                                        <input type="hidden" name="url" value="{{ $img['url'] }}">
                                        <button disabled type="submit"
                                            class="text-red-600 disabled:text-[#DED5CE] text-sm">
                                            Eliminar
                                        </button>
                                    @else
                                        <input type="hidden" name="url" value="{{ $img['url'] }}">
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                            Eliminar
                                        </button>
                                    @endif
                                </form>
                                @endrole
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center">No hay fotos disponibles en esta carpeta.</p>
                @endif
            </div>
        </div>

        <!-- Botón estático y listado de notas debajo de las fotos -->
        <div class="mt-10 border-t pt-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-[Poppins] text-[#1C6C73]">Notas Médicas del procedimiento</h2>
               
                    <button id="openNotas"
                        class="bg-[#1C6C73] hover:bg-[#155A61] text-white px-5 py-2 rounded-lg shadow transition">
                        <i class="fa-solid fa-notes-medical"></i> Agregar Nota
                    </button>
        
            </div>

            <!-- Contenedor donde se cargan las notas -->
            <div id="listaNotas" class="space-y-4">
                <p class="text-gray-400 text-center">Cargando notas...</p>
            </div>
        </div>

        <!-- Modal para las notas -->
        <div id="modalNotas"
            class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 overflow-y-auto">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl p-6 relative animate-fadeIn">

                <!-- Cerrar -->
                <button id="closeNotas" class="absolute top-3 right-3 text-gray-500 hover:text-gray-800">✕</button>

                <h2 class="text-2xl font-bold text-[#1C6C73] mb-4 text-center">Agregar Nota Médica</h2>

                <!-- Formulario -->
                <form id="formNota" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="num_med_record" value="{{ $num_med_record }}">
                    <input type="hidden" name="phase" value="{{ $step }}">
                    <input type="hidden" name="author"
                        value="{{ Auth::user()->id }}"><!-- aqui toma el usuario que esta logueado -->
                    <input type="hidden" name="clinic" value="{{ $paciente->clinic }}">
                    <input type="hidden" name="procedure_type" value="touchup">

                    <textarea name="note" rows="2"
                        class="w-full border rounded-lg p-3 text-gray-700 focus:ring-2 focus:ring-[#1C6C73]"
                        placeholder="Escribe tu nota médica..."></textarea>

                    <input type="date" name="date" id="date"
                        class="border rounded-lg p-3 text-gray-700 w-full focus:ring-2 focus:ring-[#1C6C73]">

                    <div class="flex flex-col sm:flex-row flex-wrap gap-3 items-center w-full">
                        <input type="file" name="audio_file" accept="audio/*"
                            class="w-full sm:w-auto border rounded p-2">

                        <!-- Grabadora -->
                        <div class="flex gap-2 items-center">
                            <button type="button" id="btnStartRecording"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                                Grabar
                            </button>
                            <button type="button" id="btnStopRecording"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow hidden">
                                Detener
                            </button>
                            <button type="button" id="btnDeleteRecording"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow hidden">
                                Eliminar
                            </button>
                        </div>

                        <audio id="audioPreview" controls class="hidden w-full mt-2"></audio>
                    </div>

                    <select name="status_conformidad" class="border rounded p-2 text-gray-700">
                        <option value="0">Nivel de satisfacción...</option>
                        <option value="1">Muy insatisfecho</option>
                        <option value="2">Insatisfecho</option>
                        <option value="3">Neutral</option>
                        <option value="4">Satisfecho</option>
                        <option value="5">Muy satisfecho</option>
                    </select>

                    <div class="flex justify-end mt-4">
                        <button type="button" id="btnGuardarNota"
                            class="bg-[#1C6C73] hover:bg-[#155A61] text-white px-5 py-2 rounded-lg shadow">
                            Guardar Nota
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- seccion de las firmas --}}

        <div class="mt-10 border-t pt-6">
            <h2 class="text-2xl font-[Poppins] text-[#1C6C73] mb-4">Firma</h2>

            <!-- canvas para dibujar -->
            <div class="flex flex-col items-center space-y-4">
                <canvas id="firmaCanvas" width="400" height="200"
                    class="border border-gray-300 rounded-lg bg-white shadow-md"></canvas>
                @if ($paciente->status == 'alta')
                    <div class="flex gap-3">
                        <button id="btnLimpiar"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow">
                            Limpiar
                        </button>
                        <button id="btnGuardarFirma" disabled
                            class="bg-green-600 disabled:bg-green-500 text-white px-4 py-2 rounded-lg shadow">
                            Guardar
                        </button>
                        <button id="btnEliminarFirma" disabled
                            class="bg-red-600 disabled:bg-red-500 text-white px-4 py-2 rounded-lg shadow">
                            Eliminar
                        </button>
                    </div>
                @else
                    <div class="flex gap-3">
                        <button id="btnLimpiar"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow">
                            Limpiar
                        </button>
                        <button id="btnGuardarFirma"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow">
                            Guardar
                        </button>
                        <button id="btnEliminarFirma"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow">
                            Eliminar
                        </button>
                    </div>
                @endif
                <!-- firma guardada -->
                <div id="firmaGuardada" class="mt-4 text-center text-gray-600"></div>
            </div>
        </div>

        {{-- Script del Modal de Notas  --}}

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('modalNotas');
                const openBtn = document.getElementById('openNotas');
                const closeBtn = document.getElementById('closeNotas');
                const lista = document.getElementById('listaNotas');
                const form = document.getElementById('formNota');
                const btnGuardar = document.getElementById('btnGuardarNota');

                const dateInput = document.getElementById('date');

                
                if (dateInput && !dateInput.value) {
                    dateInput.value = new Date().toISOString().split('T')[0];
                }

                // mostrar o ocultar modal
                openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
                closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
                window.addEventListener('click', e => {
                    if (e.target === modal) modal.classList.add('hidden');
                });

                // Cargar notas
                function cargarNotas() {
                    lista.innerHTML = '<p class="text-gray-400 text-center">Cargando notas...</p>';
                    fetch("{{ route('panel.notas.list') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({
                                num_med_record: "{{ $num_med_record }}",
                                phase: "{{ $step }}",
                                procedure_type: "touchup"
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success' && data.data.length > 0) {
                                lista.innerHTML = '';
                                data.data.forEach(note => {
                                    lista.innerHTML += `
                                <div class="border rounded-lg p-3 shadow-sm hover:shadow-md transition bg-gray-50">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="font-semibold text-[#1C6C73]">${note.author_name}</span>
                                        <span class="text-gray-500 text-sm">${note.date}</span>
                                    </div>
                                    <p class="text-gray-700 mb-2">${note.note}</p>
                                    ${note.audio_url ? `
                                                                                                <audio controls>
                                                                                                    <source src="/${note.audio_url}" type="audio/mpeg">
                                                                                                    Tu navegador no soporta audio.
                                                                                                </audio>
                                                                                            ` : ''}
                                    <div class="text-sm text-gray-600 italic mt-2">
                                        Satisfacción: ${note.conformidad_texto}
                                    </div>
                                    <button data-id="${note.id}" class="btnEliminarNota text-red-600 text-xs mt-2 hover:underline">
                                        Eliminar
                                    </button>
                                </div>`;
                                });
                            } else {
                                lista.innerHTML =
                                    '<p class="text-gray-400 text-center">No hay notas registradas.</p>';
                            }
                        });
                }
                cargarNotas();

                // aqui se graba el audio en teoria (si funciona!)
                let mediaRecorder;
                let audioChunks = [];
                const btnStart = document.getElementById('btnStartRecording');
                const btnStop = document.getElementById('btnStopRecording');
                const btnDelete = document.getElementById('btnDeleteRecording');
                const audioPreview = document.getElementById('audioPreview');
                const inputAudio = document.querySelector('input[name="audio_file"]');

                btnStart.addEventListener('click', async () => {
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({
                            audio: true
                        });
                        mediaRecorder = new MediaRecorder(stream);

                        audioChunks = [];
                        mediaRecorder.ondataavailable = event => {
                            if (event.data.size > 0) audioChunks.push(event.data);
                        };

                        mediaRecorder.onstop = () => {
                            const audioBlob = new Blob(audioChunks, {
                                type: 'audio/mp3'
                            });
                            const audioUrl = URL.createObjectURL(audioBlob);
                            audioPreview.src = audioUrl;
                            audioPreview.classList.remove('hidden');
                            btnDelete.classList.remove('hidden');

                            // nuevo archivo para enviar
                            const audioFile = new File([audioBlob], "grabacion_" + Date.now() +
                                ".mp3", {
                                    type: 'audio/mp3'
                                });
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(audioFile);
                            inputAudio.files = dataTransfer.files;
                        };

                        mediaRecorder.start();
                        btnStart.classList.add('hidden');
                        btnStop.classList.remove('hidden');
                        btnDelete.classList.add('hidden');
                        audioPreview.classList.add('hidden');

                    } catch (err) {
                        alert("✕ No se pudo acceder al micrófono: " + err.message);
                    }
                });

                btnStop.addEventListener('click', () => {
                    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                        mediaRecorder.stop();
                        btnStop.classList.add('hidden');
                        btnStart.classList.remove('hidden');
                    }
                });

                btnDelete.addEventListener('click', () => {
                    // eliminar audio grabado
                    audioPreview.src = "";
                    audioPreview.classList.add('hidden');
                    btnDelete.classList.add('hidden');
                    inputAudio.value = "";
                    alert("✕ Grabación eliminada");
                });

                // Guardar nota
                btnGuardar.addEventListener('click', () => {
                    const formData = new FormData(form);
                    fetch("{{ route('panel.notas.add') }}", {
                            method: "POST",
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                alert('✕' + data.message);
                                form.reset();
                                cargarNotas();
                                modal.classList.add('hidden');
                            } else {
                                alert('✓ ' + data.message);
                            }
                        })
                        .catch(err => alert('Error: ' + err));
                });

                // eliminar nota
                lista.addEventListener('click', (e) => {
                    if (e.target.classList.contains('btnEliminarNota')) {
                        if (!confirm('¿Eliminar esta nota?')) return;
                        const id = e.target.dataset.id;
                        fetch(`/panel/notas/eliminar/${id}`, {
                                method: "DELETE",
                                headers: {
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                alert(data.message);
                                cargarNotas();
                            });
                    }
                });
            });
        </script>

        {{-- Script del Drag & Drop --}}
       <script>
            document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('uploadForm');
            const fileInput = document.getElementById('fotoInput');
            const dropArea = form;
            const btnPick = document.getElementById('btnPickFiles');

            const csrf = document.querySelector('input[name="_token"]')?.value;

            const showUploading = (count) => {
                Swal.fire({
                title: 'Subiendo fotos…',
                html: `Archivos: <b>${count}</b><br>Por favor espera.`,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
                });
            };

            const showOk = (count) => {
                Swal.fire({
                icon: 'success',
                title: 'Listo',
                text: `${count} foto(s) subida(s) correctamente`,
                timer: 1400,
                showConfirmButton: false
                });
            };

            const showErr = (msg) => {
                Swal.fire({
                icon: 'error',
                title: 'Error al subir',
                text: msg || 'No se pudieron subir las fotos'
                });
            };

            async function uploadFiles(files) {
                if (!files || files.length === 0) return;

                showUploading(files.length);

                const fd = new FormData(form);

                // por si el FormData trae algo previo
                fd.delete('foto[]');

                for (const f of files) {
                fd.append('foto[]', f);
                }

                try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: fd,
                });

                // OJO: tu controller responde redirect (HTML). No parseamos JSON.
                if (!res.ok) {
                    const raw = await res.text();
                    console.error(raw);
                    throw new Error(`HTTP ${res.status}`);
                }

                Swal.close();
                showOk(files.length);

                // recarga para ver la galería actualizada
                setTimeout(() => window.location.reload(), 900);
                } catch (e) {
                Swal.close();
                console.error(e);
                showErr(e.message);
                }
            }

            // Click botón: abrir selector
            btnPick?.addEventListener('click', () => fileInput.click());

            // Selección: subir
            fileInput.addEventListener('change', () => {
                if (fileInput.files && fileInput.files.length > 0) {
                uploadFiles(fileInput.files);
                fileInput.value = ''; // importante para poder elegir lo mismo otra vez
                }
            });

            // Drag visuals
            ['dragenter','dragover'].forEach(evt => {
                dropArea.addEventListener(evt, (e) => {
                e.preventDefault();
                dropArea.classList.add('border-blue-400','bg-blue-50');
                });
            });

            ['dragleave','drop'].forEach(evt => {
                dropArea.addEventListener(evt, (e) => {
                e.preventDefault();
                dropArea.classList.remove('border-blue-400','bg-blue-50');
                });
            });

            // Drop: subir
            dropArea.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                if (files && files.length > 0) uploadFiles(files);
            });
            });
            </script>

        {{-- scripts de la firmas --}}

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const canvas = document.getElementById('firmaCanvas');
                const ctx = canvas.getContext('2d');
                let dibujando = false;

                // variables desde Blade
                const num_med = "{{ $num_med_record }}";
                const fase = "{{ $step }}";
                const clinic = "{{ $paciente->clinic }}";

                // dibuja la firma
                canvas.addEventListener('mousedown', e => {
                    dibujando = true;
                    ctx.beginPath();
                    ctx.moveTo(e.offsetX, e.offsetY);
                });

                canvas.addEventListener('mousemove', e => {
                    if (!dibujando) return;
                    ctx.lineTo(e.offsetX, e.offsetY);
                    ctx.strokeStyle = "#000";
                    ctx.lineWidth = 2;
                    ctx.stroke();
                });

                ['mouseup', 'mouseout'].forEach(evt =>
                    canvas.addEventListener(evt, () => dibujando = false)
                );

                function limpiarCanvas() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                }

                document.getElementById('btnLimpiar').addEventListener('click', limpiarCanvas);

                // carga la firma si existe
                function cargarFirma() {
                    fetch("{{ route('panel.procedimientos.buscarFirma') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                num_med,
                                fase,
                                clinic
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            const div = document.getElementById('firmaGuardada');
                            div.innerHTML = "";
                            if (data.success && data.url) {
                                div.innerHTML = `
                            <p class="text-green-600 font-semibold mb-2">Firma registrada:</p>
                            <img src="${data.url}" alt="Firma guardada"
                                class="mx-auto border rounded shadow max-w-[300px]">
                        `;
                            } else {
                                div.innerHTML =
                                    `<p class="text-gray-500">No hay firma registrada para esta fase.</p>`;
                            }
                        });
                }

                cargarFirma();

                // guarda la firma
                document.getElementById('btnGuardarFirma').addEventListener('click', function() {
                    const dataURL = canvas.toDataURL('image/png');

                    fetch("{{ route('panel.procedimientos.guardarFirma') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                firma: dataURL,
                                num_med,
                                fase,
                                clinic
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                alert("Firma guardada correctamente");
                                cargarFirma();
                                limpiarCanvas();
                            } else {
                                alert("Error al guardar la firma: " + (data.error ?? ''));
                            }
                        });
                });

                // elimina la firma
                document.getElementById('btnEliminarFirma').addEventListener('click', function() {
                    if (!confirm("¿Seguro que deseas eliminar esta firma?")) return;

                    fetch("{{ route('panel.procedimientos.eliminarFirma') }}", {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                num_med,
                                fase,
                                clinic
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                alert("Firma eliminada correctamente");
                                cargarFirma();
                            } else {
                                alert("Error al eliminar la firma: " + (data.message ?? ''));
                            }
                        });
                });
            });
        </script>

        <style>
            /* Quita la barra de desplazamiento del carrusel */
            .no-scrollbar::-webkit-scrollbar {
                display: none;
            }

            .no-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .animate-fadeIn {
                animation: fadeIn 0.3s ease-out;
            }

            #audioPreview {
                max-width: 100%;
                border-radius: 0.5rem;
            }

            #recordingIndicator {
                animation: pulse 1s infinite;
            }

            @keyframes pulse {

                0%,
                100% {
                    opacity: 1;
                }

                50% {
                    opacity: 0.4;
                }
            }
        </style>
    </section>
@endsection
