@php
    date_default_timezone_set('America/Mexico_City');
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

<form method="POST"
      action="{{ route('panel.receipts.storeAbono') }}"
      id="new_invoice"
      enctype="multipart/form-data"
      class="space-y-8 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">

@csrf 

{{-- HIDDEN --}}
<input type="hidden" name="lead_id" id="r_lead_id">
<input type="hidden" name="procedure_date" id="r_procedure_date" value="{{ $lead->procedure_date }}">
<input type="hidden" name="seller" id="r_seller" value="{{ $lead->seller }}">
<input type="hidden" name="userid" value="{{ auth()->id() }}">

{{-- HEADER --}}
<div class="flex items-center gap-4">
    <div class="w-12 h-12 rounded-2xl flex items-center justify-center
                bg-gradient-to-br from-[#1C6C73] to-[#4298A7] shadow-md">
        <i class="fas fa-file-invoice-dollar text-white"></i>
    </div>

    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            Comprobante de Abono
        </h2>
        <p class="text-sm text-gray-500">
            Registro de pago parcial del procedimiento
        </p>
    </div>
</div>

{{-- GRID --}}
<div class="grid gap-6"
     style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">

    <div>
        <label class="label-pro">Fecha de emisión *</label>
        <input type="date"
               name="receipt_date"
               value="{{ now()->format('Y-m-d') }}"
               class="input-pro"
               required>
    </div>

    <div>
        <label class="label-pro">Paciente *</label>
        <input type="text"
               id="r_patient_name"
               name="patient_name"
               readonly
               class="input-pro bg-gray-50">
    </div>

    <div>
        <label class="label-pro">Tipo de injerto *</label>
        <select id="r_procedure_type"
                name="procedure_type"
                class="input-pro"
                required>
            <option value="" disabled selected>Selecciona</option>
            <option>Capilar</option>
            <option>Barba</option>
            <option>Ambos</option>
        </select>
    </div>

    <div>
        <label class="label-pro">Fecha del abono *</label>
        <input type="date"
               name="partial_date"
               class="input-pro"
               required>
    </div>

    <div>
        <label class="label-pro">Método de pago *</label>
        <select id="payment_method"
                name="payment_method"
                class="input-pro"
                required>
            <option value="" disabled selected>Selecciona</option>
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
        <label class="label-pro">Monto abonado MXN *</label>
        <input type="number"
               step="0.01"
               id="r_partial_amount"
               name="partial_amount"
               class="input-pro text-lg font-semibold"
               required>
    </div>
</div>

{{-- DÓLARES --}}
<div id="dollar_amount"
     class="hidden grid gap-6 bg-blue-50 border border-blue-200 rounded-xl p-4"
     style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">

    <div>
        <label class="label-pro">Monto en dólares *</label>
        <input type="number"
               step="0.01"
               id="r_advance_amount_dls"
               name="advance_amount_dls"
               class="input-pro">
    </div>

    <div>
        <label class="label-pro">Tipo de cambio *</label>
        <input type="number"
               step="0.01"
               id="r_price_dls"
               name="price_dls"
               class="input-pro">
    </div>
</div>

{{-- EXTRA --}}
<div class="grid gap-6"
     style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">

    <div>
        <label class="label-pro">Clínica *</label>
        <select id="r_clinic"
                name="clinic"
                class="input-pro"
                required>
            <option value="" disabled selected>Selecciona</option>
            <option>Queretaro</option>
            <option>Pedregal</option>
            <option>Santa Fe</option>
        </select>
    </div>

    <div>
        <label class="label-pro">Banco o Medio *</label>
        <input type="text"
               name="bank_or_method"
               class="input-pro"
               required>
    </div>

    <div>
        <label class="label-pro">Subir recibo *</label>
        <input type="file"
               name="receipt_file"
               accept=".pdf,.jpg,.jpeg,.png"
               class="input-pro"
               required>
    </div>

    <div style="grid-column: 1 / -1;">
        <label class="label-pro">Notas</label>
        <textarea name="public_notes"
                  rows="2"
                  class="input-pro"></textarea>
    </div>
</div>

{{-- BOTÓN --}}
<div class="flex justify-end pt-4">
    <button type="submit"
            class="flex items-center gap-2 px-6 py-3 rounded-xl
                   bg-[#1C6C73] hover:bg-[#155b61]
                   text-white font-semibold shadow-md transition">
        <i class="fas fa-check-circle"></i>
        Generar abono
    </button>
</div>

</form>
</div>
<style>
  .label-pro{
    display:block;
    font-size:.875rem;
    font-weight:600;
    color:#374151;
    margin-bottom:.25rem;
  }
  .input-pro{
    width:100%;
    padding:.60rem .75rem;
    border-radius:.85rem;
    border:1px solid #cbd5e1;
    background:#fff;
    transition: all .2s ease;
  }
  .input-pro:focus{
    outline:none;
    border-color:#1C6C73;
    box-shadow:0 0 0 2px rgba(28,108,115,.18);
  }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const form          = document.getElementById('new_invoice');
    const paymentMethod = document.getElementById('payment_method');
    const dollarBox     = document.getElementById('dollar_amount');
    const priceBox      = document.getElementById('price_dls');
    const dlsInput      = document.getElementById('r_advance_amount_dls');
    const priceInput    = document.getElementById('r_price_dls');
    const mxnInput      = document.getElementById('r_partial_amount');

    if (!form || !paymentMethod) return;

    function toggleDollar() {
        const isDollar = paymentMethod.value === 'Dólares';

        dollarBox.classList.toggle('d-none', !isDollar);
        priceBox.classList.toggle('d-none', !isDollar);

        dlsInput.required   = isDollar;
        priceInput.required = isDollar;

        if (!isDollar) {
            dlsInput.value = '';
            priceInput.value = '';
        }
    }

    function calculateMXN() {
        const dls   = parseFloat(dlsInput.value || 0);
        const price = parseFloat(priceInput.value || 0);

        if (price > 0) {
            mxnInput.value = (dls * price).toFixed(2);
        }
    }
    paymentMethod.addEventListener('change', toggleDollar);
    dlsInput.addEventListener('input', calculateMXN);
    priceInput.addEventListener('input', calculateMXN);

    // SUBMIT AJAX
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: '¿Confirmar generación del recibo?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, generar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.ok ? r.blob() : Promise.reject())
            .then(() => {
                Swal.fire('Éxito', 'Recibo generado correctamente', 'success');
            })
            .catch(() => {
                Swal.fire('Error', 'No se pudo generar el recibo', 'error');
            });
        });
    });

});
</script>
@endpush
