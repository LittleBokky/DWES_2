<?php
    // Function to establish a database connection
    function conectarBD() {
        $conexion = new mysqli("localhost", "root", "", "tarea4");
        if ($conexion->connect_error) {
            die("Database connection failed: " . $conexion->connect_error);
        }
        return $conexion;
    }

    // Function to validate user login credentials
    function login($usuario, $contraseña) {
        $conexion = conectarBD();
        $statement = $conexion->prepare("SELECT pwd FROM usuarios WHERE usuario = ?");
        $statement->bind_param("s", $usuario);
        $statement->execute();
        $statement->bind_result($hashed_password);
        $statement->fetch();
        $statement->close();

        // Verify the entered password against the hashed password from the database
        if (password_verify($contraseña, $hashed_password)) {
            return true;   
        } else {
            return false;
        }
    }

    // Function to check and redirect if a session is not active
    function verificarSesion() {
        // Start a session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Redirect to the login page if the user is not authenticated
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php');
            exit;
        }
    }
?>
