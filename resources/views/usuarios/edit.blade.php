@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 max-w-lg">
    <h1 class="text-2xl font-bold mb-4">✏️ Editar Usuario</h1>

    <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST" class="bg-white p-6 rounded shadow">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="nombre" class="block font-medium">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" class="w-full border rounded p-2" required>
            @error('nombre') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="block font-medium">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', $usuario->email) }}" class="w-full border rounded p-2" required>
            @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="block font-medium">Nueva Contraseña (opcional)</label>
            <input type="password" id="password" name="password" class="w-full border rounded p-2">
            @error('password') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            <p class="text-sm text-gray-500 mt-1">Deja este campo vacío si no deseas cambiar la contraseña.</p>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('usuarios.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                ⬅️ Volver
            </a>
            <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                💾 Actualizar
            </button>
        </div>
    </form>
</div>
@endsection
