 <?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $_SESSION = [];
    session_destroy();

    header("Location: login/login.php");
    exit;
}

header("Location: login/login.php");
exit;
?>