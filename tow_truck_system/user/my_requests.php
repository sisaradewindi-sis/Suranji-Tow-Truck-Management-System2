<?php
 session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login/login.php");
    exit;
}

require_once "../db.php";

$user_id = (int) $_SESSION["user_id"];
$full_name = $_SESSION["full_name"] ?? "Customer";

$vehicle_names = [
    "heavy" => "Heavy Vehicles",
    "car_van" => "Cars & Vans",
    "motorcycle_threewheel" => "Motorcycles & Three-wheelers"
];

$status_names = [
    "Pending" => "Pending",
    "Accepted" => "Accepted",
    "Assigned" => "Assigned",
    "In Progress" => "In Progress",
    "Completed" => "Completed",
    "Cancelled" => "Cancelled"
];

$sql = "SELECT *
        FROM tow_requests
        WHERE customer_user_id = ?
        ORDER BY request_id DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Unable to load requests. Please check the database query.");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$requests = [];

while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

$stmt->close();

function e($value) {
    return htmlspecialchars((string) ($value ?? ""), ENT_QUOTES, "UTF-8");
}

function showDate($date) {
    if (empty($date)) {
        return "Not available";
    }

    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return "Not available";
    }

    return date("d M Y, h:i A", $timestamp);
}

function getStatusClass($status) {
    $status = strtolower(trim((string) $status));
    $status = str_replace(" ", "-", $status);

    $allowed = [
        "pending",
        "accepted",
        "assigned",
        "in-progress",
        "completed",
        "cancelled"
    ];

    return in_array($status, $allowed, true)
        ? $status
        : "pending";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Requests | Suranji Tow Truck Service</title>

    <link rel="stylesheet" href="my_requests.css?v=2">
</head>

<body>

<header class="topbar">
    <div class="brand">
        SURANJI <span>TOW TRUCK SERVICE</span>
    </div>

    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="request.php">Request a Tow</a>
        <a href="my_requests.php" class="active">My Requests</a>
        <a href="../login/logout.php">Logout</a>
    </nav>
</header>

<main class="page-container">

    <section class="page-heading">
        <p class="small-heading">CUSTOMER PORTAL</p>

        <h1>My Tow Requests</h1>

        <p class="welcome-text">
            Hello, <?= e($full_name) ?>!
            View your tow requests, distance and service fees here.
        </p>
    </section>

    <section class="summary-card">
        <div>
            <span>Total Requests</span>
            <strong><?= count($requests) ?></strong>
        </div>

        <div>
            <span>Pending Requests</span>
            <strong>
                <?php
                $pending_count = 0;

                foreach ($requests as $item) {
                    if (
                        strtolower(trim($item["status"] ?? "pending"))
                        === "pending"
                    ) {
                        $pending_count++;
                    }
                }

                echo $pending_count;
                ?>
            </strong>
        </div>
    </section>

    <?php if (empty($requests)): ?>

        <section class="empty-state">
            <div class="empty-icon">🚚</div>

            <h2>No Tow Requests Yet</h2>

            <p>
                You have not submitted a tow request.
                Create your first request to see it here.
            </p>

            <a href="request.php" class="primary-button">
                Request a Tow Truck
            </a>
        </section>

    <?php else: ?>

        <div class="requests-heading">
            <h2>Your Requests</h2>
            <span><?= count($requests) ?> request(s)</span>
        </div>

        <?php foreach ($requests as $request): ?>

            <?php
            $request_id = $request["request_id"] ?? "";
            $type = $request["vehicle_type"] ?? "";

            $vehicle_name = $vehicle_names[$type] ?? $type;

            $distance = (
                isset($request["distance_km"]) &&
                $request["distance_km"] !== "" &&
                $request["distance_km"] !== null
            )
                ? (float) $request["distance_km"]
                : null;

            $total_fee = (
                isset($request["total_fee"]) &&
                $request["total_fee"] !== "" &&
                $request["total_fee"] !== null
            )
                ? (float) $request["total_fee"]
                : null;

            $status = $request["status"] ?? "Pending";

            $status_class = getStatusClass($status);

            $pickup = $request["pickup_location"] ?? "";
            $destination = $request["destination"] ?? "";

            $created_at = $request["created_at"] ?? "";

            $vehicle_number = $request["vehicle_number"] ?? "";
            $problem = $request["problem_description"] ?? "";

            $additional_details =
                $request["additional_details"] ?? "";

            $customer_phone = $request["customer_phone"] ?? "";

            $map_location = trim($pickup);

            $map_url = "https://www.openstreetmap.org/search?query="
                . rawurlencode($map_location);
            ?>

            <article class="request-card">

                <div class="request-header">
                    <div>
                        <p class="request-label">TOW REQUEST</p>

                        <h3>
                            Request #<?= e($request_id) ?>
                        </h3>

                        <p class="request-date">
                            <?= e(showDate($created_at)) ?>
                        </p>
                    </div>

                    <span class="status <?= e($status_class) ?>">
                        <?= e($status_names[$status] ?? $status) ?>
                    </span>
                </div>

                <div class="section-title">
                    <span>01</span>
                    <h3>Vehicle Details</h3>
                </div>

                <div class="details-grid">

                    <div class="detail-item">
                        <span>Vehicle Category</span>
                        <strong><?= e($vehicle_name) ?></strong>
                    </div>

                    <div class="detail-item">
                        <span>Vehicle Number</span>
                        <strong>
                            <?= e($vehicle_number ?: "Not provided") ?>
                        </strong>
                    </div>

                    <div class="detail-item full-width">
                        <span>Problem Description</span>
                        <strong>
                            <?= e($problem ?: "Not provided") ?>
                        </strong>
                    </div>

                </div>

                <div class="section-title">
                    <span>02</span>
                    <h3>Pickup & Destination</h3>
                </div>

                <div class="location-box">

                    <div class="location-item">
                        <span class="location-dot pickup-dot"></span>

                        <div>
                            <span class="location-label">
                                Pickup Location
                            </span>

                            <strong>
                                <?= e($pickup ?: "Not provided") ?>
                            </strong>
                        </div>
                    </div>

                    <div class="location-item">
                        <span class="location-dot destination-dot"></span>

                        <div>
                            <span class="location-label">
                                Destination
                            </span>

                            <strong>
                                <?= e($destination ?: "Not provided") ?>
                            </strong>
                        </div>
                    </div>

                </div>

                <?php if ($map_location !== ""): ?>

                    <div class="map-container">
                        <iframe
                            src="https://www.openstreetmap.org/export/embed.html?search=<?= rawurlencode($map_location) ?>"
                            loading="lazy"
                            title="Pickup location map">
                        </iframe>

                        <a
                            href="<?= e($map_url) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="map-link">
                            Open pickup location map
                        </a>
                    </div>

                <?php endif; ?>

                <?php if (!empty($additional_details)): ?>

                    <div class="additional-details">
                        <span>Additional Details</span>

                        <p><?= e($additional_details) ?></p>
                    </div>

                <?php endif; ?>

                <div class="section-title">
                    <span>03</span>
                    <h3>Distance & Service Fee</h3>
                </div>

                <div class="fee-card">

                    <div class="fee-detail">
                        <span>Vehicle Category</span>

                        <strong>
                            <?= e($vehicle_name) ?>
                        </strong>
                    </div>

                    <div class="fee-detail">
                        <span>Travel Distance</span>

                        <strong>
                            <?php if ($distance !== null): ?>
                                <?= number_format($distance, 2) ?> km
                            <?php else: ?>
                                Not available
                            <?php endif; ?>
                        </strong>
                    </div>

                    <div class="fee-detail">
                        <span>Total Service Fee</span>

                        <strong class="fee-amount">
                            <?php if ($total_fee !== null): ?>
                                Rs. <?= number_format($total_fee, 2) ?>
                            <?php else: ?>
                                Not available
                            <?php endif; ?>
                        </strong>
                    </div>

                    <?php
                    if ($distance === null || $total_fee === null):
                    ?>

                        <p class="fee-warning">
                            Distance or fee was not saved for this request.
                            Older requests may not contain these values.
                        </p>

                    <?php endif; ?>

                </div>

                <div class="section-title">
                    <span>04</span>
                    <h3>Request Status</h3>
                </div>

                <div class="status-message">

                    <?php if ($status_class === "pending"): ?>

                        <span class="status-icon">⏳</span>

                        <div>
                            <strong>Waiting for confirmation</strong>

                            <p>
                                Your request has been submitted.
                                Please wait for the service team to respond.
                            </p>
                        </div>

                    <?php elseif ($status_class === "accepted"): ?>

                        <span class="status-icon">✓</span>

                        <div>
                            <strong>Request Accepted</strong>

                            <p>
                                Your tow request has been accepted.
                            </p>
                        </div>

                    <?php elseif ($status_class === "assigned"): ?>

                        <span class="status-icon">🚚</span>

                        <div>
                            <strong>Driver Assigned</strong>

                            <p>
                                A driver has been assigned to your request.
                            </p>
                        </div>

                    <?php elseif ($status_class === "in-progress"): ?>

                        <span class="status-icon">🚛</span>

                        <div>
                            <strong>Service In Progress</strong>

                            <p>
                                Your vehicle transport is in progress.
                            </p>
                        </div>

                    <?php elseif ($status_class === "completed"): ?>

                        <span class="status-icon">✓</span>

                        <div>
                            <strong>Request Completed</strong>

                            <p>
                                Your tow service has been completed.
                            </p>
                        </div>

                    <?php elseif ($status_class === "cancelled"): ?>

                        <span class="status-icon">!</span>

                        <div>
                            <strong>Request Cancelled</strong>

                            <p>
                                This request has been cancelled.
                            </p>
                        </div>

                    <?php else: ?>

                        <span class="status-icon">ℹ️</span>

                        <div>
                            <strong><?= e($status) ?></strong>

                            <p>
                                Please contact the service team for updates.
                            </p>
                        </div>

                    <?php endif; ?>

                </div>

                <div class="request-footer">
                    <span>
                        Request #<?= e($request_id) ?>
                    </span>

                    <a href="request.php" class="secondary-button">
                        New Request
                    </a>
                </div>

            </article>

        <?php endforeach; ?>

    <?php endif; ?>

    <div class="back-area">
        <a href="dashboard.php" class="back-link">
            ← Back to Dashboard
        </a>
    </div>

</main>

<footer class="footer">
    <p>
        © <?= date("Y") ?> Suranji Tow Truck Service.
        All rights reserved.
    </p>
</footer>

</body>
</html>