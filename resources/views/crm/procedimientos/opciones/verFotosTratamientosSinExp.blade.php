@extends('panel.layouts.panel')
@section('title', 'Fotos del tratamiento')

@section('content')
    <section class="py-10 px-6 bg-white">
        <div class="p-6 space-y-6">

            {{-- ENCABEZADO --}}
            <div>
                <h1 class="text-3xl font-[Poppins] text-[#1C6C73] mb-2">
                    Fotos y notas de Tratamientos (Sin Expediente)
                </h1>
                <p class="text-gray-600">Gestiona fotos y tratamientos creados sin expediente.</p>
                <br>
                <a class="bg-[#1C6C73] hover:bg-[#4298A7] text-white px-4 py-2 rounded"
                    href="{{ route('panel.tratamientos.index') }}">
                    <i class="fa-solid fa-square-caret-left"></i> Volver al listado
                </a>
            </div>

            {{-- DATOS DEL PACIENTE --}}
            <div class="bg-white shadow-md rounded-xl p-6 border border-gray-200">
                <h2 class="text-2xl font-[Poppins] text-[#1C6C73] mb-3">Datos del paciente</h2>
                <div class="grid md:grid-cols-2 gap-3 text-gray-700">
                    <p><strong>Nombre:</strong> {{ $paciente->name }}</p>
                    <p><strong>Clínica:</strong> {{ $paciente->clinic }}</p>
                    <p>
        <button id="openReciboTratamiento"
            class="bg-[#1C6C73] hover:bg-[#4298A7] text-white px-5 py-2 rounded-lg shadow">
            <i class="fa-solid fa-plus"></i>
            Generar recibo
        </button>
        </p>

        <!-- Cargar recibos -->
        <p>

        <div class="mt-4 w-full">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-gray-800">Recibos generados</h3>
            <span class="text-xs text-gray-500">Desliza para ver más</span>
          </div>

          <div id="receiptsBox"
            class="w-full rounded-2xl border border-gray-200 bg-white shadow-sm
              max-h-[260px] overflow-y-auto overscroll-contain">
            <div id="receiptsList" class="divide-y divide-gray-100 w-full"></div>
          </div>
        </div>

        </p>
                </div>
            </div>

            {{-- BOTÓN CREAR TRATAMIENTO --}}
            <div class="flex justify-end mb-4">
                <button id="openCrearTratamiento"
                    class="bg-[#1C6C73] hover:bg-[#4298A7] text-white px-5 py-2 rounded-lg shadow">
                    <i class="fa-solid fa-plus"></i> Crear Tratamiento
                </button>
            </div>

            {{-- MODAL CREAR TRATAMIENTO --}}
            <div id="modalCrearTratamiento"
                class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 overflow-y-auto">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl p-6 relative">
                    <button id="closeCrearTratamiento"
                        class="absolute top-3 right-3 text-gray-500 hover:text-gray-800">✕</button>

                    <h2 class="text-2xl font-[Poppins] text-[#1C6C73] mb-4 text-center">
                        Crear Nuevo Tratamiento
                    </h2>

                    <form id="formCrearTratamiento">
                        @csrf

                        <input type="hidden" name="px_id" value="{{ $px_id }}">
                        <input type="hidden" name="origin" value="{{ $paciente->clinic }}">
                        <input type="hidden" name="inv_type" value="0">
                        <input type="hidden" name="created_by" value="{{ Auth::user()->id }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="font-semibold">Fecha</label>
                                <input type="date" name="date" id="treatment_date_sinexp"
                                class="w-full border rounded-lg p-2">
                            </div>

                            @if ($paciente->clinic == '')
                                <div>
                                    <label class="font-semibold">Clínica</label>
                                    <select id="clinic" name="clinic" class="w-full border rounded-lg px-3 py-2">
                                        <option value="">Seleccionar...</option>
                                        <option value="Santa Fe">Santa Fe</option>
                                        <option value="Pedregal">Pedregal</option>
                                        <option value="Queretaro">Querétaro</option>
                                    </select>
                                </div>
                            @else
                                <div>
                                    <label class="font-semibold">Clínica</label>
                                    <input type="text" name="clinic" value="{{ $paciente->clinic }}"
                                        class="w-full border rounded-lg p-2" readonly>
                                </div>
                            @endif

                            <div>
                                <label class="font-semibold">Doctor</label>
                                <input type="text" name="doctor" class="w-full border rounded-lg p-2">
                            </div>

                            <div>
                                <label class="font-semibold">Tipo de Tratamiento</label>
                                <input type="text" name="type" placeholder="Ej. PRP, Exosomas..."
                                    class="w-full border rounded-lg p-2">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="font-semibold">Nota Inicial (opcional)</label>
                            <textarea name="notes" rows="2" class="w-full border rounded-lg p-3"></textarea>
                        </div>

                        <div class="flex justify-end mt-4">
                            <button type="button" id="btnGuardarTratamiento"
                                class="bg-[#1C6C73] hover:bg-[#4298A7] text-white px-5 py-2 rounded-lg shadow">
                                Guardar Tratamiento
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($tratamientos->isEmpty())
                <p class="text-gray-500 text-center">
                    No hay tratamientos creados para este paciente sin expediente.
                </p>
            @else
                <p class="text-red-600 text-lg text-center">Da clic en el tratamiento para ver la nota, fotos y acciones.
                </p>
                <p class="text-red-600 text-xl text-center">
                  <i class="fa-solid fa-arrow-down-long"></i> CARRUSEL Y NOTAS DE TRATAMIENTOS. <i class="fa-solid fa-arrow-down-long"></i>
                </p>

                {{-- NOTA PRINCIPAL --}}
                <div id="notaPrincipal" class="mt-6 {{ empty($treatmentId) ? 'hidden' : '' }}">
                    <h3 class="text-xl font-semibold text-[#1C6C73] mb-2">Nota del tratamiento</h3>
                    <div class="border rounded-lg p-4 bg-gray-50 shadow">
                        <p id="textoNotaPrincipal" class="text-gray-700 whitespace-pre-line"></p>
                    </div>
                </div>

                {{-- CARRUSEL TRATAMIENTOS --}}
                <div class="swiper mySwiper">
                    <div class="swiper-wrapper">
                        @foreach ($tratamientos as $treatment)
                            <div class="swiper-slide text-center" data-step="{{ $treatment->id }}">
                                <div class="flex flex-col items-center">
                                    <a href="{{ route('panel.tratamientos.sin-exp.view', ['px_id' => $px_id, 'treatmentId' => $treatment->id]) }}"
                                        class="view_imgs inline-block min-w-[160px] px-5 py-3 rounded-xl border transition-all duration-200 text-center leading-tight
                  {{ (int) $treatmentId === (int) $treatment->id
                      ? 'bg-[#1C6C73] text-white border-[#1C6C73] shadow-md'
                      : 'bg-white text-gray-700 border-gray-300 hover:bg-[#1C6C73]/10 hover:border-[#1C6C73]' }}"
                                        data-json-url="{{ route('panel.tratamientos.sin-exp.json', ['px_id' => $px_id, 'treatmentId' => $treatment->id]) }}"
                                        data-treatment-id="{{ $treatment->id }}" data-phase="{{ $treatment->type ?? '' }}"
                                        data-active="{{ (int) $treatmentId === (int) $treatment->id ? '1' : '0' }}">
                                        <span class="block text-base font-semibold">
                                            {{ $treatment->type ?? 'Tratamiento' }}
                                        </span>

                                        <span
                                            class="block text-xs {{ (int) $treatmentId === (int) $treatment->id ? 'text-gray-200' : 'text-gray-500' }}">
                                            {{ $treatment->date ? \Carbon\Carbon::parse($treatment->date)->format('d/m/Y') : 'Sin fecha' }}
                                        </span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>

                {{-- MENSAJE SI NO HAY SELECCIÓN --}}
                <div id="sinSeleccionMsg" class="mt-6 {{ empty($treatmentId) ? '' : 'hidden' }}">
                    <div class="border rounded-xl p-4 bg-amber-50 text-amber-900">
                        <strong>Tip:</strong> selecciona un tratamiento del carrusel para cargar nota, fotos y habilitar
                        acciones.
                    </div>
                </div>

                {{-- GALERÍA --}}
                <div id="galeriaFotos" class="mt-6">
                    @if (empty($treatmentId))
                        <p class="text-gray-500 text-center w-full">Aún no hay tratamiento seleccionado.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @forelse ($imagenes as $img)
                                <div class="border p-3 rounded-lg shadow hover:shadow-lg transition bg-white">
                                    <a href="{{ $img['url'] }}" target="_blank">
                                        <img src="{{ $img['thumb'] }}" alt="{{ $img['name'] }}"
                                            class="rounded-lg h-48 w-full object-cover mb-2"
                                            onerror="this.onerror=null;this.src='{{ $img['url'] }}'">
                                    </a>
                                    <p class="text-sm text-center truncate mb-2">{{ $img['name'] }}</p>

                                    @role('super_usuario|administrador|Médicos')
                                    <button type="button" data-url="{{ $img['url'] }}"
                                        class="deleteFoto text-red-600 hover:text-red-800 text-sm w-full">
                                        Eliminar
                                    </button>
                                    @endrole
                                </div>
                            @empty
                                <p class="text-gray-500 text-center w-full">No hay fotos disponibles.</p>
                            @endforelse
                        </div>
                    @endif
                </div>

                {{-- FORM SUBIR FOTO --}}
                <form id="uploadForm" disabled
                    action="{{ $treatmentId ? route('panel.tratamientos.sinexp.foto.store', ['px_id' => $px_id, 'treatmentId' => $treatmentId]) : '#' }}"
                    method="POST" enctype="multipart/form-data"
                    class="mt-6 border-2 border-dashed border-gray-300 rounded-xl p-6 text-center bg-gray-50 hover:bg-gray-100 transition">
                    @csrf

                    <input type="hidden" name="phase" id="phaseUploadField"
                        value="{{ $tratamientos->firstWhere('id', $treatmentId)->type ?? '' }}">
                    <input type="hidden" name="treatmentId" id="treatmentIdUpload" value="{{ $treatmentId ?? '' }}">

                    <p class="text-gray-600 mb-2">
                        Arrastra tus fotos aquí o haz clic para seleccionarlas<br>
                        <span class="text-xs text-gray-500">HEIC/HEIF/JPG/PNG (se genera thumb automáticamente)</span>
                    </p>

                    <input type="file" id="fotoInput" name="fotos[]" class="hidden" accept="image/*,.heic,.heif"
                        multiple>

                    <button type="button" id="btnSelectFile" disabled
                        class="bg-[#1C6C73] hover:bg-[#4298A7] text-white px-4 py-2 rounded disabled:opacity-60 disabled:cursor-not-allowed">
                        Seleccionar archivos
                    </button>
                </form>

                {{-- LISTA DE NOTAS --}}
                <div class="mt-10 border-t pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-[Poppins] text-[#1C6C73]">Notas Médicas</h2>

                        <button id="openNotas"
                            class="bg-[#1C6C73] hover:bg-[#4298A7] text-white px-5 py-2 rounded-lg shadow disabled:opacity-60 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-notes-medical"></i> Agregar Nota
                        </button>
                    </div>

                    <div id="listaNotas">
                        <p class="text-gray-400 text-center">
                            {{ empty($treatmentId) ? 'Selecciona un tratamiento para ver sus notas.' : 'Cargando notas…' }}
                        </p>
                    </div>
                </div>

                {{-- MODAL NOTAS --}}
                <div id="modalNotas"
                    class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 overflow-y-auto">
                    <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl p-6 relative">
                        <button id="closeNotas"
                            class="absolute top-3 right-3 text-gray-500 hover:text-gray-800">✕</button>

                        <h2 class="text-2xl font-[Poppins] text-[#1C6C73] mb-4 text-center">Agregar Nota Médica</h2>

                        <form id="formNota" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <input type="hidden" name="identifier" value="{{ $px_id }}">
                            <input type="hidden" name="identifier_type" value="id">
                            <input type="hidden" name="author" value="{{ Auth::user()->id }}">
                            <input type="hidden" name="phase" id="phaseFieldNotas"
                                value="{{ $tratamientos->firstWhere('id', $treatmentId)->type ?? '' }}">
                            <input type="hidden" name="clinic" value="{{ $paciente->clinic }}">

                            <textarea name="note" rows="2" class="w-full border rounded-lg p-3 text-gray-700"></textarea>
                            
                            <input type="date" name="date" id="nota_date_sinexp"
                             class="border rounded-lg p-3 text-gray-700 w-full">

                            <div class="flex justify-end mt-4">
                                <button type="button" id="btnGuardarNota"
                                    class="bg-[#1C6C73] hover:bg-[#4298A7] text-white px-5 py-2 rounded-lg shadow">
                                    Guardar Nota
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            @endif

             {{-- modal recibos --}}
<div id="modalReciboTratamiento"
  class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">

  <div class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl border border-gray-200 relative
              max-h-[90vh] flex flex-col">

    {{-- Header fijo --}}
    <div class="px-5 sm:px-6 pt-5 sm:pt-6 pb-3 border-b border-gray-100">
      <button id="closeReciboTratamiento"
        class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-2xl leading-none">✕</button>

      <h2 class="text-2xl sm:text-3xl font-[Poppins] text-[#1C6C73] text-center leading-tight">
        Generar recibo
      </h2>
      <p class="text-gray-500 text-center mt-1 text-sm">
        Completa los datos para generar el PDF.
      </p>
    </div>

    {{-- Body con scroll --}}
    <div class="px-5 sm:px-6 py-4 overflow-y-auto">
      <form id="formReciboTratamiento" class="space-y-4">
        @csrf

        <input type="hidden" name="lead_id" id="rc_lead_id" value="{{ (int)($px_id ?? 0) }}">

        {{-- 1 col en md, 2 col hasta lg --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
              Fecha <span class="text-red-500">*</span>
            </label>
            <input type="date" name="receipt_date" id="rc_date"
              class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#1C6C73]/30 focus:border-[#1C6C73]"
              value="{{ now()->format('Y-m-d') }}">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
              Nombre del Paciente <span class="text-red-500">*</span>
            </label>
            <input type="text" name="patient_name" id="rc_patient"
              class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#1C6C73]/30 focus:border-[#1C6C73]"
              value="{{ $paciente->name ?? '' }}">
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
              Tipo de recibo <span class="text-red-500">*</span>
            </label>
           <select name="Tipo" id="rc_tipo"
            class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm bg-white
                  focus:outline-none focus:ring-2 focus:ring-[#1C6C73]/30 focus:border-[#1C6C73]">
            <option value="Tratamiento" selected>Tratamiento</option>
            <option value="Producto">Producto</option>
          </select>

          </div>

          <div>
           <label id="rc_item_label" class="block text-sm font-semibold text-gray-700 mb-1">
            Tratamiento que se realizó <span class="text-red-500">*</span>
          </label>

          {{-- SELECT tratamientos --}}
          <select name="treatment_name" id="rc_treatment"
            class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm bg-white
                  focus:outline-none focus:ring-2 focus:ring-[#1C6C73]/30 focus:border-[#1C6C73]">
            <option value="">Selecciona</option>
            <option value="Exosomas">Exosomas</option>
            <option value="Dutasteride">Dutasteride</option>
            <option value="Kenalog">Kenalog</option>
            <option value="Factores de Crecimiento">Factores de Crecimiento</option>
          </select>

          {{-- INPUT productos (mismo campo “treatment_name” para no cambiar backend) --}}
          <input type="text" id="rc_products" name="product_name"
            placeholder="Ej. Minoxidil + Dutasteride, Shampoo, etc."
            class="hidden w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm
                  focus:outline-none focus:ring-2 focus:ring-[#1C6C73]/30 focus:border-[#1C6C73]">

          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
              Monto total (MXN) <span class="text-red-500">*</span>
            </label>
            <input type="number" name="total" id="rc_total" step="0.01" min="0"
              class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#1C6C73]/30 focus:border-[#1C6C73]">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
              Sucursal <span class="text-red-500">*</span>
            </label>
            <select name="clinic" id="rc_clinic"
              class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm bg-white
                     focus:outline-none focus:ring-2 focus:ring-[#1C6C73]/30 focus:border-[#1C6C73]">
              <option value="">Selecciona</option>
              <option value="Santa Fe" {{ ($paciente->clinic ?? '')==='Santa Fe'?'selected':'' }}>Santa Fe</option>
              <option value="Pedregal" {{ ($paciente->clinic ?? '')==='Pedregal'?'selected':'' }}>Pedregal</option>
              <option value="Queretaro" {{ ($paciente->clinic ?? '')==='Queretaro'?'selected':'' }}>Queretaro</option>
            </select>
          </div>
        </div>

        {{-- Pagos: 1 columna en md, 2 en lg --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div class="rounded-2xl border border-gray-200 p-4">
            <h3 class="font-[Poppins] text-[#1C6C73] text-base mb-3">Método de pago 1</h3>

            <label class="block text-sm font-semibold text-gray-700 mb-1">
              Forma de pago <span class="text-red-500">*</span>
            </label>
            <select name="pay_method_1" id="rc_method1"
              class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm bg-white
                     focus:outline-none focus:ring-2 focus:ring-[#1C6C73]/30 focus:border-[#1C6C73]">
              <option value="">Selecciona</option>
              <option value="Efectivo">Efectivo</option>
              <option value="Transferencia">Transferencia</option>
              <option value="Dólares">Dólares</option>
              <option value="Tarjeta de Débito">Tarjeta de Débito</option>
              <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
            </select>

            <label class="block text-sm font-semibold text-gray-700 mt-3 mb-1">
              Monto pago 1 <span class="text-red-500">*</span>
            </label>
            <input type="number" name="pay_amount_1" id="rc_amount1" step="0.01" min="0"
              class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm
                     focus:outline-none focus:ring-2 focus:ring-[#1C6C73]/30 focus:border-[#1C6C73]">
          </div>

          <div class="rounded-2xl border border-gray-200 p-4">
            <h3 class="font-[Poppins] text-[#1C6C73] text-base mb-3">Método de pago 2 (opcional)</h3>

            <label class="block text-sm font-semibold text-gray-700 mb-1">Forma de pago 2</label>
            <select name="pay_method_2" id="rc_method2"
              class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm bg-white
                     focus:outline-none focus:ring-2 focus:ring-[#1C6C73]/30 focus:border-[#1C6C73]">
              <option value="">Ninguno</option>
              <option value="Efectivo">Efectivo</option>
              <option value="Transferencia">Transferencia</option>
              <option value="Dólares">Dólares</option>
              <option value="Tarjeta de Débito">Tarjeta de Débito</option>
              <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
            </select>

            <label class="block text-sm font-semibold text-gray-700 mt-3 mb-1">Monto pago 2</label>
            <input type="number" name="pay_amount_2" id="rc_amount2" step="0.01" min="0" disabled
              class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm
                     disabled:bg-gray-100 disabled:text-gray-500
                     focus:outline-none focus:ring-2 focus:ring-[#1C6C73]/30 focus:border-[#1C6C73]">
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">
            Notas (aparecerán en el recibo)
          </label>
          <textarea name="notes" id="rc_notes" rows="3"
            class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm
                   focus:outline-none focus:ring-2 focus:ring-[#1C6C73]/30 focus:border-[#1C6C73]"></textarea>
        </div>

        <div id="rc_result" class="hidden text-sm"></div>

        {{-- Footer fijo --}}
        <div class="sticky bottom-0 bg-white pt-3 pb-1 border-t border-gray-100 flex justify-end gap-2">
          <button type="button" id="closeReciboTratamiento2"
            class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm">
            Cancelar
          </button>

          <button type="button" id="btnGenerarRecibo"
            class="bg-[#1C6C73] hover:bg-[#4298A7] text-white px-6 py-2.5 rounded-xl shadow text-sm">
            Generar PDF
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const CSRF = "{{ csrf_token() }}";
  const pxId = document.getElementById('rc_lead_id')?.value || "{{ (int)($px_id ?? 0) }}";

  const routes = {
    gen: @json(route('panel.receipts.treatments.generate')),
    list: @json(url('/panel/receipts/treatments/list')) + '/' + pxId,
  };

  const el = {
    modal: document.getElementById('modalReciboTratamiento'),
    open: document.getElementById('openReciboTratamiento'),

    close1: document.getElementById('closeReciboTratamiento'),
    close2: document.getElementById('closeReciboTratamiento2'),

    btn: document.getElementById('btnGenerarRecibo'),
    form: document.getElementById('formReciboTratamiento'),
    res: document.getElementById('rc_result'),

    tipo: document.getElementById('rc_tipo'),
    label: document.getElementById('rc_item_label'),
    selTrat: document.getElementById('rc_treatment'),
    inpProd: document.getElementById('rc_products'),

    method2: document.getElementById('rc_method2'),
    amount2: document.getElementById('rc_amount2'),
    total: document.getElementById('rc_total'),
    amount1: document.getElementById('rc_amount1'),

    receiptsList: document.getElementById('receiptsList'),
  };

  const openModal = () => el.modal?.classList.remove('hidden');
  const closeModal = () => el.modal?.classList.add('hidden');

  const show = (ok, msg, link) => {
    if (!el.res) return;
    el.res.classList.remove('hidden');
    el.res.className = ok
      ? 'mt-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg p-3'
      : 'mt-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg p-3';
    el.res.innerHTML = link
      ? `${msg}<br><a class="underline" href="${link}" target="_blank" rel="noopener">Abrir PDF</a>`
      : msg;
  };

  const safeJson = async (res) => {
    const ct = res.headers.get('content-type') || '';
    if (!ct.includes('application/json')) {
      const raw = await res.text();
      throw new Error(`Respuesta no JSON (${res.status}). ${raw.slice(0, 160)}`);
    }
    return await res.json();
  };

  // =========================
  // LISTADO DE RECIBOS
  // =========================
  const badge = (type) => {
    return (type === 'Producto')
      ? `<span class="px-2 py-0.5 text-xs rounded-full bg-amber-50 text-amber-700 border border-amber-200">Producto</span>`
      : `<span class="px-2 py-0.5 text-xs rounded-full bg-teal-50 text-teal-700 border border-teal-200">Tratamiento</span>`;
  };

  async function loadReceipts() {
    if (!el.receiptsList) return;

    el.receiptsList.innerHTML = `<div class="p-4 text-gray-500 text-sm">Cargando…</div>`;

    try {
      const res = await fetch(routes.list, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      });

      const data = await safeJson(res);
      if (!res.ok || !data.success) throw new Error(data.message || 'No se pudo cargar');

      if (!data.data || data.data.length === 0) {
        el.receiptsList.innerHTML = `<div class="p-4 text-gray-500 text-sm">Aún no hay recibos.</div>`;
        return;
      }

      el.receiptsList.innerHTML = data.data.map(r => `
  <div class="p-3 sm:p-4">
    <div class="grid grid-cols-1 gap-3 sm:flex sm:items-center sm:justify-between sm:gap-4">

      <!-- INFO -->
      <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2 min-w-0 mb-1">
          ${badge(r.type)}
          <span class="text-sm font-semibold text-gray-800">#${r.id}</span>
          <span class="text-xs text-gray-500">${r.date || ''}</span>
        </div>

        <div class="text-xs text-gray-600 truncate">
          <span class="font-medium">Método:</span> ${r.method || '—'} •
          <span class="font-medium">Total:</span> $${Number(r.amount || 0).toFixed(2)}
        </div>
      </div>

      <!-- BOTONES -->
      <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center sm:gap-2 sm:shrink-0">
        <a href="${r.url}" target="_blank" rel="noopener"
          class="text-center px-3 py-2 text-sm rounded-lg border border-gray-200 hover:bg-gray-50">
          Ver PDF
        </a>

        <button type="button" data-url="${r.url}"
          class="btnOpenPdf text-center px-3 py-2 text-sm rounded-lg bg-[#1C6C73] text-white hover:bg-[#4298A7]">
          Abrir
        </button>
      </div>

    </div>
  </div>
`).join('');


    } catch (e) {
      console.error(e);
      el.receiptsList.innerHTML = `<div class="p-4 text-red-600 text-sm">Error cargando recibos.</div>`;
    }
  }

  document.addEventListener('click', (e) => {
    const b = e.target.closest('.btnOpenPdf');
    if (!b) return;
    window.open(b.dataset.url, '_blank', 'noopener');
  });

  // =========================
  // MODAL EVENTS
  // =========================
  el.open?.addEventListener('click', () => {
    openModal();
    // opcional: limpiar mensaje
    el.res?.classList.add('hidden');
    el.res && (el.res.innerHTML = '');
  });

  el.close1?.addEventListener('click', closeModal);
  el.close2?.addEventListener('click', closeModal);

  el.modal?.addEventListener('click', (e) => {
    if (e.target === el.modal) closeModal();
  });

  // =========================
  // Habilitar monto2 solo si hay método2
  // =========================
  el.method2?.addEventListener('change', () => {
    const has = !!el.method2.value;
    if (el.amount2) {
      el.amount2.disabled = !has;
      if (!has) el.amount2.value = '';
    }
  });

  // autollenar monto1 = total si está vacío
  el.total?.addEventListener('input', () => {
    if (el.amount1 && !el.amount1.value) el.amount1.value = el.total.value;
  });

  // =========================
  // Tipo recibo: Tratamiento/Producto
  // =========================
  function syncTipoRecibo() {
    if (!el.tipo || !el.label || !el.selTrat || !el.inpProd) return;

    const isProducto = (el.tipo.value === 'Producto');

    if (isProducto) {
      el.label.innerHTML = `Producto(s) <span class="text-red-500">*</span>`;

      el.selTrat.classList.add('hidden');
      el.inpProd.classList.remove('hidden');

      // solo el activo manda value
      el.selTrat.disabled = true;
      el.inpProd.disabled = false;

      el.selTrat.required = false;
      el.inpProd.required = true;

      el.selTrat.value = "";
      el.inpProd.focus();
    } else {
      el.label.innerHTML = `Tratamiento que se realizó <span class="text-red-500">*</span>`;

      el.inpProd.classList.add('hidden');
      el.selTrat.classList.remove('hidden');

      // solo el activo manda value
      el.inpProd.disabled = true;
      el.selTrat.disabled = false;

      el.inpProd.required = false;
      el.selTrat.required = true;

      el.inpProd.value = "";
    }
  }

  el.tipo?.addEventListener('change', syncTipoRecibo);
  syncTipoRecibo(); // init

  // =========================
  // GENERAR PDF
  // =========================
  el.btn?.addEventListener('click', async () => {
    try {
const fd = new FormData(el.form); 
     const tipo = (fd.get('Tipo') || '').trim();

const required = ['receipt_date','patient_name','total','Tipo','pay_method_1','pay_amount_1','clinic'];
if (tipo === 'Producto') required.push('product_name');
else required.push('treatment_name');

const pretty = {
  receipt_date: 'Fecha',
  patient_name: 'Nombre del Paciente',
  Tipo: 'Tipo de recibo',
  treatment_name: 'Tratamiento',
  product_name: 'Producto',
  total: 'Monto total',
  pay_method_1: 'Forma de pago 1',
  pay_amount_1: 'Monto pago 1',
  clinic: 'Sucursal'
};

for (const k of required) {
  if (!String(fd.get(k) || '').trim()) {
    return show(false, `Falta: ${pretty[k] || k}`);
  }
}

      // si hay método2, requiere monto2
      if (fd.get('pay_method_2') && !String(fd.get('pay_amount_2') || '').trim()) {
        return show(false, 'Falta monto de pago 2.');
      }

      // manda JSON
      const payload = Object.fromEntries(fd.entries());

      // opcional: feedback UI
      el.btn.disabled = true;
      el.btn.classList.add('opacity-70', 'cursor-not-allowed');

      const res = await fetch(routes.gen, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': CSRF,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(payload)
      });

      const data = await safeJson(res);
      if (!res.ok || !data.success) throw new Error(data.message || 'No se pudo generar');

      show(true, 'Recibo generado correctamente.', data.pdf_url);

      //  abrir automáticamente el PDF
      if (data.pdf_url) window.open(data.pdf_url, '_blank', 'noopener');

      //  refrescar lista debajo del botón
      await loadReceipts();

      // opcional: cerrar modal
      // closeModal();

    } catch (e) {
      console.error(e);
      show(false, e.message || 'Error generando recibo');
    } finally {
      if (el.btn) {
        el.btn.disabled = false;
        el.btn.classList.remove('opacity-70', 'cursor-not-allowed');
      }
    }
  });

  // =========================
  // INIT LIST
  // =========================
  loadReceipts();
});
</script>
        </div>
    </section>

    {{-- =========================
  SWIPER + ESTILOS
========================= --}}
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            if (typeof Swiper === "undefined") return;

            const swiper = new Swiper(".mySwiper", {
                slidesPerView: "auto",
                spaceBetween: 12,
                freeMode: true,
                grabCursor: true,
                centeredSlides: false,
                watchOverflow: true,
                simulateTouch: true,
                allowTouchMove: true,
                touchStartPreventDefault: false,
                passiveListeners: true,
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev"
                },
                mousewheel: false,
                breakpoints: {
                    0: {
                        spaceBetween: 10
                    },
                    640: {
                        spaceBetween: 14
                    },
                    1024: {
                        spaceBetween: 16
                    }
                },
            });

            const active = document.querySelector('.view_imgs[data-active="1"]');
            if (active) {
                const slide = active.closest('.swiper-slide');
                const idx = Array.from(slide.parentNode.children).indexOf(slide);
                if (idx >= 0) swiper.slideTo(idx, 0);
            }
        });
    </script>

    <style>
        .mySwiper {
            padding: 8px 12px;
        }

        .mySwiper .swiper-slide {
            width: auto !important;
        }

        @media (max-width: 640px) {
            .mySwiper {
                padding: 8px 6px;
            }

            .swiper-button-next,
            .swiper-button-prev {
                display: none !important;
            }

            .view_imgs {
                min-width: 140px !important;
                padding: 10px 12px !important;
            }
        }
    </style>

    {{-- =========================
  LOGICA MODULO
========================= --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const CSRF = "{{ csrf_token() }}";
            const pxId = "{{ (int) $px_id }}";

             // ✅ fecha de hoy en formato YYYY-MM-DD
            function todayISO() {
            const d = new Date();
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
            }

            // ✅ setea "hoy" SOLO si está vacío
            function setTodayIfEmpty(selectorOrEl) {
            const el = (typeof selectorOrEl === 'string') ? document.querySelector(selectorOrEl) : selectorOrEl;
            if (!el) return;
            if (!el.value) el.value = todayISO();
            }

            const routes = {
                notasShow: "{{ route('panel.procedimientos.notas.show') }}",
                notasStore: "{{ route('panel.procedimientos.notas.store') }}",
                notasDeleteBase: "{{ url('/panel/procedimientos/notas/delete') }}",

                //  crear tratamiento sin exp
                treatmentsStore: "{{ route('panel.procedimientos.treatments.sinexp.store') }}",

                //  base fijo
                sinExpBase: @json(url('/panel/tratamientos/sin-expediente')),
            };

            const el = {
                galeria: document.getElementById('galeriaFotos'),
                notaPrincipalBox: document.getElementById('notaPrincipal'),
                textoNotaPrincipal: document.getElementById('textoNotaPrincipal'),

                phaseNotas: document.getElementById('phaseFieldNotas'),
                phaseUpload: document.getElementById('phaseUploadField'),
                treatmentIdUpload: document.getElementById('treatmentIdUpload'),

                // ✅ modal crear tratamiento
                modalCrear: document.getElementById('modalCrearTratamiento'),
                openCrear: document.getElementById('openCrearTratamiento'),
                closeCrear: document.getElementById('closeCrearTratamiento'),
                formCrearTratamiento: document.getElementById('formCrearTratamiento'),
                btnGuardarTratamiento: document.getElementById('btnGuardarTratamiento'),

                // ✅ modal notas
                modalNotas: document.getElementById('modalNotas'),
                openNotas: document.getElementById('openNotas'),
                closeNotas: document.getElementById('closeNotas'),
                listaNotas: document.getElementById('listaNotas'),
                formNota: document.getElementById('formNota'),
                btnGuardarNota: document.getElementById('btnGuardarNota'),

                // ✅ upload
                uploadForm: document.getElementById('uploadForm'),
                fotoInput: document.getElementById('fotoInput'),
                btnSelectFile: document.getElementById('btnSelectFile'),
            };

            const openModal = (m) => m && m.classList.remove('hidden');
            const closeModal = (m) => m && m.classList.add('hidden');

            const hasSelection = () => {
                const v = (el.treatmentIdUpload?.value || '').toString().trim();
                return v !== '' && parseInt(v, 10) > 0;
            };

            const setUiEnabled = (enabled) => {
                if (el.btnSelectFile) el.btnSelectFile.disabled = !enabled;
                if (el.openNotas) el.openNotas.disabled = !enabled;
            };

            function setNotaInicial(nota) {
                const txt = (nota || "").trim();
                if (!el.textoNotaPrincipal || !el.notaPrincipalBox) return;
                el.textoNotaPrincipal.textContent = txt;
                // si no hay selección, igual ocultamos (tu vista ya lo controla también)
                el.notaPrincipalBox.classList.toggle('hidden', !txt);
            }

            async function safeJson(res) {
                if (res.status === 204) return {
                    status: "success"
                };
                const ct = res.headers.get('content-type') || '';
                if (!ct.includes('application/json')) {
                    const text = await res.text();
                    throw {
                        message: 'Respuesta no JSON',
                        raw: text,
                        status: res.status
                    };
                }
                return await res.json();
            }

            function renderGaleria(images) {
                if (!el.galeria) return;

                el.galeria.innerHTML = '';

                if (!images || images.length === 0) {
                    el.galeria.innerHTML =
                        `<p class="text-gray-500 text-center w-full">No hay fotos disponibles.</p>`;
                    return;
                }

                const grid = document.createElement('div');
                grid.className = 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4';

                images.forEach(img => {
                    const card = document.createElement('div');
                    card.className = 'border p-3 rounded-lg shadow hover:shadow-lg transition bg-white';

                    card.innerHTML = `
        <a href="${img.url}" target="_blank">
          <img src="${img.thumb}" alt="${img.name}"
              class="rounded-lg h-48 w-full object-cover mb-2"
              onerror="this.onerror=null;this.src='${img.url}'">
        </a>
        <p class="text-sm text-center truncate mb-2">${img.name}</p>
        @role('super_usuario|administrador|Médicos')
        <button type="button"
                data-url="${img.url}"
                class="deleteFoto text-red-600 hover:text-red-800 text-sm w-full">
          Eliminar
        </button>
        @endrole
      `;
                    grid.appendChild(card);
                });

                el.galeria.appendChild(grid);
            }

            function marcarActivo(a) {
                document.querySelectorAll('.view_imgs').forEach(x => {
                    x.classList.remove('bg-[#1C6C73]', 'text-white', 'border-[#1C6C73]', 'shadow-md');
                    x.classList.add('bg-white', 'text-gray-700', 'border-gray-300');
                    x.dataset.active = "0";
                });

                a.classList.remove('bg-white', 'text-gray-700', 'border-gray-300');
                a.classList.add('bg-[#1C6C73]', 'text-white', 'border-[#1C6C73]', 'shadow-md');
                a.dataset.active = "1";
            }

            async function cargarNotas() {
                if (!el.listaNotas) return;

                if (!hasSelection()) {
                    el.listaNotas.innerHTML =
                        `<p class="text-gray-400 text-center">Selecciona un tratamiento para ver sus notas.</p>`;
                    return;
                }

                const phase = el.phaseNotas?.value || "";

                try {
                    const res = await fetch(routes.notasShow, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": CSRF,
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        body: JSON.stringify({
                            identifier: pxId,
                            identifier_type: "id",
                            phase: phase,
                        })
                    });

                    const data = await safeJson(res);
                    if (!res.ok) throw data;

                    el.listaNotas.innerHTML = "";

                    if (data.status === "success" && Array.isArray(data.data) && data.data.length > 0) {
                        data.data.forEach((nota) => {
                            el.listaNotas.insertAdjacentHTML('beforeend', `
            <div class="border rounded-lg p-3 shadow-sm bg-gray-50 mb-3 relative group">
              <div class="flex justify-between mb-1">
                <strong class="text-[#1C6C73]">${nota.author_name ?? '—'}</strong>
                <span class="text-gray-500 text-sm">${nota.date ?? ''}</span>
              </div>
              <p class="text-gray-700 whitespace-pre-line">${nota.note ?? ''}</p>
              ${nota.phase ? `<p class="text-sm text-gray-500 italic">Fase: ${nota.phase}</p>` : ''}

              <button type="button"
                class="deleteNota absolute top-2 right-2 text-red-500 hover:text-red-700 hidden group-hover:block"
                data-id="${nota.id}">🗑</button>
            </div>
          `);
                        });
                    } else {
                        el.listaNotas.innerHTML = `<p class="text-gray-400 text-center">No hay notas.</p>`;
                    }
                } catch (err) {
                    console.error("Error cargando notas:", err);
                    el.listaNotas.innerHTML = `<p class="text-red-500 text-center">Error al cargar notas.</p>`;
                }
            }

            async function guardarNota() {
                if (!el.formNota) return;
                if (!hasSelection()) return alert("Selecciona un tratamiento primero.");

                const fd = new FormData(el.formNota);

                try {
                    const res = await fetch(routes.notasStore, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": CSRF,
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        body: fd
                    });

                    const data = await safeJson(res);
                    if (!res.ok) throw data;

                    if (data.status === "success") {
                        closeModal(el.modalNotas);
                        el.formNota.reset();
                        await cargarNotas();
                    } else {
                        alert(data.message || "No se pudo guardar la nota");
                    }
                } catch (err) {
                    console.error("Error guardando nota:", err);
                    alert(err?.message || "Error al guardar la nota");
                }
            }

            async function eliminarFoto(url) {
                if (!confirm("¿Eliminar esta foto?")) return;

                const treatmentId = parseInt(el.treatmentIdUpload?.value || "0", 10);
                if (!treatmentId) return alert("No hay tratamiento seleccionado.");

                const deleteUrl = `${routes.sinExpBase}/${pxId}/${treatmentId}/foto`;

                try {
                    const res = await fetch(deleteUrl, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": CSRF,
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        body: JSON.stringify({
                            url
                        }),
                    });

                    const data = await safeJson(res);
                    if (!res.ok) throw data;

                    if (data.status !== "success") {
                        alert(data.message || "No se pudo eliminar la foto");
                        return;
                    }

                    const activeBtn = document.querySelector('.view_imgs[data-active="1"]');
                    if (activeBtn?.dataset?.jsonUrl) {
                        await cargarTratamientoDesdeJson(
                            activeBtn.dataset.jsonUrl,
                            activeBtn.dataset.phase || "",
                            parseInt(activeBtn.dataset.treatmentId, 10)
                        );
                    } else {
                        await cargarNotas();
                    }

                } catch (err) {
                    console.error("Error eliminando foto:", err);
                    alert(err?.message || "Error al eliminar la foto");
                }
            }

            async function cargarTratamientoDesdeJson(url, phase, treatmentId) {
                try {
                    const res = await fetch(url, {
                        headers: {
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    });

                    const data = await safeJson(res);
                    if (!res.ok || data.status !== "success") throw data;

                    setUiEnabled(true);

                    if (el.phaseNotas) el.phaseNotas.value = phase || "";
                    if (el.phaseUpload) el.phaseUpload.value = phase || "";
                    if (el.treatmentIdUpload) el.treatmentIdUpload.value = String(treatmentId);

                    if (el.uploadForm) el.uploadForm.action =
                    `${routes.sinExpBase}/${pxId}/${treatmentId}/foto`;

                    setNotaInicial(data.nota_inicial || "");
                    renderGaleria(data.data || []);
                    await cargarNotas();

                } catch (err) {
                    console.error("Error cargando tratamiento:", err);
                    setUiEnabled(false);
                    setNotaInicial("");
                    renderGaleria([]);
                    alert(err?.message || "No se pudo cargar el tratamiento.");
                }
            }

            // CREAR TRATAMIENTOscc
            async function guardarTratamientoSinExp() {
                if (!el.formCrearTratamiento) return;

                const fd = new FormData(el.formCrearTratamiento);

                // validación rápida
                const date = String(fd.get('date') || '').trim();
                const clinic = String(fd.get('clinic') || '').trim();
                const type = String(fd.get('type') || '').trim();

                if (!date) return alert('Selecciona una fecha.');
                if (!clinic) return alert('Selecciona una clínica.');
                if (!type) return alert('Escribe el tipo de tratamiento.');

                try {
                    el.btnGuardarTratamiento.disabled = true;

                    const res = await fetch(routes.treatmentsStore, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": CSRF,
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        body: fd
                    });

                    const data = await safeJson(res);
                    if (!res.ok) throw data;

                    // tu endpoint normalmente devuelve {status:"success"...}
                    if (data.status === 'success' || data.success === true) {
                        closeModal(el.modalCrear);
                        el.formCrearTratamiento.reset();
                        // para que aparezca el nuevo treatment en el carrusel
                        location.reload();
                        return;
                    }

                    alert(data.message || "No se pudo guardar el tratamiento");
                } catch (err) {
                    console.error("Error guardando tratamiento:", err);
                    alert(err?.message || "Error al guardar tratamiento");
                } finally {
                    el.btnGuardarTratamiento.disabled = false;
                }
            }

            // ===== Eventos =====
            el.btnGuardarNota?.addEventListener('click', guardarNota);

            // ✅ binds creación
            el.openCrear?.addEventListener('click', () => {
                openModal(el.modalCrear);
                setTodayIfEmpty('#treatment_date_sinexp');
                });

            el.closeCrear?.addEventListener('click', () => closeModal(el.modalCrear));
            el.btnGuardarTratamiento?.addEventListener('click', guardarTratamientoSinExp);

            // cerrar modal click afuera
            el.modalCrear?.addEventListener('click', (e) => {
                if (e.target === el.modalCrear) closeModal(el.modalCrear);
            });

            // carrusel click
            document.addEventListener("click", (e) => {
                const a = e.target.closest('.view_imgs');
                if (!a) return;
                e.preventDefault();

                const url = a.dataset.jsonUrl;
                const treatmentId = parseInt(a.dataset.treatmentId || "0", 10);
                const phase = a.dataset.phase || "";
                if (!url || !treatmentId) return;

                marcarActivo(a);
                cargarTratamientoDesdeJson(url, phase, treatmentId);
            });

            // delete foto
            document.addEventListener("click", (e) => {
                const btn = e.target.closest(".deleteFoto");
                if (!btn) return;
                eliminarFoto(btn.dataset.url);
            });

            document.addEventListener("click", async (e) => {
                const btn = e.target.closest(".deleteNota");
                if (!btn) return;

                const id = btn.dataset.id;
                if (!id) return;

                if (!confirm("¿Eliminar esta nota?")) return;

                try {
                    const res = await fetch(`${routes.notasDeleteBase}/${id}`, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": CSRF,
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                    });

                    const data = await safeJson(res);
                    if (!res.ok) throw data;

                    // refresca lista
                    await cargarNotas();
                } catch (err) {
                    console.error("Error eliminando nota:", err);
                    alert(err?.message || "No se pudo eliminar la nota");
                }
            });


            // upload
            el.btnSelectFile?.addEventListener('click', () => {
                if (!hasSelection()) return alert("Selecciona un tratamiento primero.");
                el.fotoInput?.click();
            });

            /*el.fotoInput?.addEventListener('change', function() {
                if (!this.files.length) return;
                if (!hasSelection() || !el.uploadForm?.action || el.uploadForm.action.endsWith('#')) {
                    alert('Primero selecciona un tratamiento');
                    this.value = '';
                    return;
                }
                el.uploadForm.submit();
            });*/

            // modal notas
          el.openNotas?.addEventListener('click', async () => {
            if (!hasSelection()) return alert("Selecciona un tratamiento primero.");
            openModal(el.modalNotas);

            setTodayIfEmpty('#nota_date_sinexp');

            await cargarNotas();
            });

            el.closeNotas?.addEventListener('click', () => closeModal(el.modalNotas));
            el.modalNotas?.addEventListener('click', (e) => {
                if (e.target === el.modalNotas) closeModal(el.modalNotas);
            });

            // ESC
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeModal(el.modalCrear);
                    closeModal(el.modalNotas);
                }
            });

            // ===== INIT =====
            setUiEnabled(hasSelection());

            if (hasSelection()) {
                const activeBtn = document.querySelector('.view_imgs[data-active="1"]');
                if (activeBtn?.dataset?.jsonUrl) {
                    cargarTratamientoDesdeJson(
                        activeBtn.dataset.jsonUrl,
                        activeBtn.dataset.phase || "",
                        parseInt(activeBtn.dataset.treatmentId, 10)
                    );
                } else {
                    const tid = parseInt(el.treatmentIdUpload.value, 10);
                    if (el.uploadForm && tid) el.uploadForm.action = `${routes.sinExpBase}/${pxId}/${tid}/foto`;
                }
            } else {
                setNotaInicial("");
                renderGaleria([]);
                if (el.listaNotas) el.listaNotas.innerHTML =
                    `<p class="text-gray-400 text-center">Selecciona un tratamiento para ver sus notas.</p>`;
            }

            // =========================
            // UPLOAD MULTI + SWEETALERT + DRAG&DROP (SIN EXP)
            // =========================
            const highlightDrop = (on) => {
                if (!el.uploadForm) return;
                el.uploadForm.classList.toggle('border-blue-400', on);
                el.uploadForm.classList.toggle('bg-blue-50', on);
            };

            // arma la ruta real desde sinExpBase (ya la usas)
            const getUploadUrl = () => {
                const treatmentId = parseInt(el.treatmentIdUpload?.value || "0", 10);
                if (!treatmentId) return null;
                return `${routes.sinExpBase}/${pxId}/${treatmentId}/foto`;
            };

            const refreshActiveTreatment = async () => {
                const activeBtn = document.querySelector('.view_imgs[data-active="1"]');
                if (activeBtn?.dataset?.jsonUrl) {
                    await cargarTratamientoDesdeJson(
                        activeBtn.dataset.jsonUrl,
                        activeBtn.dataset.phase || "",
                        parseInt(activeBtn.dataset.treatmentId, 10)
                    );
                }
            };

            async function uploadFilesSinExp(files) {
                if (!hasSelection()) {
                    Swal.fire({
                        icon: "warning",
                        title: "Selecciona un tratamiento primero"
                    });
                    return;
                }

                const uploadUrl = getUploadUrl();
                if (!uploadUrl) {
                    Swal.fire({
                        icon: "error",
                        title: "Ruta inválida",
                        text: "No se pudo determinar la ruta de subida."
                    });
                    return;
                }

                const arr = Array.from(files || []);
                if (!arr.length) return;

                // deshabilitar UI
                if (el.btnSelectFile) el.btnSelectFile.disabled = true;

                let okCount = 0;
                let failCount = 0;

                Swal.fire({
                    title: "Subiendo fotos…",
                    html: `0 / ${arr.length}`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });

                for (let i = 0; i < arr.length; i++) {
                    const file = arr[i];

                    const fd = new FormData();
                    fd.append("foto", file); // 👈 backend actual espera "foto"
                    fd.append("_token", CSRF);

                    try {
                        const res = await fetch(uploadUrl, {
                            method: "POST",
                            body: fd,
                            headers: {
                                "Accept": "application/json",
                                "X-Requested-With": "XMLHttpRequest",
                                "X-CSRF-TOKEN": CSRF,
                            }
                        });

                        let data = {};
                        try {
                            data = await res.json();
                        } catch {}

                        if (!res.ok || (data.status && data.status !== "success")) {
                            failCount++;
                            console.error("Upload fail:", file.name, res.status, data);
                        } else {
                            okCount++;
                        }
                    } catch (e) {
                        failCount++;
                        console.error("Upload error:", file.name, e);
                    }

                    Swal.update({
                        html: `${i + 1} / ${arr.length}<br><small class="text-gray-500">${file.name}</small>`
                    });
                }

                // fin
                if (el.btnSelectFile) el.btnSelectFile.disabled = false;
                if (el.fotoInput) el.fotoInput.value = "";

                if (failCount === 0) {
                    Swal.fire({
                        icon: "success",
                        title: "Listo",
                        text: `Se subieron ${okCount} foto(s) correctamente.`,
                        timer: 1600,
                        showConfirmButton: false,
                    });
                } else {
                    Swal.fire({
                        icon: "warning",
                        title: "Subida parcial",
                        html: `Correctas: <b>${okCount}</b><br>Fallidas: <b>${failCount}</b>`,
                    });
                }

                await refreshActiveTreatment();
            }

            // click para seleccionar
            el.btnSelectFile?.addEventListener("click", () => {
                if (!hasSelection()) return Swal.fire({
                    icon: "warning",
                    title: "Selecciona un tratamiento primero"
                });
                el.fotoInput?.click();
            });

            // cuando eliges archivos
            el.fotoInput?.addEventListener("change", function() {
                if (!this.files?.length) return;
                uploadFilesSinExp(this.files);
            });

            // Drag & drop
            ["dragenter", "dragover"].forEach(evt => {
                el.uploadForm?.addEventListener(evt, (e) => {
                    e.preventDefault();
                    if (!hasSelection()) return;
                    highlightDrop(true);
                });
            });

            ["dragleave", "drop"].forEach(evt => {
                el.uploadForm?.addEventListener(evt, (e) => {
                    e.preventDefault();
                    highlightDrop(false);
                });
            });

            el.uploadForm?.addEventListener("drop", (e) => {
                e.preventDefault();
                highlightDrop(false);

                if (!hasSelection()) {
                    return Swal.fire({
                        icon: "warning",
                        title: "Selecciona un tratamiento primero"
                    });
                }

                const files = e.dataTransfer?.files;
                if (files && files.length) uploadFilesSinExp(files);
            });

        });
    </script>
@endsection
