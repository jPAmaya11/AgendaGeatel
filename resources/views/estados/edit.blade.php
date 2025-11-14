@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 max-w-lg">
    <h1 class="text-2xl font-bold mb-4">✏️ Editar Estado</h1>

    {{-- Mensajes de error --}}
    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('estados.update', $estado->id) }}" method="POST" class="bg-white p-6 rounded shadow">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="nombre" class="block font-semibold mb-1">Nombre</label>
            <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $estado->nombre) }}"
                   class="w-full border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        <div class="mb-4">
            <label for="descripcion" class="block font-semibold mb-1">Descripción</label>
            <textarea name="descripcion" id="descripcion" rows="3"
                      class="w-full border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Opcional">{{ old('descripcion', $estado->descripcion) }}</textarea>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('estados.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                ⬅️ Volver
            </a>
            <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                💾 Actualizar
            </button>
        </div>
    </form>
</div>
@endsection
