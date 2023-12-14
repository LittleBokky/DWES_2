<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Show Tables</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f4f4f4;
        }

        form {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        input[type="submit"] {
            background-color: #008CBA;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        select, input[type="text"] {
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
    </style>
</head>

<body>
    <?php
    session_start();
    require("tablas.php");
    $dbConnection = connectToDatabase();
    ?>
    <form action="" method="post">
        <input type="submit" value="Consult" name="consult">
        <input type="submit" value="Insert" name="insert">
        <input type="submit" value="Modify" name="modify">
        <input type="submit" value="Delete" name="delete">
    </form>

    <?php
    if (isset($_POST["consult"]) || isset($_POST['insert']) || isset($_POST['modify']) || isset($_POST['delete'])) {

        $tablesList = getAllTables($dbConnection);

        echo '<form action="" method="post">';

        if (isset($_POST["consult"])) {
            filterByVendor($dbConnection);
        }

        if (isset($_POST['insert'])) {
            postIns($dbConnection, $tablesList);
        }

        if (isset($_POST['modify'])) {
            postMod($dbConnection, $tablesList);
        }

        if (isset($_POST["delete"])) {
            postShow($dbConnection, $tablesList);
        }

        echo '</form>';

        if (isset($_POST["consult"])) {
            echo '<form action="" method="post">';
            filter($dbConnection, $tablesList);
            echo '</form>';
        }
    }
    if (isset($_POST["tableFilter"])) {
        $selectedTable = $_POST["tableFilter"];
        showVentasTable($dbConnection, $selectedTable);
    } elseif (isset($_POST["table"])) {
        $vendorId = $_POST['comercial'];

        // Show data for the selected vendor

        // Keep the selected value in the select
        echo '<form action="" method="post">';
        filterByVendor($dbConnection);
        echo '</form>';
        showDataForComercial($vendorId, $dbConnection);
    } elseif (isset($_POST["tableIns"])) {
        $selectedTable = $_POST["tableIns"];
        $fields = getTableStructure($selectedTable, $dbConnection);

        if (!empty($fields)) {
            // Show insertion form with fields based on the selected table
            echo "<h2>Insert Data into the '$selectedTable' table</h2>";
            echo '<form action="" method="post">';
            echo "<input type='hidden' name='selected_table' value='$selectedTable'>";

            foreach ($fields as $field) {
                if ($field === "vendorCode") {
                    echo "$field" . generateSelectOptions($field) . "<br>";
                } elseif ($field === "productRef") {
                    echo "$field" . generateProductSelect($field) . "<br>";
                } else {
                    echo "<label for='$field'>$field:</label>";
                    echo "<input type='text' name='data[$field]'><br>";
                }
            }

            echo '<input type="submit" value="Insert Data">';
            echo '</form>';

            // Insert data logic
        }
    } elseif (isset($_POST["tableMod"])) {
        $selectedModTable = $_POST["tableMod"];
        $_SESSION["selectedModTable"] = $selectedModTable;
        $tableData = showTableWithModifyButton($selectedModTable, $dbConnection);
    } elseif (isset($_POST['tableDel'])) {
        $selectedDelTable = $_POST['tableDel'];
        $_SESSION["selectedDelTable"] = $selectedDelTable;
        showTableWithDeleteButton($selectedDelTable, $dbConnection);
    }

    if (isset($_POST['update_data'])) {

        $selectedModTable = isset($_SESSION["selectedModTable"]) ? $_SESSION["selectedModTable"] : '';

        if (!empty($selectedModTable)) {
            $tablesList = getAllTables($dbConnection);
            postMod($dbConnection, $tablesList);
            if ($selectedModTable == "vendors") {
                $formData = [
                    'vendorCode' => $_POST['vendorCode'],
                    'name' => $_POST['name'],
                    'salary' => $_POST['salary'],
                    'children' => $_POST['children'],
                    'birthDate' => $_POST['birthDate']
                ];

                // Adjust according to the structure of the 'vendors' table
                $primaryKey = "vendorCode";
                updateTableData($formData, $selectedModTable, $primaryKey, $dbConnection);
            } elseif ($selectedModTable == "products") {
                $formData = [
                    'productRef' => $_POST['productRef'],
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'price' => $_POST['price'],
                    'discount' => $_POST['discount']
                ];
                // Adjust according to the structure of the 'products' table
                $primaryKey = "productRef";
                updateTableData($formData, $selectedModTable, $primaryKey, $dbConnection);
            } elseif ($selectedModTable == "sales") {
                $formData = [
                    'vendorCode' => $_POST['vendorCode'],
                    'productRef' => $_POST['productRef'],
                    'quantity' => $_POST['quantity'],
                    'date' => $_POST['date'],
                ];

                // Adjust according to the structure of the 'sales' table
                $primaryKey = "vendorCode";
                updateTableData($formData, $selectedModTable, $primaryKey, $dbConnection);
            } else {
                echo "Invalid table. "; // Debug message
            }

            // Show the updated table
            showTableWithModifyButton($selectedModTable, $dbConnection);
        } else {
            echo "No table selected. "; // Debug message
        }
    }

    if (isset($_POST["delete_row"])) {
        $selectedDelTable = isset($_SESSION["selectedDelTable"]) ? $_SESSION["selectedDelTable"] : '';

        if (!empty($selectedDelTable)) {
            $primaryKey = getPrimaryKey($selectedDelTable, $dbConnection);

            if (!empty($primaryKey)) {
                $rowToDelete = [];

                // Build the array of primary keys
                foreach ($primaryKey as $column) {
                    $rowToDelete[] = $_POST["{$column}_delete"];
                }

                // Deletion logic based on the selected table
                if ($selectedDelTable == "vendors") {
                    deleteComercial($rowToDelete[0], $dbConnection); // Modify here
                } elseif ($selectedDelTable == "products") {
                    deleteProducto($rowToDelete[0], $dbConnection); // Modify here
                } elseif ($selectedDelTable == "sales") {
                    // Logic for the sales table
                    deleteVenta($rowToDelete[0], $rowToDelete[1], $rowToDelete[2], $dbConnection); // Modify here
                } else {
                    echo "Error: Invalid table.";
                }

                echo "The row has been successfully deleted.";
                $selectedModTable = isset($_SESSION["selectedModTable"]) ? $_SESSION["selectedModTable"] : '';

                // Show the table again after deletion
                showTableWithModifyButton($selectedModTable, $dbConnection);
            }
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["selected_table"]) && isset($_POST["data"])) {
        $selectedTable = $_POST["selected_table"];
        $data = $_POST["data"];

        // Perform data insertion
        $insertResult = insertData($selectedTable, $data, $dbConnection);

        if ($insertResult) {
            echo "Data has been successfully inserted into the '$selectedTable' table.";
        } else {
            echo "Error inserting data into the '$selectedTable' table.";
        }
    }
    $dbConnection->close();
    ?>
</body>

</html>