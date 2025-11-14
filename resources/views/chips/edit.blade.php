@extends('layouts.app')

@section('title', 'Editar Chip')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Editar Chip</h1>

    {{-- Mensajes de error --}}
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulario --}}
    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('chips.update', $chip->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Número --}}
            <div class="mb-4">
                <label for="numero" class="block text-gray-700 font-semibold mb-2">Número</label>
                <input type="text" name="numero" id="numero" value="{{ old('numero', $chip->numero) }}"
                    class="border border-gray-300 rounded w-full py-2 px-3 focus:outline-none focus:ring focus:ring-blue-200" required>
            </div>

            {{-- Compañía --}}
            <div class="mb-4">
                <label for="compania_id" class="block text-gray-700 font-semibold mb-2">Compañía</label>
                <select name="compania_id" id="compania_id"
                    class="border border-gray-300 rounded w-full py-2 px-3 focus:outline-none focus:ring focus:ring-blue-200" required>
                    @foreach ($companias as $compania)
                        <option value="{{ $compania->id }}" {{ $chip->compania_id == $compania->id ? 'selected' : '' }}>
                            {{ $compania->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tiene señal --}}
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Tiene señal</label>
                <select name="tiene_senal" class="border border-gray-300 rounded w-full py-2 px-3 focus:outline-none focus:ring focus:ring-blue-200">
                    <option value="1" {{ $chip->tiene_senal ? 'selected' : '' }}>Sí</option>
                    <option value="0" {{ !$chip->tiene_senal ? 'selected' : '' }}>No</option>
                </select>
            </div>

            {{-- Bloqueado --}}
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Bloqueado</label>
                <select name="bloqueado" class="border border-gray-300 rounded w-full py-2 px-3 focus:outline-none focus:ring focus:ring-blue-200">
                    <option value="0" {{ !$chip->bloqueado ? 'selected' : '' }}>No</option>
                    <option value="1" {{ $chip->bloqueado ? 'selected' : '' }}>Sí</option>
                </select>
            </div>

            {{-- Botones --}}
            <div class="flex justify-between">
                <a href="{{ route('chips.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Cancelar
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Actualizar Chip
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
