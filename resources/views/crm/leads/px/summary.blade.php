{{-- resources/views/leads/px/summary.blade.php --}}
<div class="space-y-6">

<h4 class="font-[Poppins] text-[#1C6C73] m-2">
    Asiganación de cotización
</h4>

<h6 class="font-[Poppins] text-[#1C6C73] m-2">
    Al asignar una cotización al Lead este pasara a ser automáticamente un Cliente.
</h6>
    <form id="update_summary"
      action="{{ route('panel.leads.summary.update', $lead->id) }}"
      method="POST" class="space-y-6" enctype="multipart/form-data">
        @csrf
    @method('PATCH')
        <input type="hidden" name="Id" value="{{ $lead->id ?? '' }}">

      {{-- Cotización --}}
        <div>
            <h4 class="font-semibold text-[#1C6C73] mb-2">Cotización del Procedimiento (MXN)</h4>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Costo efectivo / débito</label>
                    <input type="number" step="0.01" name="quoted_cash_amount" id="i_quoted_cash_amount"
                           value="{{ $quote->quoted_cash_amount ?? '' }}"
                          
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Costo tarjeta de crédito</label>
                    <input type="number" step="0.01" name="quoted_cc_amount" id="i_quoted_cc_amount"
                           value="{{ $quote->quoted_cc_amount ?? '' }}"
                          
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">¿Incluye meses?</label>
                    <input type="text" name="installments" id="i_installments"
                           value="{{ $quote->installments ?? '' }}"
                         
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>
        </div>

        {{-- BOTONES --}}
        <div class="flex justify-end">
            
                <button type="submit" class="px-4 py-2 rounded bg-[#1C6C73] text-white hover:bg-[#155357]">Guardar cambios</button>
      
        </div>

        {{-- Información del lead (En desuso)--}}
        @if (false)
            
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Nombres --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Nombre(s)</label>
                <input type="text"
                       name="first_name"
                       id="i_first_name"
                       value="{{ $lead->first_name ?? '' }}"
                       @if($isClient) disabled @endif
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-1 focus:ring-[#1C6C73] focus:border-[#1C6C73]">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Apellido(s)</label>
                <input type="text"
                       name="last_name"
                       id="i_last_name"
                       value="{{ $lead->last_name ?? '' }}"
                       @if($isClient) disabled @endif
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-1 focus:ring-[#1C6C73] focus:border-[#1C6C73]">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Género</label>
                <div class="mt-1 flex items-center gap-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="gender" id="g_Hombre" value="Hombre"
                               {{ (isset($lead->gender) && $lead->gender === 'Hombre') ? 'checked' : '' }}
                               @if($isClient) disabled @endif
                               class="form-radio">
                        <span class="ml-2 text-sm">Hombre</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="gender" id="g_Mujer" value="Mujer"
                               {{ (isset($lead->gender) && $lead->gender === 'Mujer') ? 'checked' : '' }}
                               @if($isClient) disabled @endif
                               class="form-radio">
                        <span class="ml-2 text-sm">Mujer</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Clínica</label>
                <select name="clinic" id="i_clinic" @if($isClient) disabled @endif
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Selecciona</option>
                    <option value="Santa Fe" {{ (isset($lead->clinic) && $lead->clinic == 'Santa Fe') ? 'selected' : '' }}>Santa Fe</option>
                    <option value="Pedregal" {{ (isset($lead->clinic) && $lead->clinic == 'Pedregal') ? 'selected' : '' }}>Pedregal</option>
                    <option value="Queretaro" {{ (isset($lead->clinic) && $lead->clinic == 'Queretaro') ? 'selected' : '' }}>Querétaro</option>
                    <option value="CDMX" {{ (isset($lead->clinic) && $lead->clinic == 'CDMX') ? 'selected' : '' }}>CDMX</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Teléfono (principal)</label>
                <input type="text" name="phone_1" id="i_phone_1"
                       value="{{ $lead->phone ?? '' }}"
                       @if($isClient) disabled @endif
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Número de expediente</label>
                <input type="text" readonly value="{{ $numExp ?? 'Sin asignar' }}"
                       class="mt-1 block w-full rounded-md border-gray-200 bg-gray-50 text-gray-700">
            </div>
        </div>
 
        {{-- Información de la valoración --}}
        <div>
            <h4 class="font-semibold text-[#1C6C73] mb-2">Información de la valoración</h4>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha de valoración</label>
                    <input type="date" name="assessment_date" id="i_assessment_date"
                           value="{{ isset($lead->procedure_date) ? \Carbon\Carbon::parse($lead->procedure_date)->format('Y-m-d') : '' }}"
                           @if($isClient) disabled @endif
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">¿Por qué medio nos conoció?</label>
                    <input type="text" name="first_meet_type" id="i_first_meet_type"
                           value="{{ $lead->first_meet_type ?? '' }}"
                           @if($isClient) disabled @endif
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo de valoración</label>
                    <select name="assessment" id="i_assessment_type" @if($isClient) disabled @endif
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="" disabled {{ empty($lead->assessment) ? 'selected' : '' }}>Selecciona</option>
                        <option value="Virtual" {{ (isset($lead->assessment) && $lead->assessment == 'Virtual') ? 'selected' : '' }}>Sólo Virtual</option>
                        <option value="Presencial" {{ (isset($lead->assessment) && $lead->assessment == 'Presencial') ? 'selected' : '' }}>Sólo Presencial</option>
                        <option value="Virtual y después presencial" {{ (isset($lead->assessment) && $lead->assessment == 'Virtual y después presencial') ? 'selected' : '' }}>Virtual y después presencial</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Información del procedimiento --}}
        <div>
            <h4 class="font-semibold text-[#1C6C73] mb-2">Información del procedimiento</h4>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha del procedimiento</label>
                    <input type="date" name="procedure_date" id="i_procedure_date"
                           value="{{ isset($lead->procedure_date) ? \Carbon\Carbon::parse($lead->procedure_date)->format('Y-m-d') : '' }}"
                           @if($isClient) disabled @endif
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo de injerto</label>
                    <select name="procedure_type" id="i_procedure_type" @if($isClient) disabled @endif
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Selecciona</option>
                        <option value="Capilar" {{ (isset($lead->procedure_type) && $lead->assessment_type == 'Capilar') ? 'selected' : '' }}>Capilar</option>
                        <option value="Barba" {{ (isset($lead->procedure_type) && $lead->assessment_type == 'Barba') ? 'selected' : '' }}>Barba</option>
                        <option value="Ambos" {{ (isset($lead->procedure_type) && $lead->assessment_type == 'Ambos') ? 'selected' : '' }}>Ambos</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">¿Qué se le ofreció?</label>
                    <textarea name="purpose" id="i_purpose" rows="3"
                              @if($isClient) disabled @endif
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ $lead->notes ?? '' }}</textarea>
                </div>
            </div>
        </div>
        @endif
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('update_summary');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });

      const data = await res.json();
      if (!res.ok || !data.success) throw data;

      Swal.fire('Correcto', data.message || 'Actualizado', 'success');
    } catch (err) {
      Swal.fire('Error', err?.message || 'Contacta al Administrador', 'error');
    }
  });
});
</script>
