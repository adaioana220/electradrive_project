<?php

session_start();

// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$database = "electradrive";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    echo "<script>alert('You need to be logged in to rent a car.'); window.location.href = 'login.php';</script>";
    exit();
}

// Fetch user details
$sql = "SELECT user_id, name, surname FROM users WHERE user_email = ?";
$stmt = $conn->prepare($sql);
$user_email = $_SESSION['user_email'];
$stmt->bind_param("s", $user_email);
$stmt->execute();
$user_result = $stmt->get_result();
$user_details = $user_result->fetch_assoc();

if (!$user_details) {
    // Handle case where user details are not found
    echo "<script>alert('User details not found. Please login.'); window.location.href = 'login.php';</script>";
    exit();
}

// Fetch pickup points
$sql = "SELECT pickup_point_id, pickup_point_address FROM pickup_points";
$result = $conn->query($sql);
$pickup_points = [];
while ($row = $result->fetch_assoc()) {
    $pickup_points[] = $row;
}

// Initialize variables
$total_cost = "0.00";
$pickup_date = "";
$pickup_time = "";
$dropoff_date = "";
$dropoff_time = "";
$payment_method = "";
$pickup_point_id = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['calculate_cost'])) {
        $pickup_date = $_POST['pickup_date'];
        $pickup_time = $_POST['pickup_time'];
        $dropoff_date = $_POST['dropoff_date'];
        $dropoff_time = $_POST['dropoff_time'];
        $carmodel_id = $_SESSION['carmodel_id'];

        // Perform validations
        $today = date("Y-m-d");
        if (empty($pickup_date) || empty($dropoff_date) || empty($pickup_time) || empty($dropoff_time)) {
            echo "<script>alert('Please select both pickup and dropoff dates and times.'); window.history.back();</script>";
            exit();
        } else {
            // Validate working hours
            $pickup_datetime = new DateTime("$pickup_date $pickup_time");
            $dropoff_datetime = new DateTime("$dropoff_date $dropoff_time");

            $pickup_day = $pickup_datetime->format('w');
            $dropoff_day = $dropoff_datetime->format('w');
            $pickup_hour = (int)$pickup_datetime->format('H');
            $dropoff_hour = (int)$dropoff_datetime->format('H');

            $working_hours = [
                1 => ['start' => 10, 'end' => 18], // Monday
                2 => ['start' => 10, 'end' => 18], // Tuesday
                3 => ['start' => 10, 'end' => 18], // Wednesday
                4 => ['start' => 10, 'end' => 18], // Thursday
                5 => ['start' => 10, 'end' => 18], // Friday
                6 => ['start' => 12, 'end' => 16], // Saturday
            ];

            if ($pickup_day == 0 || $dropoff_day == 0) { // Sunday
                echo "<script>alert('We are closed on Sundays. Please select another day to pickup/dropoff your car.'); window.history.back();</script>";
                exit();
            }

            if (($pickup_day >= 1 && $pickup_day <= 6 && ($pickup_hour < $working_hours[$pickup_day]['start'] || $pickup_hour >= $working_hours[$pickup_day]['end'])) ||
                ($dropoff_day >= 1 && $dropoff_day <= 6 && ($dropoff_hour < $working_hours[$dropoff_day]['start'] || $dropoff_hour >= $working_hours[$dropoff_day]['end']))) {
                echo "<script>alert('The pickup and dropoff times must correspond to the working hours. Mon - Fri: 10:00 - 18:00 || Sat: 12:00 - 16:00 || Sun: Closed'); window.history.back();</script>";
                exit();
            }

            if ($pickup_date < $today || $dropoff_date < $today) {
                echo "<script>alert('Selected dates cannot be in the past.'); window.history.back();</script>";
                exit();
            }

            // Check if dropoff date is after pickup date
            if ($dropoff_date <= $pickup_date) {
                echo "<script>alert('The dropoff date must be after the pickup date.'); window.history.back();</script>";
                exit();
            }

            // Calculate days rented
            $interval = $pickup_datetime->diff($dropoff_datetime);
            $days_rented = $interval->days;

            if ($days_rented < 1 || $days_rented > 30) {
                echo "<script>alert('Rental period must be at least 1 day and maximum 30 days.'); window.history.back();</script>";
                exit();
            }

            // Fetch cost_per_day from cars table based on model_id
            $sql = "SELECT cost_per_day FROM cars WHERE model_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $carmodel_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $car_details = $result->fetch_assoc();

            if (!$car_details || !isset($car_details['cost_per_day'])) {
                echo "<script>alert('Error fetching car details.'); window.history.back();</script>";
                exit();
            }

            $cost_per_day = $car_details['cost_per_day'];
            $total_cost = ($days_rented + 1) * $cost_per_day;
        }
    }

    if (isset($_POST['rental_form'])) {
        $user_id = $user_details['user_id'];
        $carbrand_id = $_SESSION['carbrand_id'];
        $carmodel_id = $_SESSION['carmodel_id'];
        $pickup_date = $_POST['pickup_date'];
        $pickup_time = $_POST['pickup_time'];
        $dropoff_date = $_POST['dropoff_date'];
        $dropoff_time = $_POST['dropoff_time'];
        $payment_method = $_POST['payment_method'];
        $pickup_point_id = $_POST['pickup_point_id'];

        $pickup_datetime = new DateTime("$pickup_date $pickup_time");
        $dropoff_datetime = new DateTime("$dropoff_date $dropoff_time");
        $days_rented = $pickup_datetime->diff($dropoff_datetime)->days;

        $current_datetime = date("Y-m-d H:i:s");

        // Fetch cost_per_day from cars table based on model_id
        $sql = "SELECT cost_per_day FROM cars WHERE model_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $carmodel_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $car_details = $result->fetch_assoc();

        if (!$car_details || !isset($car_details['cost_per_day'])) {
            echo "<script>alert('Error fetching car details.'); window.history.back();</script>";
            exit();
        }

        $cost_per_day = $car_details['cost_per_day'];
        $total_cost = ($days_rented+1) * $cost_per_day;

        $sql = "INSERT INTO rentals (user_id, carbrand_id, carmodel_id, days_rented, dropoff_date, dropoff_time, payment_method, pickup_date, pickup_point_id, pickup_time, total_cost, rental_datetime) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisssssssis", $user_id, $carbrand_id, $carmodel_id, $days_rented, $dropoff_date, $dropoff_time, $payment_method, $pickup_date, $pickup_point_id, $pickup_time, $total_cost, $current_datetime);
        $stmt->execute();

        // Update number_available in cars table
        $sql_update = "UPDATE cars SET number_available = number_available - 1 WHERE model_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $carmodel_id);
        $stmt_update->execute();

        echo "<script>alert('Car rented successfully! Your total is $total_cost €. Please arrive at the location you have chosen at least 30 minutes before your desired pickup time to make your payment. Thank you.'); window.location.href = 'vehicles.php';</script>";
        exit();
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ElectraDrive - Rental</title>
    <link rel="stylesheet" href="../css/rentalStyle.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
<div class="wrapper">
    <h1>Car Rental Form</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <!-- Hidden field to identify the form -->
        <input type="hidden" name="form_name" value="rental_form">

        <div class="input-box">
            <label for="car_name">Car Name</label>
            <input type="text" id="car_name" name="car_name"
                   value="<?php echo htmlspecialchars($_SESSION['car_name']); ?>" readonly>
            <i class="bi bi-car-front-fill"></i>
        </div>
        <div class="input-box">
            <label for="name">First Name</label>
            <input type="text" id="name" value="<?php echo htmlspecialchars($user_details['name']); ?>" name="name"
                   readonly>
            <i class="bi bi-person-fill"></i>
        </div>
        <div class="input-box">
            <label for="surname">Surname</label>
            <input type="text" id="surname" value="<?php echo htmlspecialchars($user_details['surname']); ?>"
                   name="surname" readonly>
            <i class="bi bi-person-fill"></i>
        </div>
        <div class="input-box">
            <label for="pickup_date">Pickup Date</label>
            <input type="date" id="pickup_date" name="pickup_date" value="<?php echo htmlspecialchars($pickup_date); ?>"
                   required>
        </div>
        <div class="input-box">
            <label for="pickup_time">Pickup Time</label>
            <input type="time" id="pickup_time" name="pickup_time" value="<?php echo htmlspecialchars($pickup_time); ?>"
                   required>
        </div>
        <div class="input-box">
            <label for="dropoff_date">Dropoff Date</label>
            <input type="date" id="dropoff_date" name="dropoff_date"
                   value="<?php echo htmlspecialchars($dropoff_date); ?>" required>
        </div>
        <div class="input-box">
            <label for="dropoff_time">Dropoff Time</label>
            <input type="time" id="dropoff_time" name="dropoff_time"
                   value="<?php echo htmlspecialchars($dropoff_time); ?>" required>
        </div>
        <div class="input-box text-center">
            <button class="btn" type="submit" name="calculate_cost">Calculate Cost</button>
        </div>
        <div class="input-box text-center">
            <p id="total_cost">Total cost: <?php echo htmlspecialchars($total_cost) . "€"; ?></p>
        </div>
        <div class="input-box text-center">
            <label for="pickup_point_id">Pickup Point</label>
            <select id="pickup_point_id" name="pickup_point_id" required>
                <?php foreach ($pickup_points as $point): ?>
                    <option value="<?php echo $point['pickup_point_id']; ?>" <?php echo ($pickup_point_id == $point['pickup_point_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($point['pickup_point_address']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="input-box text-center">
            <label for="payment_method">Payment Method</label>
            <select id="payment_method" name="payment_method" required>
                <option value="Credit Card" <?php echo ($payment_method == 'Credit Card') ? 'selected' : ''; ?>>Credit
                    Card
                </option>
                <option value="Cash" <?php echo ($payment_method == 'Cash') ? 'selected' : ''; ?>>Cash</option>
            </select>
        </div>
        <div class="input-box">
            <button class="btn" type="submit" name="rental_form">Submit Rental Form</button>
        </div>
        <div class="back-button">
            <p>Back to <a href="vehicles.php">vehicles page</a></p>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
