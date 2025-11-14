@extends('layouts.app')

@section('title', 'Listado de Chips')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Listado de Chips</h1>

    {{-- Mensaje de éxito --}}
    @if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    {{-- Botón para crear nuevo chip --}}
    <div class="mb-4">
        <a href="{{ route('chips.create') }}" class="bg-blue-600 hover:bg-blue-700 text-black px-4 py-2 rounded">
            + Nuevo Chip
        </a>
    </div>


    {{-- Tabla de chips --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-700 uppercase text-sm">
                    <th class="py-3 px-4 border-b">ID</th>
                    <th class="py-3 px-4 border-b">Número</th>
                    <th class="py-3 px-4 border-b">Compañía</th>
                    <th class="py-3 px-4 border-b">Tiene Señal</th>
                    <th class="py-3 px-4 border-b">Bloqueado</th>
                    <th class="py-3 px-4 border-b text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($chips as $chip)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4">{{ $chip->id }}</td>
                    <td class="py-3 px-4">{{ $chip->numero }}</td>
                    <td class="py-3 px-4">{{ $chip->compania->nombre ?? 'Sin compañía' }}</td>
                    <td class="py-3 px-4">
                        @if ($chip->tiene_senal)
                        <span class="text-green-600 font-semibold">Sí</span>
                        @else
                        <span class="text-red-600 font-semibold">No</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        @if ($chip->bloqueado)
                        <span class="text-red-600 font-semibold">Sí</span>
                        @else
                        <span class="text-green-600 font-semibold">No</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 flex justify-center gap-2">
                        <a href="{{ route('chips.show', $chip->id) }}" class="text-blue-600 hover:underline">Ver</a>
                        <a href="{{ route('chips.edit', $chip->id) }}" class="text-yellow-600 hover:underline">Editar</a>
                        <form action="{{ route('chips.destroy', $chip->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este chip?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500">No hay chips registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection