@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">📋 Lista de Registros</h1>

    {{-- Mensaje de éxito --}}
    @if(session('success'))
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    {{-- Botón crear --}}
    <a href="{{ route('registros.create') }}" class="bg-white text-black border border-black px-4 py-2 rounded hover:bg-gray-100">
        ➕ Nuevo Registro
    </a>



    {{-- Tabla --}}
    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="py-2 px-4 border-b">ID</th>
                    <th class="py-2 px-4 border-b">Chip</th>
                    <th class="py-2 px-4 border-b">Estado</th>
                    <th class="py-2 px-4 border-b">Usuario</th>
                    <th class="py-2 px-4 border-b">Cambios</th>
                    <th class="py-2 px-4 border-b">Revisión</th>
                    <th class="py-2 px-4 border-b">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registros as $registro)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 border-b">{{ $registro->id }}</td>
                    <td class="py-2 px-4 border-b">{{ $registro->chip->numero ?? '—' }}</td>
                    <td class="py-2 px-4 border-b">{{ $registro->estado->nombre ?? '—' }}</td>
                    <td class="py-2 px-4 border-b">{{ $registro->usuario->nombre ?? '—' }}</td>
                    <td class="py-2 px-4 border-b">{{ $registro->conteo_cambios }}</td>
                    <td class="py-2 px-4 border-b">{{ $registro->fecha_revision }}</td>
                    <td class="py-2 px-4 border-b flex space-x-2">
                        {{-- Ver --}}
                        <a href="{{ route('registros.show', $registro->id) }}"
                            class="bg-white text-black border border-black px-3 py-1 rounded hover:bg-gray-100">
                            👁️ Ver
                        </a>

                        {{-- Editar --}}
                        <a href="{{ route('registros.edit', $registro->id) }}"
                            class="bg-white text-black border border-black px-3 py-1 rounded hover:bg-gray-100">
                            ✏️ Editar
                        </a>

                        {{-- Eliminar --}}
                        <form action="{{ route('registros.destroy', $registro->id) }}"
                            method="POST"
                            onsubmit="return confirm('¿Eliminar este registro?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="bg-white text-black border border-black px-3 py-1 rounded hover:bg-gray-100">
                                🗑️ Eliminar
                            </button>
                        </form>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-gray-500">No hay registros aún.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection