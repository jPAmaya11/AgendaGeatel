@extends('layouts.app')

@section('title', 'Detalles del Chip')

@section('content')
<div class="container mx-auto px-4 py-8">

    <h1 class="text-2xl font-bold mb-6 text-gray-800">Detalles del Chip</h1>

    <div class="bg-white shadow rounded-lg p-6">
        <div class="mb-4">
            <h2 class="text-gray-700 font-semibold">Número:</h2>
            <p class="text-gray-900">{{ $chip->numero }}</p>
        </div>

        <div class="mb-4">
            <h2 class="text-gray-700 font-semibold">Compañía:</h2>
            <p class="text-gray-900">{{ $chip->compania->nombre }}</p>
        </div>

        <div class="mb-4">
            <h2 class="text-gray-700 font-semibold">Tiene señal:</h2>
            <p class="{{ $chip->tiene_senal ? 'text-green-600' : 'text-red-600' }}">
                {{ $chip->tiene_senal ? 'Sí' : 'No' }}
            </p>
        </div>

        <div class="mb-4">
            <h2 class="text-gray-700 font-semibold">Bloqueado:</h2>
            <p class="{{ $chip->bloqueado ? 'text-red-600' : 'text-green-600' }}">
                {{ $chip->bloqueado ? 'Sí' : 'No' }}
            </p>
        </div>

        <div class="mb-4">
            <h2 class="text-gray-700 font-semibold">Fecha de creación:</h2>
            <p class="text-gray-900">{{ $chip->created_at->format('d/m/Y H:i') }}</p>
        </div>

        <div class="flex justify-between mt-6">
            <a href="{{ route('chips.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                Volver
            </a>

            <a href="{{ route('chips.edit', $chip->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Editar
            </a>
        </div>
    </div>

</div>
@endsection
