@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 max-w-2xl">
    <h1 class="text-2xl font-bold mb-4">➕ Nuevo Registro</h1>

    <form action="{{ route('registros.store') }}" method="POST" class="bg-white p-6 rounded shadow">
        @csrf

        <div class="mb-4">
            <label class="block font-medium">Chip</label>
            <select name="chip_id" class="w-full border rounded px-3 py-2" required>
                <option value="">-- Seleccionar chip disponible --</option>
                @forelse($chips as $chip)
                <option value="{{ $chip->id }}">
                    {{ $chip->numero }} ({{ $chip->compania->nombre ?? 'Sin compañía' }})
                </option>
                @empty
                <option value="">⚠️ No hay chips disponibles</option>
                @endforelse
            </select>

        </div>

        <div class="mb-4">
            <label class="block font-medium">Estado</label>
            <select name="estado_id" class="w-full border rounded px-3 py-2">
                @foreach($estados as $estado)
                <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block font-medium">Usuario</label>
            <select name="usuario_id" class="w-full border rounded px-3 py-2">
                @foreach($usuarios as $usuario)
                <option value="{{ $usuario->id }}">{{ $usuario->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block font-medium">Conteo de Cambios</label>
            <input type="number" name="conteo_cambios" value="0" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-4">
            <label class="block font-medium">Fecha de Revisión</label>
            <input type="date" name="fecha_revision" class="w-full border rounded px-3 py-2">
        </div>

        <div class="flex justify-between">
            <a href="{{ route('registros.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">⬅️ Volver</a>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">💾 Guardar</button>
        </div>
    </form>
</div>
@endsection