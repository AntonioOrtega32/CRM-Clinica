@extends('panel.layouts.panel')

@section('title', 'Crear Usuario')

@section('content')

<div class="max-w-xl mx-auto bg-white p-8 rounded-2xl shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">
        Crear Usuario
    </h2>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-100 px-4 py-3 text-green-800 text-sm font-medium">
            ✅ {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('panel.usuarios.store') }}" method="POST" class="space-y-5">
        @csrf

        <!-- Nombre -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre</label>
            <input type="text" name="name" value="{{ old('name') }}"
                   class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 text-gray-700">
            @error('name')
                <p class="mt-1 text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <!-- Correo -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Correo electrónico</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 text-gray-700">
            @error('email')
                <p class="mt-1 text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <!-- Contraseña -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Contraseña</label>
            <input type="password" name="password"
                   class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 text-gray-700">
            @error('password')
                <p class="mt-1 text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirmar -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Confirmar Contraseña</label>
            <input type="password" name="password_confirmation"
                   class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 text-gray-700">
        </div>

        <!-- Rol -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Rol</label>
            <select name="role"
                    class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3 text-gray-700">
                <option value="">Selecciona un rol</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>
            @error('role')
                <p class="mt-1 text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <!-- Botón -->
        <div class="pt-4">
            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg shadow-md transition-colors duration-300">
                Crear Usuario
            </button>
        </div>
    </form>
</div>
@endsection
