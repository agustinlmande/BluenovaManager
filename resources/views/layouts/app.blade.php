<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bluenova Manager</title>

    <!-- 🔹 Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- 🔹 Font Awesome (para íconos) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">





</head>

<body class="bg-light">

    {{-- 🔹 Incluimos la barra de navegación --}}
    @include('layouts.navbar')

    {{-- 🔹 Contenido dinámico --}}
    <main class="py-4">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- 🔹 Bootstrap JS Bundle (necesario para el menú colapsable) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>