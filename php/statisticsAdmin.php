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

// Initialize variables needed
$resultUsers = null;
$resultRental = null;
$totalEarnings = null;
$weeklyEarnings = [];
$monthlyEarnings = [];

// Fetch users data
if (isset($_POST['datesBetweenUsers'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Validate the dates
    if (empty($start_date) || empty($end_date)) {
        echo "<script>alert('Both start and end dates are required.');</script>";
    } else {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        // Validate the dates
        if (empty($start_date) || empty($end_date)) {
            echo "<script>alert('Both start and end dates are required.');</script>";
        } else {
            // Fetch users within the date range
            $sql = "SELECT user_id, name, surname, user_email, registration_datetime FROM users WHERE registration_datetime BETWEEN '$start_date' AND '$end_date'";
            $resultUsers = $conn->query($sql);

            // Get total count of users
            $totalUsersCount = $resultUsers ? $resultUsers->num_rows : 0;
        }
    }
}


// Fetch rentals data
if (isset($_POST['datesBetweenRental'])) {
    $start_date_rental = $_POST['start_date_rental'];
    $end_date_rental = $_POST['end_date_rental'];

    // Validate the dates
    if (empty($start_date_rental) || empty($end_date_rental)) {
        echo "<script>alert('Both start and end dates are required.');</script>";
    } else {
        // Fetch rentals within the date range
        $sql = "SELECT r.rental_number, r.user_id, r.carbrand_id, r.carmodel_id, r.pickup_date, r.pickup_time, r.dropoff_date, r.dropoff_time, r.rental_datetime, u.name, u.surname, c.cost_per_day, cb.brand_name, cm.model_name
                FROM rentals r
                JOIN car_brands cb ON r.carbrand_id = cb.brand_id
                JOIN car_models cm ON r.carmodel_id = cm.model_id
                JOIN cars c ON r.carmodel_id = c.model_id AND r.carbrand_id = c.brand_id
                JOIN users u ON r.user_id = u.user_id
                WHERE r.rental_datetime BETWEEN '$start_date_rental' AND '$end_date_rental'";
        $resultRental = $conn->query($sql);

        // Get total count of rentals
        $totalRentalsCount = $resultRental ? $resultRental->num_rows : 0;
    }
}

if (isset($_POST['earningsByMonth'])) {
    $selected_month = $_POST['month'];
    $selected_year = $_POST['year'];

    // Validate the month and year
    if (empty($selected_month) || empty($selected_year)) {
        echo "<script>alert('Both month and year are required.');</script>";
    } else {
        // Fetch total earnings for the selected month and year
        $sql = "SELECT SUM(r.total_cost) AS total_earnings
                FROM rentals r
                WHERE YEAR(r.rental_datetime) = '$selected_year' AND MONTH(r.rental_datetime) = '$selected_month'";
        $resultEarnings = $conn->query($sql);
        if ($resultEarnings && $row = $resultEarnings->fetch_assoc()) {
            $totalEarnings = $row['total_earnings'];
        } else {
            $totalEarnings = 0;
        }

        // SQL query to fetch weekly earnings for the selected month and year
        $sql = "SELECT WEEK(r.rental_datetime, 1) AS week_of_month,
               SUM(r.total_cost) AS total_earnings
        FROM rentals r
        WHERE YEAR(r.rental_datetime) = '$selected_year'
          AND MONTH(r.rental_datetime) = '$selected_month'
        GROUP BY week_of_month
        ORDER BY week_of_month";

        $resultWeeklyEarnings = $conn->query($sql);

        if ($resultWeeklyEarnings) {
            $weeklyEarnings = [];
            while ($row = $resultWeeklyEarnings->fetch_assoc()) {
                $weeklyEarnings[] = $row;
            }
        } else {
            // Handle query execution error
            echo "Error executing query: " . $conn->error;
        }
    }
}


if (isset($_POST['earningsByYear'])) {
    $selected_year = $_POST['year_for_monthly_earnings'];

    // Validate the year
    if (empty($selected_year)) {
        echo "<script>alert('Year is required.');</script>";
    } else {
        // Fetch monthly earnings for the selected year
        $sql = "SELECT MONTH(r.rental_datetime) AS month, SUM(r.total_cost) AS total_earnings
                FROM rentals r
                WHERE YEAR(r.rental_datetime) = ?
                GROUP BY MONTH(r.rental_datetime)";

        // Prepare and bind parameters
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $selected_year);
        $stmt->execute();
        $resultMonthlyEarnings = $stmt->get_result();

        $monthlyEarnings = array();
        while ($row = $resultMonthlyEarnings->fetch_assoc()) {
            $monthlyEarnings[] = $row;
        }
    }
}


// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ElectraDrive - Administrator</title>
    <link rel="stylesheet" href="../css/statisticsAdminStyle.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
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
    <h1>Statistics</h1>
</div>

<div class="container-fluid mt-4 c">
    <div class="row">
        <h1>> User registration date & time</h1>
        <form method="post" action="#" class="d-flex justify-content-center">
            <div class="form-group mb-4 mx-2">
                <input type="hidden" name="datesBetweenUsers" value="1">
                <label for="start_date">Start date:</label>
                <input type="date" id="start_date" name="start_date" class="form-control" required>
            </div>
            <div class="form-group mb-4 mx-2">
                <label for="end_date">End date:</label>
                <input type="date" id="end_date" name="end_date" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary align-self-end mb-4">Fetch Users</button>
        </form>
        <?php if ($resultUsers && $resultUsers->num_rows > 0) {
            echo "<div class='table-responsive'>";
            echo "<table class='table table-hover table-striped-columns text-center'>";
            echo "<thead><tr><th>ID</th><th>Name</th><th>Surname</th><th>Email</th><th>Registration Date</th></tr></thead>";
            echo "<tbody>";
            while ($row = $resultUsers->fetch_assoc()) {
                echo "<tr><td>{$row['user_id']}</td><td>{$row['name']}</td><td>{$row['surname']}</td><td>{$row['user_email']}</td><td>{$row['registration_datetime']}</td></tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
            echo "<p>Total users in selected date range: {$totalUsersCount}</p>";

        } elseif ($resultUsers && $resultUsers->num_rows == 0) {
            echo "<p>No users found within specified date range.<p>";
        } ?>
    </div>
    <div class="row">
        <h1>> Rentals date & time</h1>
        <form method="post" action="#" class="d-flex justify-content-center">
            <div class="form-group mb-4 mx-2">
                <input type="hidden" name="datesBetweenRental" value="1">
                <label for="start_date_rental">Start date:</label>
                <input type="date" id="start_date_rental" name="start_date_rental" class="form-control" required>
            </div>
            <div class="form-group mb-4 mx-2">
                <label for="end_date_rental">End date:</label>
                <input type="date" id="end_date_rental" name="end_date_rental" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary align-self-end mb-4">Fetch Rentals</button>
        </form>
        <?php if ($resultRental && $resultRental->num_rows > 0) {
            echo "<div class='table-responsive'>";
            echo "<table class='table table-hover table-striped-columns text-center'>";
            echo "<thead><tr><th>Rental number</th><th>User ID</th><th>User Name</th><th>User Surname</th><th>Car Brand</th><th>Car Model</th><th>Pickup Date</th><th>Pickup Time</th><th>Dropoff Date</th><th>Dropoff Time</th><th>Rental Duration (Days)</th><th>Price/Day</th><th>Total</th><th>Rental Date & Time</th></tr></thead>";
            echo "<tbody>";
            while ($row = $resultRental->fetch_assoc()) {
                $rentalStart = new DateTime($row['pickup_date']);
                $rentalEnd = new DateTime($row['dropoff_date']);
                $rentalDuration = $rentalStart->diff($rentalEnd)->days + 1; // Include both start and end date
                $totalPrice = $rentalDuration * $row['cost_per_day'];
                echo "<tr><td>{$row['rental_number']}</td>
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
                            <td>{$row['rental_datetime']}</td></tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
            echo "<p>Total rentals in selected date range: {$totalRentalsCount}</p>";
        } elseif ($resultRental && $resultRental->num_rows == 0) {
            echo "<p>No rentals found within specified date range.<p>";
        } ?>
    </div>
    <div class="row">
        <h1>> Total earned by month</h1>
        <form method="post" action="#" class="d-flex justify-content-center">
            <div class="form-group mb-4 mx-2">
                <input type="hidden" name="earningsByMonth" value="1">
                <label for="month">Month:</label>
                <select id="month" name="month" class="form-control" required>
                    <?php
                    for ($m = 1; $m <= 12; $m++) {
                        $monthName = date("F", mktime(0, 0, 0, $m, 1, date("Y")));
                        echo "<option value=\"$m\">$monthName</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group mb-4 mx-2">
                <label for="year">Year:</label>
                <select id="year" name="year" class="form-control" required>
                    <?php
                    for ($y = 2023; $y <= 2030; $y++) {
                        echo "<option value=\"$y\">$y</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary align-self-end mb-4">Fetch Earnings</button>
        </form>
        <div id="weeklyEarningsChart" style="height: 370px; width: 100%;"></div>
        <?php if (!is_null($totalEarnings)) {
            $selectedMonthName = date("F", mktime(0, 0, 0, $selected_month, 1));
            echo "<p class='mt-5'>{$selectedMonthName} {$selected_year} total earnings: {$totalEarnings}€</p>";
        } ?>
    </div>

    <div class="row">
        <h1>> Earnings by Year</h1>
        <form method="post" action="#" class="d-flex justify-content-center">
            <div class="form-group mb-4 mx-2">
                <input type="hidden" name="earningsByYear" value="1">
                <label for="year_for_monthly_earnings">Year:</label>
                <select id="year_for_monthly_earnings" name="year_for_monthly_earnings" class="form-control" required>
                    <?php
                    for ($y = 2023; $y <= 2030; $y++) {
                        echo "<option value=\"$y\">$y</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary align-self-end mb-4">Fetch Yearly Earnings</button>
        </form>
        <div id="monthlyEarningsChart" style="height: 370px; width: 100%;"></div>
    </div>
</div>

<div id="copyright" class="row text-center">
    <p>Administrator - 2024 © ElectraDrive</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    window.onload = function () {
        // Get the data from PHP
        var weeklyEarningsData = <?php echo json_encode($weeklyEarnings); ?>;
        var monthlyEarningsData = <?php echo json_encode($monthlyEarnings); ?>;

        var weeklyEarningsChart = new CanvasJS.Chart("weeklyEarningsChart", {
            animationEnabled: true,
            theme: "light2",
            title: {
                text: "Monthly Earnings"
            },
            axisX: {
                title: "Week of Month",
                interval: 1
            },
            axisY: {
                title: "Earnings (€)",
                minimum: 0
            },
            data: [{
                type: "column", // Use column type for bar chart
                dataPoints: weeklyEarningsData.map(function (entry) {
                    return {label: "Week " + entry.week_of_month, y: parseFloat(entry.total_earnings)};
                }),
                toolTipContent: "Week {label}: {y} €"
            }]
        });

        weeklyEarningsChart.render();

        var monthlyEarningsChart = new CanvasJS.Chart("monthlyEarningsChart", {
            animationEnabled: true,
            theme: "light2",
            title: {
                text: "Monthly Earnings for Year <?php echo $selected_year; ?>"
            },
            axisX: {
                title: "Month",
                interval: 1,
                minimum: 1,
                maximum: 12,
                labelFormatter: function(e) {
                    var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                    return monthNames[e.value - 1];
                }
            },
            axisY: {
                title: "Earnings (€)",
                minimum: 0
            },
            data: [{
                type: "line",
                dataPoints: monthlyEarningsData.map(function(entry) {
                    return { x: entry.month, y: parseFloat(entry.total_earnings) };
                }),
                markerType: "circle",
                markerSize: 8,
                toolTipContent: "Month {x}: {y} €"
            }]
        });

        monthlyEarningsChart.render();
    }
</script>
</body>
</html>
