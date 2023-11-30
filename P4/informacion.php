<?php
session_start(); // Start session management for user authentication
include("funciones.php"); // Include the functions file containing session verification logic
verificarSesion(); // Call the function to verify user session
$usuario = $_SESSION["usuario"]; // Get the username from the session
$horas = date("H:i:s", $_SESSION["hora_conexion"]); // Format and retrieve the session start time
$colorEscogido = isset($_COOKIE['colorEscogido']) ? $_COOKIE['colorEscogido'] : 'white'; // Get the chosen background color from the cookie
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Information</title>
</head>
<style>
    body {
        background-color: <?php echo $colorEscogido ?>;
    }
</style>

<body>
    <h1>Application Information</h1>

    <!-- Display user information and welcome message -->
    <p>Welcome <?php echo $usuario ?>, logged in since <?php echo $horas ?></p>

    <!-- Placeholder text for additional information -->
    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptas labore suscipit totam! Quod ipsa ut explicabo animi assumenda reprehenderit nulla velit sit voluptate, repellat quos. Hic esse quasi sequi eligendi.</p>

    <!-- Link to return to the application page -->
    <a href="aplicacion.php">Application</a>
</body>

</html>
