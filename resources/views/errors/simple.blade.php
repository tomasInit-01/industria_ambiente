<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .error-title {
            color: #343a40;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .error-message {
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .btn-home {
            background-color: #1a6fa3;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: transform 0.3s ease;
            display: inline-block;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <img src="{{ asset('gifs/lab_gif.gif') }}" alt="Error GIF" style="max-width: 200px; margin-bottom: 1rem;">
        </div>
        
        <h1 class="error-title">¡Ups! Ocurrió un error</h1>
        
        <p class="error-message">
            Algo salió mal. Por favor, intenta nuevamente más tarde.
        </p>
        
        <div class="mt-4">
            <a href="{{ url('/') }}" class="btn-home">
                <i class="fas fa-home me-2"></i>
                Volver al inicio
            </a>
        </div>
    </div>
</body>
</html>
