<div x-show="reciboOpen" x-cloak
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4 overflow-auto"
    @click.self="reciboOpen=false"><!-- clic afuera cierra el modal -->

    <div class="rounded-2xl shadow-xl w-full max-w-2xl p-6 overflow-y-auto max-h-[90vh]"
        style="background-color: #DED5CE; color: #1C6C73;">

        <h2 class="text-2xl font-bold mb-4" style="color: #4298A7;">Generar Recibo</h2>

        <!-- Botón cerrar -->
        <button @click="reciboOpen=false" class="absolute top-3 right-3 text-2xl font-bold"
            style="color: #1C6C73;">&times;</button>

        <!-- Tipo de recibo -->
        <div class="mb-4">
            <label class="block mb-1 font-semibold" style="color: #CDAF95;">Tipo de recibo *</label>
            <select id="type_receipt" class="w-full border rounded-lg px-3 py-2 focus:outline-none"
                style="border-color: #C8BAAF; background-color: #FFF; color: #1C6C73;">
                <option value="" disabled selected>Selecciona</option>
                <option value="anticipo">Anticipo</option>
                <option value="abono">Abono</option>
                <option value="liquidacion">Liquidación</option>
                <option value="producto">Producto</option>
                <option value="tratamiento">Tratamiento</option>
            </select>
        </div>
        <button @click="reciboOpen=false" class="absolute top-3 right-3 text-2xl font-bold hover:text-red-600"
            style="color: #1C6C73;">&times;</button>
        <!-- Formularios por tipo -->
        <div id="form_sections">
            <!-- Formulario Anticipo -->
            <!-- Formulario Anticipo - Tailwind -->
            <form class="hidden" data-type="anticipo" method="POST" action="{{ route('panel.receipts.store') }}"
                id="anticipo_form">
                @csrf


                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- Fecha en que se expide -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Fecha en que se expide <span
                                class="text-red-500">*</span></label>
                        <input type="date" class="w-full border rounded px-3 py-2" name="receipt_date"
                            id="receipt_date" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <!-- Nombre del Paciente -->
                    <input type="hidden" name="lead_id" value="">
                    <input type="hidden" name="uploaded_by" value="{{ auth()->user()->id }}">

                    <!-- Nombre del paciente -->
                    <label class="block mb-1 font-semibold" style="color:#CDAF95;">Buscar paciente *</label>
                    <input type="text" name="patient_name"
                        class="autocomplete-input w-full border rounded px-2 py-1 mb-2" required>
                    <ul class="autocomplete-list absolute w-full mt-1 rounded shadow-lg z-50 hidden"
                        style="background-color:#C8BAAF; color:#1C6C73; max-height:150px; overflow:auto;"></ul>
                    <!-- Tipo de Injerto -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Tipo de Injerto <span
                                class="text-red-500">*</span></label>
                        <select class="w-full border rounded px-3 py-2 bg-white" name="procedure_type"
                            id="procedure_type" required readonly>
                            <option value="" selected disabled>Selecciona</option>
                            <option value="Capilar">Capilar</option>
                            <option value="Barba">Barba</option>
                            <option value="Ambos">Ambos</option>
                        </select>
                    </div>

                    <!-- Fecha del Anticipo -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Fecha del Anticipo <span
                                class="text-red-500">*</span></label>
                        <input type="date" class="w-full border rounded px-3 py-2" name="payment_date"
                            id="payment_date" required>
                    </div>

                    <!-- Método de Pago -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Método de pago <span
                                class="text-red-500">*</span></label>
                        <select class="w-full border rounded px-3 py-2 bg-white" name="payment_method"
                            id="payment_method" required>
                            <option value="" selected disabled>Selecciona</option>
                            <option value="Transferencia">Transferencia</option>
                            <option value="TDC">Tarjeta de crédito</option>
                            <option value="TDD">Tarjeta de débito</option>
                            <option value="Enlace digital">Enlace digital</option>
                            <option value="Depósito">Depósito</option>
                            <option value="Efectivo">Efectivo</option>
                            <option value="Dólares">Dólares</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <!-- Monto en Dólares -->
                    <div class="amount-dls-div hidden">
                        <label class="block text-gray-700 font-semibold mb-1">Monto Abonado (USD)</label>
                        <input type="number" class="w-full border rounded px-3 py-2" name="advance_amount_dls"
                            id="advance_amount_dls" placeholder="0" min="0">
                    </div>

                    <div class="amount-dls-div hidden">
                        <label class="block text-gray-700 font-semibold mb-1">Precio del dólar</label>
                        <input type="number" class="w-full border rounded px-3 py-2" name="price_dls" id="price_dls"
                            placeholder="0" min="0" step="0.01">
                    </div>

                    <!-- Monto del Anticipo MXN -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Monto del Anticipo (MXN) <span
                                class="text-red-500">*</span></label>
                        <input type="number" class="w-full border rounded px-3 py-2" name="advance_amount"
                            id="advance_amount_mxn" placeholder="0" min="0" step="0.01" required>
                    </div>

                    <!-- Costo total -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Costo total del procedimiento (MXN) <span
                                class="text-red-500">*</span></label>
                        <input type="number" class="w-full border rounded px-3 py-2" name="total_amount"
                            id="total_amount" placeholder="0" min="0" step="1" required>
                    </div>

                    <!-- Importe pendiente -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Importe a liquidar (MXN) <span
                                class="text-red-500">*</span></label>
                        <input type="number" class="w-full border rounded px-3 py-2 bg-gray-100"
                            name="pending_amount" id="pending_amount" placeholder="0" min="0" readonly>
                    </div>

                    <!-- Fecha procedimiento -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Fecha del procedimiento <span
                                class="text-red-500">*</span></label>
                        <input type="date" class="w-full border rounded px-3 py-2 bg-gray-100"
                            name="procedure_date" id="procedure_date" required disabled readonly>
                        <label class="inline-flex items-center mt-2">
                            <input type="checkbox" name="open_date" id="open_date" value="1" disabled
                                class="mr-2">
                            <strong>Fecha abierta para 2024</strong>
                        </label>
                    </div>

                    <!-- Clínica -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Generado en <span
                                class="text-red-500">*</span></label>
                        <select class="w-full border rounded px-3 py-2 bg-white" name="clinic" id="clinic"
                            required>
                            <option value="" selected disabled>Selecciona</option>
                            <option value="Queretaro">Queretaro</option>
                            <option value="Pedregal">Pedregal</option>
                            <option value="Santa Fe">Santa Fe</option>
                        </select>
                    </div>

                    <!-- Vendedor -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Vendedor <span
                                class="text-red-500">*</span></label>
                        <select class="w-full border rounded px-3 py-2 bg-white" name="vendedor" id="vendedor"
                            required>
                            <option value="" selected disabled>Selecciona</option>
                            <option value="Janeth Ruíz">Janeth Ruíz</option>
                            <option value="Marisol Olmos">Marisol Olmos</option>
                            <option value="Adriana Silva">Adriana Silva</option>
                            <option value="Dra Monse">Dra Monse Mata</option>
                            <option value="Alison Ruiz">Alison Ruiz</option>
                        </select>
                    </div>

                    <!-- Correo electrónico -->
                    <div class="md:col-span-3">
                        <label class="block text-gray-700 font-semibold mb-1">Correo electrónico</label>
                        <input type="email" class="w-full border rounded px-3 py-2" name="email" required>
                    </div>

                    <!-- Notas y checkbox -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Notas (aparecerán en el recibo)</label>
                        <textarea class="w-full border rounded px-3 py-2" rows="3" name="public_notes" id="public_notes"></textarea>
                        <label class="inline-flex items-center mt-2">
                            <input type="checkbox" name="ingles" id="ingles" class="mr-2"> Generar en inglés
                        </label>
                    </div>

                    <!-- Botón -->
                    <div class="md:col-span-3 text-right">
                        <button type="submit"
                            class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded font-semibold">Generar</button>
                    </div>

                </div>
            </form>



            <!-- Formulario Abono -->
            <form class="hidden" data-type="abono" method="POST" action="{{ route('panel.receipts.storeAbono') }}"
                id="abonoForm">
                @csrf
                <input type="hidden" name="lead_id" value="">
                <input type="hidden" name="type" value="abono">

                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Buscar paciente *</label>
                <input type="text" name="patient_name"
                    class="autocomplete-input w-full border rounded px-2 py-1 mb-2" required>
                <ul class="autocomplete-list absolute w-full mt-1 rounded shadow-lg z-50 hidden"
                    style="background-color:#C8BAAF; color:#1C6C73; max-height:150px; overflow:auto;"></ul>

                <label>Monto Abonado *</label>
                <input type="number" name="amount" id="r_amount" class="w-full border rounded px-2 py-1 mb-2"
                    min="0" required>

                <label>Fecha del Abono *</label>
                <input type="date" name="payment_date" class="w-full border rounded px-2 py-1 mb-2" required>

                <label>Observación Abono</label>
                <input type="text" name="abono_note" class="w-full border rounded px-2 py-1 mb-2">

                <label>Método de pago *</label>
                <select name="method" id="r_method" class="w-full border rounded px-2 py-1 mb-2" required>
                    <option value="" disabled selected>Selecciona</option>
                    <option value="Transferencia">Transferencia</option>
                    <option value="TDC">Tarjeta de crédito</option>
                    <option value="TDD">Tarjeta de débito</option>
                    <option value="Enlace digital">Enlace digital</option>
                    <option value="Depósito">Depósito</option>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Dólares">Dólares</option>
                    <option value="Otro">Otro</option>
                </select>

                <div class="amount-dls-div hidden mt-2">
                    <label>Monto Abonado (USD)</label>
                    <input type="number" name="amount_conversion" id="r_amount_usd"
                        class="w-full border rounded px-2 py-1 mb-2" min="0" step="0.01">
                    <label>Tipo de cambio</label>
                    <input type="number" name="conversion" id="r_conversion"
                        class="w-full border rounded px-2 py-1 mb-2" min="0" step="0.01">
                </div>
                <label>Clínica *</label>
                <select name="clinic" class="w-full border rounded px-2 py-1 mb-2" required>
                    <option value="" disabled selected>Selecciona</option>
                    <option value="Pedregal">Pedregal</option>
                    <option value="Santa Fe">Santa Fe</option>
                    <option value="Queretaro">Querétaro</option>
                </select>

                <button type="submit" class="mt-2 px-4 py-2 rounded" style="background:#4298A7; color:#FFF;">Generar
                    Abono</button>
            </form>
            <!-- Formulario Liquidación -->
            <form class="hidden" data-type="liquidacion" method="POST"
                action="{{ route('panel.receipts.store') }}">
                @csrf

                <!-- Tipo de recibo -->
                <input type="hidden" name="type" value="liquidacion">

                <!-- Fecha de expedición -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Fecha en que se expide *</label>
                <input type="date" name="receipt_date" class="w-full border rounded px-2 py-1 mb-2"
                    value="{{ date('Y-m-d') }}" required>

                <!-- Nombre del paciente -->
                <input type="hidden" name="lead_id" value="">
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Buscar paciente *</label>
                <input type="text" name="patient_name"
                    class="autocomplete-input w-full border rounded px-2 py-1 mb-2" required>
                <ul class="autocomplete-list absolute w-full mt-1 rounded shadow-lg z-50 hidden"
                    style="background-color:#C8BAAF; color:#1C6C73; max-height:150px; overflow:auto;"></ul>

                <!-- Tipo de injerto -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Tipo de Injerto *</label>
                <select name="procedure_type" class="w-full border rounded px-2 py-1 mb-2" required>
                    <option selected disabled>Selecciona</option>
                    <option value="Capilar">Capilar</option>
                    <option value="Barba">Barba</option>
                    <option value="Ambos">Ambos</option>
                </select>

                <!-- Fecha del anticipo -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Fecha del Anticipo *</label>
                <input type="date" name="advance_date" class="w-full border rounded px-2 py-1 mb-2" required>

                <!-- Monto abonado -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Monto Abonado (MXN) *</label>
                <div class="flex">
                    <span class="px-2 py-1 border rounded-l bg-gray-200">$</span>
                    <input type="number" name="advance_amount"
                        class="w-full border-t border-b border-r rounded-r px-2 py-1 mb-2" placeholder="0"
                        min="1" required>
                </div>

                <!-- Método de pago del anticipo -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Método de pago del anticipo *</label>
                <select name="advance_payment_method" class="w-full border rounded px-2 py-1 mb-2" required>
                    <option selected disabled value="">Selecciona</option>
                    <option value="Transferencia">Transferencia</option>
                    <option value="TDC">Tarjeta de crédito</option>
                    <option value="TDD">Tarjeta de débito</option>
                    <option value="Enlace digital">Enlace digital</option>
                    <option value="Depósito">Depósito</option>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Dólares">Dólares</option>
                    <option value="Otro">Otro</option>
                </select>

                <!-- Fecha de liquidación -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Fecha de Liquidación *</label>
                <input type="date" name="payment_date" class="w-full border rounded px-2 py-1 mb-2" required>

                <!-- Método de pago de liquidación -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Método de pago de liquidación *</label>
                <select name="method" class="w-full border rounded px-2 py-1 mb-2" required>
                    <option selected disabled value="">Selecciona</option>
                    <option value="Transferencia">Transferencia</option>
                    <option value="TDC">Tarjeta de crédito</option>
                    <option value="TDD">Tarjeta de débito</option>
                    <option value="Enlace digital">Enlace digital</option>
                    <option value="Depósito">Depósito</option>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Dólares">Dólares</option>
                    <option value="Otro">Otro</option>
                </select>

                <!-- Monto liquidado -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Monto que liquidó (MXN) *</label>
                <input type="number" name="amount" class="w-full border rounded px-2 py-1 mb-2" min="1"
                    step="0.1" required>

                <!-- Costo total del procedimiento -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Costo total del procedimiento (MXN)
                    *</label>
                <input type="number" name="total_amount" class="w-full border rounded px-2 py-1 mb-2"
                    min="1" step="0.1" required>

                <!-- Clínica -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Generado en *</label>
                <select name="clinic" class="w-full border rounded px-2 py-1 mb-2" required>
                    <option selected disabled>Selecciona</option>
                    <option value="Queretaro">Querétaro</option>
                    <option value="Pedregal">Pedregal</option>
                    <option value="Santa Fe">Santa Fe</option>
                </select>

                <!-- Vendedor -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Vendedor *</label>
                <select name="vendedor" class="w-full border rounded px-2 py-1 mb-2" required>
                    <option selected disabled>Selecciona</option>
                    <option value="Janeth Ruíz">Janeth Ruíz</option>
                    <option value="Marisol Olmos">Marisol Olmos</option>
                    <option value="Adriana Silva">Adriana Silva</option>
                </select>

                <!-- Notas -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Notas (aparecerán en el recibo)</label>
                <textarea name="public_notes" class="w-full border rounded px-2 py-1 mb-2"></textarea>

                <!-- Botón -->
                <button type="submit" class="mt-2 px-4 py-2 rounded" style="background:#4298A7; color:#FFF;">
                    Generar Liquidación
                </button>
            </form>


            <!-- Formulario Producto -->
            <!-- Formulario Producto -->
            <form class="hidden" data-type="producto" method="POST"
                action="{{ route('panel.receipts.storeproduc') }}">
                @csrf
                <input type="hidden" name="lead_id" value="">
                <input type="hidden" name="uploaded_by" value="{{ auth()->user()->id }}">

                <!-- Nombre del paciente -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Buscar paciente *</label>
                <input type="text" name="patient_name"
                    class="autocomplete-input w-full border rounded px-2 py-1 mb-2" required>
                <ul class="autocomplete-list absolute w-full mt-1 rounded shadow-lg z-50 hidden"
                    style="background-color:#C8BAAF; color:#1C6C73; max-height:150px; overflow:auto;"></ul>

                <!-- Nombre del producto -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Nombre Producto *</label>
                <input type="text" name="producto_name" class="w-full border rounded px-2 py-1 mb-2" required>

                <!-- Cantidad -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Cantidad *</label>
                <input type="number" name="cantidad" class="w-full border rounded px-2 py-1 mb-2" min="1"
                    required>

                <!-- Método de pago -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Forma de pago *</label>
                <select name="method" class="w-full border rounded px-2 py-1 mb-2" required>
                    <option value="" disabled selected>Selecciona</option>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Transferencia">Transferencia</option>
                    <option value="TDC">Tarjeta de crédito</option>
                    <option value="TDD">Tarjeta de débito</option>
                    <option value="Dólares">Dólares</option>
                    <option value="Deposito">Depósito</option>
                </select>

                <!-- Monto en dólares (opcional) -->
                <div class="amount-dls-div hidden mt-2">
                    <label>Monto (USD)</label>
                    <input type="number" name="amount_conversion" class="w-full border rounded px-2 py-1 mb-2"
                        min="0" step="0.01">
                    <label>Tipo de cambio</label>
                    <input type="number" name="conversion" class="w-full border rounded px-2 py-1 mb-2"
                        min="0" step="0.01">
                </div>

                <!-- Monto total en MXN -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Monto total (MXN) *</label>
                <input type="number" name="amount" class="w-full border rounded px-2 py-1 mb-2" min="0"
                    step="0.1" required>

                <!-- Fecha del recibo -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Fecha del recibo *</label>
                <input type="date" name="receipt_date" class="w-full border rounded px-2 py-1 mb-2"
                    value="{{ date('Y-m-d') }}" required>

                <!-- Fecha de pago -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Fecha de pago *</label>
                <input type="date" name="payment_date" class="w-full border rounded px-2 py-1 mb-2"
                    value="{{ date('Y-m-d') }}" required>

                <!-- Clínica -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Sucursal *</label>
                <select name="clinic" class="w-full border rounded px-2 py-1 mb-2" required>
                    <option value="" disabled selected>Selecciona</option>
                    <option value="Pedregal">Pedregal</option>
                    <option value="Queretaro">Querétaro</option>
                    <option value="Santa Fe">Santa Fe</option>
                </select>

                <!-- Notas públicas -->
                <label class="block mb-1 font-semibold" style="color:#CDAF95;">Notas (aparecerán en el recibo)</label>
                <textarea name="public_notes" class="w-full border rounded px-2 py-1 mb-2"></textarea>

                <!-- Botón -->
                <button type="submit" class="mt-2 px-4 py-2 rounded" style="background:#4298A7; color:#FFF;">
                    Generar Recibo de Producto
                </button>
            </form>

            <!-- Formulario Tratamiento -->
            <!-- Formulario -->
            <form class="hidden" data-type="tratamiento" method="POST"
                action="{{ route('panel.receipts.storeTreatment') }}"> @csrf
                <input type="hidden" name="invoice_type" value="tratamiento">


                <div class="row">
                    <input type="hidden" name="lead_id" value="">
                    <label class="block mb-1 font-semibold" style="color:#CDAF95;">Buscar paciente *</label>
                    <input type="text" name="patient_name"
                        class="autocomplete-input w-full border rounded px-2 py-1 mb-2" required>
                    <ul class="autocomplete-list absolute w-full mt-1 rounded shadow-lg z-50 hidden"
                        style="background-color:#C8BAAF; color:#1C6C73; max-height:150px; overflow:auto;"></ul>

                    <div class="col-md-3 mb-2">
                        <label>Fecha de Aplicación</label>
                        <input type="date" name="receipt_date" id="receipt_date" class="form-control"
                            value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label>Clínica</label>
                        <select name="clinic" class="form-control" required>
                            <option value="" selected disabled>Selecciona</option>
                            <option value="Pedregal">Pedregal</option>
                            <option value="Queretaro">Queretaro</option>
                            <option value="Santa Fe">Santa Fe</option>
                        </select>
                    </div>
                </div>

                <hr>

                {{-- Carrito de productos --}}
                <h5>Agregar Tratamiento o Producto</h5>
                <div class="row">
                    <div class="col-md-3">
                        <label>Tipo</label>
                        <select id="productType" class="form-control">
                            <option value="Tratamiento">Tratamiento</option>
                            <option value="Producto">Producto</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Producto</label>
                        <select id="productName" class="form-control"></select>
                    </div>
                    <div class="col-md-2">
                        <label>Cantidad</label>
                        <input type="number" id="productQty" class="form-control" value="1" min="1">
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-success btn-block"
                            onclick="addToCart()">Agregar</button>
                    </div>
                </div>

                <table class="table mt-3">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="cartBody"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right"><strong>Total:</strong></td>
                            <td colspan="2"><strong id="total_amount">$0.00</strong></td>
                        </tr>
                    </tfoot>
                </table>

                {{-- Métodos de pago --}}
                <h5>Método(s) de pago</h5>
                <div class="row">
                    <div class="col-md-4">
                        <label>Método 1</label>
                        <select name="payment_method_1" class="form-control" required>
                            <option selected disabled value="">Selecciona</option>
                            <option value="Efectivo">Efectivo</option>
                            <option value="Transferencia">Transferencia</option>
                            <option value="Tarjeta_de_Débito">Tarjeta de Débito</option>
                            <option value="Tarjeta_de_Credito">Tarjeta de Credito</option>
                            <option value="Dólares">Dólares</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Pago 1</label>
                        <input type="number" id="payment1" name="payment_amount_1" class="form-control"
                            step="0.01" value="0" min="0">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="useSecondPayment">
                            <label class="form-check-label" for="useSecondPayment">Usar segunda forma de pago</label>
                        </div>
                    </div>
                </div>

                {{-- Segunda forma de pago --}}
                <div id="secondPaymentSection" class="row mt-2" style="display: none;">
                    <div class="col-md-4">
                        <label>Método 2</label>
                        <select name="payment_method_2" class="form-control">
                            <option selected disabled value="">Selecciona</option>
                            <option value="Efectivo">Efectivo</option>
                            <option value="Transferencia">Transferencia</option>
                            <option value="Tarjeta_de_Débito">Tarjeta de Débito</option>
                            <option value="Tarjeta_de_Credito">Tarjeta de Credito</option>
                            <option value="Dólares">Dólares</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Pago 2</label>
                        <input type="number" id="payment2" name="payment_amount_2" class="form-control"
                            step="0.01" value="0" min="0">
                    </div>
                </div>

                <div class="col-md-4 mt-3">
                    <label>Monto Restante</label>
                    <input type="text" id="remaining_amount" class="form-control" readonly value="$0.00">
                </div>

                <button type="submit" class="btn btn-primary mt-3">Guardar Aplicación</button>
            </form>

        </div>
        <button type="button" @click="reciboOpen=false"
            class="px-4 py-2 rounded border border-gray-400 hover:bg-gray-200 font-semibold">
            Cancelar
        </button>
    </div>
</div>

<!-- Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const searchUrl = "{{ route('panel.receipts.search') }}";
        const typeSelect = document.getElementById('type_receipt');
        const formSections = document.getElementById('form_sections');

        // =============================
        // Mostrar el formulario según tipo
        // =============================
        typeSelect.addEventListener('change', function() {
            const selected = this.value;
            formSections.querySelectorAll('form').forEach(f => f.classList.add('hidden'));
            const form = formSections.querySelector(`form[data-type="${selected}"]`);
            if (form) form.classList.remove('hidden');
        });

        // =============================
        // Función genérica para autocomplete
        // =============================
        function setupAutocomplete(input) {
            const list = input.nextElementSibling;

            input.addEventListener('input', function() {
                const q = this.value.trim();
                if (q.length < 2) return list.classList.add('hidden');

                fetch(`${searchUrl}?q=${encodeURIComponent(q)}`)
                    .then(res => res.json())
                    .then(data => {
                        list.innerHTML = '';
                        if (!data.length) return list.classList.add('hidden');

                        data.forEach(p => {
                            const li = document.createElement('li');
                            li.textContent = `${p.num_med_record ?? 'NA'} - ${p.name}`;
                            li.dataset.id = p.id;
                            li.dataset.name = p.name;
                            li.dataset.costo = p.costo ?? 0;
                            li.dataset.restante = p.restante ?? 0;
                            li.classList.add('px-3', 'py-2', 'cursor-pointer');
                            li.style.backgroundColor = "#C8BAAF";
                            li.style.color = "#1C6C73";

                            li.addEventListener('click', function() {
                                input.value = this.dataset.name;
                                input.form.querySelector('input[name="lead_id"]')
                                    .value = this.dataset.id;

                                const totalInput = input.form.querySelector(
                                    'input[name="total_amount"]');
                                const restanteInput = input.form.querySelector(
                                    'input[name="amount"]');
                                if (totalInput) totalInput.value = this.dataset
                                    .costo;
                                if (restanteInput) restanteInput.value = this
                                    .dataset.restante;

                                list.classList.add('hidden');
                            });

                            list.appendChild(li);
                        });

                        list.classList.remove('hidden');
                    })
                    .catch(() => list.classList.add('hidden'));
            });

            // Cerrar lista al hacer click fuera
            document.addEventListener('click', e => {
                if (!input.contains(e.target) && !list.contains(e.target)) {
                    list.classList.add('hidden');
                }
            });
        }

        // Inicializar autocomplete en todos los inputs
        document.querySelectorAll('.autocomplete-input').forEach(setupAutocomplete);

        // =============================
        // Mostrar campos de dólares
        // =============================
        function setupDollarFields(select) {
            const div = select.form.querySelector('.amount-dls-div');
            select.addEventListener('change', () => {
                if (select.value === 'Dólares') div.classList.remove('hidden');
                else div.classList.add('hidden');
            });
        }

        document.querySelectorAll('select[name="method"]').forEach(setupDollarFields);

    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mostrar campos dólares si el método es Dólares
        document.querySelectorAll('select[name="method"]').forEach(select => {
            select.addEventListener('change', () => {
                const div = select.form.querySelector('.amount-dls-div');
                if (select.value === 'Dólares') div.classList.remove('hidden');
                else div.classList.add('hidden');
            });
        });

        // Autocomplete genérico
        function setupAutocomplete(input) {
            const list = input.nextElementSibling;
            const searchUrl = "{{ route('panel.receipts.search') }}";

            input.addEventListener('input', function() {
                const q = this.value.trim();
                if (q.length < 2) return list.classList.add('hidden');

                fetch(`${searchUrl}?q=${encodeURIComponent(q)}`)
                    .then(res => res.json())
                    .then(data => {
                        list.innerHTML = '';
                        if (!data.length) return list.classList.add('hidden');

                        data.forEach(p => {
                            const li = document.createElement('li');
                            li.textContent = `${p.num_med_record ?? 'NA'} - ${p.name}`;
                            li.dataset.id = p.id;
                            li.dataset.name = p.name;
                            li.classList.add('px-3', 'py-2', 'cursor-pointer');
                            li.style.backgroundColor = "#C8BAAF";
                            li.style.color = "#1C6C73";
                            li.addEventListener('click', () => {
                                input.value = li.dataset.name;
                                input.form.querySelector('input[name="lead_id"]')
                                    .value = li.dataset.id;
                                list.classList.add('hidden');
                            });
                            list.appendChild(li);
                        });

                        list.classList.remove('hidden');
                    })
                    .catch(() => list.classList.add('hidden'));
            });

            document.addEventListener('click', e => {
                if (!input.contains(e.target) && !list.contains(e.target)) list.classList.add('hidden');
            });
        }

        document.querySelectorAll('.autocomplete-input').forEach(setupAutocomplete);
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('#receiptForm'); // Asegúrate de tener id="receiptForm" en tu form
        const pdfModal = document.getElementById('pdfModal');
        const pdfFrame = document.getElementById('pdfFrame');
        const closeBtn = document.getElementById('closePdfModal');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);

            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        pdfFrame.src = data.pdf_url; // cargar PDF en iframe
                        pdfModal.classList.remove('hidden'); // mostrar modal
                    } else {
                        alert('Error al generar recibo');
                    }
                })
                .catch(err => console.error(err));
        });

        // Cerrar modal
        closeBtn.addEventListener('click', function() {
            pdfModal.classList.add('hidden');
            pdfFrame.src = '';
        });
    });
</script>
<!-- Modal PDF -->
<div id="pdfModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl p-4 relative">
        <button id="closePdfModal" class="absolute top-2 right-2 text-xl font-bold">&times;</button>
        <iframe id="pdfFrame" src="" class="w-full h-[80vh] border rounded"></iframe>
    </div>
</div>
