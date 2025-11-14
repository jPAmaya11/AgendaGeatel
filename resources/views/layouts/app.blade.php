<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Geatel') }}</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f5f6fa;
        }

        .sidebar {
            min-height: 100vh;
            background-color: #1e1e2f;
            color: white;
            padding-top: 20px;
        }

        .sidebar a {
            color: #b8b8d4;
            text-decoration: none;
            display: block;
            padding: 10px 20px;
            transition: 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #343450;
            color: #fff;
        }

        .content {
            padding: 30px;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            {{-- Sidebar --}}
            <nav class="col-md-2 sidebar">
                <h4 class="text-center mb-4">📱 Geatel</h4>
                <a href="/prueba" class="{{ Request::is('prueba') ? 'active' : '' }}">🏠 Dashboard</a>
                <a href="{{ url('chips') }}" class="{{ Request::is('chips*') ? 'active' : '' }}">💾 Chips</a>
                <a href="{{ url('companias') }}" class="{{ Request::is('companias*') ? 'active' : '' }}">🏢 Compañías</a>
                <a href="{{ url('estados') }}" class="{{ Request::is('estados*') ? 'active' : '' }}">⚙️ Estados</a>
                <a href="{{ url('usuarios') }}" class="{{ Request::is('usuarios*') ? 'active' : '' }}">👤 Usuarios</a>
                <a href="{{ url('registros') }}" class="{{ Request::is('registros*') ? 'active' : '' }}">📊 Registros</a>
            </nav>


            {{-- Main Content --}}
            <main class="col-md-10 content">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>