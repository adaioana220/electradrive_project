<?php

session_start();

// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$database = "electradrive";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Determine sorting criteria
$sort_criteria = isset($_GET['sort']) ? $_GET['sort'] : 'battery_capacity';

// Determine filter criteria
$filter_brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$filter_drive = isset($_GET['drive']) ? $_GET['drive'] : '';
$filter_body = isset($_GET['body']) ? $_GET['body'] : '';

// SQL query to fetch details of all cars with number_available >= 1, sorted by selected criteria
$sql = "SELECT car_brands.brand_id, car_brands.brand_name, car_models.model_id, car_models.model_name, cars.release_year, cars.battery_capacity, cars.range_min, cars.range_max, cars.accel_0_100, cars.horsepower, cars.charge_time, cars.fastcharge_time, cars.drive, cars.cargo_vol, cars.car_body, cars.number_of_seats, cars.number_available, cars.cost_per_day 
        FROM cars 
        JOIN car_models ON cars.model_id = car_models.model_id
        JOIN car_brands ON cars.brand_id = car_brands.brand_id
        WHERE cars.number_available >= 1";

if ($filter_brand) {
    $sql .= " AND car_brands.brand_name = '$filter_brand'";
}

if ($filter_drive) {
    $sql .= " AND cars.drive = '$filter_drive'";
}

if ($filter_body) {
    $sql .= " AND cars.car_body = '$filter_body'";
}

$sql .= " ORDER BY cars.$sort_criteria DESC";

$result = $conn->query($sql);

$car_details = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $car_details[] = $row;
    }
}

// Fetch available brands, drives, and body types for the filters
$brands_result = $conn->query("SELECT DISTINCT brand_name FROM car_brands JOIN cars ON car_brands.brand_id = cars.brand_id WHERE cars.number_available >= 1");
$drives_result = $conn->query("SELECT DISTINCT drive FROM cars WHERE number_available >= 1");
$body_types_result = $conn->query("SELECT DISTINCT car_body FROM cars WHERE number_available >= 1");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ElectraDrive - Vehicles</title>
    <link rel="stylesheet" href="../css/vehiclesStyle.css">
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
                    <a class="nav-link" href="homepage.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="vehicles.php">Vehicles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">Contact</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="account.php">Account</a>
                </li>
                <?php if($_SESSION['user_email'] == "admin@electradrive.com") { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="indexAdmin.php">Admin</a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>

<div class="div text-center mt-5">
    <h1>Vehicles</h1>
</div>

<div class="container-fluid mt-4 c">
    <div class="row text-end">
        <div class="dropdown me-2">
            <button class="btn btn-secondary dropdown-toggle my-2" type="button" id="filterBrandDropdown"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                Filter by Brand
            </button>
            <ul class="dropdown-menu" aria-labelledby="filterBrandDropdown">
                <?php while ($brand = $brands_result->fetch_assoc()) { ?>
                    <li><a class="dropdown-item"
                           href="?brand=<?php echo $brand['brand_name']; ?>"><?php echo $brand['brand_name']; ?></a>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle my-2" type="button" id="filterDriveDropdown"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                Filter by Drive
            </button>
            <ul class="dropdown-menu" aria-labelledby="filterDriveDropdown">
                <?php while ($drive = $drives_result->fetch_assoc()) { ?>
                    <li><a class="dropdown-item"
                           href="?drive=<?php echo $drive['drive']; ?>"><?php echo $drive['drive']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle my-2" type="button" id="filterBodyDropdown"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                Filter by Body Type
            </button>
            <ul class="dropdown-menu" aria-labelledby="filterBodyDropdown">
                <?php while ($body_type = $body_types_result->fetch_assoc()) { ?>
                    <li><a class="dropdown-item"
                           href="?body=<?php echo $body_type['car_body']; ?>"><?php echo $body_type['car_body']; ?></a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle my-2" type="button" id="sortDropdown" data-bs-toggle="dropdown"
                aria-expanded="false">
            Sort By
        </button>
        <ul class="dropdown-menu" aria-labelledby="sortDropdown">
            <li><a class="dropdown-item" href="?sort=battery_capacity">Battery Capacity</a></li>
            <li><a class="dropdown-item" href="?sort=range_max">Maximum Range</a></li>
            <li><a class="dropdown-item" href="?sort=charge_time">Charge Time</a></li>
            <li><a class="dropdown-item" href="?sort=fastcharge_time">Fastcharge Time</a></li>
            <li><a class="dropdown-item" href="?sort=cargo_vol">Cargo Volume</a></li>
            <li><a class="dropdown-item" href="?sort=number_of_seats">Number of Seats</a></li>
            <li><a class="dropdown-item" href="?sort=number_available">Number available</a></li>
        </ul>
    </div>
    <div id="car-container">
        <?php foreach ($car_details as $car) {
            $car_image_name = strtolower(str_replace([' ', '-'], '', $car["model_name"]));
            $image_path = "../images/car_images/$car_image_name.png";
            ?>
            <div class="row car-card" data-battery_capacity="<?php echo $car["battery_capacity"]; ?>"
                 data-range_max="<?php echo $car["range_max"]; ?>" data-charge_time="<?php echo $car["charge_time"]; ?>"
                 data-fastcharge_time="<?php echo $car["fastcharge_time"]; ?>"
                 data-cargo_vol="<?php echo $car["cargo_vol"]; ?>"
                 data-number_of_seats="<?php echo $car["number_of_seats"]; ?>">
                <div class="card mb-3">
                    <div class="row g-0">
                        <div class="col-xl-4 align-self-center g-2">
                            <?php if (file_exists($image_path)) { ?>
                                <img src="<?php echo $image_path; ?>" class="img-fluid"
                                     alt="<?php echo $car["brand_name"] . " " . $car["model_name"]; ?>">
                            <?php } else { ?>
                                <p>No Image Available</p>
                            <?php } ?>
                        </div>
                        <div class="col-xl-8">
                            <div class="card-body">
                                <h5 class="card-title my-3"><?php echo $car["brand_name"] . " " . $car["model_name"] . " (" . $car["release_year"] . ")"; ?></h5>
                                <h5>Details</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped-columns text-center">
                                        <thead>
                                        <tr>
                                            <th scope="col">Battery</th>
                                            <th scope="col">Minimum range</th>
                                            <th scope="col">Maximum range</th>
                                            <th scope="col">Acceleration 0-100km/h</th>
                                            <th scope="col">Horsepower</th>
                                            <th scope="col">Charge time</th>
                                            <th scope="col">Fastcharge time</th>
                                            <th scope="col">Drive</th>
                                            <th scope="col">Cargo volume</th>
                                            <th scope="col">Car body</th>
                                            <th scope="col">Number of seats</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><?php echo $car["battery_capacity"]; ?> kWh</td>
                                            <td><?php echo $car["range_min"]; ?> km</td>
                                            <td><?php echo $car["range_max"]; ?> km</td>
                                            <td><?php echo $car["accel_0_100"]; ?> sec</td>
                                            <td><?php echo $car["horsepower"]; ?> HP</td>
                                            <td><?php echo $car["charge_time"]; ?> hrs</td>
                                            <td><?php echo $car["fastcharge_time"]; ?> min</td>
                                            <td><?php echo $car["drive"]; ?></td>
                                            <td><?php echo $car["cargo_vol"]; ?> L</td>
                                            <td><?php echo $car["car_body"]; ?></td>
                                            <td><?php echo $car["number_of_seats"]; ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if ($car["number_available"] > 0) { ?>
                                    <p class="text-end mt-3 nba">Number
                                        available: <?php echo $car["number_available"]; ?></p>
                                    <p class="text-center mt-4 mx-auto pr">Price: <?php echo $car["cost_per_day"]; ?>
                                        €/day</p>
                                    <div class="text-center">
                                        <form method="post" action="save_car_details.php">
                                            <input type="hidden" name="carbrand_id"
                                                   value="<?php echo $car['brand_id']; ?>">
                                            <input type="hidden" name="carmodel_id"
                                                   value="<?php echo $car['model_id']; ?>">
                                            <input type="hidden" name="car_name"
                                                   value="<?php echo $car['brand_name'] . ' ' . $car['model_name']; ?>">
                                            <button type="submit" class="btn my-3">Rent this car</button>
                                        </form>
                                    </div>
                                <?php } else { ?>
                                    <h3 class="text-center mt-4">No cars available.</h3>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <?php } ?>
    </div>
</div>

<div id="copyright" class="row text-center">
    <p>2024 © ElectraDrive | <a href="contact.php">Contact us</a></p>
</div>

<script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
