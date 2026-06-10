<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

<form method="POST"
      action="{{ route('panel.receipts.storetreatment') }}"
      id="treatmentForm"
      class="space-y-8 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">

@csrf

{{-- ================= HIDDEN ================= --}}
<input type="hidden" name="lead_id" id="t_lead_id">
<input type="hidden" name="userid" value="{{ auth()->id() }}">
<input type="hidden" name="products" id="products_input">
<input type="hidden" name="total_amount" id="total_amount_input">
<input type="hidden" name="total_usd" id="total_usd">

{{-- ================= HEADER ================= --}}
<div class="flex items-center gap-4">
    <div class="w-12 h-12 rounded-2xl flex items-center justify-center
        bg-gradient-to-br from-[#1C6C73] to-[#4298A7] shadow-md">
        <i class="fas fa-syringe text-white"></i>
    </div>
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Recibos de Tratamientos / Productos</h2>
        <p class="text-sm text-gray-500">Registro de tratamientos y productos</p>
    </div>
</div>

{{-- ================= DATOS ================= --}}
<div class="grid gap-6 md:grid-cols-3">

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
               id="t_patient_name"
               name="patient_name"
               readonly
               class="input-pro bg-gray-50">
    </div>

    <div>
        <label class="label-pro">Clínica *</label>
        <select name="clinic" id="t_clinic" class="input-pro" required>
            <option value="">Selecciona</option>
            <option value="Queretaro">Queretaro</option>
            <option value="Pedregal">Pedregal</option>
            <option value="Santa Fe">Santa Fe</option>
        </select>
    </div>

</div>

{{-- ================= CARRITO ================= --}}
<div class="border-t pt-6">

<h3 class="text-lg font-semibold text-gray-700 mb-4">
    Tratamientos / Productos
</h3>

<div class="grid gap-6 md:grid-cols-4 mb-4">

    <div>
        <label class="label-pro">Tipo</label>
        <select id="productType" class="input-pro">
            <option value="Tratamiento">Tratamiento</option>
            <option value="Producto">Producto</option>
        </select>
    </div>

    <div>
        <label class="label-pro">Nombre</label>
        <select id="productName" class="input-pro"></select>
    </div>

    <div>
        <label class="label-pro">Cantidad</label>
        <input type="number" id="productQty" min="1" value="1" class="input-pro">
    </div>

    <div>
        <label class="label-pro">Precio unitario *</label>
        <input type="number" id="productPrice" step="0.01" min="0" class="input-pro">
    </div>

    <div class="flex items-end">
        <button type="button"
                onclick="addToCart()"
                class="w-full px-4 py-3 rounded-xl bg-[#1C6C73] text-white">
            Agregar
        </button>
    </div>

</div>

<table class="w-full text-sm border rounded-xl">
<thead class="bg-gray-100">
<tr>
    <th>Tipo</th>
    <th>Nombre</th>
    <th>Precio</th>
    <th>Cant</th>
    <th>Subtotal</th>
    <th></th>
</tr>
</thead>

<tbody id="cartBody"></tbody>

<tfoot class="bg-gray-50">
<tr>
    <td colspan="4" class="text-right font-semibold">Total</td>
    <td id="total_amount">$0.00</td>
    <td></td>
</tr>
</tfoot>
</table>

</div>

{{-- ================= MÉTODO DE PAGO ================= --}}
<div class="border rounded-xl p-6 bg-white">

<div class="grid gap-6 md:grid-cols-2">

    <div>
        <label class="label-pro">Método de pago *</label>
        <select name="pay_method_1"
                id="main_payment_method"
                class="input-pro"
                required>
            <option value="">Selecciona</option>
            <option value="Efectivo">Efectivo</option>
            <option value="Transferencia">Transferencia</option>
            <option value="TDD">TDD</option>
            <option value="TDC">TDC</option>
            <option value="Dólares">Dólares</option>
        </select>
    </div>

    <div>
        <label class="label-pro">Monto MXN *</label>
        <input type="number"
               step="0.01"
               min="0"
               id="amount_mxn"
               name="paid_total"
               class="input-pro"
               value="0"
               required>
    </div>

</div>

{{-- USD --}}
<div id="usdBlock"
     class="hidden mt-6 p-4 rounded-xl border border-blue-200 bg-blue-50">

<div class="grid gap-6 md:grid-cols-2">

    <div>
        <label class="label-pro">USD</label>
        <input type="number" id="amount_usd" step="0.01" min="0" class="input-pro">
    </div>

    <div>
        <label class="label-pro">Tipo de cambio</label>
        <input type="number" id="exchange_rate" step="0.01" min="0" value="17.00" class="input-pro">
    </div>

</div>

<p class="text-sm mt-3 text-blue-700">
    MXN calculado: <strong id="mxnCalculated">$0.00</strong>
</p>

</div>

</div>

{{-- ================= BOTÓN ================= --}}
<div class="flex justify-end pt-6">
<button type="submit"
        class="px-6 py-3 rounded-xl bg-[#1C6C73] text-white font-semibold">
    Guardar tratamiento
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
}
.input-pro:focus{
    outline:none;
    border-color:#1C6C73;
    box-shadow:0 0 0 2px rgba(28,108,115,.18);
}
</style>

<script>
const qs = id => document.getElementById(id);
const money = n => '$' + Number(n || 0).toFixed(2);

/* ================= USD ================= */
const methodSelect = qs('main_payment_method');
const usdBlock = qs('usdBlock');
const amountUSD = qs('amount_usd');
const exchangeRate = qs('exchange_rate');
const amountMXN = qs('amount_mxn');
const mxnText = qs('mxnCalculated');
const totalUsdInp = qs('total_usd');

function calculateMXN() {
    const usd = parseFloat(amountUSD.value || 0);
    const rate = parseFloat(exchangeRate.value || 0);
    const mxn = usd * rate;

    amountMXN.value = mxn.toFixed(2);
    mxnText.textContent = money(mxn);
    totalUsdInp.value = usd.toFixed(2);
}

methodSelect.addEventListener('change', () => {
    if (methodSelect.value === 'Dólares') {
        usdBlock.classList.remove('hidden');
        amountMXN.readOnly = true;
        calculateMXN();
    } else {
        usdBlock.classList.add('hidden');
        amountMXN.readOnly = false;
        totalUsdInp.value = '';
    }
});

amountUSD.addEventListener('input', calculateMXN);
exchangeRate.addEventListener('input', calculateMXN);

/* ================= CARRITO ================= */
const catalog = {
    Tratamiento: ['Exosomas','Dutasteride','Kenalog','Factores de Crecimiento'],
    Producto: ['Minoxidil','Folix']
};

let cart = [];

function populateProducts() {
    const type = qs('productType').value;
    qs('productName').innerHTML = '';
    catalog[type].forEach(p => {
        const o = document.createElement('option');
        o.value = p;
        o.textContent = p;
        qs('productName').appendChild(o);
    });
}

populateProducts();
qs('productType').addEventListener('change', populateProducts);

window.addToCart = () => {
    const type = qs('productType').value;
    const name = qs('productName').value;
    const qty = parseInt(qs('productQty').value || 1);
    const price = parseFloat(qs('productPrice').value || 0);

    if (!name || price <= 0 || qty < 1) {
        Swal.fire('Error','Datos inválidos','warning');
        return;
    }

    cart.push({ tipo:type, nombre:name, cant:qty, precio:price });
    render();
};

function render() {
    let total = 0;
    qs('cartBody').innerHTML = '';

    cart.forEach((p,i) => {
        const sub = p.cant * p.precio;
        total += sub;

        qs('cartBody').innerHTML += `
        <tr class="border-t">
            <td class="p-2">${p.tipo}</td>
            <td class="p-2">${p.nombre}</td>
            <td class="p-2">${money(p.precio)}</td>
            <td class="p-2">${p.cant}</td>
            <td class="p-2 font-semibold">${money(sub)}</td>
            <td class="p-2">
                <button type="button" onclick="removeItem(${i})">✕</button>
            </td>
        </tr>`;
    });

    qs('total_amount').textContent = money(total);
    qs('total_amount_input').value = total.toFixed(2);
    qs('products_input').value = JSON.stringify(cart);
}

window.removeItem = i => {
    cart.splice(i,1);
    render();
};

/* ================= SUBMIT ================= */
qs('treatmentForm').addEventListener('submit', e => {
    const total = parseFloat(qs('total_amount_input').value || 0);
    const paid = parseFloat(qs('amount_mxn').value || 0);

    if (cart.length === 0) {
        e.preventDefault();
        Swal.fire('Error','Carrito vacío','warning');
        return;
    }

    if (Math.abs(total - paid) > 0.01) {
        e.preventDefault();
        Swal.fire('Error','El pago no coincide con el total','warning');
    }
});
</script>
