@extends('panel.layouts.panel')

@section('title', 'Leads')

@section('content')
<div class="p-6 bg-white shadow rounded-lg">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Listado de Leads</h2>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead class="bg-gray-100 border-b border-gray-300">
                <tr>
                    <th class="px-4 py-2 text-left text-gray-600 font-semibold">ID</th>
                    <th class="px-4 py-2 text-left text-gray-600 font-semibold">Nombre Completo</th>
                    <th class="px-4 py-2 text-left text-gray-600 font-semibold">Clínica</th>
                    <th class="px-4 py-2 text-left text-gray-600 font-semibold">Teléfono</th>
                    <th class="px-4 py-2 text-left text-gray-600 font-semibold">Etapa</th>
                    <th class="px-4 py-2 text-left text-gray-600 font-semibold">Propietario(a)</th>
                    <th class="px-4 py-2 text-left text-gray-600 font-semibold">Creado en</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($leads as $lead)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-4 py-3 text-gray-700">{{ $lead->id }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $lead->first_name }} {{ $lead->last_name }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $lead->clinic }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $lead->phone }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                {{ $lead->stage }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $lead->seller }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $lead->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-4 text-center text-gray-500">No hay leads registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
