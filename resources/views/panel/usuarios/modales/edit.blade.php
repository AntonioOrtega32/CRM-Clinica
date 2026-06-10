<div x-show="editModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white rounded-2xl shadow-md w-full max-w-2xl p-6 overflow-y-auto max-h-[90vh]">
        <h2 class="text-xl font-bold mb-4" x-text="editModal==='create' ? 'Agregar Empleado' : 'Editar Empleado'"></h2>

        <form
            x-bind:action="editModal==='create'
                ? '{{ route('panel.empleados.store') }}'
                : '{{ route('panel.empleados.update', ':id') }}'.replace(':id', editModal)"
            method="POST"
            enctype="multipart/form-data"
        >
            @csrf
            <template x-if="editModal !== 'create'">
                <input type="hidden" name="_method" value="PUT">
            </template>

            {{-- Nombre y Apellido --}}
            <div class="grid grid-cols-2 gap-4 mb-3">
                <div>
                    <label class="block mb-1">Nombre</label>
                    <input type="text" name="nombre" x-model="empleadoActual.nombre" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block mb-1">Apellido</label>
                    <input type="text" name="apellido" x-model="empleadoActual.apellido" class="w-full p-2 border rounded">
                </div>
            </div>
    <div>
        <label class="block mb-1">Clínica</label>
        <select name="clinica" x-model="empleadoActual.clinica" class="w-full p-2 border rounded">
            <option value="">Selecciona clínica</option>
            <option value="Santa Fe">Santa Fe</option>
            <option value="Queretaro">Queretaro</option>
            <option value="Pedregal">Pedregal</option> <!-- Nueva clínica agregada -->
        </select>
    </div>
            {{-- Puesto y Departamento --}}
            <div class="grid grid-cols-2 gap-4 mb-3">
                <div>
                    <label class="block mb-1">Puesto</label>
                    <input type="text" name="puesto" x-model="empleadoActual.puesto" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block mb-1">Departamento</label>
                    <input type="text" name="departamento" x-model="empleadoActual.departamento" class="w-full p-2 border rounded">
                </div>
            </div>

            {{-- Usuario CRM y Estatus --}}
            <div class="grid grid-cols-2 gap-4 mb-3">
                <div>
                    <label class="block mb-1">Usuario CRM</label>
                    <select name="user_id" x-model="empleadoActual.user_id" class="w-full p-2 border rounded">
                        <option value="">No asignado</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-1">Estatus</label>
                    <select name="estatus" x-model="empleadoActual.estatus" class="w-full p-2 border rounded">
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>
            </div>

            {{-- Fechas y Teléfono --}}
            <div class="grid grid-cols-2 gap-4 mb-3">
                <div>
                    <label class="block mb-1">Fecha de ingreso</label>
                    <input type="date" name="fecha_ingreso" x-model="empleadoActual.fecha_ingreso" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block mb-1">Teléfono</label>
                    <input type="text" name="telefono" x-model="empleadoActual.telefono" class="w-full p-2 border rounded">
                </div>
            </div>

            {{-- Fecha de nacimiento e identificación --}}
            <div class="grid grid-cols-2 gap-4 mb-3">
                <div>
                    <label class="block mb-1">Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento" x-model="empleadoActual.fecha_nacimiento" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block mb-1">No. Seguro Social</label>
                    <input type="text" name="identificacion" x-model="empleadoActual.identificacion" class="w-full p-2 border rounded">
                </div>
            </div>

            {{-- Dirección --}}
            <div class="mb-3">
                <label class="block mb-1">Dirección</label>
                <textarea name="direccion" x-model="empleadoActual.direccion" class="w-full p-2 border rounded"></textarea>
            </div>

            {{-- Contactos de emergencia --}}
            <div class="grid grid-cols-2 gap-4 mb-3">
                <div>
                    <label class="block mb-1">Emergencia 1 - Nombre</label>
                    <input type="text" name="emergencia_nombre" x-model="empleadoActual.emergencia_nombre" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block mb-1">Emergencia 1 - Teléfono</label>
                    <input type="text" name="emergencia_telefono" x-model="empleadoActual.emergencia_telefono" class="w-full p-2 border rounded">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-3">
                <div>
                    <label class="block mb-1">Emergencia 2 - Nombre</label>
                    <input type="text" name="emergencia2_nombre" x-model="empleadoActual.emergencia2_nombre" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block mb-1">Emergencia 2 - Teléfono</label>
                    <input type="text" name="emergencia2_telefono" x-model="empleadoActual.emergencia2_telefono" class="w-full p-2 border rounded">
                </div>
            </div>

            {{-- Notas --}}
            <div class="mb-3">
                <label class="block mb-1">Notas</label>
                <textarea name="notas" x-model="empleadoActual.notas" class="w-full p-2 border rounded"></textarea>
            </div>

            {{-- Foto --}}
            <div class="mb-3">
                <label class="block mb-1">Foto</label>
                <input type="file" name="foto" class="w-full p-2 border rounded"
                       @change="fotoPreview = URL.createObjectURL($event.target.files[0])">
                <template x-if="fotoPreview">
                    <img :src="fotoPreview" class="w-24 h-24 rounded-full mt-2">
                </template>
            </div>

            {{-- Botones --}}
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" @click="editModal = null" class="px-4 py-2 bg-gray-300 rounded">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Guardar</button>
            </div>
        </form>
    </div>
</div>
