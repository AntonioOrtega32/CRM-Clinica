    @extends('panel.layouts.panel')
    @section('title', 'Ver Lead')
    @section('content')
        <div class="container bg-white mx-auto px-4">

            {{-- Título dinámico --}}
            <div class="mb-6">
                <br>
                <h1 class="text-2xl text-[#1C6C73] font-[Poppins] ">
                    @if ($isClient)
                        Ver Cliente
                    @else
                        Ver Lead
                    @endif
                </h1>
            </div>

            {{-- ENCABEZADO PRINCIPAL (como el CRM viejo) --}}
            <div class="bg-white p-6 rounded shadow-md mb-6">

                {{-- Nombre completo --}}
                <h2 class="text-3xl font-[Poppins] text-[#4298A7] mb-2">
                    @if ($lead->first_name && $lead->last_name == null)
                     Sin nombre
                    @else
                    {{ $lead->first_name}} 
                    {{ $lead->last_name}}
                    @endif
                </h2>

                {{-- BADGES estilo viejo CRM --}}
                <div class="flex flex-wrap gap-2 text-sm">

                    {{-- Tipo de procedimiento --}}
                    @if ($lead->interested_in)
                        <span class="px-3 py-1 bg-[#CDAF95] text-white rounded">
                            {{ $lead->interested_in }}
                        </span>
                    @endif

                    {{-- Número de expediente --}}
                    <span class="px-3 py-1 bg-[#1C6C73] text-white rounded">
                        Número de Exp: {{ $numExp ?? 'Sin num de expediente asignado' }}
                    </span>

                    {{-- Fecha de procedimiento --}}
                    @if ($lead->procedure_date)
                        <span class="px-3 py-1 bg-[#DED5CE] text-white rounded">
                            Fecha de procedimiento: {{ \Carbon\Carbon::parse($lead->procedure_date)->format('d/m/Y') }}
                        </span>
                    @endif

                </div>

            </div>

            {{-- CONTENEDOR PARA LAS TABS --}}
            <div x-data="{ tab: '{{ $isClient ? 'PX' : 'valoracion' }}' }" class="bg-white p-6 rounded shadow-md">

                {{-- NAV DE TABS --}}
                <div class="border-b mb-4">
                    <nav class="flex space-x-6 text-sm font-[Poppins]">

                        <button @click="tab = 'valoracion'"
                            :class="tab === 'valoracion' ? 'text-[#1C6C73] border-b-2 border-[#1C6C73]' : 'text-gray-500'"
                            class="pb-2 focus:outline-none">
                            <i class="fa-regular fa-comment"></i>
                            Valoración
                        </button>

                        <button @click="tab = 'PX'"
                            :class="tab === 'PX' ? 'text-[#1C6C73] border-b-2 border-[#1C6C73]' : 'text-gray-500'"
                            class="pb-2 focus:outline-none">
                            <i class="fa-regular fa-address-card"></i>
                            Perfil del Px
                        </button>

                        <button @click="tab = 'recibos'"
                            :class="tab === 'recibos' ? 'text-[#1C6C73] border-b-2 border-[#1C6C73]' : 'text-gray-500'"
                            class="pb-2 focus:outline-none">
                            <i class="fas fa-receipt"></i>
                            Generar Recibos
                        </button>

                        <button @click="tab = 'trans'"
                            :class="tab === 'trans' ? 'text-[#1C6C73] border-b-2 border-[#1C6C73]' : 'text-gray-500'"
                            class="pb-2 focus:outline-none">
                            <i class="fa-solid fa-box-archive"></i>
                            Historial de Transacciones
                        </button>
                    </nav>
                </div>

                {{-- CONTENIDO DINÁMICO --}}

                {{-- TAB: Valoración --}}
                <div x-show="tab === 'valoracion'" x-init="loadValoracion()" class="pt-6">
                      <p class="text-[#1C6C73] text-lg">Introduce los datos del paciente para asignar o actualizar la valoración.</p>

                    <form id="assessmentForm" action="{{ route('panel.leads.assessment.store', $lead->id) }}" method="POST"
                        enctype="multipart/form-data" class="space-y-6">

                        @csrf

                        <input type="hidden" name="assessment_id" id="assessment_id" value="">
                        <div class="bg-white border rounded-xl shadow-sm p-6">

                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-semibold text-[#1C6C73]">
                                    Información de valoración
                                </h3>
                            </div>

                            <div class="mb-6">
                                <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">
                                    Datos generales
                                </h4>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                    {{-- Fecha valoración --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Fecha de valoración *</label>
                                        <input type="date" name="assessment_date" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    </div>

                                    {{-- Nombre --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Nombre(s) *</label>
                                        <input type="text" name="client_firstname" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-1 focus:ring-[#1C6C73] focus:border-[#1C6C73]">
                                    </div>

                                    {{-- Apellidos --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Apellido(s) *</label>
                                        <input type="text" name="client_lastname" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-1 focus:ring-[#1C6C73] focus:border-[#1C6C73]">
                                    </div>

                                    {{-- Numero de Telefono --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Número de teléfono *</label>
                                        <input type="text" id="client_phone" name="client_phone" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-1 focus:ring-[#1C6C73] focus:border-[#1C6C73]">
                                    </div>

                                    {{-- Genero --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Género *</label>
                                        <div class="mt-1 flex items-center gap-4">
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="client_gender" value="Hombre" required
                                                    class="form-radio">
                                                <span class="ml-2 text-sm">Hombre</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="client_gender" value="Mujer" required
                                                    class="form-radio">
                                                <span class="ml-2 text-sm">Mujer</span>
                                            </label>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="mb-6">
                                <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">
                                    Procedimiento
                                </h4>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                    {{-- Fecha injerto --}}
                                    <div>
                                            <input type="date" name="procedure_date" id="procedure_date"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-1 focus:ring-[#1C6C73] focus:border-[#1C6C73]">

                                            <label class="text-sm">
                                            <input type="checkbox" name="open_date" id="open_date" value="1"> Fecha abierta
                                        </label>
                                    </div>

                                    <script>
                                        document.addEventListener('DOMContentLoaded', () => {
                                        const chk = document.getElementById('open_date');
                                        const inp = document.getElementById('procedure_date');

                                        if (!chk || !inp) return;

                                        const sync = () => {
                                            if (chk.checked) {
                                            inp.value = '';
                                            inp.disabled = true;
                                            inp.removeAttribute('required');
                                            } else {
                                            inp.disabled = false;
                                            inp.setAttribute('required', 'required');
                                            }
                                        };

                                        chk.addEventListener('change', sync);
                                        sync(); // init
                                        });
                                    </script>
                                    {{-- Tipo de injerto --}}
                                    <div>
                                        <label class="form-label">Tipo de injerto *</label>
                                        <select name="procedure_type" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                            <option value="">Selecciona</option>
                                            <option value="" selected disabled>Selecciona</option>
                                            <option value="Capilar">Capilar</option>
                                            <option value="Barba">Barba</option>
                                            <option value="Ambos">Ambos</option>
                                            <option value="Tratamientos">Tratamientos</option>
                                            <option value="2do Procedimiento">2do Procedimiento</option>
                                            <option value="Micro">Micro</option>
                                            <option value="Ceja">Ceja</option>
                                            <option value="Retoque">Retoque</option>
                                        </select>
                                    </div>

                                   {{-- Evaluador --}}
                            <div>
                                <label class="form-label">¿Quién realizó la valoración? *</label>
                                <select name="assessment_employee" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Selecciona</option>
                                    <option value="Dr. Carlos Mendoza">Dr. Carlos Mendoza</option>
                                    <option value="Dra. Laura Gómez">Dra. Laura Gómez</option>
                                    <option value="Dra. Sofía Navarro">Dra. Sofía Navarro</option>
                                    <option value="Dra. Valeria Castillo">Dra. Valeria Castillo</option>
                                    <option value="Carmen Torres">Carmen Torres</option>
                                    <option value="Diana Herrera">Diana Herrera</option>
                                    <option value="Fernanda Ramos">Fernanda Ramos</option>
                                    <option value="Dra. Patricia Reyes">Dra. Patricia Reyes</option>
                                    <option value="Dr. Roberto Morales Sánchez">Dr. Roberto Morales Sánchez</option>
                                    <option value="Dra. Jimena Vargas">Dra. Jimena Vargas</option>
                                </select>
                            </div>
                            <div class="mb-6">
                                <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">
                                    Evaluación y origen
                                </h4>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    {{-- Origen --}}
                                    <div>
                                        <label class="form-label">¿Cómo nos conoció? *</label>
                                        <select name="first_meet_type" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-1 focus:ring-[#1C6C73] focus:border-[#1C6C73]">
                                            <option value="">Selecciona</option>
                                            <option value="Facebook">Facebook</option>
                                            <option value="Instagram">Instagram</option>
                                            <option value="Tiktok">Tiktok</option>
                                            <option value="Google">Google</option>
                                            <option value="Whatsapp">Whatsapp</option>
                                            <option value="Referido">Referido</option>
                                            <option value="Organico">Orgánico</option>
                                            <option value="Recomendado">Recomendado</option>
                                            <option value="Pagina">Pagina</option>
                                            <option value="Px">Ya es px</option>
                                            <option value="Campaña">Campaña publicitaria</option>
                                            <option value="Otro">Otro</option>
                                            <option value="Px">Desconocido</option>
                                        </select>
                                    </div>

                                    {{-- Clínica --}}
                                    <div>
                                        <label class="form-label">Clínica *</label>
                                        <select name="clinic" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-1 focus:ring-[#1C6C73] focus:border-[#1C6C73]">
                                            <option value="">Selecciona</option>
                                            <option value="Queretaro">Queretaro</option>
                                            <option value="Pedregal">Pedregal</option>
                                            <option value="Santa Fe">Santa Fe</option>
                                        </select>
                                    </div>

                                    {{-- Tipo valoración --}}
                                    <div>
                                        <label class="form-label">Tipo de valoración *</label>
                                        <select name="assessment_type" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                            <option value="">Selecciona</option>
                                            <option value="" selected disabled>Selecciona</option>
                                            <option value="Presencial">Presencial</option>
                                            <option value="Virtual">Virtual</option>
                                            <option value="Virtual y después presencial">Virtual y después presencial
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-6">
                                <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">
                                    Diseño
                                </h4>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">

                                    <div>
                                        <label class="form-label">Foto del diseño</label>
                                        <input type="file" name="photo" id="photoInput" class="form-input" disabled>

                                        <label class="inline-flex items-center mt-2 text-sm text-gray-600">
                                            <input type="checkbox" id="noPhoto" class="mr-2">
                                            Sin fotografía
                                        </label>
                                    </div>

                                    <script>
                                        document.addEventListener('DOMContentLoaded', () => {
                                        const input = document.getElementById('photoInput');
                                        const noPhoto = document.getElementById('noPhoto');

                                        const img = document.getElementById('designPreviewImg');
                                        const empty = document.getElementById('designPreviewEmpty');

                                        if (!input || !img || !empty) return;

                                        function setPreview(src) {
                                            if (src) {
                                            img.src = src;
                                            img.classList.remove('hidden');
                                            empty.classList.add('hidden');
                                            } else {
                                            img.src = '';
                                            img.classList.add('hidden');
                                            empty.classList.remove('hidden');
                                            }
                                        }

                                        input.addEventListener('change', () => {
                                            if (input.files && input.files[0]) {
                                            const url = URL.createObjectURL(input.files[0]);
                                            setPreview(url);
                                            } else {
                                            setPreview(null);
                                            }
                                        });

                                        noPhoto?.addEventListener('change', () => {
                                            if (noPhoto.checked) setPreview(null);
                                        });

                                        // opcional: si quieres mostrar la imagen ya guardada (cuando loadValoracion traiga url)
                                        window.setAssessmentPreview = (url) => setPreview(url);
                                        });
                                    </script>


                                    <div class="border border-dashed rounded-lg p-4 text-center">
                                        <img id="designPreviewImg" src="" alt="Vista previa"
                                            class="hidden mx-auto max-h-[260px] rounded-lg shadow" />

                                        <div id="designPreviewEmpty" class="text-gray-400">
                                            Vista previa del diseño
                                        </div>
                                    </div>

                                </div>
                            </div>


                            <div class="mb-6">
                                <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">
                                    Observaciones
                                </h4>
                                {{-- Descripción --}}
                                <div class="md:col-span-3">
                                    <label class="form-label">¿Qué se le ofreció? *</label>
                                    <textarea name="description" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </textarea>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="bg-[#1C6C73] hover:bg-[#155b61] text-white px-6 py-2 rounded">
                                Guardar valoración
                            </button>
                        </div>
                    </form>

                    <div class="flex justify-end gap-3 pt-4 border-t">

                        <button type="reset" class="px-5 py-2 rounded border text-gray-600 hover:bg-gray-100">
                            Cancelar
                        </button>

                        <a href="{{ route('panel.clientes.valoracion.pdf', ['leadId' => $lead->id]) }}" disabled
                            class="bg-[#1C6C73] hover:bg-[#155b61] text-white px-6 py-2 rounded-lg shadow inline-block">
                            Generar hoja de valoración
                        </a>

                    </div>

                    <script>
                        document.getElementById('noPhoto').addEventListener('change', function() {
                            const input = document.getElementById('photoInput');
                            input.required = !this.checked;
                            if (this.checked) input.value = '';
                        });
                    </script>

                  <script>
function loadValoracion() {
  fetch("{{ route('panel.leads.assessment', $lead->id) }}")
    .then(res => res.json())
    .then(data => {

      // helpers
      const set = (selector, val) => {
        const el = document.querySelector(selector);
        if (el) el.value = (val ?? '');
      };
      const setRadio = (name, val) => {
        document.querySelectorAll(`input[type="radio"][name="${name}"]`)
            .forEach(r => r.checked = (r.value === (val ?? '')));
        };

      // ✅ default: limpiar assessment_id siempre
      const aid = document.getElementById('assessment_id');
      if (aid) aid.value = '';

      // ✅ 1) llenar con LEAD (fallback)
      if (data.lead) {
        set('[name="client_firstname"]', data.lead.first_name);
        set('[name="client_lastname"]', data.lead.last_name);
        set('[name="clinic"]', data.lead.clinic);
        set('[name="first_meet_type"]', data.lead.origin);
        set('[name="procedure_type"]', data.lead.interested_in);
        set('[name="clinic"]', data.lead.clinic);
        set('[name="client_phone"]', data.lead.phone);
        

        //set('[name="assessment_employee"]', data.lead.seller);
      }

      // ✅ si NO hay assessment, deja limpios los campos que son del assessment
      if (!data.assessment) {
        set('[name="assessment_date"]', '');
        set('[name="procedure_date"]', '');
       // set('[name="procedure_type"]', '');
        set('[name="assessment_employee"]', '');
        set('[name="assessment_type"]', '');
        set('[name="description"]', '');
        

        // preview vacío
        window.setAssessmentPreview?.(null);
        return;
      }

      // ✅ 2) si SÍ hay assessment, sobreescribe
      const a = data.assessment;

      if (aid) aid.value = a.id ?? '';

      set('[name="assessment_date"]', a.date);
      set('[name="procedure_date"]', a.procedure_date);
      set('[name="procedure_type"]', a.procedure_type);
      set('[name="assessment_employee"]', a.closer);
      set('[name="assessment_type"]', a.type);
      set('[name="description"]', a.notes);
      set('[name="clinic"]', a.clinic);
      setRadio('client_gender', a.gender);
      set('[name="client_phone"]', a.phone);

      // foto correcta (viene afuera)
      window.setAssessmentPreview?.(data.photo_url);
    })
    .catch(err => console.error('loadValoracion error:', err));
}
</script>

                    {{-- Vista del pdf actual xd ausilio llevo dos dias en esto --}}
                    <div x-data="assessmentPdf({{ $lead->id }})" x-init="load()" class="mt-6">
                        <template x-if="loading">
                            <div class="text-sm text-gray-500">Cargando PDF de valoración...</div>
                        </template>

                        <template x-if="!loading && !exists">
                            <div class="text-sm text-gray-400">
                                Aún no hay PDF generado para esta valoración.
                            </div>
                        </template>

                        <template x-if="!loading && exists">
                            <div class="bg-white border rounded-xl p-4 flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-gray-700">Hoja de valoración (PDF)</div>
                                    <div class="text-sm text-gray-500" x-text="filename"></div>
                                </div>

                                <div class="flex gap-2">
                                    <a :href="url" target="_blank"
                                        class="px-4 py-2 rounded bg-[#1C6C73] text-white hover:bg-[#155b61]">
                                        Ver PDF
                                    </a>

                                    <a :href="url" download
                                        class="px-4 py-2 rounded border text-gray-700 hover:bg-gray-100">
                                        Descargar/Imprimir
                                    </a>
                                </div>
                            </div>

                            <!-- Preview embebido (opcional) -->
                            <div class="mt-4 border rounded-xl overflow-hidden" style="height: 600px;">
                                <iframe :src="url" class="w-full h-full"></iframe>
                            </div>
                        </template>
                    </div>
                    <script>
                        function assessmentPdf(leadId) {
                            return {
                                loading: false,
                                exists: false,
                                url: null,
                                filename: null,

                                async load() {
                                    this.loading = true;
                                    try {
                                        const res = await fetch(`/panel/clientes/${leadId}/valoracion/pdf/existe`, {
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest'
                                            }
                                        });
                                        const data = await res.json();
                                        this.exists = !!data.exists;
                                        this.url = data.url || null;
                                        this.filename = data.filename || null;
                                    } catch (e) {
                                        this.exists = false;
                                    } finally {
                                        this.loading = false;
                                    }
                                }
                            }
                        }
                    </script>
                </div>


                {{-- TAB: Info del PX --}}
                <div x-show="tab === 'PX'" x-data="{ subtab: 'summary' }" class="pt-4">

                    {{-- TÍTULO --}}
                    <h3 class="text-lg font-semibold mb-5 text-[#1C6C73]">Cotización del Paciente</h3>

                    {{-- SUB-TABS --}}
                    <div class="border-b mb-4">
                        <nav class="flex space-x-6 text-sm font-[Poppins]">

                            <button @click="subtab = 'summary'"
                                :class="subtab === 'summary' ? 'text-[#1C6C73] border-b-2 border-[#1C6C73]' : 'text-gray-500'"
                                class="pb-2">Cotización</button>

                            <button @click="subtab = 'procedure'"
                                :class="subtab === 'procedure' ? 'text-[#1C6C73] border-b-2 border-[#1C6C73]' : 'text-gray-500'"
                                class="pb-2">Pago del Proced.</button>

                            <button @click="subtab = 'photos'"
                                :class="subtab === 'photos' ? 'text-[#1C6C73] border-b-2 border-[#1C6C73]' : 'text-gray-500'"
                                class="pb-2">Fotos</button>

                            <button @click="subtab = 'hc'"
                                :class="subtab === 'hc' ? 'text-[#1C6C73] border-b-2 border-[#1C6C73]' : 'text-gray-500'"
                                class="pb-2">Historia Clínica</button>

                            <button @click="subtab = 'id'"
                                :class="subtab === 'id' ? 'text-[#1C6C73] border-b-2 border-[#1C6C73]' : 'text-gray-500'"
                                class="pb-2">Identificación</button>

                            <button @click="subtab = 'labs'"
                                :class="subtab === 'labs' ? 'text-[#1C6C73] border-b-2 border-[#1C6C73]' : 'text-gray-500'"
                                class="pb-2">Laboratorios</button>

                            <button @click="subtab = 'contract'"
                                :class="subtab === 'contract' ? 'text-[#1C6C73] border-b-2 border-[#1C6C73]' : 'text-gray-500'"
                                class="pb-2">Contrato</button>

                        </nav>
                    </div>

                    {{-- CONTENIDO DINÁMICO DE SUBTABS --}}

                    {{-- RESUMEN --}}
                    <div x-show="subtab === 'summary'" class="pt-4">
                        @include('crm.leads.px.summary')
                    </div>

                    {{-- PROCEDIMIENTO / PAGOS --}}
                    <div x-show="subtab === 'procedure'" class="pt-4">
                        @include('crm.leads.px.procedure')
                    </div>

                    {{-- FOTOS --}}
                    <div x-show="subtab === 'photos'" class="pt-4">
                        @include('crm.leads.px.photos')
                    </div>

                    {{-- HISTORIA CLINICA --}}
                    <div x-show="subtab === 'hc'" x-init="loadHCFiles()" class="pt-4">
                        @include('crm.leads.px.hc')
                    </div>

                    {{-- IDENTIFICACIÓN --}}
                    <div x-show="subtab === 'id'" x-init="loadIDFiles()" class="pt-4">
                        @include('crm.leads.px.id')
                    </div>

                    {{-- LABS --}}
                    <div x-show="subtab === 'labs'" x-init="loadLabsFiles()" class="pt-4">
                        @include('crm.leads.px.labs')
                    </div>

                    {{-- CONTRATO --}}
                    <div x-show="subtab === 'contract'" class="pt-4">
                        @include('crm.leads.px.contract')
                    </div>

                </div>
                {{-- TAB: Tab de Recibos --}}
                {{-- TAB: Recibos --}}
                <div x-show="tab === 'recibos'" x-cloak class="pt-4" x-data="recibosData({
                    lead_id: {{ $lead->id }},
                    patient_name: @js($lead->full_name),
                    procedure_type: @js($lead->procedure_type),
                    procedure_date: @js($lead->procedure_date),
                    clinic: @js($lead->clinic),
                    total_amount: {{ $totalAmount ?? 0 }}
                })">
                    <div class="border-b mb-6">
                        <nav class="flex gap-6 text-sm font-[Poppins]">
                            <button @click="subrecibo='abono'; $nextTick(() => fillForm())">Abono</button>
                            <button @click="subrecibo='anticipo'">Anticipo</button>
                            <button @click="subrecibo='liquidacion'; $nextTick(() => fillForm())">Liquidación</button>
                            <button @click="subrecibo='tratamiento'; $nextTick(() => fillForm())">Tratamiento</button>
                            <button @click="subrecibo='producto'; $nextTick(() => fillForm())">Producto</button>

                        </nav>
                    </div>

                    <template x-if="subrecibo === 'abono'">
                        <div>@include('crm.leads.recibos.abono')</div>
                    </template>

                    <template x-if="subrecibo === 'anticipo'">
                        <div>@include('crm.leads.recibos.anticipo')</div>
                    </template>

                    <template x-if="subrecibo === 'liquidacion'">
                        <div>@include('crm.leads.recibos.liquidacion')</div>
                    </template>

                    <template x-if="subrecibo === 'tratamiento'">
                        <div>@include('crm.leads.recibos.tratamiento')</div>
                    </template>

                    <template x-if="subrecibo === 'producto'">
                        <div>@include('crm.leads.recibos.producto')</div>
                    </template>
                </div>

                {{-- TAB: Historial de Transacciones --}}
                <div x-show="tab === 'trans'" x-init="loadTransactions()" class="pt-4">
                    {{-- HIstorial de transacciones --}}
                    <div id="payments-summary" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-gray-50 p-4 rounded border">
                            <p class="text-sm text-gray-500">Cotización</p>
                            <p class="text-lg font-semibold" id="quotedAmount">$0.00</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded border">
                            <p class="text-sm text-gray-500">Pagado</p>
                            <p class="text-lg font-semibold text-green-600" id="paidAmount">$0.00</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded border">
                            <p class="text-sm text-gray-500">Pendiente</p>
                            <p class="text-lg font-semibold text-red-600" id="pendingAmount">$0.00</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-[Poppins] text-[#1C6C73]">
                            Historial de Transacciones
                        </h3>
                    </div>
                    
                    {{-- Contenedor de la tabla --}}
                    <div class="w-full overflow-x-auto">                    

                    {{-- TABLA --}}
                    <table id="transactionsTable"
                        class="table table-striped table-bordered display nowrap" style="width:100%">
                        <thead>
                            <tr style="background-color:#4298a7;color:white;">
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Notas</th>
                                <th>Opciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    <script>
                        let transactionsLoaded = false;

                        window.loadTransactions = function() {

                            if (transactionsLoaded) return;
                            transactionsLoaded = true;

                            $('#transactionsTable').DataTable({
                                ajax: {
                                    url: "{{ route('panel.leads.transactions', $lead->id) }}",
                                    dataSrc: function(json) {

                                window.__TOTAL_COTIZADO = Number(json.summary.quoted || 0);

                                $('#quotedAmount').text('$' + window.__TOTAL_COTIZADO.toFixed(2));
                                $('#paidAmount').text('$' + Number(json.summary.paid).toFixed(2));
                                $('#pendingAmount').text('$' + Number(json.summary.pending).toFixed(2));

                                return json.data;
                                }

                                },
                                columns: [{
                                        data: 'id'
                                    },
                                    {
                                        data: 'type'
                                    },
                                    {
                                        data: 'date'
                                    },
                                    {
                                        data: 'amount',
                                        render: data => '$' + Number(data).toFixed(2)
                                    },
                                    {
                                        data: 'payment_method',
                                        defaultContent: '-'
                                    },
                                    {
                                        data: 'notes',
                                        defaultContent: '-'
                                    },
                                    {
                                        data: null,
                                        scrollX: true,
                                        render: function(data) {

                                            let buttons = '';

                                            if (data.receipt_url) {
                                                buttons += `
                                                
                                        <a href="${data.receipt_url}" target="_blank"
                                        class="bg-[#1C6C73] hover:bg-[#4298A7] text-white px-2 py-1 rounded text-xs mr-1">
                                        Recibo
                                        </a>`;
                                            }

                                             if (data.receipt_img) {
                                                buttons += `
                                                <a href="${data.receipt_img}" target="_blank"
                                                    class="bg-[#CDAF95] hover:bg-[#DED5CE] text-white px-2 py-1 rounded text-xs mr-1">
                                                    Imagen
                                                </a>`;
                                            }

                                            buttons += `
                                            @role('Administrador|super_usuario|Super Usuario')
                                                <button onclick="deletePayment(${data.id})"
                                                    class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs">
                                                    Eliminar
                                                </button>
                                            @endrole`;

                                            return buttons;
                                        }
                                    }
                                ],
                                dom: 'Bfrtip',
                                buttons: [{
                                        extend: 'excel',
                                        text: 'Excel',
                                        className: 'bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg',
                                        title: 'Historial de Transacciones - {{ $lead->full_name }}'
                                    },
                                    {
                                        extend: 'pdf',
                                        text: 'PDF',
                                        className: 'bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg',
                                        title: 'Historial de Transacciones - {{ $lead->full_name }}'
                                    }
                                ],
                                language: {
                                    url: "//cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json",
                                    zeroRecords: "No hay transacciones registradas"
                                }
                            });
                        };

                        function deletePayment(id) {
                    Swal.fire({
                        title: '¿Eliminar pago?',
                        text: 'Esta acción no se puede deshacer',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then(result => {
                        if (!result.isConfirmed) return;

                        fetch(`/panel/leads/payments/${id}`, {
                        method: 'DELETE',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                        })
                        .then(async res => {
                        const data = await res.json();

                        if (!res.ok) {
                            throw data;
                        }

                        return data;
                        })
                        .then(() => {
                        $('#transactionsTable').DataTable().ajax.reload();
                        Swal.fire('Eliminado', 'El pago fue eliminado', 'success');
                        })
                        .catch(err => {
                        Swal.fire(
                            'Acción no permitida',
                            err?.message || 'No tienes permisos para eliminar, contacta a administración.',
                            'error'
                        );
                        });
                    });
                    }
                        function recibosData(data) {
                            return {
                                subrecibo: 'abono',
                                ...data,
    total_amount: Number(data.total_amount || window.__TOTAL_COTIZADO || 0),

                                init() {
                                    this.fillForm();
                                },

                                fillForm() {
                                    const set = (id, value) => {
                                        const el = document.getElementById(id);
                                        if (el && value !== null && value !== undefined) {
                                            el.value = value;
                                        }
                                    };

                                    const setters = {
                                        abono: () => {
                                            set('r_lead_id', this.lead_id);
                                            set('r_patient_name', this.patient_name);
                                            set('r_procedure_type', this.procedure_type);
                                            set('r_procedure_date', this.procedure_date);
                                            set('r_clinic', this.clinic);
                                        },

                                        liquidacion: () => {

                                            set('l_lead_id', this.lead_id);
                                            set('l_patient_name', this.patient_name);
                                            set('l_procedure_type', this.procedure_type);
                                            set('l_procedure_date', this.procedure_date);
                                            set('l_clinic', this.clinic);

                                            window.dispatchEvent(new CustomEvent('liquidacion:set-total', {
                                                detail: Number(this.total_amount)
                                            }));

                                            window.dispatchEvent(new CustomEvent('liquidacion:set-patient', {
                                                detail: this.patient_name
                                            }));
                                        },


                                        tratamiento: () => {
                                            set('t_lead_id', this.lead_id);
                                            set('t_patient_name', this.patient_name);
                                            set('t_clinic', this.clinic);
                                        },
                                        producto: () => {
                                            set('p_lead_id', this.lead_id);
                                            set('p_patient_name', this.patient_name);
                                            set('p_clinic', this.clinic);
                                        }
                                    };

                                    if (setters[this.subrecibo]) {
                                        setters[this.subrecibo]();
                                    }
                                }
                            }
                        }
                    </script>
                </div>
                </div>
            </div>

        </div>
    @endsection
