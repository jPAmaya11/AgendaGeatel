@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">📋 Lista de Estados</h1>

    {{-- Mensaje de éxito --}}
    @if(session('success'))
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    {{-- Botón para crear --}}
    <a href="{{ route('estados.create') }}" class="bg-blue-600 text-black px-4 py-2 rounded hover:bg-blue-700">
        ➕ Nuevo Estado
    </a>


    {{-- Tabla --}}
    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow text-black"> {{-- texto negro por defecto --}}
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="py-2 px-4 border-b">ID</th>
                    <th class="py-2 px-4 border-b">Nombre</th>
                    <th class="py-2 px-4 border-b">Descripción</th>
                    <th class="py-2 px-4 border-b">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($estados as $estado)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 border-b">{{ $estado->id }}</td>
                    <td class="py-2 px-4 border-b">{{ $estado->nombre }}</td>
                    <td class="py-2 px-4 border-b">{{ $estado->descripcion ?? '—' }}</td>
                    <td class="py-2 px-4 border-b flex space-x-2">
                        <a href="{{ route('estados.show', $estado->id) }}" class="bg-blue-200 text-black px-3 py-1 rounded hover:bg-blue-300">👁️ Ver</a>
                        <a href="{{ route('estados.edit', $estado->id) }}" class="bg-yellow-200 text-black px-3 py-1 rounded hover:bg-yellow-300">✏️ Editar</a>
                        <form action="{{ route('estados.destroy', $estado->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este estado?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-200 text-black px-3 py-1 rounded hover:bg-red-300">🗑️ Eliminar</button>
                        </form>

                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-3 px-4 text-center text-gray-500">No hay estados registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection