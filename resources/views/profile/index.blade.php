    @extends('panel.layouts.panel')

@section('title', 'Perfil de Usuario')

@section('content')
<div class="max-w-md mx-auto bg-white p-8 rounded-2xl shadow-md text-center">
    <!-- Avatar -->
    <div class="flex justify-center mb-6">
        <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-4xl">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-12 h-12">
                <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/>
            </svg>
        </div>
    </div>

    <!-- Información del usuario -->
    <h2 class="text-2xl font-bold mb-4">{{ auth()->user()->name }}</h2>
    <p class="text-gray-600 mb-2"><strong>Email:</strong> {{ auth()->user()->email }}</p>
    <p class="text-gray-600 mb-2"><strong>Rol:</strong> {{ auth()->user()->roles->pluck('name')->first() ?? '-' }}</p>
    <p class="text-gray-600"><strong>Fecha de creación:</strong> {{ auth()->user()->created_at?->format('d/m/Y H:i') ?? '-' }}</p>

    <!-- Botón editar -->
  
</div>
@endsection
