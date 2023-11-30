<?php
session_start(); // Start session management for user authentication
require("funciones.php"); // Include the functions file containing the login logic

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve user input from the login form
    $usuario = $_POST["Usuario"];
    $contraseña = $_POST["Contraseña"];

    // Check if the entered credentials are valid using the login function
    if (login($usuario, $contraseña)) {
        // If valid, set session variables and redirect to the application page
        $_SESSION["usuario"] = $usuario;
        $_SESSION["hora_conexion"] = time();
        header("location: aplicacion.php");
        exit;
    } else {
        // If invalid, set an error message
        $error = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
</head>

<body>
    <h1>Login</h1>

    <?php if (isset($error)) : ?>
        <!-- Display error message if login credentials are incorrect -->
        <p><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <!-- Login form -->
        <label for="Usuario">Usuario</label><br>
        <input type="text" name="Usuario"><br><br>
        <label for="Contraseña">Contraseña</label><br>
        <input type="password" name="Contraseña"><br>
        <input type="submit" value="Enviar" name="IniciarSesion"><br>
    </form>
</body>

</html>
