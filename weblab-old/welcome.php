<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido</title>
    <link rel="stylesheet" href="css/welcome.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <button>Cotizaciones</button>
            <button>Órdenes</button>
            <button>Clientes</button>
            <form action="logout.php" method="post">
                <button type="submit">Salir</button>
            </form>
        </div>
        <div class="content">
            <h1>¡Hola, <?php echo $_SESSION['username']; ?>!</h1>
            <p>Bienvenido al panel de administración.</p>
        </div>
    </div>
</body>
</html>
