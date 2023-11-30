<?php
session_start(); // Start session management for user authentication
include("funciones.php"); // Include the functions file containing session verification logic
verificarSesion(); // Call the function to verify user session
$usuario = $_SESSION["usuario"]; // Get the username from the session
$horas = date("H:i:s", $_SESSION["hora_conexion"]); // Format and retrieve the session start time

if (isset($_POST["Cerrar"])) {
    // If the logout button is clicked, unset and destroy the session, then redirect to the login page
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

$colorEscogido = isset($_COOKIE['colorEscogido']) ? $_COOKIE['colorEscogido'] : 'white'; // Get the chosen background color from the cookie
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application</title>
</head>
<style>
    body {
        background-color: <?php echo $colorEscogido ?>;
    }
</style>

<body>
    <h1>Application</h1>

    <nav>
        <ul>
            <!-- Navigation links to other pages -->
            <li><a href="informacion.php">Information</a></li>
            <li><a href="preferencias.php">Preferences</a></li><br>

            <!-- Logout form -->
            <form action="" method="POST">
                <input type="submit" value="Logout" name="Cerrar">
            </form>
        </ul>
    </nav>
</body>

</html>
