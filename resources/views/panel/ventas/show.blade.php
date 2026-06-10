@extends('panel.layouts.panel')

@section('title', 'Detalle del Lead')

@section('content')

    <!-- ====== ALERTA DE ÉXITO ====== -->
    @if (session('success'))
        <div class="max-w-4xl mx-auto mb-4">
            <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <!-- ====== CONTENEDOR PRINCIPAL ====== -->
    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg p-6">

        <!-- Título -->
        <h1 class="text-2xl font-semibold text-gray-800 mb-4">
            Información del Lead
        </h1>

        <!-- Grid de información -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <p class="text-sm text-gray-500">Nombre</p>
                <p class="text-lg font-medium">{{ $lead->first_name }} {{ $lead->last_name }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Clínica</p>
                <p class="text-lg font-medium">{{ $lead->clinic }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Origen</p>
                <p class="text-lg font-medium">{{ $lead->origin }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Teléfono</p>
                <p class="text-lg font-medium">{{ $lead->phone }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Interesado en</p>
                <p class="text-lg font-medium">{{ $lead->interested_in }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Etapa</p>
                <p class="text-lg font-medium">{{ $lead->stage }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Calificación</p>
                <p class="text-lg font-medium">{{ $lead->quali }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Vendedor</p>
                <p class="text-lg font-medium">{{ $lead->seller }}</p>
            </div>

            @if($lead->link)
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">Link</p>
                    <a href="{{ $lead->link }}" target="_blank" class="text-blue-600 underline break-all">
                        {{ $lead->link }}
                    </a>
                </div>
            @endif

            @if($lead->notes)
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">Notas</p>
                    <p class="text-gray-700 whitespace-pre-line">{{ $lead->notes }}</p>
                </div>
            @endif

        </div>

        <!-- Boton de regreso -->
        <div class="mt-6">
            <a href="{{ route('panel.ventas.index') }}"
               class="inline-block bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800">
                Regresar
            </a>
        </div>

    </div>

@endsection
