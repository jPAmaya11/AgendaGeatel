@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 max-w-2xl">
    <h1 class="text-2xl font-bold mb-4">👁️ Detalle del Registro #{{ $registro->id }}</h1>

    <div class="bg-white p-6 rounded shadow">
        <p><strong>ID:</strong> {{ $registro->id }}</p>

        <p class="mt-3"><strong>Chip:</strong>
            {{ optional($registro->chip)->numero ?? '—' }}
            @if(optional($registro->chip)->compania)
                ({{ $registro->chip->compania->nombre }})
            @endif
        </p>

        <p class="mt-3"><strong>Estado:</strong> {{ $registro->estado->nombre ?? '—' }}</p>
        <p class="mt-3"><strong>Usuario:</strong> {{ $registro->usuario->nombre ?? '—' }}</p>
        <p class="mt-3"><strong>Conteo de cambios:</strong> {{ $registro->conteo_cambios }}</p>
        <p class="mt-3"><strong>Fecha de revisión:</strong> {{ \Carbon\Carbon::parse($registro->fecha_revision)->format('d/m/Y') }}</p>

        <p class="mt-3"><strong>Creado:</strong> {{ $registro->created_at->format('d/m/Y H:i') }}</p>
        <p><strong>Actualizado:</strong> {{ $registro->updated_at->format('d/m/Y H:i') }}</p>
    </div>

    <div class="flex justify-between mt-4">
        <a href="{{ route('registros.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">⬅️ Volver</a>
        <div class="flex gap-2">
            <a href="{{ route('registros.edit', $registro->id) }}" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">✏️ Editar</a>

            <form action="{{ route('registros.destroy', $registro->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este registro?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">🗑️ Eliminar</button>
            </form>
        </div>
    </div>
</div>
@endsection
