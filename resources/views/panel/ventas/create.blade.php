@extends('panel.layouts.panel')

@section('title', 'Nuevo Lead')

@section('content')

@if (session('success'))
    <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-green-800">
        <strong>✅ Éxito:</strong> {{ session('success') }}
    </div>
@endif

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white shadow-lg rounded-2xl p-8">
        <h2 class="text-2xl font-bold text-verdeOscuro mb-6 text-center">Recolecta toda la información del nuevo lead</h2>

        <form method="POST" action="{{ route('panel.ventas.store') }}" id="new_lead" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

                {{-- Nombre --}}
                <div>
                    <label class="block font-semibold text-gray-700">Nombre (s) <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required
                           class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-verdeOscuro focus:border-verdeOscuro">
                    @error('first_name') <small class="text-red-500">{{ $message }}</small> @enderror
                </div>

                {{-- Apellido --}}
                <div>
                    <label class="block font-semibold text-gray-700">Apellido (s)</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}"
                           class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-verdeOscuro focus:border-verdeOscuro">
                </div>

                {{-- Clínica --}}
                <div>
                    <label class="block font-semibold text-gray-700">Clínica <span class="text-red-500">*</span></label>
                    <select name="clinic" required
                            class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-verdeOscuro focus:border-verdeOscuro">
                        <option disabled selected>Selecciona</option>
                        <option value="CDMX">CDMX</option>
                        <option value="Queretaro">Querétaro</option>
                    </select>
                </div>

                {{-- Origen --}}
                <div>
                    <label class="block font-semibold text-gray-700">Origen <span class="text-red-500">*</span></label>
                    <select name="origin" required
                            class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-verdeOscuro focus:border-verdeOscuro">
                        <option value="" disabled selected>Selecciona</option>
                        <option value="Facebook">Facebook</option>
                        <option value="Instagram">Instagram</option>
                        <option value="Tiktok">Tiktok</option>
                        <option value="Google">Google</option>
                        <option value="Whatsapp">Whatsapp</option>
                        <option value="Referido">Referido</option>
                        <option value="Organico">Orgánico</option>
                        <option value="Recomendado">Recomendado</option>
                        <option value="Pagina">Página</option>
                        <option value="Px">Ya es px</option>
                        <option value="Campaña">Campaña publicitaria</option>
                        <option value="Otro">Otro</option>
                        <option value="Desconocido">Desconocido</option>
                    </select>
                </div>

                {{-- Teléfono --}}
                <div>
                    <label class="block font-semibold text-gray-700">Teléfono Celular <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                           class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-verdeOscuro focus:border-verdeOscuro">
                </div>

                {{-- Interesado en --}}
                <div>
                    <label class="block font-semibold text-gray-700">Interesado en <span class="text-red-500">*</span></label>
                    <select name="interested_in" required
                            class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-verdeOscuro focus:border-verdeOscuro">
                        <option value="" disabled selected>Selecciona</option>
                        <option value="Capilar">Injerto Capilar</option>
                        <option value="Barba">Injerto Barba</option>
                        <option value="Ambos">Ambos Injertos</option>
                        <option value="Tratamientos">Tratamientos</option>
                        <option value="Micro">Micro</option>
                        <option value="Retoque">Retoque</option>
                    </select>
                </div>

                {{-- Etapa del prospecto --}}
                <div>
                    <label class="block font-semibold text-gray-700">Etapa del Prospecto <span class="text-red-500">*</span></label>
                    <select name="stage" required
                            class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-verdeOscuro focus:border-verdeOscuro">
                        <option value="" disabled selected>Selecciona</option>
                        <option value="Nuevo Lead">Nuevo Lead</option>
                        <option value="En prospección">En prospección</option>
                        <option value="Interesado">Interesado</option>
                        <option value="Agendó valoración">Agendó valoración</option>
                        <option value="Valorado">Valorado</option>
                    </select>
                </div>

                {{-- Calificación --}}
                <div>
                    <label class="block font-semibold text-gray-700">Calificación <span class="text-red-500">*</span></label>
                    <select name="quali" required
                            class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-verdeOscuro focus:border-verdeOscuro">
                        <option value="" disabled selected>Selecciona la Etapa</option>
                        <option value="En conversación">En conversación</option>
                        <option value="Descartado">Descartado</option>
                        <option value="Inactivo">Inactivo</option>
                        <option value="Interesado">Interesado</option>
                        <option value="Seguimiento">Seguimiento</option>
                        <option value="En negociación">En negociación</option>
                    </select>
                </div>

                {{-- Propietaria --}}
                <div>
                    <label class="block font-semibold text-gray-700">Propietaria (o) <span class="text-red-500">*</span></label>
                    <select name="seller" required
                            class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-verdeOscuro focus:border-verdeOscuro">
                        <option value="" disabled selected>Selecciona</option>
                        <option value="Janeth Ruíz">Janeth Ruíz</option>
                        <option value="Marisol Olmos">Marisol Olmos</option>
                        <option value="Paola Segura">Paola Segura</option>
                        <option value="Dr. Alejandro Santana">Dr. Alejandro Santana</option>
                        <option value="Dra. Lizbeth Carmona">Dra. Lizbeth Carmona</option>
                        <option value="Dra. Amairani Romero">Dra. Amairani Romero</option>
                        <option value="Dra. Oriana Aguilar">Dra. Oriana Aguilar</option>
                        <option value="Dra. Monserrat Mata">Dra. Monserrat Mata</option>
                    </select>
                </div>


                {{-- Link Respond --}}
                <div class="col-span-1 md:col-span-2 xl:col-span-3">
                    <label class="block font-semibold text-gray-700">Link de Respond (si aplica)</label>
                    <input type="text" name="link" value="{{ old('link') }}"
                           class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-verdeOscuro focus:border-verdeOscuro">
                </div>

                {{-- Notas --}}
                <div class="col-span-1 md:col-span-2 xl:col-span-3">
                    <label class="block font-semibold text-gray-700">Notas</label>
                    <textarea name="notes" rows="5"
                              class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-verdeOscuro focus:border-verdeOscuro">{{ old('notes') }}</textarea>
                </div>

                {{-- Botón --}}
                <div class="col-span-1 md:col-span-2 xl:col-span-3 flex justify-end">
                    <button type="submit" class="bg-[#1C6C73] text-white font-semibold px-6 py-2 rounded-md hover:bg-verdeOscuro/80">Agregar nuevo lead</button>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection
