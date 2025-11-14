@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 max-w-lg">
    <h1 class="text-2xl font-bold mb-4">👁️ Detalles del Estado</h1>

    <div class="bg-white p-6 rounded shadow">
        <p><strong>ID:</strong> {{ $estado->id }}</p>
        <p><strong>Nombre:</strong> {{ $estado->nombre }}</p>
        <p><strong>Descripción:</strong> {{ $estado->descripcion ?? '—' }}</p>
        <p><strong>Creado el:</strong> {{ $estado->created_at->format('d/m/Y H:i') }}</p>
        <p><strong>Última actualización:</strong> {{ $estado->updated_at->format('d/m/Y H:i') }}</p>
    </div>

    <div class="flex justify-between mt-4">
        <a href="{{ route('estados.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            ⬅️ Volver
        </a>
        <a href="{{ route('estados.edit', $estado->id) }}" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
            ✏️ Editar
        </a>
    </div>
</div>
@endsection
