<?php

// Start the session
session_start();

// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$database = "electradrive";

// Create a database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Determine sorting criteria for rental history
$rentalHistorySort = isset($_GET['rental_history_sort']) ? $_GET['rental_history_sort'] : 'pickup_date';

// Determine sorting criteria for current rentals
$currentRentalsSort = isset($_GET['current_rentals_sort']) ? $_GET['current_rentals_sort'] : 'pickup_date';

// Determine sorting criteria for upcoming rentals
$upcomingRentalsSort = isset($_GET['upcoming_rentals_sort']) ? $_GET['upcoming_rentals_sort'] : 'pickup_date';

// Fetch rental history
$rentalHistoryQuery =
    "SELECT r.rental_number, r.user_id, r.carbrand_id, r.carmodel_id, r.pickup_date, r.pickup_time, r.dropoff_date, r.dropoff_time, r.rental_datetime, u.name, u.surname, c.cost_per_day, cb.brand_name, cm.model_name
    FROM rentals r
    JOIN car_brands cb ON r.carbrand_id = cb.brand_id
    JOIN car_models cm ON r.carmodel_id = cm.model_id
    JOIN cars c ON r.carmodel_id = c.model_id AND r.carbrand_id = c.brand_id
    JOIN users u ON r.user_id = u.user_id
    WHERE r.dropoff_date < CURDATE()
    ORDER BY $rentalHistorySort ASC";
$rentalHistoryResult = $conn->query($rentalHistoryQuery);

// Fetch current rentals
$currentRentalsQuery =
    "SELECT r.rental_number, r.user_id, r.carbrand_id, r.carmodel_id, r.pickup_date, r.pickup_time, r.dropoff_date, r.dropoff_time, r.rental_datetime, u.name, u.surname, c.cost_per_day, cb.brand_name, cm.model_name
    FROM rentals r
    JOIN car_brands cb ON r.carbrand_id = cb.brand_id
    JOIN car_models cm ON r.carmodel_id = cm.model_id
    JOIN cars c ON r.carmodel_id = c.model_id AND r.carbrand_id = c.brand_id
    JOIN users u ON r.user_id = u.user_id
    WHERE CURDATE() BETWEEN r.pickup_date AND r.dropoff_date
    ORDER BY $currentRentalsSort ASC";
$currentRentalsResult = $conn->query($currentRentalsQuery);

// Fetch upcoming rentals
$upcomingRentalsQuery =
    "SELECT r.rental_number, r.user_id, r.carbrand_id, r.carmodel_id, r.pickup_date, r.pickup_time, r.dropoff_date, r.dropoff_time, r.rental_datetime, u.name, u.surname, c.cost_per_day, cb.brand_name, cm.model_name
    FROM rentals r
    JOIN car_brands cb ON r.carbrand_id = cb.brand_id
    JOIN car_models cm ON r.carmodel_id = cm.model_id
    JOIN cars c ON r.carmodel_id = c.model_id AND r.carbrand_id = c.brand_id
    JOIN users u ON r.user_id = u.user_id
    WHERE r.pickup_date > CURDATE()
    ORDER BY $upcomingRentalsSort ASC";
$upcomingRentalsResult = $conn->query($upcomingRentalsQuery);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ElectraDrive - Administrator</title>
    <link rel="stylesheet" href="../css/rentalsAdminStyle.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>

<nav class="navbar navbar-expand-xl mx-auto">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link" href="indexAdmin.php">Index</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="usersAdmin.php">Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="brandsAdmin.php">Brands</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="vehiclesAdmin.php">Vehicles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rentalsAdmin.php">Rentals</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="statisticsAdmin.php">Statistics</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="div text-center mt-5">
    <h1>Rental information</h1>
</div>

<div class="container-fluid mt-4 c">
    <div class="row">
        <h1>> Rental history</h1>
        <div class="dropdown mb-3">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="sortRentalHistoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Sort By
            </button>
            <ul class="dropdown-menu" aria-labelledby="sortRentalHistoryDropdown">
                <li><a class="dropdown-item" href="?rental_history_sort=rental_number">Rental number</a></li>
                <li><a class="dropdown-item" href="?rental_history_sort=user_id">User ID</a></li>
                <li><a class="dropdown-item" href="?rental_history_sort=name">User Name</a></li>
                <li><a class="dropdown-item" href="?rental_history_sort=surname">User Surname</a></li>
                <li><a class="dropdown-item" href="?rental_history_sort=carbrand_id">Car Brand</a></li>
                <li><a class="dropdown-item" href="?rental_history_sort=carmodel_id">Car Model</a></li>
                <li><a class="dropdown-item" href="?rental_history_sort=pickup_date">Pickup Date</a></li>
                <li><a class="dropdown-item" href="?rental_history_sort=pickup_time">Pickup Time</a></li>
                <li><a class="dropdown-item" href="?rental_history_sort=dropoff_date">Dropoff Date</a></li>
                <li><a class="dropdown-item" href="?rental_history_sort=dropoff_time">Dropoff Time</a></li>
                <li><a class="dropdown-item" href="?rental_history_sort=cost_per_day">Price/Day</a></li>
                <li><a class="dropdown-item" href="?rental_history_sort=totalPrice">Total</a></li>
                <li><a class="dropdown-item" href="?upcoming_rentals_sort=rental_datetime">Rental Date & Time</a></li>
            </ul>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped-columns text-center">
                <thead>
                <tr>
                    <th scope="col">Rental Number</th>
                    <th scope="col">User ID</th>
                    <th scope="col">User Name</th>
                    <th scope="col">User Surname</th>
                    <th scope="col">Car Brand</th>
                    <th scope="col">Car Model</th>
                    <th scope="col">Pickup Date</th>
                    <th scope="col">Pickup Time</th>
                    <th scope="col">Dropoff Date</th>
                    <th scope="col">Dropoff Time</th>
                    <th scope="col">Rental Duration (Days)</th>
                    <th scope="col">Price/Day</th>
                    <th scope="col">Total</th>
                    <th scope="col">Rental Date & Time</th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                <?php
                if ($rentalHistoryResult->num_rows > 0) {
                    while ($row = $rentalHistoryResult->fetch_assoc()) {
                        $rentalStart = new DateTime($row['pickup_date']);
                        $rentalEnd = new DateTime($row['dropoff_date']);
                        $rentalDuration = $rentalStart->diff($rentalEnd)->days + 1; // Include both start and end date
                        $totalPrice = $rentalDuration * $row['cost_per_day'];
                        echo "<tr>
                            <td>{$row['rental_number']}</td>
                            <td>{$row['user_id']}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['surname']}</td>
                            <td>{$row['brand_name']}</td>
                            <td>{$row['model_name']}</td>
                            <td>{$row['pickup_date']}</td>
                            <td>{$row['pickup_time']}</td>
                            <td>{$row['dropoff_date']}</td>
                            <td>{$row['dropoff_time']}</td>
                            <td>{$rentalDuration}</td>
                            <td>{$row['cost_per_day']}</td>
                            <td>{$totalPrice}</td>
                            <td>{$row['rental_datetime']}</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>No rental history found.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <h1>> Current rentals</h1>
        <div class="dropdown mb-3">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="sortCurrentRentalsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Sort By
            </button>
            <ul class="dropdown-menu" aria-labelledby="sortCurrentRentalsDropdown">
                <li><a class="dropdown-item" href="?rental_history_sort=rental_number">Rental number</a></li>
                <li><a class="dropdown-item" href="?rental_history_sort=user_id">User ID</a></li>
                <li><a class="dropdown-item" href="?rental_history_sort=name">User Name</a></li>
                <li><a class="dropdown-item" href="?rental_history_sort=surname">User Surname</a></li>
                <li><a class="dropdown-item" href="?current_rentals_sort=carbrand_id">Car Brand</a></li>
                <li><a class="dropdown-item" href="?current_rentals_sort=carmodel_id">Car Model</a></li>
                <li><a class="dropdown-item" href="?current_rentals_sort=pickup_date">Pickup Date</a></li>
                <li><a class="dropdown-item" href="?current_rentals_sort=pickup_time">Pickup Time</a></li>
                <li><a class="dropdown-item" href="?current_rentals_sort=dropoff_date">Dropoff Date</a></li>
                <li><a class="dropdown-item" href="?current_rentals_sort=dropoff_time">Dropoff Time</a></li>
                <li><a class="dropdown-item" href="?current_rentals_sort=cost_per_day">Price/Day</a></li>
                <li><a class="dropdown-item" href="?upcoming_rentals_sort=rental_datetime">Rental Date & Time</a></li>
            </ul>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped-columns text-center">
                <thead>
                <tr>
                    <th scope="col">Rental Number</th>
                    <th scope="col">User ID</th>
                    <th scope="col">User Name</th>
                    <th scope="col">User Surname</th>
                    <th scope="col">Car Brand</th>
                    <th scope="col">Car Model</th>
                    <th scope="col">Pickup Date</th>
                    <th scope="col">Pickup Time</th>
                    <th scope="col">Dropoff Date</th>
                    <th scope="col">Dropoff Time</th>
                    <th scope="col">Rental Duration (Days)</th>
                    <th scope="col">Price/Day</th>
                    <th scope="col">Total</th>
                    <th scope="col">Rental Date & Time</th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                <?php
                if ($currentRentalsResult->num_rows > 0) {
                    while ($row = $currentRentalsResult->fetch_assoc()) {
                        $rentalStart = new DateTime($row['pickup_date']);
                        $rentalEnd = new DateTime($row['dropoff_date']);
                        $rentalDuration = $rentalStart->diff($rentalEnd)->days + 1; // Include both start and end date
                        $totalPrice = $rentalDuration * $row['cost_per_day'];
                        echo "<tr>
                            <td>{$row['rental_number']}</td>
                            <td>{$row['user_id']}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['surname']}</td>
                            <td>{$row['brand_name']}</td>
                            <td>{$row['model_name']}</td>
                            <td>{$row['pickup_date']}</td>
                            <td>{$row['pickup_time']}</td>
                            <td>{$row['dropoff_date']}</td>
                            <td>{$row['dropoff_time']}</td>
                            <td>{$rentalDuration}</td>
                            <td>{$row['cost_per_day']}</td>
                            <td>{$totalPrice}</td>
                            <td>{$row['rental_datetime']}</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>No current rentals found.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <h1>> Upcoming rentals</h1>
        <div class="dropdown mb-3">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="sortUpcomingRentalsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Sort By
            </button>
            <ul class="dropdown-menu" aria-labelledby="sortUpcomingRentalsDropdown">
                <li><a class="dropdown-item" href="?rental_history_sort=rental_number">Rental number</a></li>
                <li><a class="dropdown-item" href="?rental_history_sort=user_id">User ID</a></li>
                <li><a class="dropdown-item" href="?rental_history_sort=name">User Name</a></li>
                <li><a class="dropdown-item" href="?rental_history_sort=surname">User Surname</a></li>
                <li><a class="dropdown-item" href="?upcoming_rentals_sort=carbrand_id">Car Brand</a></li>
                <li><a class="dropdown-item" href="?upcoming_rentals_sort=carmodel_id">Car Model</a></li>
                <li><a class="dropdown-item" href="?upcoming_rentals_sort=pickup_date">Pickup Date</a></li>
                <li><a class="dropdown-item" href="?upcoming_rentals_sort=pickup_time">Pickup Time</a></li>
                <li><a class="dropdown-item" href="?upcoming_rentals_sort=dropoff_date">Dropoff Date</a></li>
                <li><a class="dropdown-item" href="?upcoming_rentals_sort=dropoff_time">Dropoff Time</a></li>
                <li><a class="dropdown-item" href="?upcoming_rentals_sort=cost_per_day">Price/Day</a></li>
                <li><a class="dropdown-item" href="?upcoming_rentals_sort=total_cost">Total</a></li>
                <li><a class="dropdown-item" href="?upcoming_rentals_sort=rental_datetime">Rental Date & Time</a></li>
            </ul>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped-columns text-center">
                <thead>
                <tr>
                    <th scope="col">Rental Number</th>
                    <th scope="col">User ID</th>
                    <th scope="col">User Name</th>
                    <th scope="col">User Surname</th>
                    <th scope="col">Car Brand</th>
                    <th scope="col">Car Model</th>
                    <th scope="col">Pickup Date</th>
                    <th scope="col">Pickup Time</th>
                    <th scope="col">Dropoff Date</th>
                    <th scope="col">Dropoff Time</th>
                    <th scope="col">Rental Duration (Days)</th>
                    <th scope="col">Price/Day</th>
                    <th scope="col">Total</th>
                    <th scope="col">Rental Date & Time</th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                <?php
                if ($upcomingRentalsResult->num_rows > 0) {
                    while ($row = $upcomingRentalsResult->fetch_assoc()) {
                        $rentalStart = new DateTime($row['pickup_date']);
                        $rentalEnd = new DateTime($row['dropoff_date']);
                        $rentalDuration = $rentalStart->diff($rentalEnd)->days + 1; // Include both start and end date
                        $totalPrice = $rentalDuration * $row['cost_per_day'];
                        echo "<tr>
                            <td>{$row['rental_number']}</td>
                            <td>{$row['user_id']}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['surname']}</td>
                            <td>{$row['brand_name']}</td>
                            <td>{$row['model_name']}</td>
                            <td>{$row['pickup_date']}</td>
                            <td>{$row['pickup_time']}</td>
                            <td>{$row['dropoff_date']}</td>
                            <td>{$row['dropoff_time']}</td>
                            <td>{$rentalDuration}</td>
                            <td>{$row['cost_per_day']}</td>
                            <td>{$totalPrice}</td>
                            <td>{$row['rental_datetime']}</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>No upcoming rentals found.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="copyright" class="row text-center">
    <p>Administrator - 2024 © ElectraDrive</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
