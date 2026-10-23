<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login/login.php");
    exit;
}

if (($_SESSION["role"] ?? "") !== "operator") {
    exit("Access denied. Operator only.");
}

require_once "../db.php";

$operator_name = $_SESSION["full_name"] ?? "Operator";

$total_requests = 0;
$pending_requests = 0;
$completed_requests = 0;
$requests = [];
$error_message = "";

try {
    $result = $conn->query(
        "SELECT COUNT(*) AS total FROM tow_requests"
    );
    if ($result) {
        $total_requests = (int)$result->fetch_assoc()["total"];
    }

    $result = $conn->query(
        "SELECT COUNT(*) AS total FROM tow_requests
         WHERE status = 'Pending'"
    );
    if ($result) {
        $pending_requests = (int)$result->fetch_assoc()["total"];
    }

    $result = $conn->query(
        "SELECT COUNT(*) AS total FROM tow_requests
         WHERE status = 'Completed'"
    );
    if ($result) {
        $completed_requests = (int)$result->fetch_assoc()["total"];
    }

    $result = $conn->query(
        "SELECT * FROM tow_requests ORDER BY 1 DESC"
    );

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
    }
} catch (Throwable $e) {
    $error_message =
        "Request data load karanna bari una. Database table eke columns saha connection eka check karanna.";
}

function safeText($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operator Dashboard | Suranji Tow Truck Service</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #4c4b4b;
            color: #252525;
        }

        header {
            background: #171717;
            color: white;
            padding: 20px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            border-bottom: 4px solid #ff8500;
        }

        .brand {
            font-size: 21px;
            font-weight: bold;
        }

        .brand span {
            color: #ff8500;
        }

        .welcome {
            margin-top: 7px;
            color: #cccccc;
            font-size: 14px;
        }

        .logout {
            background: #ff8500;
            color: #171717;
            padding: 10px 17px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }

        .container {
            width: 92%;
            max-width: 1250px;
            margin: 30px auto;
        }

        .page-heading {
            margin-bottom: 22px;
        }

        .page-heading h1 {
            font-size: 29px;
            margin-bottom: 8px;
        }

        .page-heading p {
            color: #101010;
            line-height: 1.6;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: white;
            padding: 23px;
            border-radius: 10px;
            border-left: 5px solid #ff8500;
            box-shadow: 0 3px 12px #0000000d;
        }

        .stat-card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .stat-card h2 {
            font-size: 32px;
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 28px;
        }

        .action-btn {
            display: inline-block;
            padding: 13px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            background: #ff8500;
            color: #171717;
        }

        .action-btn.secondary {
            background: #292929;
            color: white;
        }

        .section {
            background: white;
            border-radius: 10px;
            padding: 23px;
            margin-bottom: 25px;
            box-shadow: 0 3px 12px #0000000d;
        }

        .section h2 {
            font-size: 21px;
            margin-bottom: 17px;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        th, td {
            text-align: left;
            padding: 13px 12px;
            border-bottom: 1px solid #e6e6e6;
            font-size: 14px;
            vertical-align: top;
        }

        th {
            background: #222;
            color: white;
        }

        .status {
            display: inline-block;
            background: #fff0d9;
            color: #8a4800;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .notice {
            background: #fff2df;
            color: #754000;
            padding: 13px;
            border-radius: 6px;
            margin-bottom: 18px;
            line-height: 1.6;
        }

        .empty {
            color: #777;
            padding: 15px 0;
        }

        footer {
            background: #171717;
            color: #ccc;
            text-align: center;
            padding: 19px;
            margin-top: 40px;
            font-size: 13px;
        }

        @media (max-width: 700px) {
            .stats {
                grid-template-columns: 1fr;
            }

            .page-heading h1 {
                font-size: 24px;
            }

            .section {
                padding: 15px;
            }

            header {
                padding: 18px 4%;
            }

            .container {
                width: 94%;
                margin-top: 22px;
            }
        }
    </style>
</head>
<body>

<header>
    <div>
        <div class="brand">SURANJI <span>TOW TRUCK SERVICE</span></div>
        <p class="welcome">
            Welcome, <?= safeText($operator_name) ?>!
        </p>
    </div>

    <a class="logout" href="../logout.php">Logout</a>
</header>

<main class="container">

    <div class="page-heading">
        <h1>Operator Dashboard</h1>
        <p>Manage customer tow requests and monitor service activities.</p>
    </div>

    <div class="stats">
        <div class="stat-card">
            <p>Total Tow Requests</p>
            <h2><?= $total_requests ?></h2>
        </div>

        <div class="stat-card">
            <p>Pending Requests</p>
            <h2><?= $pending_requests ?></h2>
        </div>

        <div class="stat-card">
            <p>Completed Requests</p>
            <h2><?= $completed_requests ?></h2>
        </div>
    </div>

    <div class="actions">
         <a class="action-btn" href="../user/request.php">
    + Create Customer Request
</a>

        <a class="action-btn secondary" href="customers.php">
            Find Customer
        </a>
    </div>

    <section class="section">
        <h2>All Tow Requests</h2>

        <?php if ($error_message !== ""): ?>
            <div class="notice"><?= safeText($error_message) ?></div>
        <?php endif; ?>

        <?php if (count($requests) > 0): ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Vehicle</th>
                            <th>Pickup Location</th>
                            <th>Destination</th>
                            <th>Distance (km)</th>
                            <th>Total Fee (Rs.)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td>
                                    <?= safeText(
                                        $request["request_id"]
                                        ?? $request["id"]
                                        ?? "-"
                                    ) ?>
                                </td>
                                <td><?= safeText($request["customer_name"] ?? "-") ?></td>
                                <td><?= safeText($request["customer_phone"] ?? "-") ?></td>
                                <td>
                                    <?= safeText($request["vehicle_type"] ?? "-") ?>
                                    <br>
                                    <?= safeText($request["vehicle_brand"] ?? "") ?>
                                    <?= safeText($request["vehicle_model"] ?? "") ?>
                                </td>
                                <td><?= safeText($request["pickup_location"] ?? "-") ?></td>
                                <td><?= safeText($request["destination"] ?? "-") ?></td>
                                <td><?= safeText($request["distance_km"] ?? "-") ?></td>
                                <td><?= safeText($request["total_fee"] ?? "-") ?></td>
                                <td>
                                    <span class="status">
                                        <?= safeText($request["status"] ?? "Unknown") ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="empty">No tow requests found yet.</p>
        <?php endif; ?>
    </section>

</main>

<footer>
    &copy; <?= date("Y") ?> Suranji Tow Truck Service. All rights reserved.
</footer>

</body>
</html>