<div class="bg-white p-4 rounded shadow">

    {{-- Saldo pendiente --}}
    <div class="mb-4">
        <h2 class="text-xl font-semibold text-red-600">
            Pendiente: ${{ number_format($pendingAmount, 2) }}
        </h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- HISTORIAL DE PAGOS --}}
        <div>
            <h3 class="text-lg font-semibold mb-2 text-[#1C6C73]">Historial de Pagos</h3>

            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="py-2 text-left">Fecha</th>
                        <th class="py-2 text-left">Tipo</th>
                        <th class="py-2 text-left">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                        <tr class="border-b">
                            <td class="py-2">
                                {{ \Carbon\Carbon::parse($p->payment_date)->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-2">
                                {{ ucfirst($p->type) }}
                            </td>
                            <td class="py-2">
                                ${{ number_format($p->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-3 text-center text-gray-500">
                                No hay pagos registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- COTIZACIÓN --}}
        <div>
            <h3 class="text-lg font-semibold mb-2 text-[#1C6C73]">Cotización</h3>

            @if($quote)
                <div class="space-y-2 text-sm">
                    <p class="text-gray-700">
                        Cotización en Tarjeta:
                        <strong>${{ number_format($quote->quoted_cc_amount, 2) }}</strong>
                    </p>

                    <p class="text-gray-700">
                        Cotización en Efectivo:
                        <strong>${{ number_format($quote->quoted_cash_amount, 2) }}</strong>
                    </p>

                    <p class="text-gray-700">
                        ¿Se ofrecieron meses?:
                        <strong class="text-yellow-600">
                            {{ $quote->installments ?? 'No' }}
                        </strong>
                    </p>
                </div>
            @else
                <p class="text-sm text-gray-500">
                    No hay cotización registrada para este paciente.
                </p>
            @endif
        </div>

    </div>

</div>
