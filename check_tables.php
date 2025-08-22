<?php
include 'dbconnect.php';

// Function to show table structure
function showTableStructure($conn, $tableName) {
    echo "<h2>Structure of table: $tableName</h2>";
    $result = mysqli_query($conn, "DESCRIBE $tableName");
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
}

// Show structure of all relevant tables
showTableStructure($conn, 'desks');
showTableStructure($conn, 'resources');
showTableStructure($conn, 'rooms');
?> 