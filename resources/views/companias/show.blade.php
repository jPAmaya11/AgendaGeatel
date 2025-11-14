@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">🏢 {{ $compania->nombre }}</h1>
    <p class="mb-4 text-gray-600">{{ $compania->descripcion ?? 'Sin descripción disponible.' }}</p>

    <h2 class="text-xl font-semibold mb-2">📡 Chips Asociados:</h2>

    @if($compania->chips->isEmpty())
        <p class="text-gray-500">No hay chips asociados a esta compañía.</p>
    @else
        <ul class="list-disc ml-6">
            @foreach($compania->chips as $chip)
                <li class="mb-1">
                    <span class="font-semibold">{{ $chip->numero }}</span> 
                    — Señal: {{ $chip->tiene_senal ? '✅ Sí' : '❌ No' }} 
                    — Bloqueado: {{ $chip->bloqueado ? '🚫 Sí' : '🟢 No' }}
                </li>
            @endforeach
        </ul>
    @endif

    <div class="mt-6">
        <a href="{{ route('companias.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            ⬅️ Volver
        </a>
    </div>
</div>
@endsection
