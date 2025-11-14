@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">👥 Lista de Usuarios</h1>

    <a href="{{ route('usuarios.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mb-4 inline-block">
        ➕ Nuevo Usuario
    </a>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <table class="min-w-full bg-white border rounded shadow">
        <thead>
            <tr class="bg-gray-100 text-left">
                <th class="px-4 py-2 border">ID</th>
                <th class="px-4 py-2 border">Nombre</th>
                <th class="px-4 py-2 border">Email</th>
                <th class="px-4 py-2 border">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($usuarios as $usuario)
                <tr>
                    <td class="px-4 py-2 border">{{ $usuario->id }}</td>
                    <td class="px-4 py-2 border">{{ $usuario->nombre }}</td>
                    <td class="px-4 py-2 border">{{ $usuario->email }}</td>
                    <td class="px-4 py-2 border">
                        <a href="{{ route('usuarios.show', $usuario->id) }}" class="text-blue-600 hover:underline">👁️ Ver</a> |
                        <a href="{{ route('usuarios.edit', $usuario->id) }}" class="text-yellow-600 hover:underline">✏️ Editar</a> |
                        <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('¿Eliminar este usuario?')">🗑️ Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
