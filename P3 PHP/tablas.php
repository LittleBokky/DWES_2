<?php

// Connect to the database
function connectToDatabase()
{
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "ventas_comerciales";

    $connection = new mysqli($servername, $username, $password, $database);

    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    return $connection;
}

// Get names of all tables in the database
function getAllTables($databaseConnection)
{
    $tables = [];

    $sql = "SHOW TABLES";
    $result = $databaseConnection->query($sql);

    if ($result) {
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
    }

    return $tables;
}

// Get the structure of a table
function getTableStructure($tableName, $databaseConnection)
{
    $fields = [];

    $sql = "DESCRIBE $tableName";
    $result = $databaseConnection->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $fields[] = $row["Field"];
        }
        $result->free();
    }

    return $fields;
}

// Insert data into a table
function insertData($tableName, $data, $databaseConnection)
{
    if (!$databaseConnection || $databaseConnection->connect_error) {
        die("Error connecting to the database: " . $databaseConnection->connect_error);
    }

    $fields = getTableStructure($tableName, $databaseConnection);
    $fieldNames = implode(', ', $fields);
    $placeholders = rtrim(str_repeat('?, ', count($fields)), ', ');

    $sql = "INSERT INTO $tableName ($fieldNames) VALUES ($placeholders)";
    $stmt = $databaseConnection->prepare($sql);

    if ($stmt) {
        $types = '';
        $bindValues = [];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $value = $data[$field];

                $types .= getBindType($value);
                $bindValues[] = $value;
            } else {
                $types .= 's';
                $bindValues[] = null;
            }
        }

        $bindReferences = array_merge([$types], $bindValues);
        $stmt->bind_param(...$bindReferences);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    $stmt->close();
    return false;
}

// Show table with modify button
function showTableWithModifyButton($tableName, $databaseConnection)
{
    if (empty($tableName)) {
        echo "Please provide the name of a table.";
        return [];
    }

    $sql = "SELECT * FROM $tableName";
    $result = $databaseConnection->query($sql);

    $tableData = [];

    if ($result->num_rows > 0) {
        echo "<h2>Data for table '$tableName'</h2>";
        echo "<table border='1'>";

        $firstRow = $result->fetch_assoc();
        echo "<tr>";
        foreach ($firstRow as $column => $value) {
            echo "<th>$column</th>";
        }
        echo "<th>Modify</th>";
        echo "</tr>";

        $result->data_seek(0);
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $column => $value) {
                echo "<td>" . $value . "</td>";
            }

            $tableData[] = $row;

            $rowId = $row[array_keys($row)[0]];
            echo '<td>
                <form action="" method="post">
                    <input type="hidden" name="row_id" value="' . $rowId . '">
                    <input type="hidden" name="tableMod" value="' . $tableName . '">
                    <input type="submit" name="modify_row" value="Modify">
                </form>
            </td>';
            echo "</tr>";
        }
        echo "</table>";

        if (isset($_POST["modify_row"]) && isset($_POST["row_id"])) {
            $selectedRow = findRowById($tableData, $_POST["row_id"]);

            if ($selectedRow) {
                echo '<h2>Modification Form for ' . ucfirst($tableName) . '</h2>
                    <form action="" method="POST">';

                foreach ($selectedRow as $column => $value) {
                    echo '<label for="' . $column . '">' . ucfirst($column) . ':</label>
                        <input type="text" id="' . $column . '" name="' . $column . '" value="' . $value . '" required>';
                }

                echo '<input type="submit" value="Update Data" name="update_data">
                    </form>';
            } else {
                echo "Selected row not found.";
            }
        }
    } else {
        echo "No results found.";
    }

    return $tableData;
}

// Update table data
function updateTableData($data, $tableName, $primaryKey, $databaseConnection)
{
    if ($databaseConnection) {
        $fields = getTableStructure($tableName, $databaseConnection);
        $setClause = buildSetClause($data, $fields);

        $sql = "UPDATE $tableName SET $setClause WHERE $primaryKey = '" . $data[$primaryKey] . "'";

        if ($databaseConnection->query($sql) === TRUE) {
            echo "<br>Data updated successfully.";
        } else {
            echo "Error updating data: " . $databaseConnection->error;
        }
    }
}

// Filter data by vendor
function filterByVendor($databaseConnection)
{
    echo '<label for="comercial">Select a vendor:</label>';
    $query = "SELECT nombre FROM comerciales";
    $result = $databaseConnection->query($query);

    echo '<select name="comercial" id="comercial">';
    while ($row = $result->fetch_assoc()) {
        $selected = ($_POST["comercial"] == $row["nombre"]) ? 'selected' : '';
        echo '<option value="' . $row["nombre"] . '" ' . $selected . '>'
            . $row["nombre"] . '</option>';
    }
    echo '</select>';
    echo '<input type="submit" value="Show" name="table">';
}

// Post-show function
function postShow($databaseConnection, $tables)
{
    echo '<label for="tableDel">Select a table:</label>
        <select name="tableDel" id="tableDel">';
    foreach ($tables as $table) {
        $selected = ($_POST["table"] == $table) ? 'selected' : '';
        echo '<option value="' . $table . '" ' . $selected . '>' . $table . '</option>';
    }
    echo '<input type="submit" value="Show">';
}

// Post-insert function
function postIns($databaseConnection, $tables)
{
    echo '<label for="tableIns">Table name:</label>
        <select name="tableIns" id="tableIns">';
    foreach ($tables as $table) {
        echo '<option value="' . $table . '">' . $table . '</option>';
    }
    echo '<input type="submit" value="Show">';
}
function filter($databaseConnection, $tables)
{
    echo '<label for="tableFilter">Table name:</label>
        <select name="tableFilter" id="tableFilter">';
    foreach ($tables as $table) {
        echo '<option value="' . $table . '">' . $table . '</option>';
    }
    echo '<input type="submit" value="Show">';
}
function showVentasTable($databaseConnection, $table)
{
    echo "<h2>Datos de la tabla '{$table}'</h2>";

    // Get data from the 'Ventas' table
    $sql = "SELECT * FROM $table";
    $result = $databaseConnection->query($sql);

    if ($result && $result->num_rows > 0) {
        echo '<table border="1"><tr>';

        // Show column headers
        while ($fieldInfo = $result->fetch_field()) {
            echo "<th>{$fieldInfo->name}</th>";
        }

        echo "</tr>";

        // Show data for each row
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";

            foreach ($row as $value) {
                echo "<td>$value</td>";
            }

            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "No se encontraron resultados en la tabla 'Ventas'.";
    }
}
// Post-modify function
function postMod($databaseConnection, $tables)
{
    echo '<label for="tableMod">Table name:</label>
        <select name="tableMod" id="tableMod">';
    foreach ($tables as $table) {
        echo '<option value="' . $table . '">' . $table . '</option>';
    }
    echo '<input type="submit" value="Show">';
}

// Show data for a specific vendor
function showDataForComercial($comercialId, $databaseConnection)
{
    $query = "SELECT p.referencia, p.nombre AS nombre_producto, p.descripcion, p.precio, p.descuento,
                     v.refProducto, v.cantidad, v.fecha
              FROM productos p
              JOIN ventas v ON p.referencia = v.refProducto
              WHERE v.codComercial = (SELECT codigo FROM comerciales WHERE nombre = '$comercialId')";

    $result = $databaseConnection->query($query);

    echo '<table border="1">
            <tr>
                <th>Product Reference</th>
                <th>Product Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Discount</th>
                <th>Quantity Sold</th>
                <th>Sale Date</th>
            </tr>';

    while ($row = $result->fetch_assoc()) {
        echo '<tr>
                <td>' . $row["referencia"] . '</td>
                <td>' . $row["nombre_producto"] . '</td>
                <td>' . $row["descripcion"] . '</td>
                <td>' . $row["precio"] . '</td>
                <td>' . $row["descuento"] . '</td>
                <td>' . $row["cantidad"] . '</td>
                <td>' . $row["fecha"] . '</td>
              </tr>';
    }

    echo '</table>';
}

// Helper function to find a row by ID
function findRowById($tableData, $rowId)
{
    foreach ($tableData as $row) {
        if ($row[array_keys($row)[0]] == $rowId) {
            return $row;
        }
    }

    return null;
}

// Helper function to get the bind type for a value
function getBindType($value)
{
    if (is_numeric($value)) {
        return (strpos($value, '.') !== false) ? 'd' : 'i';
    } elseif (strtotime($value) !== false) {
        return 's';
    } else {
        return 's';
    }
}

// Helper function to build the SET clause for SQL update
function buildSetClause($data, $fields)
{
    $setClause = '';

    foreach ($data as $field => $value) {
        if (in_array($field, $fields)) {
            $setClause .= "$field = '$value', ";
        }
    }

    return rtrim($setClause, ', ');
}

// Function to delete a vendor and their associated sales
function deleteComercial($codigo, $databaseConnection)
{
    // Delete sales associated with the vendor
    $sqlVentas = "DELETE FROM Ventas WHERE codComercial = ?";
    executeDeleteStatement($sqlVentas, $codigo, $databaseConnection);

    // Delete the vendor
    $sqlComercial = "DELETE FROM Comerciales WHERE codigo = ?";
    executeDeleteStatement($sqlComercial, $codigo, $databaseConnection);
}

// Function to delete a product and its associated sales
function deleteProducto($referencia, $databaseConnection)
{
    // Delete sales associated with the product
    $sqlVentas = "DELETE FROM Ventas WHERE refProducto = ?";
    executeDeleteStatement($sqlVentas, $referencia, $databaseConnection);

    // Delete the product
    $sqlProducto = "DELETE FROM Productos WHERE referencia = ?";
    executeDeleteStatement($sqlProducto, $referencia, $databaseConnection);
}

// Function to delete a specific sale
function deleteVenta($codComercial, $refProducto, $fecha, $databaseConnection)
{
    // Delete the specific sale
    $sqlVenta = "DELETE FROM Ventas WHERE codComercial = ? AND refProducto = ? AND fecha = ?";
    $stmtVenta = $databaseConnection->prepare($sqlVenta);
    $stmtVenta->bind_param("sss", $codComercial, $refProducto, $fecha);
    $stmtVenta->execute();
    $stmtVenta->close();
}

// Function to get the primary key of a table
function getPrimaryKey($tableName, $databaseConnection)
{
    $fields = getTableStructure($tableName, $databaseConnection);
    $primaryKey = [];

    foreach ($fields as $field) {
        $sql = "SHOW COLUMNS FROM $tableName LIKE '$field'";
        $result = $databaseConnection->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['Key'] == 'PRI') {
                $primaryKey[] = $field;
            }
        }
    }

    return $primaryKey;
}

// Function to delete a row from a table
function deleteRow($tableName, $primaryKey, $rowValue, $databaseConnection)
{
    $sql = "DELETE FROM $tableName WHERE $primaryKey = ?";
    executeDeleteStatement($sql, $rowValue, $databaseConnection);
}

// Function to show table with delete button
function showTableWithDeleteButton($tableName, $databaseConnection)
{
    echo "<h2>Table: $tableName</h2>";

    // Get data from the table
    $sql = "SELECT * FROM $tableName";
    $result = $databaseConnection->query($sql);

    if ($result && $result->num_rows > 0) {
        echo '<table border="1"><tr>';

        // Show column headers
        while ($fieldInfo = $result->fetch_field()) {
            echo "<th>{$fieldInfo->name}</th>";
        }

        echo "<th>Action</th></tr>";

        // Show data with form and delete button for each row
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";

            foreach ($row as $value) {
                echo "<td>$value</td>";
            }

            echo "<td>";
            echo '<form action="" method="post">';
            echo "<input type='hidden' name='tableWithDelete' value='$tableName'>";

            // Add hidden fields for the primary key
            foreach ($row as $column => $value) {
                echo "<input type='hidden' name='{$column}_delete' value='$value'>";
            }

            echo '<input type="submit" value="Delete Row" name="delete_row">';
            echo '</form>';
            echo "</td>";

            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "The table is empty.";
    }
}

// Function to generate select options
function generateSelectOptions($field)
{
    $databaseConnection = connectToDatabase();
    generateSelect($field, "comerciales", "codigo", $databaseConnection);
    $databaseConnection->close();
}

// Function to generate product select options
function generateProductSelect($field)
{
    $databaseConnection = connectToDatabase();
    generateSelect($field, "productos", "referencia", $databaseConnection);
    $databaseConnection->close();
}

// Helper function to execute delete statements
function executeDeleteStatement($sql, $param, $databaseConnection)
{
    $stmt = $databaseConnection->prepare($sql);
    $stmt->bind_param("s", $param);
    $stmt->execute();
    $stmt->close();
}

// Helper function to generate select options for a table
function generateSelect($field, $tableName, $primaryKey, $databaseConnection)
{
    $fields = getTableStructure($tableName, $databaseConnection);

    if (in_array($primaryKey, $fields)) {
        $sql = "SELECT $primaryKey FROM $tableName";
        $result = $databaseConnection->query($sql);

        if ($result) {
            echo "<select name='data[$field]'>";

            while ($row = $result->fetch_assoc()) {
                $value = $row[$primaryKey];
                echo "<option value=\"$value\" name='data[$field]'>$value</option>";
            }

            echo '</select>';
            $result->free();
        } else {
            echo "Error executing query: " . $databaseConnection->error;
        }
    } else {
        echo "The table \"$tableName\" does not contain a field named \"$primaryKey\"";
    }
}

?>

