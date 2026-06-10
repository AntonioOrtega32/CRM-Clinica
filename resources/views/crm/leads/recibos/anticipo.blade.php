<!-- CONTENEDOR PRINCIPAL -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <form action="{{ route('panel.receipts.storeAnticipo') }}" method="POST" enctype="multipart/form-data"

leadData: {
  id: {{ $lead->id }},
  name: '',
  clinic: '',
  interested_in: ''
},

    x-data="{
  leadData: { id: {{ $lead->id }}, name: '', clinic: '' },
  anticipo: { total: 0, paid: 0, pending: 0 },

  // Método 1
  method1: '',
  amount1: 0,
  isDollar1: false,
  dls1: 0,
  rate1: 0,

  // Método 2
  isSecondMethod: false,
  method2: '',
  amount2: 0,
  isDollar2: false,
  dls2: 0,
  rate2: 0,

  seller: '{{ auth()->user()->name }}',
  procedure_date: '',
  procedure_date_label: 'Fecha abierta',

  init() { this.loadAnticipo() },

  get paid() {
    return (Number(this.amount1) || 0) + (this.isSecondMethod ? (Number(this.amount2) || 0) : 0);
  },

  get pending() {
    return Math.max((Number(this.anticipo.total) || 0) - this.paid, 0);
  },

  get finalMethod() {
    if (this.isSecondMethod && this.method2) return `${this.method1} + ${this.method2}`;
    return this.method1;
  },

  loadAnticipo() {
    if (!this.leadData.id) return;

    fetch(`/panel/leads/${this.leadData.id}/anticipo-json`)
      .then(r => r.json())
      .then(res => {
        if (!res.success) return;
this.leadData.interested_in = res.lead.interested_in;

        this.leadData.name = res.lead.name;
        this.leadData.clinic = res.lead.clinic;
        this.anticipo = res.anticipo;

        // FECHA DEL PROCEDIMIENTO
        if (res.assessment?.procedure_date === 'fecha_abierta') {
          this.procedure_date = '';
          this.procedure_date_label = 'Fecha abierta';
        } else if (res.assessment?.procedure_date) {
          this.procedure_date = res.assessment.procedure_date;
          this.procedure_date_label = res.assessment.procedure_date;
        }
      });
  },

  onMethodChange(n) {
    if (n === 1) {
      this.isDollar1 = this.method1 === 'Dólares';
      if (!this.isDollar1) { this.dls1 = 0; this.rate1 = 0; }
    }
    if (n === 2) {
      this.isDollar2 = this.method2 === 'Dólares';
      if (!this.isDollar2) { this.dls2 = 0; this.rate2 = 0; }
    }
    this.calculate();
  },

  toggleSecond() {
    if (!this.isSecondMethod) {
      this.method2 = '';
      this.amount2 = 0;
      this.dls2 = 0;
      this.rate2 = 0;
      this.isDollar2 = false;
    }
    this.calculate();
  },

  calculate() {
    // Conv USD -> MXN para método 1
    if (this.isDollar1 && Number(this.rate1) > 0) {
      this.amount1 = +(Number(this.dls1) * Number(this.rate1)).toFixed(2);
    }

    // Conv USD -> MXN para método 2
    if (this.isSecondMethod && this.isDollar2 && Number(this.rate2) > 0) {
      this.amount2 = +(Number(this.dls2) * Number(this.rate2)).toFixed(2);
    }
  },

  submitAnticipo() {
    // ✅ Validaciones para evitar que method1 llegue vacío
    if (!this.method1) {
      Swal.fire('Falta método', 'Selecciona el método de pago principal', 'warning');
      return;
    }
    if ((Number(this.amount1) || 0) <= 0) {
      Swal.fire('Falta monto', 'Captura el monto del método 1', 'warning');
      return;
    }
    if (this.isSecondMethod) {
      if (!this.method2) {
        Swal.fire('Falta método', 'Selecciona el método de pago (2)', 'warning');
        return;
      }
      if ((Number(this.amount2) || 0) <= 0) {
        Swal.fire('Falta monto', 'Captura el monto del método 2', 'warning');
        return;
      }
    }

    const form = this.$el;
    const fd = new FormData(form);

    // ✅ Mandamos lo que espera tu backend
    fd.set('lead_id', this.leadData.id);
    fd.set('advance_amount', this.paid);          // TOTAL
    fd.set('payment_method', this.finalMethod);   // MÉTODO FINAL
    fd.set('total_amount', this.anticipo.total);
    fd.set('pending_amount', this.pending);
    fd.set('vendedor', this.seller);
    fd.set('procedure_date', this.procedure_date ? this.procedure_date : 'fecha_abierta');

    // ✅ Detalle métodos (lo usa tu backend para armar finalMethod / breakdown)
    fd.set('payment_method_1', this.method1);
    fd.set('amount_1', this.amount1);

    fd.set('payment_method_2', this.isSecondMethod ? this.method2 : '');
    fd.set('amount_2', this.isSecondMethod ? this.amount2 : '');

    const file = fd.get('receipt_file');
    if (!file || file.size === 0) {
      Swal.fire('Archivo requerido', 'Debes subir el comprobante antes de continuar', 'warning');
      return;
    }

    Swal.fire({
      title: '¿Generar anticipo?',
      text: `Se guardará el anticipo por $${Number(this.paid).toFixed(2)}`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí, generar',
      cancelButtonText: 'Cancelar'
    }).then(res => {
      if (!res.isConfirmed) return;

      fetch(form.action, {
        method: 'POST',
        body: fd,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
        }
      })
      .then(r => r.ok ? r.json() : r.json().then(e => Promise.reject(e)))
      .then(data => {
        if (data.success) {
          Swal.fire('Correcto', 'Anticipo generado', 'success');
          window.open(data.path, '_blank');
        } else {
          Swal.fire('Error', data.message ?? 'Error al guardar', 'error');
        }
      })
      .catch(err => Swal.fire('Error', err?.message || 'No se pudo generar el anticipo', 'error'));
    });
  }
}"

        x-init="init()" @submit.prevent="submitAnticipo">


        @csrf

        <!-- HIDDEN -->
        <input type="hidden" name="lead_id" id="a_lead_id" x-model="leadData.id">
        <input type="hidden" name="userid" value="{{ auth()->id() }}">
        <input type="hidden" id="a_total_amount" name="total_amount" x-model="anticipo.total">
        <input type="hidden" id="a_pending_amount" name="pending_amount" x-model="pending">

        <!-- ================= HEADER ================= -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl flex items-center justify-center
                       bg-gradient-to-br from-[#1C6C73] to-[#4298A7]
                       shadow-md">
                    <i class="fas fa-file-invoice-dollar text-white text-lg"></i>
                </div>

                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800">
                        Comprobante de Anticipo
                    </h2>
                    <p class="text-sm text-gray-500">
                        Registro del pago inicial del procedimiento
                    </p>
                </div>
            </div>

            <!-- ESTADO -->
            <div class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                   transition-all duration-300 border"
                :class="paid > 0 ?
                    'bg-green-50 text-green-700 border-green-200' :
                    'bg-gray-100 text-gray-500 border-gray-200'">
                <span class="w-2 h-2 rounded-full" :class="paid > 0 ? 'bg-green-500 animate-pulse' : 'bg-gray-400'">
                </span>

                <span x-text="paid > 0 ? 'Anticipo capturado' : 'Esperando monto'"></span>
            </div>
        </div>

        <!-- ================= CARD ================= -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-8">

            <!-- ================= CAMPOS PRINCIPALES ================= -->
            <div class="grid gap-6" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">

                <div>
                    <label class="label-pro">Fecha de emisión *</label>
                    <input type="date" name="receipt_date" value="{{ now()->format('Y-m-d') }}" class="input-pro">
                </div>

                <div>
                    <label class="label-pro">Paciente *</label>
                    <input type="text" id="a_patient_name" name="patient_name" x-model="leadData.name" readonly
                        class="input-pro bg-gray-50">
                </div>

                <div>
                    <label class="label-pro">Tipo de injerto *</label>
                    <select disabled class="input-pro bg-gray-50">
    <option x-text="leadData.interested_in || '—'"></option>
</select>

<input type="hidden" name="procedure_type" :value="leadData.interested_in">

                </div>

                <div>
                    <label class="label-pro">Fecha del anticipo *</label>
                    <input type="date" name="payment_date" class="input-pro">
                </div>

                <div>
                  <select name="payment_method_1" x-model="method1" @change="onMethodChange(1)" class="input-pro">
                    <option selected>Selecciona</option>
                    <option>Efectivo</option>
                    <option>Transferencia</option>
                    <option>TDC</option>
                    <option>TDD</option>
                    <option>Depósito</option>
                    <option>Enlace digital</option>
                    <option>Dólares</option>
                </select>
                </div>

                <div>
                    <label class="label-pro">Anticipo MXN *</label>
                    <input type="number" step="0.01"
                        x-model.number="amount1"
                        @input="calculate"
                        class="input-pro text-lg font-semibold"
                        placeholder="Monto método 1">
                </div>

                <!-- Nuevo: Fecha del procedimiento (visible) -->
                <div>
                    <label class="label-pro flex items-center gap-2">
                        Fecha del procedimiento *
                        <span x-show="procedure_date_label === 'Fecha abierta'"
                            class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 font-semibold">
                            Fecha abierta
                        </span>
                    </label>

    <input type="date"
       name="procedure_date_input"
       x-model="procedure_date"
       class="input-pro"
       @change="
           if ($event.target.value) {
               procedure_date_label = $event.target.value
           }
       ">
       <input type="hidden"
       name="procedure_date"
       :value="procedure_date ? procedure_date : 'fecha_abierta'">


                </div>


            </div>

            <!-- ================= DÓLARES ================= -->
            <div x-show="isDollar1" x-transition class="grid gap-6 bg-blue-50 border border-blue-200 rounded-xl p-4"
             style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
            <div>
                <label class="label-pro">Monto en dólares</label>
                <input type="number" x-model.number="dls1" @input="calculate" class="input-pro">
            </div>
            <div>
                <label class="label-pro">Tipo de cambio</label>
                <input type="number" step="0.01" x-model.number="rate1" @input="calculate" class="input-pro">
            </div>
            </div>

            <!-- ================= SEGUNDO MÉTODO ================= -->
            <div class="rounded-2xl border border-gray-200 p-5 bg-gray-50 space-y-4">
  <label class="flex items-center gap-2 text-sm text-gray-700">
    <input type="checkbox" x-model="isSecondMethod" @change="toggleSecond()">
    Agregar segundo método de pago
  </label>

  <div x-show="isSecondMethod" x-transition class="space-y-4">
    <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
      <div>
        <label class="label-pro">Método (2)</label>
        <select name="payment_method_2" x-model="method2" @change="onMethodChange(2)" class="input-pro">
          <option value="" disabled>Selecciona</option>
          <option>Efectivo</option>
          <option>Transferencia</option>
          <option>TDC</option>
          <option>TDD</option>
          <option>Depósito</option>
          <option>Enlace digital</option>
          <option>Dólares</option>
        </select>
      </div>

      <div>
        <label class="label-pro">Monto MXN (2)</label>
        <input type="number" step="0.01" x-model.number="amount2" @input="calculate" name="amount_2"
               class="input-pro" placeholder="Monto método 2">
      </div>
    </div>

    <div x-show="isDollar2" x-transition class="grid gap-4 p-4 rounded-xl border border-blue-200 bg-blue-50"
         style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
      <div>
        <label class="label-pro">Dólares (USD) (2)</label>
        <input type="number" x-model.number="dls2" @input="calculate" class="input-pro">
      </div>
      <div>
        <label class="label-pro">Tipo de cambio (2)</label>
        <input type="number" step="0.01" x-model.number="rate2" @input="calculate" class="input-pro">
      </div>
    </div>
  </div>
</div>




            <!-- ================= RESUMEN FINANCIERO ================= -->
            <div class="relative rounded-2xl border bg-gradient-to-br from-gray-50 to-white p-6 overflow-hidden">

                <!-- Glow decorativo -->
                <div
                    class="absolute -top-10 -right-10 w-32 h-32
                        bg-[#1C6C73]/10 rounded-full blur-2xl">
                </div>

                <!-- GRID -->
                <div class="grid gap-6" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">

                    <!-- TOTAL -->
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">
                            Total del procedimiento
                        </p>

                        <p class="text-3xl font-bold text-[#1C6C73]">
                            $
                            <span x-text="anticipo.total > 0 ? anticipo.total.toFixed(2) : '— —'"></span>
                        </p>

                        <p class="text-xs text-gray-400 mt-1" x-show="anticipo.total <= 0">
                            Esperando cotización…
                        </p>
                    </div>

                    <!-- PENDIENTE -->
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">
                            Importe a liquidar
                        </p>

                        <p class="text-3xl font-bold transition-all duration-300"
                            :class="anticipo.total > 0 ?
                                (pending > 0 ? 'text-red-600' : 'text-green-600') :
                                'text-gray-400'">
                            $
                            <span x-text="anticipo.total > 0 ? pending.toFixed(2) : '— —'"></span>
                        </p>

                        <p class="text-xs mt-1 transition"
                            :class="anticipo.total > 0 ?
                                (pending > 0 ? 'text-red-400' : 'text-green-500') :
                                'text-gray-400'">
                            <span
                                x-text="anticipo.total > 0 ? (pending > 0 ? 'Pendiente por liquidar' : 'Procedimiento cubierto') : '—'"></span>
                        </p>
                    </div>
                </div>

                <!-- BARRA DE PROGRESO -->
                <div class="mt-6" x-show="anticipo.total > 0">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Progreso de pago</span>
                        <span x-text="Math.min((amount / anticipo.total) * 100, 100).toFixed(0) + '%'"></span>
                    </div>

                    <div class="w-full h-2 rounded-full bg-gray-200 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500"
                            :class="paid >= anticipo.total ? 'bg-green-500' : 'bg-[#1C6C73]'"
                            :style="`width: ${Math.min((paid / anticipo.total) * 100, 100)}%`"></div>
                    </div>
                </div>
            </div>

            <!-- ================= EXTRA ================= -->
            <div class="grid gap-6" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">

                <div>
                    <label class="label-pro">Clínica *</label>
                    <select id="a_clinic" name="clinic" x-model="leadData.clinic" class="input-pro">
                        <option value="">Selecciona</option>
                        <option value="Queretaro">Queretaro</option>
                        <option value="Pedregal">Pedregal</option>
                        <option value="Santa Fe">Santa Fe</option>

                    </select>
                </div>

                <div>
                    <label class="label-pro">Subir recibo *</label>
                    <input type="file" name="receipt_file" accept=".pdf,.jpg,.jpeg,.png" class="input-pro">
                </div>

                <!-- Nuevo: Vendedora -->
                <div>
                    <label class="label-pro">Vendedora</label>
                    <select name="vendedor" x-model="seller" class="input-pro">
                        <option value="">Selecciona una vendedora</option>
                        <option value="Dr. Monse Mata">Dr. Monse Mata</option>
                        <option value="Marisol Olmos">Marisol Olmos</option>
                        <option value="Janeth Ruiz">Janeth Ruiz</option>
                        <option value="Paola Segura">Paola Segura</option>
                    </select>
                </div>


                <div style="grid-column: 1 / -1;">
                    <label class="label-pro">Notas</label>
                    <textarea name="public_notes" rows="2" class="input-pro"></textarea>
                </div>
            </div>

            <!-- ================= BOTÓN ================= -->
            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="flex items-center gap-2 px-6 py-3 rounded-xl
                       bg-[#1C6C73] hover:bg-[#155b61]
                       text-white font-semibold shadow-md transition
                       disabled:opacity-50"
                    :disabled="paid <= 0 || paid > anticipo.total">
                    <i class="fas fa-check-circle"></i>
                    Generar anticipo
                </button>
            </div>

        </div>
    </form>
</div>

<style>
    .label-pro {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.25rem;
    }

    .input-pro {
        width: 100%;
        padding: 0.55rem 0.75rem;
        border-radius: 0.75rem;
        border: 1px solid #cbd5e1;
        transition: all 0.2s ease;
        background-color: white;
    }

    .input-pro:focus {
        outline: none;
        border-color: #1C6C73;
        box-shadow: 0 0 0 1px #1C6C73;
    }
</style>
