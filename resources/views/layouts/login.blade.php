<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Cotizaciones')</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        .body-login {
            width: 100%;
            height: 100vh;
            background: linear-gradient(135deg, #3f93d2, #1a6fa3);
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body class="body-login">
    <main class="container"">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
