<?php
require_once "db.php";

$name = "Test User";
$email = "test@suranji.com";
$password = password_hash("Test1234!", PASSWORD_DEFAULT);
$role = "user";

$stmt = $conn->prepare(
    "INSERT INTO users (full_name, email, password, role)
     VALUES (?, ?, ?, ?)"
);

$stmt->bind_param("ssss", $name, $email, $password, $role);

if ($stmt->execute()) {
    echo "Test user created successfully!";
} else {
    echo "Could not create test user.";
}
?>