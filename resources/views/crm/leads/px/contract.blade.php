<div class="space-y-6">

    <div class="bg-gray-50 p-4 rounded border">
        <h3 class="text-lg font-semibold text-[#1C6C73] mb-3">
            Contrato
        </h3>

        <form method="POST"
              action="{{ route('panel.leads.contrato.generar') }}"
              target="_blank"
              class="space-y-4">

            @csrf

            <input type="hidden" name="lead_id" value="{{ $lead->id }}">

            <div>
                <label class="block text-sm">Nombre del paciente</label>
                @if ($lead->full_name == null)
                <input type="text" name="nombre" class="w-full border rounded px-3 py-2" required>    
                @else
                <input type="text" name="nombre" class="w-full border rounded px-3 py-2"
                       value="{{ $lead->full_name }}" placeholder="{{ $lead->full_name }}" required readonly>
                @endif
            </div>

            <div>
                <label class="block text-sm">Fecha</label>
                <input type="date" name="fecha" class="w-full border rounded px-3 py-2"
                       value="{{ now()->toDateString() }}" required>
            </div>

            <div>
                <label class="block text-sm">Monto total</label>
                <input type="number" step="0.01" name="monto" class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block text-sm">Anticipo</label>
                <input type="number" step="0.01" name="anticipo" class="w-full border rounded px-3 py-2" required>
            </div>

            <button class="px-4 py-2 bg-[#1C6C73] text-white rounded">
                Generar contrato
            </button>

        </form>
    </div>

</div>
