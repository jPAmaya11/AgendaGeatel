<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de Evento</title>
</head>
<body>
    <h1>{{ $event->title }}</h1>
    <p>{{ $message }}</p>
    <p>Recuerda que el evento se llevará a cabo en: {{ $event->start_at }}</p>
    <p>¡Nos vemos pronto!</p>
</body>
</html>
