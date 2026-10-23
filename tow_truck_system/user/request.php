 <?php
session_start();

// ==========================================
// LOGIN CHECK START
// ==========================================
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login/login.php");
    exit;
}
// LOGIN CHECK END
// ==========================================


// ==========================================
// DATABASE CONNECTION START
// ==========================================
require_once "../db.php";
// DATABASE CONNECTION END
// ==========================================

$full_name = $_SESSION["full_name"] ?? "Customer";
$success = "";
$error = "";


// ==========================================
// VEHICLE CATEGORY FEES
// Fee per kilometre (km)
// ==========================================
$fees = [
    "heavy" => 2000,
    "car_van" => 1250,
    "motorcycle_threewheel" => 800
];


// ==========================================
// SAVE TOW REQUEST START
// ==========================================
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["submit_request"])
) {
    $user_id = (int) $_SESSION["user_id"];

    $vehicle_type = trim($_POST["vehicle_type"] ?? "");
    $brand = trim($_POST["brand"] ?? "");
    $model = trim($_POST["model"] ?? "");
    $registration = trim($_POST["registration"] ?? "");
    $colour = trim($_POST["colour"] ?? "");
    $problem = trim($_POST["problem"] ?? "");
    $pickup = trim($_POST["pickup"] ?? "");
    $destination = trim($_POST["destination"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $details = trim($_POST["details"] ?? "");

    $distance_km = filter_var(
        $_POST["distance_km"] ?? null,
        FILTER_VALIDATE_FLOAT
    );

    $submitted_fee = filter_var(
        $_POST["total_fee"] ?? null,
        FILTER_VALIDATE_FLOAT
    );

    $allowedProblems = [
        "Engine Problem",
        "Accident",
        "Tyre Problem",
        "Battery Problem",
        "Other"
    ];

    if (
        !isset($fees[$vehicle_type]) ||
        $brand === "" ||
        $model === "" ||
        $registration === "" ||
        !in_array($problem, $allowedProblems, true) ||
        $pickup === "" ||
        $destination === "" ||
        $phone === "" ||
        $distance_km === false ||
        $distance_km === null ||
        $distance_km <= 0 ||
        $distance_km > 10000 ||
        $submitted_fee === false ||
        $submitted_fee === null
    ) {
        $error = "Please fill in all required fields and calculate a valid route.";
    } else {
        // Calculate fee again using the server-side category rate.
        $distance_km = round($distance_km, 2);
        $total_fee = round($distance_km * $fees[$vehicle_type], 2);

        if (abs($submitted_fee - $total_fee) > 0.01) {
            $error = "The fee does not match the distance. Please calculate the route again.";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO tow_requests (
                    customer_user_id,
                    customer_name,
                    customer_phone,
                    vehicle_type,
                    vehicle_brand,
                    vehicle_model,
                    vehicle_registration,
                    vehicle_colour,
                    problem_description,
                    pickup_location,
                    destination,
                    additional_details,
                    distance_km,
                    total_fee
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            if ($stmt) {
                $stmt->bind_param(
                    "isssssssssssdd",
                    $user_id,
                    $full_name,
                    $phone,
                    $vehicle_type,
                    $brand,
                    $model,
                    $registration,
                    $colour,
                    $problem,
                    $pickup,
                    $destination,
                    $details,
                    $distance_km,
                    $total_fee
                );

                if ($stmt->execute()) {
                    $stmt->close();

                    header("Location: my_requests.php?submitted=1");
                    exit;
                } else {
                    error_log("Tow request error: " . $stmt->error);
                    $error = "The request could not be saved. Please try again.";
                    $stmt->close();
                }
            } else {
                error_log("Prepare error: " . $conn->error);
                $error = "The database query could not be prepared.";
            }
        }
    }
}
// SAVE TOW REQUEST END
// ==========================================

if (isset($_GET["submitted"])) {
    $success = "Your tow truck request was saved successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Request a Tow Truck - Suranji</title>

    <link rel="stylesheet" href="request.css?v=2">

    <!-- MAP LIBRARY CSS START -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >
    <!-- MAP LIBRARY CSS END -->
</head>

<body>

<header>
    <h1>SURANJI TOW TRUCK SERVICE</h1>
    <p>Customer Tow Truck Request</p>
</header>

<main class="container">

    <h2>Request a Tow Truck</h2>

    <p class="description">
        Hello,
        <?php echo htmlspecialchars($full_name, ENT_QUOTES, "UTF-8"); ?>!
        Enter your vehicle and transport details below.
    </p>

    <!-- SUCCESS MESSAGE START -->
    <?php if ($success !== ""): ?>
        <p class="message success">
            <?php echo htmlspecialchars($success, ENT_QUOTES, "UTF-8"); ?>
        </p>
    <?php endif; ?>
    <!-- SUCCESS MESSAGE END -->

    <!-- ERROR MESSAGE START -->
    <?php if ($error !== ""): ?>
        <p class="message error">
            <?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?>
        </p>
    <?php endif; ?>
    <!-- ERROR MESSAGE END -->


    <!-- ==========================================
         TOW REQUEST FORM START
         ========================================== -->
    <form id="requestForm" method="POST">

        <input type="hidden" name="submit_request" value="1">

        <div class="form-grid">

            <!-- VEHICLE CATEGORY START -->
            <div class="field full-width">
                <label for="vehicle_type">Vehicle Category *</label>

                <select id="vehicle_type" name="vehicle_type" required>
                    <option value="">-- Select Vehicle Category --</option>
                    <option value="heavy">Heavy Vehicles</option>
                    <option value="car_van">Cars & Vans</option>
                    <option value="motorcycle_threewheel">
                        Motorcycles & Three-wheelers
                    </option>
                </select>
            </div>
            <!-- VEHICLE CATEGORY END -->


            <!-- VEHICLE DETAILS START -->
            <div class="field">
                <label for="brand">Vehicle Brand *</label>
                <input type="text" id="brand" name="brand"
                       placeholder="e.g. Toyota" required>
            </div>

            <div class="field">
                <label for="model">Vehicle Model *</label>
                <input type="text" id="model" name="model"
                       placeholder="e.g. Corolla" required>
            </div>

            <div class="field">
                <label for="registration">Registration Number *</label>
                <input type="text" id="registration" name="registration"
                       placeholder="e.g. ABC-1234" required>
            </div>

            <div class="field">
                <label for="colour">Vehicle Colour</label>
                <input type="text" id="colour" name="colour"
                       placeholder="Enter vehicle colour">
            </div>
            <!-- VEHICLE DETAILS END -->


            <!-- VEHICLE PROBLEM START -->
            <div class="field full-width">
                <label for="problem">Vehicle Problem *</label>

                <select id="problem" name="problem" required>
                    <option value="">-- Select Problem --</option>
                    <option value="Engine Problem">Engine Problem</option>
                    <option value="Accident">Accident</option>
                    <option value="Tyre Problem">Tyre Problem</option>
                    <option value="Battery Problem">Battery Problem</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <!-- VEHICLE PROBLEM END -->


            <!-- PICKUP LOCATION START -->
            <div class="field full-width">
                <label for="pickup">Pickup Location *</label>

                <input type="text" id="pickup" name="pickup"
                       placeholder="e.g. Deniyaya Bus Stand" required>
            </div>
            <!-- PICKUP LOCATION END -->


            <!-- DESTINATION START -->
            <div class="field full-width">
                <label for="destination">Destination *</label>

                <input type="text" id="destination" name="destination"
                       placeholder="e.g. Suranji Service Center" required>
            </div>
            <!-- DESTINATION END -->


            <!-- CUSTOMER CONTACT START -->
            <div class="field full-width">
                <label for="phone">Contact Number *</label>

                <input type="tel" id="phone" name="phone"
                       placeholder="Enter your contact number" required>
            </div>
            <!-- CUSTOMER CONTACT END -->


            <!-- ADDITIONAL DETAILS START -->
            <div class="field full-width">
                <label for="details">Additional Details</label>

                <textarea id="details" name="details"
                          placeholder="Describe the problem, if needed"></textarea>
            </div>
            <!-- ADDITIONAL DETAILS END -->

        </div>


        <!-- ==========================================
             MAP SECTION START
             Pickup & Destination Map
             Route Distance (KM) Calculation
             Category-wise Total Fee Calculation
             ========================================== -->

        <section class="map-section">

            <h3>Pickup & Destination Map</h3>

            <p>
                Enter both locations above, then calculate the route.
            </p>

            <!-- MAP DISPLAY AREA -->
            <div id="routeMap"></div>

            <!-- CALCULATE ROUTE BUTTON -->
            <button type="button" id="calculateRoute" class="map-btn">
                Calculate Route & Distance
            </button>

            <!-- MAP STATUS MESSAGE -->
            <p id="mapMessage" role="status" aria-live="polite">
                Enter both locations to calculate the route.
            </p>

            <!-- ROUTE DISTANCE DISPLAY -->
            <div class="distance-box">
                <span>Route Distance</span>
                <strong id="distanceDisplay">-- km</strong>
            </div>

            <!-- TOTAL FEE DISPLAY -->
            <div class="distance-box">
                <span>Estimated Total Fee</span>
                <strong id="fee">Rs. 0.00</strong>
            </div>

            <p id="feeDescription">
                Select a vehicle category and calculate the route.
            </p>

            <!-- VALUES SENT TO PHP -->
            <input type="hidden" name="distance_km" id="distance_km">
            <input type="hidden" name="total_fee" id="total_fee">

        </section>

        <!-- ==========================================
             MAP SECTION END
             ========================================== -->


        <!-- SUBMIT REQUEST BUTTON -->
        <button type="submit" class="submit-btn">
            Submit Tow Truck Request
        </button>

    </form>
    <!-- TOW REQUEST FORM END -->


    <a href="dashboard.php" class="back-link">
        ← Back to Dashboard
    </a>

</main>


<!-- ==========================================
     MAP JAVASCRIPT LIBRARY START
     ========================================== -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- MAP JAVASCRIPT LIBRARY END -->


<script>
// ==========================================
// MAP JAVASCRIPT START
// 1. Initialize OpenStreetMap
// 2. Find Pickup and Destination
// 3. Calculate Driving Route Distance
// 4. Calculate Total Fee
// 5. Send Distance and Fee with Request
// ==========================================

const vehicleType = document.getElementById("vehicle_type");
const pickupInput = document.getElementById("pickup");
const destinationInput = document.getElementById("destination");
const calculateButton = document.getElementById("calculateRoute");
const mapMessage = document.getElementById("mapMessage");
const distanceDisplay = document.getElementById("distanceDisplay");
const feeDisplay = document.getElementById("fee");
const feeDescription = document.getElementById("feeDescription");
const distanceInput = document.getElementById("distance_km");
const totalFeeInput = document.getElementById("total_fee");
const requestForm = document.getElementById("requestForm");

const fees = {
    heavy: 200,
    car_van: 125,
    motorcycle_threewheel: 80
};

let routeMap = null;
let routeLayer = null;
let pickupMarker = null;
let destinationMarker = null;
let routeReady = false;


// Reset distance and fee when route details change
function resetRoute() {
    routeReady = false;
    distanceInput.value = "";
    totalFeeInput.value = "";
    distanceDisplay.textContent = "-- km";
    feeDisplay.textContent = "Rs. 0.00";
}


// Initialize the map
if (typeof L !== "undefined") {
    routeMap = L.map("routeMap").setView([6.9271, 79.8612], 8);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution: "&copy; OpenStreetMap contributors"
    }).addTo(routeMap);
} else {
    mapMessage.textContent =
        "Map could not load. Please check your internet connection.";
}


// Find a location using Nominatim
async function findLocation(address) {
    const url =
        "https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&countrycodes=lk&q="
        + encodeURIComponent(address + ", Sri Lanka");

    const response = await fetch(url, {
        headers: {
            "Accept": "application/json"
        }
    });

    if (!response.ok) {
        throw new Error("Location search failed. Please try again later.");
    }

    const results = await response.json();

    if (!results.length) {
        throw new Error(
            'Could not find "' + address +
            '". Please enter a more specific location.'
        );
    }

    return {
        lat: Number(results[0].lat),
        lon: Number(results[0].lon),
        name: results[0].display_name
    };
}


// Calculate the road route and total fee
async function calculateRoute() {
    if (!routeMap) {
        mapMessage.textContent =
            "Map is unavailable. Please check your internet connection.";
        return;
    }

    const pickup = pickupInput.value.trim();
    const destination = destinationInput.value.trim();
    const category = vehicleType.value;

    resetRoute();

    if (!pickup || !destination) {
        mapMessage.textContent =
            "Please enter both pickup and destination locations.";
        return;
    }

    if (!fees[category]) {
        mapMessage.textContent =
            "Please select a vehicle category first.";
        return;
    }

    calculateButton.disabled = true;
    calculateButton.textContent = "Calculating route...";
    mapMessage.textContent =
        "Finding locations and calculating the driving route...";

    try {
        // Find pickup location
        const start = await findLocation(pickup);

        // Avoid sending geocoding requests too quickly
        await new Promise(resolve => setTimeout(resolve, 1100));

        // Find destination
        const end = await findLocation(destination);

        // Ask OSRM for the driving route
        const routeUrl =
            "https://router.project-osrm.org/route/v1/driving/"
            + start.lon + "," + start.lat + ";"
            + end.lon + "," + end.lat
            + "?overview=full&geometries=geojson";

        const routeResponse = await fetch(routeUrl);

        if (!routeResponse.ok) {
            throw new Error(
                "The route service is unavailable. Please try again later."
            );
        }

        const routeData = await routeResponse.json();

        if (routeData.code !== "Ok" || !routeData.routes.length) {
            throw new Error(
                "No driving route was found between these locations."
            );
        }

        const route = routeData.routes[0];

        // Convert metres to kilometres and keep two decimal places
        const km = Math.round((route.distance / 1000) * 100) / 100;

        // Calculate category fee per kilometre
        const totalFee = Math.round(km * fees[category] * 100) / 100;

        // Remove previous route and markers
        if (routeLayer) {
            routeMap.removeLayer(routeLayer);
        }

        if (pickupMarker) {
            routeMap.removeLayer(pickupMarker);
        }

        if (destinationMarker) {
            routeMap.removeLayer(destinationMarker);
        }

        // Draw route on the map
        routeLayer = L.geoJSON(route.geometry, {
            style: {
                color: "#f97316",
                weight: 5
            }
        }).addTo(routeMap);

        // Add pickup marker
        pickupMarker = L.marker([start.lat, start.lon])
            .addTo(routeMap)
            .bindPopup("Pickup: " + start.name);

        // Add destination marker
        destinationMarker = L.marker([end.lat, end.lon])
            .addTo(routeMap)
            .bindPopup("Destination: " + end.name);

        // Zoom map to include both locations and route
        routeMap.fitBounds(routeLayer.getBounds(), {
            padding: [25, 25]
        });

        // Display distance
        distanceDisplay.textContent = km.toFixed(2) + " km";

        // Display total fee
        feeDisplay.textContent =
            "Rs. " + totalFee.toLocaleString("en-US", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

        feeDescription.textContent =
            "Rs. " + fees[category].toLocaleString("en-US")
            + " per km × " + km.toFixed(2) + " km";

        // Store values for PHP form submission
        distanceInput.value = km.toFixed(2);
        totalFeeInput.value = totalFee.toFixed(2);

        routeReady = true;

        mapMessage.textContent =
            "Route calculated successfully. Please check the route and fee.";

    } catch (error) {
        resetRoute();

        mapMessage.textContent =
            error.message || "Could not calculate the route. Please try again.";

    } finally {
        calculateButton.disabled = false;
        calculateButton.textContent = "Calculate Route & Distance";
    }
}


// Calculate route when button is clicked
calculateButton.addEventListener("click", calculateRoute);


// If the pickup location changes, recalculate the route
pickupInput.addEventListener("input", function() {
    resetRoute();
    mapMessage.textContent =
        "Pickup location changed. Please calculate the route again.";
});


// If destination changes, recalculate the route
destinationInput.addEventListener("input", function() {
    resetRoute();
    mapMessage.textContent =
        "Destination changed. Please calculate the route again.";
});


// If category changes, calculate the route and fee again
vehicleType.addEventListener("change", function() {
    resetRoute();
    feeDescription.textContent =
        "Please calculate the route for the selected category.";
    mapMessage.textContent =
        "Vehicle category changed. Please calculate the route again.";
});


// Prevent submission without a calculated route
requestForm.addEventListener("submit", function(event) {
    if (!routeReady || !distanceInput.value || !totalFeeInput.value) {
        event.preventDefault();

        mapMessage.textContent =
            "Please calculate the route before submitting your request.";

        calculateButton.scrollIntoView({
            behavior: "smooth",
            block: "center"
        });
    }
});

// ==========================================
// MAP JAVASCRIPT END
// ==========================================
</script>

</body>
</html>