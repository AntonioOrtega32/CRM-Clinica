<!-- Modal Eliminar Empleado -->
<div x-show="deleteModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white rounded-2xl shadow-md w-full max-w-md p-6">
        <h2 class="text-xl font-bold mb-4">Confirmar Eliminación</h2>

        <p>¿Estás seguro de que deseas eliminar al empleado <strong
                x-text="empleados.find(e => e.id == deleteModal)?.nombre + ' ' + empleados.find(e => e.id == deleteModal)?.apellido"></strong>?
        </p>

        <div class="flex justify-end mt-6 gap-2">
            <button @click="deleteModal = null" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancelar</button>

       <form :action="`/panel/panel/empleados/${deleteModal}/destroy`" method="POST">
    @csrf
    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Eliminar</button>
</form>

        </div>
    </div>
</div>

<script>
    // Cerrar modal al presionar ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modal = document.querySelector('[x-data]');
            if (modal) {
                modal.__x.$data.deleteModal = null;
                modal.__x.$data.editModal = null;
                modal.__x.$data.viewModal = null;
            }
        }
    });
</script>
