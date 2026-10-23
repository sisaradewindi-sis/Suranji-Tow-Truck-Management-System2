 <?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login/login.php");
    exit;
}

$full_name = $_SESSION["full_name"] ?? "User";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>User Dashboard - SURANJI TOW TRUCK SERVICE</title>

     <link rel="stylesheet" href="dashboard.css?v=2">
</head>

<body>

    <!-- Header -->
    <header class="dashboard-header">

        <div class="brand">
            <div class="brand-icon">S</div>

            <div>
                <h1>SURANJI TOW TRUCK SERVICE</h1>
                <p>Your trusted vehicle transport service</p>
            </div>
        </div>

        <a href="notifications.php" class="notification-link">
            <span class="bell-icon">🔔</span>
            <span>Notifications</span>
        </a>

    </header>

    <!-- Main Dashboard -->
    <main class="dashboard-container">

        <!-- Welcome Section -->
        <section class="welcome-section">

            <div>
                <p class="welcome-label">CUSTOMER DASHBOARD</p>

                <h2>
                    Welcome,
                    <?php echo htmlspecialchars($full_name); ?>!
                </h2>

                <p>
                    Welcome to Suranji Tow Truck Service.
                    How can we help you today?
                </p>
            </div>

            <div class="welcome-icon">🚚</div>

        </section>

        <!-- Dashboard Cards -->
        <section class="dashboard-grid">

            <!-- Request Card -->
            <div class="dashboard-card request-card">

                <div class="card-icon">🚛</div>

                <h3>Request a Tow Truck</h3>

                <p>
                    Is your vehicle broken down?
                    Submit a request to transport your vehicle
                    to a garage or your preferred location.
                </p>

                <a href="request.php" class="primary-btn">
                    Make a Request →
                </a>

            </div>

            <!-- Notification Card -->
            <div class="dashboard-card notification-card">

                <div class="card-icon">🔔</div>

                <h3>Notifications</h3>

                <p>
                    Check updates about your requests,
                    request status and tow truck arrangements.
                </p>

                <a href="notifications.php" class="secondary-btn">
                    View Notifications →
                </a>

            </div>

            <!-- Request History Card -->
            <div class="dashboard-card history-card">

                <div class="card-icon">📋</div>

                <h3>My Requests</h3>

                <p>
                    View your tow truck requests and
                    check their current status.
                </p>

                <a href="my_requests.php" class="secondary-btn">
                    View My Requests →
                </a>

            </div>

        </section>

        <!-- Information Section -->
        <section class="info-section">

            <h3>How to Use Our Service</h3>

            <div class="info-steps">

                <div class="info-step">
                    <span>1</span>
                    <p>Submit your tow truck request.</p>
                </div>

                <div class="info-step">
                    <span>2</span>
                    <p>Wait for the operator to arrange a truck.</p>
                </div>

                <div class="info-step">
                    <span>3</span>
                    <p>Check your notifications for updates.</p>
                </div>

            </div>

        </section>

        <!-- Logout -->
        <div class="logout-section">

            <form action="../logout.php" method="post">
                <button type="submit" class="logout-btn">
                    Logout
                </button>
            </form>

        </div>

    </main>

    <!-- Footer -->
    <footer class="footer">

        <div class="footer-line"></div>

        <h3>SURANJI TOW TRUCK SERVICE</h3>

        <p>Your trusted partner for vehicle transport.</p>

        <p>
            &copy; 2026 Suranji Tow Truck Service.
            All rights reserved.
        </p>

    </footer>

    <script src="dashboard.js" defer></script>

</body>
</html>