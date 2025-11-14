@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">✏️ Editar Compañía</h1>

    {{-- Mostrar errores de validación --}}
    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc pl-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulario --}}
    <form action="{{ route('companias.update', $compania->id) }}" method="POST" class="bg-white p-6 rounded shadow-md">
        @csrf
        @method('PUT')

        {{-- Nombre --}}
        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Nombre de la Compañía</label>
            <input type="text" name="nombre" value="{{ old('nombre', $compania->nombre) }}" required
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300">
        </div>

        {{-- Descripción --}}
        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Descripción</label>
            <textarea name="descripcion" rows="3"
                      class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300">{{ old('descripcion', $compania->descripcion) }}</textarea>
        </div>

        {{-- Botones --}}
        <div class="flex space-x-3">
            <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                💾 Actualizar
            </button>
            <a href="{{ route('companias.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                ↩️ Volver
            </a>
        </div>
    </form>
</div>
@endsection
