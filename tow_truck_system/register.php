 <?php
session_start();
require_once "db.php";

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($full_name === "" || $email === "" || $password === "") {
        $message = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } else {
        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "This email is already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = "customer";

            $stmt = $conn->prepare(
                "INSERT INTO users
                (full_name, email, phone, password, role)
                VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssss",
                $full_name,
                $email,
                $phone,
                $hash,
                $role
            );

            if ($stmt->execute()) {
                $message = "Registration successful! You can now log in.";
                $success = true;
            } else {
                $message = "Registration failed. Please try again.";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - SURANJI TOW TRUCK SERVICE</title>

    <link rel="stylesheet" href="register.css">
</head>
<body>

    <header class="top-bar">
        <div class="brand">
            <span class="brand-icon">S</span>
            <span>SURANJI TOW TRUCK SERVICE</span>
        </div>

        <a class="login-link" href="login/login.php">
            Login
        </a>
    </header>

    <main class="page-container">

        <section class="register-card">

            <div class="card-heading">
                <div class="heading-icon">+</div>
                <h1>Create an Account</h1>
                <p>Join Suranji Tow Truck Service today.</p>
            </div>

            <?php if ($message !== ""): ?>
                <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>

                <a class="submit-btn" href="login/login.php">
                    Go to Login →
                </a>

            <?php else: ?>

                <form method="post" action="">

                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            placeholder="Enter your full name"
                            value="<?php echo htmlspecialchars($full_name ?? ''); ?>"
                            autocomplete="name"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email address"
                            value="<?php echo htmlspecialchars($email ?? ''); ?>"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            placeholder="Enter your phone number"
                            value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                            autocomplete="tel"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Minimum 8 characters"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >
                        <small>Use at least 8 characters.</small>
                    </div>

                    <button class="submit-btn" type="submit">
                        Create Account →
                    </button>

                </form>

                <p class="bottom-text">
                    Already have an account?
                    <a href="login/login.php">Login here</a>
                </p>

            <?php endif; ?>

        </section>

    </main>

    <footer class="footer">
        <div class="footer-line"></div>
        <p>© 2026 SURANJI TOW TRUCK SERVICE</p>
        <p>Reliable Tow Truck Service, When You Need It.</p>
    </footer>

</body>
</html>