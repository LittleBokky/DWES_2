<?php
session_start(); // Start session management for user authentication
include("funciones.php"); // Include the functions file containing session verification logic
verificarSesion(); // Call the function to verify user session
$usuario = $_SESSION["usuario"]; // Get the username from the session
$horas = date("H:i:s", $_SESSION["hora_conexion"]); // Format and retrieve the session start time

// Check if a color preference has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["color"])) {
    $color = $_POST["color"];
    setcookie('colorEscogido', $color, time() + 3600, '/'); // Expires in one hour
    $colorEscogido = $color;
} 
// Check if the "Reestablecer" button is clicked
elseif (isset($_POST["Reestablecer"])) {
    setcookie('colorEscogido', "white", time() + 3600, '/'); // Expires in one hour
    $colorEscogido = "white";
} 
// Use the existing color preference or default to white if none is set
else {
    $colorEscogido = isset($_COOKIE['colorEscogido']) ? $_COOKIE['colorEscogido'] : 'white';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preferences</title>
    <style>
        body {
            background-color: <?php echo $colorEscogido ?>;
        }
    </style>
</head>

<body>
    <h1>Preferences</h1>
    <!-- Display user information and welcome message -->
    <p>Welcome <?php echo $usuario ?>, logged in since <?php echo $horas ?></p>

    <!-- Form to select background color preference -->
    <form action="" method="POST">
        <label for="color">Select your preferred background color:</label>
        <select name="color" id="color">
            <option value="white" <?php echo($colorEscogido === "white") ? "selected" : ""; ?>>White</option>
            <option value="blue" <?php echo($colorEscogido === "blue") ? "selected" : ""; ?>>Blue</option>
            <option value="green" <?php echo($colorEscogido === "green") ? "selected" : ""; ?>>Green</option>
            <option value="red" <?php echo($colorEscogido === "red") ? "selected" : ""; ?>>Red</option>
        </select>
        <input type="submit" value="Submit">
    </form>

    <!-- Form to reset color preference to default (white) -->
    <form action="" method="POST">
        <input type="submit" name="Reestablecer" value="Reset">
    </form>

    <!-- Link to return to the application page -->
    <p><a href="aplicacion.php">Application</a></p>
</body>

</html>
