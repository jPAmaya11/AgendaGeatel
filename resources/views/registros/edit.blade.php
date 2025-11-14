@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 max-w-2xl">
    <h1 class="text-2xl font-bold mb-4">✏️ Editar Registro #{{ $registro->id }}</h1>

    {{-- Errores --}}
    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('registros.update', $registro->id) }}" method="POST" class="bg-white p-6 rounded shadow">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block font-medium mb-1">Chip</label>
            <select name="chip_id" class="w-full border rounded px-3 py-2" required>
                <option value="">Seleccione un chip</option>
                @foreach($chips as $chip)
                    <option value="{{ $chip->id }}" {{ old('chip_id', $registro->chip_id) == $chip->id ? 'selected' : '' }}>
                        {{ $chip->numero }} @if($chip->compania) ({{ $chip->compania->nombre }}) @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">Estado</label>
            <select name="estado_id" class="w-full border rounded px-3 py-2" required>
                <option value="">Seleccione un estado</option>
                @foreach($estados as $estado)
                    <option value="{{ $estado->id }}" {{ old('estado_id', $registro->estado_id) == $estado->id ? 'selected' : '' }}>
                        {{ $estado->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">Usuario</label>
            <select name="usuario_id" class="w-full border rounded px-3 py-2" required>
                <option value="">Seleccione un usuario</option>
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->id }}" {{ old('usuario_id', $registro->usuario_id) == $usuario->id ? 'selected' : '' }}>
                        {{ $usuario->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">Conteo de Cambios</label>
            <input type="number" name="conteo_cambios" min="0" class="w-full border rounded px-3 py-2"
                   value="{{ old('conteo_cambios', $registro->conteo_cambios) }}" required>
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">Fecha de Revisión</label>
            <input type="date" name="fecha_revision" class="w-full border rounded px-3 py-2"
                   value="{{ old('fecha_revision', \Carbon\Carbon::parse($registro->fecha_revision)->format('Y-m-d')) }}" required>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('registros.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">⬅️ Volver</a>
            <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">💾 Actualizar</button>
        </div>
    </form>
</div>
@endsection
