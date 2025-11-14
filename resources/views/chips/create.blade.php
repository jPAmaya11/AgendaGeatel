@extends('layouts.app')

@section('title', 'Registrar Chip')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Registrar Nuevo Chip</h1>

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
        <form action="{{ route('chips.store') }}" method="POST">
            @csrf

            {{-- Número --}}
            <div class="mb-4">
                <label for="numero" class="block text-gray-700 font-semibold mb-2">Número</label>
                <input type="text" name="numero" id="numero" value="{{ old('numero') }}"
                    class="border border-gray-300 rounded w-full py-2 px-3 focus:outline-none focus:ring focus:ring-blue-200" required>
            </div>

            {{-- Compañía --}}
            <div class="mb-4">
                <label for="compania_id" class="block text-gray-700 font-semibold mb-2">Compañía</label>
                <select name="compania_id" id="compania_id"
                    class="border border-gray-300 rounded w-full py-2 px-3 focus:outline-none focus:ring focus:ring-blue-200" required>
                    <option value="">Seleccione una compañía</option>
                    @foreach ($companias as $compania)
                        <option value="{{ $compania->id }}" {{ old('compania_id') == $compania->id ? 'selected' : '' }}>
                            {{ $compania->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tiene señal --}}
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Tiene señal</label>
                <select name="tiene_senal" class="border border-gray-300 rounded w-full py-2 px-3 focus:outline-none focus:ring focus:ring-blue-200">
                    <option value="1" {{ old('tiene_senal') == '1' ? 'selected' : '' }}>Sí</option>
                    <option value="0" {{ old('tiene_senal') == '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>

            {{-- Bloqueado --}}
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Bloqueado</label>
                <select name="bloqueado" class="border border-gray-300 rounded w-full py-2 px-3 focus:outline-none focus:ring focus:ring-blue-200">
                    <option value="0" {{ old('bloqueado') == '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('bloqueado') == '1' ? 'selected' : '' }}>Sí</option>
                </select>
            </div>

            {{-- Botones --}}
            <div class="flex justify-between">
                <a href="{{ route('chips.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Cancelar
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Guardar Chip
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
