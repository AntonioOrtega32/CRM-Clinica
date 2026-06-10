@extends('panel.layouts.panel')
@section('title', 'Fotos del tratamiento')
@section('content')
    <section class="py-10 px-6 bg-white">
        <div class="p-6 space-y-6">
            {{-- Encabezado --}}
            <div>
                <h1 class="text-3xl font-[Poppins] text-[#1C6C73] mb-2">Fotos y notas de Tratamientos</h1>
                <p class="text-gray-600">Gestiona las fotos y tratamientos del paciente.</p>
                <br>
                 @if ($trat == 2)
                    <a class="bg-[#4298a7] hover:bg-[#1C6C73] text-white px-4 py-2 rounded"
                    href="{{ route('panel.tratamientos.index') }}">
                    <i class="fa-solid fa-square-caret-left"></i> Volver al Reporte
                </a>
                @else
                <a class="bg-[#1C6C73] hover:bg-[#4298a7] text-white px-4 py-2 rounded"
                    href="{{ route('panel.procedimientos.index') }}">
                    <i class="fa-solid fa-square-caret-left"></i> Volver al Reporte
                </a>
                @endif
            </div>
            {{-- Datos del paciente --}}
            <div class="bg-white shadow-md rounded-xl p-6 border border-gray-200">
                <h2 class="text-2xl font-[Poppins] text-[#1C6C73] mb-3">Datos del paciente</h2>
                <div class="grid md:grid-cols-2 gap-3 text-gray-700">
                    <p><strong>Nombre:</strong> {{ $paciente->name ?? 'No disponible' }}</p>
                    <p><strong># Expediente:</strong> {{ $num_med_record }}</p>
                    <p><strong>Procedimiento:</strong> {{ $paciente->procedure_type ?? '—' }}</p>
                    <p><strong>Clínica:</strong> {{ $paciente->clinic ?? '—' }}</p>
                    <p><strong>Especialista:</strong> {{ $paciente->specialist ?? '—' }}</p>
                    <p><strong>Fecha del Procedimiento:</strong> {{ $paciente->procedure_date ?? '—' }}</p>
                    <p><strong>Enfermedades:</strong> {{ $paciente->enfermedades ?? 'Ninguna' }}</p>
                    <p><strong>Tratamiento?</strong> {{ $paciente->touchup ?? 'No' }}</p>
                    <p><strong>Estatus:</strong>
                        <span class="px-2 py-1 rounded text-white bg-[#CDAF95] text-sm">
                            {{ ucfirst($paciente->status ?? 'Desconocido') }}
                        </span>
                    </p>
                      <p>
        <button id="openReciboTratamiento"
            class="bg-[#1C6C73] hover:bg-[#4298A7] text-white px-5 py-2 rounded-lg shadow">
            <i class="fa-solid fa-plus"></i>
            Generar recibo
        </button>
        </p>
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
            {{-- Botón Crear Tratamiento --}}
            <div class="flex justify-end mb-4">
                <button id="openCrearTratamiento"
                    class="bg-[#1C6C73] hover:bg-[#155A61] text-white px-5 py-2 rounded-lg shadow">
                    <i class="fa-solid fa-plus"></i> Crear Tratamiento
                </button>
            </div>
            {{-- Modal Crear Tratamiento --}}
            <div id="modalCrearTratamiento"
                class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 overflow-y-auto">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl p-6 relative">
                    <button id="closeCrearTratamiento"
                        class="absolute top-3 right-3 text-gray-500 hover:text-gray-800">✕</button>
                    <h2 class="text-2xl font-bold text-[#1C6C73] mb-4 text-center">Crear Nuevo Tratamiento</h2>

                    <form id="formCrearTratamiento">
                        @csrf
                        <input type="hidden" name="num_med_record" value="{{ $num_med_record }}">
                        <input type="hidden" name="created_by" value="{{ Auth::user()->id }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="font-semibold">Fecha</label>
                                <input type="date" name="date" id="date_treatment"
                                 class="w-full border rounded-lg p-2">
                            </div>

                            <div>
                                <label class="font-semibold">Clínica</label>
                                <input type="text" name="clinic" value="{{ $paciente->clinic }}"
                                    class="w-full border rounded-lg p-2">
                            </div>

                            <div>
                                <label class="font-semibold">Doctor</label>
                                <input type="text" name="doctor" class="w-full border rounded-lg p-2"
                                    placeholder="Nombre del doctor">
                            </div>

                            <div>
                                <label class="font-semibold">Tipo de Tratamiento</label>
                                <input type="text" name="type" class="w-full border rounded-lg p-2"
                                    placeholder="Ej. PRP, Exosomas...">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="font-semibold">Nota Inicial (opcional)</label>
                            <textarea name="notes" rows="2" class="w-full border rounded-lg p-3"></textarea>
                        </div>

                        <div class="flex justify-end mt-4">
                            <button type="button" id="btnGuardarTratamiento"
                                class="bg-[#1C6C73] hover:bg-[#155A61] text-white px-5 py-2 rounded-lg shadow">
                                Guardar Tratamiento
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Nota Principal del Tratamiento --}}
            <div id="notaPrincipal" class="mt-6 hidden">
                <h3 class="text-xl font-semibold text-[#1C6C73] mb-2">Nota del tratamiento</h3>
                <div class="border rounded-lg p-4 bg-gray-50 shadow">
                    <p id="textoNotaPrincipal" class="text-gray-700 whitespace-pre-line"></p>
                </div>
            </div>

            <p class="text-red-500 text-center w-full text-lg">Selecciona un tratamiento para ver las fotos, acciones y notas.</p>
            <p class="text-red-500 text-center w-full text-xl">
               <i class="fa-solid fa-arrow-down-long"></i> CARRUSEL DE TRATAMIENTOS <i class="fa-solid fa-arrow-down-long"></i>
            </p>

            {{-- Carrusel de Tratamientos --}}
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    @foreach ($tratamientos as $treatment)
                        <div class="swiper-slide text-center" data-step="{{ $treatment->id }}">
                            <div class="flex flex-col items-center">
                                <a href="{{ route('panel.procedimientos.fotos.json', ['num_med_record' => $num_med_record, 'treatmentId' => $treatment->id]) }}"
                                    class="treatment-card view_imgs inline-block px-4 py-3 sm:px-5 rounded-xl border transition-all duration-200 text-center leading-tight
                            {{ $treatmentId === $treatment->id
                                ? 'bg-[#1C6C73] text-white border-[#1C6C73] shadow-md'
                                : 'bg-white text-gray-700 border-gray-300 hover:bg-[#1C6C73]/10 hover:border-[#1C6C73]' }}"
                                    data-phase="{{ $treatment->type }}" data-id="{{ $treatment->id }}">

                                    <span class="block text-base font-semibold">
                                        {{ $treatment->type ?? 'Tratamiento' }}
                                    </span>

                                    <span
                                        class="block text-xs {{ $treatmentId === $treatment->id ? 'text-gray-200' : 'text-gray-500' }}">
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

            {{-- Swiper  --}}
            <script>
                const swiper = new Swiper(".mySwiper", {
                    slidesPerView: "auto",
                    spaceBetween: 12,
                    freeMode: {
                        enabled: true,
                        sticky: false,
                        momentumBounce: false,
                    },

                    // IMPORTANTE en móvil
                    simulateTouch: true,
                    touchStartPreventDefault: false,
                    touchMoveStopPropagation: true,

                    // evita que Swiper “se coma” el scroll vertical
                    direction: "horizontal",

                    // mejor UX
                    grabCursor: true,

                    // quítalo para móvil (molesta)
                    mousewheel: false,

                    // Flechas solo en pantallas md+
                    navigation: {
                        nextEl: ".swiper-button-next",
                        prevEl: ".swiper-button-prev",
                    },

                    breakpoints: {
                        0: {
                            spaceBetween: 10,
                        },
                        768: {
                            spaceBetween: 15,
                            mousewheel: true, // solo desktop si quieres
                        }
                    }
                });
            </script>
            {{-- Estilos específicos --}}
            <style>
                .mySwiper {
                    padding: 8px 12px;
                }

                /* que se vea “scrollable” en móvil */
                .mySwiper .swiper-wrapper {
                    align-items: stretch;
                }

                /* con slidesPerView:auto necesitas ancho */
                .mySwiper .swiper-slide {
                    width: auto !important;
                    padding-right: 6px;
                    /* separa un poco */
                }

                /* tus tarjetas: mínimo ancho en móvil */
                .treatment-card {
                    min-width: 220px;
                    /* ajusta a gusto */
                    max-width: 280px;
                }

                /* opcional: en móvil ocultar flechas */
                @media (max-width: 767px) {

                    .swiper-button-next,
                    .swiper-button-prev {
                        display: none !important;
                    }
                }
            </style>


            {{-- Galería de Fotos --}}
            <div id="galeriaFotos" class="mt-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <p class="text-gray-500 text-center w-full">Selecciona un tratamiento para ver las fotos.</p>
            </div>

            
            <form id="uploadForm" action="{{ url('panel/procedimientos/fotos/subir/treatment/' . $num_med_record) }}" disabled
                method="POST" enctype="multipart/form-data"
                class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center bg-gray-50 hover:bg-gray-100 transition">

                @csrf

                <input type="hidden" name="phase" id="phaseFieldUpload">
                <input type="hidden" name="phaseUpload" id="phaseUpload">

                <!-- Campo oculto para el treatmentId -->
                <input type="hidden" name="treatmentId" id="treatmentId">

                <p class="text-gray-600 mb-2">
                    Arrastra tus fotos aquí o haz clic para seleccionarlas
                </p>

                <input type="file"
                    id="fotoInput"
                    name="fotos[]"
                    class="hidden"
                    accept="image/*,.heic,.heif"
                    multiple>

                <button type="button" onclick="document.getElementById('fotoInput').click()" disabled
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Seleccionar archivo
                </button>
            </form>

            {{-- Notas médicas --}}
            <div class="mt-10 border-t pt-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-[Poppins] text-[#1C6C73]">Notas Médicas del tratamiento</h2>
                    <button id="openNotas"
                        class="bg-[#1C6C73] hover:bg-[#155A61] text-white px-5 py-2 rounded-lg shadow transition {{ $paciente->status == 'alta' ? 'disabled:bg-[#C8BAAF]' : '' }}"
                        {{ $paciente->status == 'alta' ? 'disabled' : '' }}>
                        <i class="fa-solid fa-notes-medical"></i> Agregar Nota
                    </button>
                </div>
                <div id="listaNotas">
                    <p class="text-gray-400 text-center">Selecciona un tratamiento para ver sus notas.</p>
                </div>
            </div>
            {{-- Modal Notas --}}
            <div id="modalNotas"
                class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 overflow-y-auto">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl p-6 relative animate-fadeIn">
                    <button id="closeNotas" class="absolute top-3 right-3 text-gray-500 hover:text-gray-800">✕</button>
                    <h2 class="text-2xl font-bold text-[#1C6C73] mb-4 text-center">Agregar Nota Médica</h2>
                    <form id="formNota" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <input type="hidden" name="identifier_type" value="exp">
                        <input type="hidden" name="num_med_record" value="{{ $num_med_record }}">
                        <input type="hidden" name="author" value="{{ Auth::user()->id }}">
                        <input type="hidden" name="clinic" value="{{ $paciente->clinic }}">
                        <input type="hidden" name="phase" id="phaseFieldNotas">
                        <textarea name="note" rows="2"
                            class="w-full border rounded-lg p-3 text-gray-700 focus:ring-2 focus:ring-[#1C6C73]"
                            placeholder="Escribe tu nota médica..."></textarea>
                        <input type="date" name="date" id="nota_date"
                            class="border rounded-lg p-3 text-gray-700 w-full focus:ring-2 focus:ring-[#1C6C73]">
                        <div class="flex justify-end mt-4">
                            <button type="button" id="btnGuardarNota"
                                class="bg-[#1C6C73] hover:bg-[#155A61] text-white px-5 py-2 rounded-lg shadow">
                                Guardar Nota
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
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

        <input type="hidden" name="num_med_record" id="rc_num_med_record" value="{{ $num_med_record }}">
        <input type="hidden" name="identifier_type" value="exp">

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
<script>
    document.addEventListener('DOMContentLoaded', () => {
  const CSRF = "{{ csrf_token() }}";
  const numMedRecord = document.getElementById('rc_num_med_record')?.value || "{{ $num_med_record }}";

const routes = {
  gen: @json(route('panel.receipts.treatments.generateExp')),
  list: @json(url('panel/receipts/treatments/list-exp')) + '/' + numMedRecord,
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

     const tipoVal = fd.get('Tipo');

        const requiredBase = ['receipt_date','patient_name','Tipo','total','pay_method_1','pay_amount_1','clinic'];
        const requiredItem = (tipoVal === 'Producto') ? ['product_name'] : ['treatment_name'];
        const required = [...requiredBase, ...requiredItem];

        const pretty = {
        receipt_date: 'Fecha',
        patient_name: 'Nombre del Paciente',
        Tipo: 'Tipo de recibo',
        treatment_name: 'Tratamiento',
        product_name: 'Producto(s)',
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

      // ✅ abrir automáticamente el PDF
      if (data.pdf_url) window.open(data.pdf_url, '_blank', 'noopener');

      // ✅ refrescar lista debajo del botón
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
        {{-- Scripts --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                
            // fecha de hoy en formato YYYY-MM-DD
            function todayISO() {
            const d = new Date();
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
            }

            // setea "hoy" SOLO si está vacío
            function setTodayIfEmpty(selectorOrEl) {
            const el = (typeof selectorOrEl === 'string') ? document.querySelector(selectorOrEl) : selectorOrEl;
            if (!el) return;
            if (!el.value) el.value = todayISO();
            }

                //Subir foto
                // === SUBIR FOTO DE TRATAMIENTO ===
              document.getElementById("fotoInput").addEventListener("change", async function () {
                const files = Array.from(this.files || []);
                if (!files.length) return;

                const phase = document.getElementById("phaseUpload").value;
                const treatmentId = document.getElementById("treatmentId").value;
                const identifier = "{{ $num_med_record }}";

                if (!treatmentId) {
                    Swal.fire({
                    icon: "warning",
                    title: "Selecciona un tratamiento",
                    text: "Primero selecciona un tratamiento antes de subir fotos.",
                    });
                    this.value = "";
                    return;
                }

                const uploadUrl = `/panel/procedimientos/fotos/subir/treatment/${identifier}/${treatmentId}`;

                // UI: deshabilitar botón
                const button = document.querySelector("#uploadForm button");
                const originalText = button.innerHTML;
                button.disabled = true;

                // SweetAlert loader
                let okCount = 0;
                let failCount = 0;

                Swal.fire({
                    title: "Subiendo fotos…",
                    html: `0 / ${files.length}`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                    Swal.showLoading();
                    }
                });

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];

                    const formData = new FormData();
                    formData.append("foto", file); // backend actual espera "foto"
                    formData.append("phase", phase);
                    formData.append("treatmentId", treatmentId);
                    formData.append("_token", "{{ csrf_token() }}");

                    try {
                    const res = await fetch(uploadUrl, {
                        method: "POST",
                        body: formData,
                        headers: { "X-Requested-With": "XMLHttpRequest" },
                    });

                    // intenta leer json si existe
                    let data = {};
                    try { data = await res.json(); } catch {}

                    if (!res.ok || data.status !== "success") {
                        failCount++;
                        console.error("Upload failed:", { file: file.name, resStatus: res.status, data });
                    } else {
                        okCount++;
                    }
                    } catch (e) {
                    failCount++;
                    console.error("Upload error:", file.name, e);
                    }

                    Swal.update({
                    html: `${i + 1} / ${files.length}<br><small class="text-gray-500">Subiendo: ${file.name}</small>`
                    });
                }

                // terminar
                button.disabled = false;
                button.innerHTML = originalText;
                this.value = "";

                if (failCount === 0) {
                    Swal.fire({
                    icon: "success",
                    title: "Listo",
                    text: `Se subieron ${okCount} foto(s) correctamente.`,
                    timer: 1800,
                    showConfirmButton: false,
                    });
                } else {
                    Swal.fire({
                    icon: "warning",
                    title: "Subida parcial",
                    html: `Correctas: <b>${okCount}</b><br>Fallidas: <b>${failCount}</b>`,
                    });
                }

                // recargar galería del tratamiento seleccionado
                const selectedBtn = document.querySelector('.view_imgs[data-active="1"]')
                    || document.querySelector('.view_imgs.bg-\\[\\#1C6C73\\]');
                if (selectedBtn) selectedBtn.click();
                });


                //Eliminar foto
                document.addEventListener("click", async function(e) {
                    if (!e.target.classList.contains("btnEliminarFoto")) return;

                    const url = e.target.dataset.url;

                    if (!confirm("¿Eliminar esta foto?")) return;

                    const res = await fetch("{{ route('panel.procedimientos.eliminarFotoTreatment') }}", {
                        method: "DELETE",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            url
                        })
                    });

                    // Para debugear si algo falla
                    const raw = await res.text();
                    console.log("RAW RESPONSE:", raw);

                    let data;

                    try {
                        data = JSON.parse(raw);
                    } catch (e) {
                        alert("Respuesta no válida del servidor");
                        return;
                    }

                    if (data.success) {
                        const selectedBtn = document.querySelector(".view_imgs.active-treatment");
                        if (selectedBtn) selectedBtn.click();
                    } else {
                        alert("Error al eliminar: " + data.error);
                    }
                });

                //    MODAL CREAR TRATAMIENTO

                // Abrir modal
                document.getElementById("openCrearTratamiento").addEventListener("click", () => {
                    document.getElementById("modalCrearTratamiento").classList.remove("hidden");
                      // ✅ set default date
                    setTodayIfEmpty("#treatment_date");
                });

                // Cerrar modal con botón
                document.getElementById("closeCrearTratamiento").addEventListener("click", () => {
                    document.getElementById("modalCrearTratamiento").classList.add("hidden");
                });

                // Cerrar modal al hacer clic fuera
                document.getElementById("modalCrearTratamiento").addEventListener("click", (event) => {
                    if (event.target === document.getElementById("modalCrearTratamiento")) {
                        document.getElementById("modalCrearTratamiento").classList.add("hidden");
                    }
                });

                // GUARDAR TRATAMIENTO
                document.getElementById("btnGuardarTratamiento").addEventListener("click", function() {

                    const form = document.getElementById("formCrearTratamiento");
                    const formData = new FormData(form);

                    fetch("{{ route('panel.procedimientos.tratamientos.store') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": form.querySelector('input[name="_token"]').value,
                            },
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === "success") {

                                // Cerrar modal
                                document.getElementById("modalCrearTratamiento").classList.add("hidden");

                                // Limpiar formulario
                                form.reset();

                                // Recargar la página
                                location.reload();
                            }
                        })
                        .catch(error => console.error(error));
                });
                //  MODAL DE NOTAS

                document.getElementById("openNotas").addEventListener("click", () => {
                    document.getElementById("modalNotas").classList.remove("hidden");

                      setTodayIfEmpty('#nota_date');
                });

                document.getElementById("closeNotas").addEventListener("click", () => {
                    document.getElementById("modalNotas").classList.add("hidden");
                });

                // Cerrar clic fuera del modal
                document.getElementById("modalNotas").addEventListener("click", function(e) {
                    if (e.target === this) {
                        this.classList.add("hidden");
                    }
                });

                //  FUNCIÓN REUTILIZABLE PARA CARGAR NOTAS
                function cargarNotas() {
                    const phase = document.getElementById("phaseFieldNotas").value;
                    const identifier = "{{ $num_med_record }}";

                    fetch("{{ route('panel.procedimientos.notas.show') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify({
                                identifier: identifier,
                                identifier_type: "exp",
                                phase: phase
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            const lista = document.querySelector('#listaNotas');
                            lista.innerHTML = "";

                            if (data.status === "success" && data.data.length > 0) {
                                const notasOrdenadas = data.data.sort((a, b) => new Date(a.date) - new Date(b
                                    .date));

                                notasOrdenadas.forEach(nota => {
                                    lista.innerHTML += `
                                    <div class="border rounded-lg p-3 shadow-sm bg-gray-50 mb-3 relative group">
                                        <div class="flex justify-between mb-1">
                                            <strong class="text-[#1C6C73]">${nota.author_name}</strong>
                                            <span class="text-gray-500 text-sm">${nota.date}</span>
                                        </div>

                                        <p class="text-gray-700">${nota.note}</p>
                                        ${nota.phase ? `<p class="text-sm text-gray-500 italic">Fase: ${nota.phase}</p>` : ''}

                                        <button
                                            class="deleteNota absolute top-2 right-2 text-red-500 hover:text-red-700 hidden group-hover:block"
                                            data-id="${nota.id}">
                                            🗑
                                        </button>
                                    </div>
                                `;
                                });
                            } else {
                                lista.innerHTML =
                                    `<p class="text-gray-400 text-center">No hay notas adicionales.</p>`;
                            }
                        });
                }

                //  ELIMINAR NOTA
                document.addEventListener("click", function(e) {
                    if (e.target.classList.contains("deleteNota")) {
                        const id = e.target.dataset.id;

                        if (!confirm("¿Seguro que deseas eliminar esta nota?")) return;

                        fetch(`/panel/procedimientos/notas/delete/${id}`, {
                                method: "DELETE",
                                headers: {
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.status === "success") {
                                    cargarNotas();
                                } else {
                                    alert("Error al eliminar nota.");
                                }
                            });
                    }
                });

                //  GUARDAR NOTA
                document.getElementById("btnGuardarNota").addEventListener("click", function() {

                    const form = document.getElementById("formNota");
                    const formData = new FormData(form);

                    fetch("{{ route('panel.procedimientos.notas.store') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {

                            if (data.status === "success") {

                                // Cerrar modal
                                document.getElementById("modalNotas").classList.add("hidden");

                                // Reset form
                                form.reset();

                                // Recargar notas del tratamiento actual
                                cargarNotas();
                            }
                        });
                });

                //  CLICK EN TRATAMIENTO
                document.querySelectorAll('.view_imgs').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();

                        const urlFotos = this.getAttribute('href');
                        const phase = this.dataset.phase || null;
                        // Prioriza data-id en el link; si no existe, busca data-step en el contenedor
                        const treatmentId = this.dataset.id || (this.closest('.swiper-slide')?.dataset
                            .step ?? null);

                        // Guardar phase y treatmentId en campos ocultos
                        document.getElementById("phaseFieldUpload").value = phase;
                        document.getElementById("phaseUpload").value = phase;
                        document.getElementById("phaseFieldNotas").value = phase;
                        document.getElementById("treatmentId").value = treatmentId;

                        // Actualizar action del form de subida
                        const uploadForm = document.getElementById('uploadForm');
                        uploadForm.action =
                            "{{ url('panel/procedimientos/fotos/subir/treatment') }}/{{ $num_med_record }}/" +
                            treatmentId;

                        // (1) Cargar fotos + nota inicial (igual que ya tienes)
                        fetch(urlFotos)
                            .then(res => res.json())
                            .then(data => {
                                const galeria = document.querySelector('#galeriaFotos');
                                galeria.innerHTML = '';

                                if (data.status === 'success' && data.data.length > 0) {
                                    data.data.forEach(img => {
                                        galeria.innerHTML += `
                            <div class="border p-3 rounded-lg shadow bg-white">
                                <a href="${img.url}" target="_blank">
                                    <img src="${img.thumb}" class="rounded-lg h-48 w-full object-cover mb-2">
                                </a>
                                <p class="text-sm text-center truncate">${img.name}</p>
                                @role('super_usuario|administrador|Médicos')
                                <button class="btnEliminarFoto" data-url="${img.url}">
                                Eliminar
                            </button>
                                @endrole
                            </div>`;
                                    });
                                } else {
                                    galeria.innerHTML =
                                        '<p class="text-gray-500 text-center w-full">No hay fotos.</p>';
                                }

                                // Nota inicial
                                const notaPrincipalBox = document.querySelector('#notaPrincipal');
                                const textoNotaPrincipal = document.querySelector(
                                    '#textoNotaPrincipal');

                                if (data.nota_inicial && data.nota_inicial.trim() !== "") {
                                    notaPrincipalBox.classList.remove('hidden');
                                    textoNotaPrincipal.innerHTML = data.nota_inicial;
                                } else {
                                    notaPrincipalBox.classList.add('hidden');
                                }
                            });

                        // (2) Cargar notas adicionales (tu función cargarNotas() también funciona)
                        cargarNotas();

                        // (3) Marcar seleccionado en carrusel (tu lógica existente)
                        // (3) Marcar seleccionado en carrusel (tu lógica existente + active-treatment)
                        document.querySelectorAll('.view_imgs').forEach(x => {
                            x.classList.remove(
                                'bg-[#1C6C73]', 'text-white', 'border-[#1C6C73]',
                                'shadow-md',
                                'active-treatment'
                            );
                            x.classList.add('bg-white', 'text-gray-700', 'border-gray-300');
                        });

                        this.classList.remove('bg-white', 'text-gray-700', 'border-gray-300');
                        this.classList.add(
                            'bg-[#1C6C73]', 'text-white', 'border-[#1C6C73]', 'shadow-md',
                            'active-treatment'
                        );

                    });
                });

            });
        </script>



    </section>
@endsection
