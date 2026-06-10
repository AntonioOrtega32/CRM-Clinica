@php
    date_default_timezone_set('America/Mexico_City');
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

<form method="POST"
      action="{{ route('panel.receipts.storeproduc') }}"
      id="productoForm"
      class="space-y-8 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">

@csrf

{{-- HIDDEN --}}
<input type="hidden" name="lead_id" id="p_lead_id">
<input type="hidden" name="userid" value="{{ auth()->id() }}">

{{-- HEADER --}}
<div class="flex items-center gap-4">
    <div class="w-12 h-12 rounded-2xl flex items-center justify-center
                bg-gradient-to-br from-[#1C6C73] to-[#4298A7] shadow-md">
        <i class="fas fa-shopping-bag text-white"></i>
    </div>

    <div>
        <h2 class="text-2xl font-bold text-gray-800">Compra de Producto</h2>
        <p class="text-sm text-gray-500">Generar recibo por venta de producto</p>
    </div>
</div>

{{-- GRID --}}
<div class="grid gap-6" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">

    <div>
        <label class="label-pro">Fecha *</label>
        <input type="date"
               name="receipt_date"
               value="{{ now()->format('Y-m-d') }}"
               class="input-pro"
               required>
    </div>

    <div>
        <label class="label-pro">Paciente *</label>
        <input type="text"
               id="p_patient_name"
               name="patient_name"
               readonly
               class="input-pro bg-gray-50">
    </div>

    <div>
        <label class="label-pro">Producto(s) *</label>
        <input type="text"
               name="product"
               class="input-pro"
               placeholder="Nombre del producto"
               required>
    </div>

    <div>
        <label class="label-pro">Método de pago *</label>
        <select name="payment_method"
                id="p_payment_method"
                class="input-pro"
                required>
            <option value="" disabled selected>Selecciona</option>
            <option>Efectivo</option>
            <option>Transferencia</option>
            <option>TDD</option>
            <option>TDC</option>
            <option>Depósito</option>
            <option>Dólares</option>
        </select>
    </div>

    <div>
        <label class="label-pro">Monto total MXN *</label>
        <input type="number"
               step="0.01"
               name="amount"
               id="p_amount"
               class="input-pro text-lg font-semibold"
               required>
    </div>

    <div>
        <label class="label-pro">Sucursal *</label>
        <select name="clinic"
                id="p_clinic"
                class="input-pro"
                required>
            <option value="" disabled selected>Selecciona</option>
            <option>Queretaro</option>
            <option>Pedregal</option>
            <option>Santa Fe</option>
        </select>
    </div>
</div>

{{-- BLOQUE DÓLARES --}}
<div id="p_dollar_box"
     class="bg-blue-50 border border-blue-200 rounded-xl p-4 hidden">

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem;">
        <div>
            <label class="label-pro">USD</label>
            <input type="number"
                   id="p_dls"
                   name="advance_amount_dls"
                   class="input-pro"
                   placeholder="0.00">
        </div>

        <div>
            <label class="label-pro">Tipo de cambio</label>
            <input type="number"
                   step="0.01"
                   id="p_rate"
                   name="price_dls"
                   class="input-pro"
                   placeholder="0.00">
        </div>
    </div>

    <div style="margin-top: 1rem; font-size: 0.95rem; color: #1C6C73; font-weight: 600;">
        MXN calculado: <span id="p_mxn_result">$0.00</span>
    </div>
</div>

{{-- NOTAS --}}
<div>
    <label class="label-pro">Notas</label>
    <textarea name="notes"
              rows="2"
              class="input-pro"></textarea>
</div>

{{-- BOTÓN --}}
<div class="flex justify-end pt-4">
    <button type="submit"
            class="flex items-center gap-2 px-6 py-3 rounded-xl
                   bg-[#1C6C73] hover:bg-[#155b61]
                   text-white font-semibold shadow-md transition">
        <i class="fas fa-check-circle"></i>
        Generar recibo
    </button>
</div>

</form>
</div>

{{-- ESTILOS --}}
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
}
.input-pro:focus{
    outline:none;
    border-color:#1C6C73;
    box-shadow:0 0 0 2px rgba(28,108,115,.18);
}
.dollar-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.5rem;
}
.hidden {
    display: none;
}
</style>

{{-- JS --}}
<script>
document.addEventListener('DOMContentLoaded', function() {

    const paymentSelect = document.getElementById('p_payment_method');
    const dollarBox     = document.getElementById('p_dollar_box');
    const dlsInput      = document.getElementById('p_dls');
    const rateInput     = document.getElementById('p_rate');
    const amountInput   = document.getElementById('p_amount');
    const mxnResult     = document.getElementById('p_mxn_result');
    const form          = document.getElementById('productoForm');

    // Validar que los elementos existan
    if (!paymentSelect || !dollarBox) {
        console.error('Error: No se encontraron elementos necesarios');
        return;
    }

    /* =============================
       MOSTRAR / OCULTAR DÓLARES
    ============================= */
    function toggleDollar() {
        const value = paymentSelect.value;
        console.log('Método seleccionado:', value);

        if (value.trim() === 'Dólares') {
            console.log('Mostrando recuadro de dólares');
            dollarBox.classList.remove('hidden');
        } else {
            console.log('Ocultando recuadro de dólares');
            dollarBox.classList.add('hidden');
            dlsInput.value  = '';
            rateInput.value = '';
            mxnResult.textContent = '$0.00';
            amountInput.value = '';
        }
    }

    /* =============================
       CONVERSIÓN DÓLARES → MXN
    ============================= */
    function calculateMXN() {
        const dls  = parseFloat(dlsInput.value) || 0;
        const rate = parseFloat(rateInput.value) || 0;
        const total = dls * rate;

        if (dls > 0 && rate > 0) {
            amountInput.value = total.toFixed(2);
            mxnResult.textContent = '$' + total.toFixed(2);
            console.log('Cálculo: ' + dls + ' USD × ' + rate + ' = $' + total.toFixed(2) + ' MXN');
        } else {
            amountInput.value = '';
            mxnResult.textContent = '$0.00';
        }
    }

    /* =============================
       EVENT LISTENERS
    ============================= */
    paymentSelect.addEventListener('change', toggleDollar);
    dlsInput.addEventListener('input', calculateMXN);
    rateInput.addEventListener('input', calculateMXN);

    // Ejecutar al cargar la página
    console.log('Inicializando formulario');
    toggleDollar();

    /* =============================
       SUBMIT + PDF
    ============================= */
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (paymentSelect.value === 'Dólares' && (!dlsInput.value || !rateInput.value)) {
            Swal.fire(
                'Datos incompletos',
                'Ingresa los dólares y el tipo de cambio',
                'warning'
            );
            return;
        }

        Swal.fire({
            title: '¿Generar recibo de producto?',
            text: 'Se abrirá el PDF en una nueva ventana',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, generar'
        }).then(result => {
            if (!result.isConfirmed) return;

            const win = window.open('', '_blank');

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.pdf_url) {
                    win.location.href = data.pdf_url;
                    Swal.fire('Correcto', 'Recibo generado', 'success');
                } else {
                    win.close();
                    Swal.fire('Error', 'No se generó el PDF', 'error');
                }
            })
            .catch(() => {
                win.close();
                Swal.fire('Error', 'Error inesperado', 'error');
            });
        });
    });

});
</script>

