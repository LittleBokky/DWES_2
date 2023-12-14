<?php
session_start();
require_once('functions.php');

if (!isset($_SESSION['lista_compras'])) {
    $_SESSION['lista_compras'] = array();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['agregar'])) {
        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
        $cantidad = isset($_POST['cantidad']) ? $_POST['cantidad'] : 0;
        $precio = isset($_POST['precio']) ? $_POST['precio'] : 0;
        if (!empty($nombre)) {
            $producto = new Producto($nombre, $cantidad, $precio);
            $_SESSION['lista_compras'][] = $producto;
        }
    } elseif (isset($_POST['modificar'])) {
        $index = isset($_POST['index']) ? $_POST['index'] : -1;
        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
        $cantidad = isset($_POST['cantidad']) ? $_POST['cantidad'] : 0;
        $precio = isset($_POST['precio']) ? $_POST['precio'] : 0;
        if ($index >= 0 && !empty($nombre)) {
            $_SESSION['lista_compras'] = modificarProducto($_SESSION['lista_compras'], $index, $nombre, $cantidad, $precio);
        }
    } elseif (isset($_POST['borrar'])) {
        $index = isset($_POST['index']) ? $_POST['index'] : -1;
        if ($index >= 0) {
            $_SESSION['lista_compras'] = borrarProducto($_SESSION['lista_compras'], $index);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lista de Compras</title>
</head>
<body>
    <h1>Lista de Compras</h1>

    <!-- Formulario para agregar un producto -->
    <h2>Agregar Producto</h2>
    <form method="POST">
        Nombre: <input type="text" name="nombre" required>
        Cantidad: <input type="number" name="cantidad" required>
        Precio: <input type="number" step="0.01" name="precio" required>
        <button type="submit" name="agregar">Agregar</button>
    </form>

    <!-- Formulario para mostrar la lista de compras -->
    <h2>Lista de Compras</h2>
    <?php mostrarListaCompras($_SESSION['lista_compras']); ?>

</body>
</html>