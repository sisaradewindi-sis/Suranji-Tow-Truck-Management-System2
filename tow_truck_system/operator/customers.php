 <?php
session_start();

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login/login.php");
    exit;
}

// Check operator role
if (strtolower(trim($_SESSION["role"] ?? "")) !== "operator") {
    exit("Access denied. Operator only.");
}

// Database connection
require_once "../db.php";

$search = "";
$customers = [];
$error_message = "";
$searched = false;

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
    $searched = true;

    if ($search !== "") {
        $keyword = "%" . $search . "%";

        $sql = "SELECT id, full_name, email, phone, created_at
                FROM users
                WHERE LOWER(TRIM(role)) = 'customer'
                AND (
                    full_name LIKE ?
                    OR email LIKE ?
                    OR phone LIKE ?
                )
                ORDER BY full_name ASC";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error_message = "Search query failed: " . $conn->error;
        } else {
            $stmt->bind_param(
                "sss",
                $keyword,
                $keyword,
                $keyword
            );

            if ($stmt->execute()) {
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    $customers[] = $row;
                }

                $result->free();
            } else {
                $error_message = "Search failed: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}

function safeText($value) {
    return htmlspecialchars(
        (string)($value ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Find Customer | Suranji Tow Truck Service</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #414040;
            color: #252525;
            line-height: 1.6;
        }

        header {
            background: #171717;
            color: white;
            padding: 22px 5%;
            border-bottom: 4px solid #ff8500;
        }

        .brand {
            font-size: 22px;
            font-weight: bold;
        }

        .brand span {
            color: #ff8500;
        }

        .container {
            width: 92%;
            max-width: 1100px;
            margin: 32px auto;
            min-height: 65vh;
        }

        .back-link {
            display: inline-block;
            color: white;
            text-decoration: none;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .back-link:hover {
            color: #ff8500;
        }

        .heading {
            color: white;
            margin-bottom: 24px;
        }

        .heading h1 {
            font-size: 29px;
            margin-bottom: 8px;
        }

        .heading p {
            color: #dddddd;
        }

        .panel {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 3px 12px #00000020;
        }

        .panel h2 {
            font-size: 21px;
            margin-bottom: 18px;
        }

        .search-form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .search-form input {
            flex: 1;
            min-width: 220px;
            padding: 13px 14px;
            border: 1px solid #cccccc;
            border-radius: 6px;
            font-size: 15px;
        }

        .search-button {
            padding: 13px 22px;
            border: none;
            border-radius: 6px;
            background: #ff8500;
            color: #171717;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .search-button:hover {
            background: #e97800;
        }

        .clear-link {
            display: inline-block;
            margin-top: 12px;
            color: #555555;
            text-decoration: none;
        }

        .clear-link:hover {
            color: #ff8500;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 650px;
            border-collapse: collapse;
        }

        th, td {
            text-align: left;
            padding: 13px 12px;
            border-bottom: 1px solid #e6e6e6;
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        th {
            background: #222222;
            color: white;
        }

        tbody tr:hover {
            background: #fff4e6;
        }

        .customer-name {
            font-weight: bold;
        }

        .notice {
            background: #fff0da;
            color: #754000;
            padding: 14px;
            border-radius: 6px;
            margin-bottom: 16px;
            overflow-wrap: anywhere;
        }

        .empty-message {
            color: #666666;
        }

        .result-count {
            color: #555555;
            font-size: 14px;
            margin-bottom: 16px;
        }

        footer {
            background: #171717;
            color: #cccccc;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            header {
                padding: 18px 4%;
            }

            .container {
                width: 94%;
                margin: 22px auto;
            }

            .heading h1 {
                font-size: 24px;
            }

            .panel {
                padding: 17px;
            }

            .search-form {
                flex-direction: column;
            }

            .search-form input,
            .search-button {
                width: 100%;
                min-width: 0;
            }
        }
    </style>
</head>

<body>

<header>
    <div class="brand">
        SURANJI <span>TOW TRUCK SERVICE</span>
    </div>
</header>

<main class="container">

    <a class="back-link" href="dashboard.php">
        &larr; Back to Operator Dashboard
    </a>

    <section class="heading">
        <h1>Find Customer</h1>
        <p>Search registered customers by name, phone number or email.</p>
    </section>

    <section class="panel">
        <h2>Search for a Customer</h2>

        <form class="search-form" method="GET" action="customers.php">
            <input
                type="text"
                name="search"
                placeholder="Enter name, phone number or email"
                value="<?= safeText($search) ?>"
                required
            >

            <button class="search-button" type="submit">
                Search Customer
            </button>
        </form>

        <?php if ($searched): ?>
            <a class="clear-link" href="customers.php">Clear Search</a>
        <?php endif; ?>
    </section>

    <section class="panel">
        <h2>Customer Search Results</h2>

        <?php if ($error_message !== ""): ?>

            <div class="notice">
                <?= safeText($error_message) ?>
            </div>

        <?php elseif (!$searched): ?>

            <p class="empty-message">
                Enter a customer name, phone number or email above to search.
            </p>

        <?php elseif ($search === ""): ?>

            <p class="empty-message">Please enter something to search.</p>

        <?php elseif (count($customers) === 0): ?>

            <div class="notice">
                No registered customers found for
                "<?= safeText($search) ?>".
                Please check the name, email or phone number.
            </div>

        <?php else: ?>

            <p class="result-count">
                Found <?= count($customers) ?> customer(s).
            </p>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Customer ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Registered Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?= safeText($customer["id"]) ?></td>
                                <td class="customer-name">
                                    <?= safeText($customer["full_name"]) ?>
                                </td>
                                <td><?= safeText($customer["email"]) ?></td>
                                <td><?= safeText($customer["phone"]) ?></td>
                                <td><?= safeText($customer["created_at"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </section>

</main>

<footer>
    &copy; <?= date("Y") ?> Suranji Tow Truck Service. All rights reserved.
</footer>

</body>
</html>