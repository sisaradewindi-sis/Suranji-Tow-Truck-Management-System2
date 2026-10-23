 <?php
$host = "localhost";
$username = "tow_user";
$password = "TowTruck#2026Local";
$database = "tow_truck_db";
$port = 3308;

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database,
    $port
);

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");
?>