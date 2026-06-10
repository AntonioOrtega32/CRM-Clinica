<div x-show="viewModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">

        <template x-for="empleado in empleados" :key="empleado.id">
            <div x-show="viewModal == empleado.id" class="flex flex-col items-center text-center">

                <!-- Foto del empleado -->
                <div class="w-32 h-32 mb-4">
                    <template x-if="empleado.foto">
                        <img :src="'{{ asset('') }}' + empleado.foto" alt="Foto Empleado" class="w-32 h-32 rounded-full object-cover mx-auto shadow-lg">
                    </template>
                    <template x-if="!empleado.foto">
                        <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center text-gray-400">
                            Sin foto
                        </div>
                    </template>
                </div>

                <!-- Nombre y puesto -->
                <h2 class="text-2xl font-bold text-gray-700 mb-1" x-text="empleado.nombre + ' ' + empleado.apellido"></h2>
                <p class="text-gray-500 mb-4" x-text="empleado.puesto"></p>

                <!-- Información general -->
                <div class="w-full text-left">
                    <div class="mb-2"><strong>Departamento:</strong> <span x-text="empleado.departamento || 'No asignado'"></span></div>
                    <div class="mb-2"><strong>Teléfono:</strong> <span x-text="empleado.telefono || 'No registrado'"></span></div>
                    <div class="mb-2"><strong>Fecha de ingreso:</strong> <span x-text="empleado.fecha_ingreso || 'No registrada'"></span></div>
                    <div class="mb-2"><strong>Fecha de nacimiento:</strong> <span x-text="empleado.fecha_nacimiento || 'No registrada'"></span></div>
                    <div class="mb-2"><strong>Usuario CRM:</strong> <span x-text="empleado.user ? empleado.user.name : 'No asignado'"></span></div>
                    <div class="mb-2"><strong>Estatus:</strong> <span x-text="empleado.estatus"></span></div>
                </div>

                <hr class="my-4 w-full">

                <!-- Contactos de emergencia -->
                <div class="w-full text-left">
                    <h3 class="font-semibold text-gray-700 mb-2">Contactos de emergencia</h3>
                    <div class="mb-1"><strong>Emergencia 1:</strong> <span x-text="empleado.emergencia_nombre || 'N/A'"></span> (<span x-text="empleado.emergencia_telefono || 'N/A'"></span>)</div>
                    <div class="mb-1"><strong>Emergencia 2:</strong> <span x-text="empleado.emergencia2_nombre || 'N/A'"></span> (<span x-text="empleado.emergencia2_telefono || 'N/A'"></span>)</div>
                </div>

                <hr class="my-4 w-full">

                <!-- Notas -->
                <div class="w-full text-left">
                    <h3 class="font-semibold text-gray-700 mb-2">Notas</h3>
                    <p class="text-gray-600" x-text="empleado.notas || 'Sin notas'"></p>
                </div>

                <!-- Botón de cerrar -->
                <div class="flex justify-center mt-6">
                    <button @click="viewModal = null" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Cerrar</button>
                </div>

            </div>
        </template>

    </div>
</div>
    