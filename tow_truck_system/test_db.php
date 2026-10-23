<?php
require_once "db.php";

echo "Database connected successfully!";

$result = $conn->query("SELECT * FROM users");

if ($result) {
    echo "<br>Users table is working!";
    echo "<br>Total users: " . $result->num_rows;
} else {
    echo "<br>Users table error: " . $conn->error;
}
?>