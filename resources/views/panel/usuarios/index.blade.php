@extends('panel.layouts.panel')

@section('title', 'Usuarios')

@section('content')
    <div x-data="{ editModal: null, viewModal: null, deleteModal: null }" class="p-6 bg-white shadow rounded-lg">

        <div class="flex justify-between items-center mb-4">
            <h1 class="text-xl font-bold text-gray-700">Usuarios</h1>
            <a href="{{ route('panel.usuarios.create') }}"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                Crear Usuario
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table id="usersTable" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-6 py-4">{{ $user->id }}</td>
                            <td class="px-6 py-4">{{ $user->name }}</td>
                            <td class="px-6 py-4">{{ $user->email }}</td>
                            <td class="px-6 py-4">{{ $user->roles->pluck('name')->first() ?? '-' }}</td>
                            <td class="px-6 py-4 flex gap-2">

                                                    @role('super_usuario')
                            <a href="{{ route('panel.usuarios.show', $user->id) }}"
                            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                            Ver
                            </a>
                        @endrole

                                <!-- Editar -->
                                <button type="button"
                                    onclick="document.getElementById('editModal-{{ $user->id }}').classList.remove('hidden')"
                                    class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                    Editar
                                </button>

                                <!-- Eliminar -->
                                <button @click="deleteModal = {{ $user->id }}"
                                    class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                                    Eliminar
                                </button>

                            </td>
                        </tr>

                        {{-- Modal Ver --}}
                        <div x-show="viewModal == {{ $user->id }}" x-cloak
                            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                            <div class="bg-white rounded-2xl shadow-md w-full max-w-md p-6">
                                <h2 class="text-xl font-bold mb-4">Usuario: {{ $user->name }}</h2>
                                <p><strong>Email:</strong> {{ $user->email }}</p>
                                <p><strong>Rol:</strong> {{ $user->roles->pluck('name')->first() ?? '-' }}</p>
                                <div class="flex justify-end mt-4">
                                    <button @click="viewModal = null"
                                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cerrar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Botón para abrir modal -->


                        <!-- Editar Usuario Modal -->
                        <div id="editModal-{{ $user->id }}"
                            class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                            <div class="bg-white rounded-2xl shadow-md w-full max-w-md p-6">
                                <h2 class="text-xl font-bold mb-4 border-b pb-2 text-gray-800">Editar Usuario</h2>

                                <form action="{{ route('panel.usuarios.update', $user->id) }}" method="POST"
                                    class="space-y-4">
                                    @csrf
                                    @method('PUT') <!-- Muy importante para que funcione PUT -->

                                    <!-- Nombre -->
                                    <div>
                                        <label class="block mb-1 font-semibold text-gray-700">Nombre</label>
                                        <input type="text" name="name" value="{{ $user->name }}"
                                            class="w-full p-2 border rounded focus:ring-2 focus:ring-[#FF3E0D] focus:border-[#FF3E0D]">
                                    </div>

                                    <!-- Email -->
                                    <div>
                                        <label class="block mb-1 font-semibold text-gray-700">Email</label>
                                        <input type="email" name="email" value="{{ $user->email }}"
                                            class="w-full p-2 border rounded focus:ring-2 focus:ring-[#FF3E0D] focus:border-[#FF3E0D]">
                                    </div>

                                    <!-- Contraseña (opcional) -->
                                    <div>
                                        <label class="block mb-1 font-semibold text-gray-700">Contraseña (opcional)</label>
                                        <input type="password" name="password"
                                            class="w-full p-2 border rounded focus:ring-2 focus:ring-[#FF3E0D] focus:border-[#FF3E0D]">
                                    </div>

                                    <!-- Confirmar Contraseña -->
                                    <div>
                                        <label class="block mb-1 font-semibold text-gray-700">Confirmar Contraseña</label>
                                        <input type="password" name="password_confirmation"
                                            class="w-full p-2 border rounded focus:ring-2 focus:ring-[#FF3E0D] focus:border-[#FF3E0D]">
                                    </div>

                                    <!-- Rol -->
                                    <div>
                                        <label class="block mb-1 font-semibold text-gray-700">Rol</label>
                                        <select name="role"
                                            class="w-full p-2 border rounded focus:ring-2 focus:ring-[#FF3E0D] focus:border-[#FF3E0D]">
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->name }}"
                                                    {{ $user->roles->pluck('name')->first() == $role->name ? 'selected' : '' }}>
                                                    {{ ucfirst($role->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Botones -->
                                    <div class="flex justify-end gap-2 mt-4">
                                        <button type="button"
                                            onclick="document.getElementById('editModal-{{ $user->id }}').classList.add('hidden')"
                                            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancelar</button>
                                        <button type="submit"
                                            class="px-4 py-2 bg-[#FF3E0D] text-white rounded hover:bg-[#e63700]">Guardar</button>
                                    </div>
                                </form>
                            </div>
                        </div>


                        {{-- Modal Eliminar --}}
                        <div x-show="deleteModal == {{ $user->id }}" x-cloak
                            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                            <div class="bg-white rounded-2xl shadow-md w-full max-w-sm p-6">
                                <h2 class="text-xl font-bold mb-4">Eliminar Usuario</h2>
                                <p>¿Estás seguro de eliminar a <strong>{{ $user->name }}</strong>?</p>
                                <div class="flex justify-end gap-2 mt-4">
                                    <form action="{{ route('panel.usuarios.destroy', $user->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="px-4 py-2 bg-red-500 text-white rounded">Eliminar</button>
                                    </form>
                                    <button @click="deleteModal = null"
                                        class="px-4 py-2 bg-gray-300 rounded">Cancelar</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Inicializar DataTables
        document.addEventListener('DOMContentLoaded', function() {
            $('#usersTable').DataTable({
               responsive: true,
      scrollX: true,
      autoWidth: true,
      buttons: [
        { extend: 'excel', 
            text: 'Excel',
            className: 'bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg',
            exportOptions: {
              columns: [0,1,2,3]
            }  
        },
        { extend: 'pdf', 
            text: 'PDF', 
            className: 'bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg', 
            exportOptions: {
              columns: [0,1,2,3]
            }          
        }
      ],
      dom: 'Bfrtip',
      language: {
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        infoEmpty: "Mostrando 0 a 0 de 0 registros",
        lengthMenu: "Mostrar _MENU_ registros",
        search: "Buscar:",
        loadingRecords: "Loading...",
        processing: "Procesando...",
        zeroRecords: "No hay registros aún",
        paginate: {
          next: '→',
          previous: '←',
          first: 'Inicio',
          last: 'Ultimo'
        },
      },
    });
        });
    </script>
@endsection
