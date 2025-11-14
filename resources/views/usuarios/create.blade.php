@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 max-w-lg">
    <h1 class="text-2xl font-bold mb-4">➕ Crear Usuario</h1>

    <form action="{{ route('usuarios.store') }}" method="POST" class="bg-white p-6 rounded shadow">
        @csrf

        <div class="mb-4">
            <label for="nombre" class="block font-medium">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" class="w-full border rounded p-2" required>
            @error('nombre') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="block font-medium">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full border rounded p-2" required>
            @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="block font-medium">Contraseña</label>
            <input type="password" id="password" name="password" class="w-full border rounded p-2" required>
            @error('password') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-between">
            <a href="{{ route('usuarios.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                ⬅️ Cancelar
            </a>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                💾 Guardar
            </button>
        </div>
    </form>
</div>
@endsection
