<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "petshop_db";

// Poveži se s MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Provjera konekcije
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Database Test</h1>";

// Funkcija za prikaz tabele
function showTable($conn, $tableName, $columns) {
    $sql = "SELECT * FROM $tableName";
    $result = $conn->query($sql);

    echo "<h2>$tableName</h2>";

    if ($result && $result->num_rows > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr>";
        foreach ($columns as $col) {
            echo "<th>$col</th>";
        }
        echo "</tr>";

        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($columns as $col) {
                echo "<td>" . $row[$col] . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No data in $tableName.</p>";
    }
}

// Pozovi funkciju za sve tabele baze
showTable($conn, "users", ["id", "name", "email", "password"]);
showTable($conn, "pets", ["id", "name", "type", "age", "owner_id"]);
showTable($conn, "products", ["id", "name", "price", "stock"]);
showTable($conn, "appointments", ["id", "pet_id", "date", "description"]);
showTable($conn, "orders", ["id", "order_date", "total"]);

$conn->close();
?>
