<?php
session_start();
require 'db.php';

$username = $_POST['username'];
$password = $_POST['password'];

$username = $conn->real_escape_string($username);
$passwordHash = hash('sha256', $password);

$sql = "SELECT * FROM users WHERE username = '$username' AND password = '$passwordHash'";
$result = $conn->query($sql);

if ($result->num_rows == 1) {
    $_SESSION['username'] = $username;
    header("Location: welcome.php");
} else {
    $_SESSION['error'] = "Usuario o contraseña incorrectos";
    header("Location: index.php");
}
?>
