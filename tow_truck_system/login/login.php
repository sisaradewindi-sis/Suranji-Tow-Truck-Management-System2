 <?php
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once "../db.php";

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
        "SELECT id, full_name, email, password, role
         FROM users
         WHERE email = ?"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
   
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user["password"])) {
             $_SESSION["user_id"] = $user["id"];
$_SESSION["full_name"] = $user["full_name"];
$_SESSION["role"] = $user["role"];

$role = strtolower(trim($user["role"]));

if ($role === "customer") {
    header("Location: ../user/dashboard.php");
    exit;
} elseif ($role === "operator") {
    header("Location: ../operator/dashboard.php");
    exit;
} elseif ($role === "driver") {
    header("Location: ../driver/dashboard.php");
    exit;
} else {
    session_destroy();
    $error = "Invalid user role. Please contact the administrator.";
}
        }
    }

    $error = "Invalid email address or password.";
}
?>
 
 <!DOCTYPE html>
<html lang="en">
<head>
    <!-- Website basic information -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Browser tab title -->
    <title>Login - SURANJI TOW TRUCK SERVICE</title>

    <!-- Connect the login CSS file -->
    <link rel="stylesheet" href="login.css">
</head>
<body>

    <!-- Main login container -->
    <div class="login-container">

        <!-- Display the website name -->
        <h1>SURANJI TOW TRUCK SERVICE</h1>

        <!-- Display the login heading -->
        <h2>Login</h2>
        <?php if (!empty($error)): ?>
    <p style="color: red;">
        <?php echo htmlspecialchars($error); ?>
    </p>
<?php endif; ?>

        <!-- Create a form to enter login details -->
        <form action="" method="post">

            <!-- Email address input field -->
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>

            <!-- Password input field -->
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <!-- Login button -->
            <button type="submit">Login</button>

        </form>

        <!-- Display the footer message -->
         <p>
    Don't have an account?
    <a href="../register.php">Register here</a>
</p>
        <div class="login-footer">
            SURANJI TOW TRUCK SERVICE
        </div>

    </div>

</body>
</html>