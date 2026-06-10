<div class="max-w-6xl mx-auto px-4">
    <script>
        const leadId = {{ $lead->id }};
    </script>


    <form id="liquidacionForm" action="{{ route('panel.receipts.store') }}" method="POST" enctype="multipart/form-data"
        x-data="liquidacionForm({{ $totalAmount ?? 0 }})" x-init="fetch(`/panel/leads/${leadId}/liquidacion-json`)
            .then(async r => {
                const text = await r.text()
                console.log('🧪 RAW:', text)
                return JSON.parse(text)
            })
            .then(data => {
                console.log('📦 liquidacion-json:', data)
                fillForm(data)
            })
            .catch(e => console.error('❌ Error liquidacion-json', e))" @submit.prevent="submitLiquidacion" class="space-y-8">

        @csrf

        <!-- AUTOFILL (los llena tu recibosData->fillForm) -->
        <input type="hidden" name="lead_id" id="l_lead_id">
        <input type="hidden" name="procedure_date" id="l_procedure_date">
        <!-- FIXES BACKEND -->
        <input type="hidden" name="procedure_type" id="l_procedure_type_hidden">
        <input type="hidden" name="settlement_payment_method" :value="finalMethod">
        <input type="hidden" name="bank_or_method" :value="bankOrMethod">
        <!-- TOTAL REAL DEL PROCEDIMIENTO -->

        <!-- TOTAL REAL DEL PROCEDIMIENTO (NO QUITAR) -->
        <input type="hidden" id="l_total_amount" name="total_amount" />


        <!-- Campos que SÍ se mandan -->
        <input type="hidden" name="settlement_amount" :value="paid.toFixed(2)">

        <!-- ================= HEADER ================= -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-[#1C6C73]">Liquidación del Procedimiento</h2>
                <p class="text-sm text-gray-500">Cierra el pago del procedimiento con 1 o 2 métodos</p>
            </div>

        </div>

        <!-- ================= CARD ================= -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-8">

            <!-- ================= DATOS PRINCIPALES ================= -->
            <div class="grid gap-6" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
                <div>
                    <label class="label-pro">Fecha de emisión *</label>
                    <input type="date" name="receipt_date" value="{{ now()->format('Y-m-d') }}" required
                        class="input-pro">
                </div>

                <div>
                    <label class="label-pro">Paciente *</label>
                    <input type="text" name="patient_name" id="l_patient_name" readonly class="input-pro bg-gray-50">
                </div>

                <div>
                    <label class="label-pro">Tipo de injerto *</label>
                    <select name="procedure_type" id="l_procedure_type" disabled class="input-pro bg-gray-50">
                        <option>Capilar</option>
                        <option>Barba</option>
                        <option>Ambos</option>
                        <option>Ceja</option>
                    </select>
                </div>

                <div>
                    <label class="label-pro">Fecha de liquidación *</label>
                    <input type="date" name="settlement_date" required class="input-pro">
                </div>

                <div>
                    <label class="label-pro">Generado en (Clínica) *</label>
                    <select name="clinic" id="l_clinic" required class="input-pro">
                        <option value="" disabled selected>Selecciona</option>
                        <option>Queretaro</option>
                        <option>Pedregal</option>
                        <option>Santa Fe</option>
                    </select>
                </div>

                <div>
                    <label class="label-pro">Vendedora / Vendedor *</label>
                    <select name="vendedor" required class="input-pro">
                        <option value="" disabled selected>Selecciona</option>
                        <option>Marisol Olmos</option>
                        <option>Paola Segura</option>
                        <option>Janeth Ruiz</option>
                        <!-- agrega tu listado real -->
                    </select>
                </div>

                <div style="grid-column: 1 / -1;">
                    <label class="label-pro">Banco o medio (transfer, BBVA, efectivo, stripe, etc.) *</label>
                    <input type="text" x-model="bankOrMethod" name="bank_or_method" required class="input-pro">
                </div>

                <!-- Mostrar el monto total -->

            </div>
            <!-- ================= ANTICIPO ================= -->
            <div class="grid gap-6" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">

                <!-- Fecha del Anticipo -->
                <div>
                    <label class="label-pro">
                        Fecha del Anticipo <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="advance_date" class="input-pro">
                </div>

                <!-- Monto Abonado -->
                <div>
                    <label class="label-pro">
                        Monto Abonado (MXN) <span class="text-red-500">*</span>
                    </label>
                    <div class="flex">
                        <span class="px-3 py-2 border border-r-0 rounded-l-xl bg-gray-100">$</span>
                        <input type="number" step="0.01" name="advance_amount" class="input-pro rounded-l-none"
                            placeholder="0.00">
                    </div>
                </div>

                <!-- Método de pago del Anticipo -->
                <div>
                    <label class="label-pro">
                        Método de pago del anticipo <span class="text-red-500">*</span>
                    </label>
                    <select name="advance_payment_method" class="input-pro">
                        <option value="" selected disabled>Selecciona</option>
                        <option>Transferencia</option>
                        <option>Tarjeta de crédito</option>
                        <option>Tarjeta de débito</option>
                        <option>Enlace digital</option>
                        <option>Depósito</option>
                        <option>Efectivo</option>
                        <option>Dólares</option>
                        <option>Otro</option>
                    </select>
                </div>

            </div>

            <!-- ================= METODO 1 ================= -->
            <div class="rounded-2xl border border-gray-200 p-5 bg-gray-50 space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="font-semibold text-gray-700">Método de pago principal *</h4>
                    <span class="text-xs text-gray-500">Monto MXN se calcula si eliges Dólares</span>
                </div>

                <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
                    <div>
                        <label class="label-pro">Método *</label>
                        <select x-model="method1" name="payment_method_1" required class="input-pro"
                            @change="onMethodChange(1)">
                            <option value="" disabled>Selecciona método</option>
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
                        <label class="label-pro">Monto MXN *</label>
                        <input type="number" step="0.01" x-model.number="amount1" name="amount_1" required
                            class="input-pro" placeholder="Monto MXN" @input="recalc()">
                    </div>
                </div>

                <!-- DÓLARES Método 1 -->
                <div x-show="method1 === 'Dólares'" x-transition
                    class="grid gap-4 p-4 rounded-xl border border-blue-200 bg-blue-50"
                    style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">

                    <div>
                        <label class="label-pro">Dólares (USD)</label>
                        <input type="number" step="0.01" x-model.number="dls1" name="dls_1" class="input-pro"
                            placeholder="USD" @input="convertUsd(1)">
                    </div>

                    <div>
                        <label class="label-pro">Tipo de cambio</label>
                        <input type="number" step="0.01" x-model.number="rate1" name="rate_1"
                            class="input-pro" placeholder="Ej: 17.25" @input="convertUsd(1)">
                    </div>

                    <p class="text-xs text-blue-700" style="grid-column: 1 / -1;">
                        MXN calculado automáticamente: <strong>$<span
                                x-text="(Number(amount1)||0).toFixed(2)"></span></strong>
                    </p>
                </div>
            </div>

            <!-- ================= METODO 2 ================= -->
            <div class="rounded-2xl border border-gray-200 p-5 bg-gray-50 space-y-4">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" x-model="isSecondMethod" @change="toggleSecond()">
                    Agregar segundo método de pago
                </label>

                <div x-show="isSecondMethod" x-transition class="space-y-4">
                    <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
                        <div>
                            <label class="label-pro">Método (2)</label>
                            <select x-model="method2" name="payment_method_2" class="input-pro"
                                @change="onMethodChange(2)">
                                <option value="" disabled>Selecciona método</option>
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
                            <input type="number" step="0.01" x-model.number="amount2" name="amount_2"
                                class="input-pro" placeholder="Monto MXN" @input="recalc()">
                        </div>
                    </div>

                    <!-- DÓLARES Método 2 -->
                    <div x-show="method2 === 'Dólares'" x-transition
                        class="grid gap-4 p-4 rounded-xl border border-blue-200 bg-blue-50"
                        style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">

                        <div>
                            <label class="label-pro">Dólares (USD) (2)</label>
                            <input type="number" step="0.01" x-model.number="dls2" name="dls_2"
                                class="input-pro" placeholder="USD" @input="convertUsd(2)">
                        </div>

                        <div>
                            <label class="label-pro">Tipo de cambio (2)</label>
                            <input type="number" step="0.01" x-model.number="rate2" name="rate_2"
                                class="input-pro" placeholder="Ej: 17.25" @input="convertUsd(2)">
                        </div>

                        <p class="text-xs text-blue-700" style="grid-column: 1 / -1;">
                            MXN calculado automáticamente: <strong>$<span
                                    x-text="(Number(amount2)||0).toFixed(2)"></span></strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- ================= RESUMEN ================= -->
            <div class="relative rounded-2xl border bg-gradient-to-br from-gray-50 to-white p-6 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-[#1C6C73]/10 rounded-full blur-2xl"></div>

                <div class="grid gap-6" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Total procedimiento</p>
                        <p class="text-2xl font-bold text-[#1C6C73]">$<span x-text="procedureTotal.toFixed(2)"></span>
                        </p>
                    </div>

                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Monto liquidado</p>
                        <p class="text-2xl font-bold text-green-600">$<span x-text="paid.toFixed(2)"></span></p>
                    </div>

                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Pendiente</p>
                        <p class="text-2xl font-bold" :class="pending === 0 ? 'text-green-600' : 'text-red-600'">
                            $<span x-text="pending.toFixed(2)"></span>
                        </p>
                    </div>
                </div>

                <div class="mt-5">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Progreso</span>
                        <span x-text="progress + '%'"></span>
                    </div>

                    <div class="w-full h-2 rounded-full bg-gray-200 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500"
                            :class="pending === 0 ? 'bg-green-500' : 'bg-[#1C6C73]'" :style="`width:${progress}%`">
                        </div>
                    </div>
                </div>
            </div>
            <!--  <div class="mt-4 p-3 rounded bg-red-50 text-red-700 text-xs">
    <strong>DEBUG:</strong><br>
    amount1: <span x-text="amount1"></span><br>
    amount2: <span x-text="amount2"></span><br>
    paid: <span x-text="paid"></span><br>
    total: <span x-text="total"></span>
  </div>

           ================= ARCHIVO + NOTAS ================= -->
            <div class="grid gap-6" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
                <div>
                    <label class="label-pro">Subir comprobante *</label>
                    <input type="file" name="receipt_file" required class="input-pro"
                        accept=".pdf,.jpg,.jpeg,.png">
                </div>

                <div style="grid-column: 1 / -1;">
                    <label class="label-pro">Notas (aparecen en el recibo)</label>
                    <textarea name="public_notes" rows="3" class="input-pro" placeholder="Observaciones..."></textarea>
                </div>
            </div>

            <!-- ================= BOTÓN ================= -->
            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="px-6 py-3 rounded-xl bg-[#1C6C73] text-white font-semibold
                    hover:bg-[#155b61] transition disabled:opacity-40"
                    >
                    Generar liquidación
                </button>
            </div>
        </div>
    </form>
</div>

<style>
    .label-pro {
        display: block;
        font-size: .875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: .25rem;
    }

    .input-pro {
        width: 100%;
        padding: .60rem .75rem;
        border-radius: .85rem;
        border: 1px solid #cbd5e1;
        background: #fff;
        transition: all .2s ease;
    }

    .input-pro:focus {
        outline: none;
        border-color: #1C6C73;
        box-shadow: 0 0 0 2px rgba(28, 108, 115, .18);
    }
</style>
<script>
    function liquidacionForm(initialTotal) {
        return {
            /* ======================
               TOTALES REALES
            ====================== */
            procedureTotal: Number(initialTotal || 0),
            alreadyPaid: 0,

            /* ======================
               INPUTS
            ====================== */
            method1: '',
            amount1: 0,
            dls1: 0,
            rate1: 0,

            isSecondMethod: false,
            method2: '',
            amount2: 0,
            dls2: 0,
            rate2: 0,

            bankOrMethod: '',

            /* ======================
               INIT
            ====================== */
            init() {
                if (window.recibosData && Object.keys(window.recibosData).length) {
                    this.fillForm(window.recibosData);
                }
            },

            /* ======================
               AUTOFILL (COMO ANTICIPO)
            ====================== */
            fillForm(data) {

                /* ======================
                   DATOS BASE
                ====================== */
                document.getElementById('l_lead_id').value =
                    data?.lead?.id ?? '';

                document.getElementById('l_patient_name').value =
                    data?.lead?.patient_name ?? '';

                document.getElementById('l_clinic').value =
                    data?.lead?.clinic ?? '';

                document.getElementById('l_procedure_date').value =
                    data?.procedure?.procedure_date ?? '';

                const sel = document.getElementById('l_procedure_type');
                const hidden = document.getElementById('l_procedure_type_hidden');

                if (sel) sel.value = data?.procedure?.procedure_type ?? '';
                if (hidden) hidden.value = data?.procedure?.procedure_type ?? '';

                /* ======================
                   FINANCIEROS (🔥 CLAVE)
                ====================== */
                this.procedureTotal = Number(
                    data?.liquidacion?.procedure_total ?? 0
                );

                this.alreadyPaid = Number(
                    data?.liquidacion?.total_paid ?? 0
                );

          


                /* 👉 enviar total al backend */
                document.getElementById('l_total_amount').value = this.procedureTotal;

                console.log('✅ TOTAL:', this.procedureTotal);
                console.log('✅ YA PAGADO:', this.alreadyPaid);
                console.log('✅ PENDIENTE:', this.amount1);
            },

            /* ======================
               CALCULADOS
            ====================== */
            get paid() {
                return (
                    (Number(this.amount1) || 0) +
                    (this.isSecondMethod ? (Number(this.amount2) || 0) : 0)
                );
            },

            get pending() {
                return Math.max(
                    this.procedureTotal - this.alreadyPaid - this.paid,
                    0
                );
            },

            get progress() {
                const totalPagado = this.alreadyPaid + this.paid;
                return this.procedureTotal > 0 ?
                    Math.min((totalPagado / this.procedureTotal) * 100, 100).toFixed(0) :
                    0;
            },

            get finalMethod() {
                if (this.isSecondMethod && this.method2) {
                    return `${this.method1} + ${this.method2}`;
                }
                return this.method1;
            },

            /* ======================
               MÉTODOS
            ====================== */
            toggleSecond() {
                if (!this.isSecondMethod) {
                    this.method2 = '';
                    this.amount2 = 0;
                    this.dls2 = 0;
                    this.rate2 = 0;
                }
            },

            onMethodChange(n) {
                if (n === 1 && this.method1 !== 'Dólares') {
                    this.dls1 = 0;
                    this.rate1 = 0;
                }
                if (n === 2 && this.method2 !== 'Dólares') {
                    this.dls2 = 0;
                    this.rate2 = 0;
                }
            },

            convertUsd(n) {
                if (n === 1 && this.rate1 > 0) {
                    this.amount1 = +(this.dls1 * this.rate1).toFixed(2);
                }
                if (n === 2 && this.rate2 > 0) {
                    this.amount2 = +(this.dls2 * this.rate2).toFixed(2);
                }
            },

            /* ======================
               SUBMIT
            ====================== */
            submitLiquidacion() {
                const form = document.getElementById('liquidacionForm');

                Swal.fire({
                    title: '¿Confirmar liquidación?',
                    text: `Se liquidará $${this.paid.toFixed(2)}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí',
                }).then(res => {
                    if (!res.isConfirmed) return;

                    fetch(form.action, {
                            method: 'POST',
                            body: new FormData(form),
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(r => r.ok ? r.json() : r.json().then(e => Promise.reject(e)))
                        .then(data => {
                            Swal.fire('Correcto', 'Liquidación generada', 'success');
                            if (data.pdf_url) window.open(data.pdf_url, '_blank');
                        })
                        .catch(err =>
                            Swal.fire('Error', err?.message || 'Error', 'error')
                        );
                });
            }
        }
    }
</script>


<script>
    // Define los datos para llenar el formulario automáticamente
    // Ajusta $recibosData en tu controlador con los valores reales
    window.recibosData = @json($recibosData ?? []);
</script>
