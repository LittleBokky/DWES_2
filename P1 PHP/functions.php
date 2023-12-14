<?php
class Producto {
    private $nombre;
    private $cantidad;
    private $precio;

    public function __construct($nombre, $cantidad, $precio) {
        $this->nombre = $nombre;
        $this->cantidad = $cantidad;
        $this->precio = $precio;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function getCantidad() {
        return $this->cantidad;
    }

    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    public function getPrecio() {
        return $this->precio;
    }

    public function setPrecio($precio) {
        $this->precio = $precio;
    }

    public function calcularTotal() {
        return $this->cantidad * $this->precio;
    }
}

function mostrarListaCompras($lista) {
    if (empty($lista)) {
        echo "No hay productos en la lista de compras.";
        return;
    }

    echo '<table border="1">';
    echo '<tr><th>Nombre</th><th>Cantidad</th><th>Precio</th><th>Total</th><th>Acciones</th></tr>';
    foreach ($lista as $index => $producto) {
        echo '<tr>';
        echo '<td>' . $producto->getNombre() . '</td>';
        echo '<td>' . $producto->getCantidad() . '</td>';
        echo '<td>' . $producto->getPrecio() . '</td>';
        echo '<td>' . $producto->calcularTotal() . '</td>';
        echo '<td>
                <form method="POST">
                    <input type="hidden" name="index" value="' . $index . '">
                    <button type="submit" name="modificar">Modificar</button>
                    <button type="submit" name="borrar">Borrar</button>
                </form>
            </td>';
        echo '</tr>';
    }
    echo '</table>';

    // Calcular el precio total de la compra
    $precioTotal = calcularPrecioCompraTotal($lista);
    echo '<p>Precio Total de Compra: ' . $precioTotal . '</p>';
}

function calcularPrecioCompraTotal($lista) {
    $precioTotal = 0;
    foreach ($lista as $producto) {
        $precioTotal += $producto->calcularTotal();
    }
    return $precioTotal;
}

function borrarProducto($lista, $index) {
    unset($lista[$index]);
    $lista = array_values($lista);
    return $lista;
}

function modificarProducto($lista, $index, $nombre, $cantidad, $precio) {
    if (!empty($nombre)) {
        $lista[$index]->setNombre($nombre);
        $lista[$index]->setCantidad($cantidad);
        $lista[$index]->setPrecio($precio);
    }
    return $lista;
}

?>