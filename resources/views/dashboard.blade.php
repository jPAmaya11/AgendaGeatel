@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">📊 Panel Principal</h1>
    <p>Bienvenido al sistema de gestión de <strong>Geatel</strong>.</p>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">💾 Chips</h5>
                    <p class="card-text">Gestiona los chips registrados.</p>
                    <a href="{{ url('chips') }}" class="btn btn-primary btn-sm">Ver Chips</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">🏢 Compañías</h5>
                    <p class="card-text">Administra las compañías de telefonía.</p>
                    <a href="{{ url('companias') }}" class="btn btn-primary btn-sm">Ver Compañías</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">⚙️ Estados</h5>
                    <p class="card-text">Define los estados de los chips.</p>
                    <a href="{{ url('estados') }}" class="btn btn-primary btn-sm">Ver Estados</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">👤 Usuarios</h5>
                    <p class="card-text">Gestiona los usuarios del sistema.</p>
                    <a href="{{ url('usuarios') }}" class="btn btn-primary btn-sm">Ver Usuarios</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">📋 Registros</h5>
                    <p class="card-text">Consulta los cambios realizados.</p>
                    <a href="{{ url('registros') }}" class="btn btn-primary btn-sm">Ver Registros</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
