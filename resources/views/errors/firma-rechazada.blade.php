<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Rechazada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .error-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }
        .error-icon {
            font-size: 4rem;
            color: #ffc107;
            margin-bottom: 1rem;
        }
        .error-title {
            color: #856404;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .error-message {
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin: 0.5rem;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1 class="error-title">Firma Rechazada</h1>
        <p class="error-message">
            {{ $mensaje ?? 'La firma digital fue rechazada. El documento no ha sido firmado.' }}
        </p>
        <a href="{{ route('informes.index') }}" class="btn">Volver a Informes</a>
        <a href="javascript:history.back()" class="btn btn-secondary">Regresar</a>
    </div>
</body>
</html>
