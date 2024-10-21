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

// Function to sanitize input
function sanitize_input($data)
{
    $data = trim($data);
    $data = htmlspecialchars($data);
    $data = stripslashes($data);
    return $data;
}

function contains_invalid_characters($input)
{
    // This regex matches any character that is not a letter, digit, space, hyphen, dot, or plus sign
    return preg_match('/[^a-zA-Z0-9\s\-+.]/', $input);
}

// Function to handle file upload
function handle_file_upload($file, $model_name)
{
    $target_dir = "../../images/car_images/";
    $expected_filename = strtolower(str_replace(' ', '', $model_name)) . ".png";
    $uploaded_filename = strtolower(str_replace(' ', '', pathinfo($file['name'], PATHINFO_FILENAME))) . ".png";
    $target_file = $target_dir . $expected_filename;
    $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Check if file is a PNG
    if ($imageFileType != "png") {
        return "Only PNG files are allowed.";
    }

    // Check if the file is named correctly
    if ($uploaded_filename !== $expected_filename) {
        return "File must be named exactly like the model name, in lowercase with no spaces.";
    }

    // Check if file already exists and delete the old file
    if (file_exists($target_file)) {
        unlink($target_file);
    }

    // Check file size (limit to 2MB)
    if ($file["size"] > 2000000) {
        return "File is too large.";
    }

    // Attempt to move the uploaded file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return "Success!";
    } else {
        return "There was an error uploading your file.";
    }
}

// Handle add vehicle form
if (isset($_POST['addVehicle'])) {
    // Sanitize input
    $model_name = sanitize_input($_POST['model_name']);
    $brand_name = sanitize_input($_POST['brand_name']);
    $release_year = sanitize_input($_POST['release_year']);
    $battery_capacity = sanitize_input($_POST['battery_capacity']);
    $range_min = sanitize_input($_POST['range_min']);
    $range_max = sanitize_input($_POST['range_max']);
    $charge_time = sanitize_input($_POST['charge_time']);
    $fastcharge_time = sanitize_input($_POST['fastcharge_time']);
    $drive = sanitize_input($_POST['drive']);
    $cargo_vol = sanitize_input($_POST['cargo_vol']);
    $number_of_seats = sanitize_input($_POST['number_of_seats']);
    $number_available = sanitize_input($_POST['number_available']);
    $cost_per_day = sanitize_input($_POST['cost_per_day']);

    $errors = true;

    // Verify numeric fields
    if (!is_numeric($release_year) || strlen($release_year) != 4 || !is_numeric($battery_capacity) || !is_numeric($range_min) || !is_numeric($range_max) || !is_numeric($charge_time) || !is_numeric($charge_time) || !is_numeric($fastcharge_time) || !is_numeric($cargo_vol) || !is_numeric($number_of_seats) || !is_numeric($cost_per_day) || !is_numeric($number_available)) {
        $errors = false;
    }

    // Check for invalid characters
    $fields = [
        'Model Name' => $model_name,
        'Brand Name' => $brand_name,
        'Release Year' => $release_year,
        'Battery Capacity' => $battery_capacity,
        'Range Max' => $range_max,
        'Charge Time' => $charge_time,
        'Fast Charge Time' => $fastcharge_time,
        'Drive' => $drive,
        'Cargo Volume' => $cargo_vol,
        'Number of Seats' => $number_of_seats,
        'Number Available' => $number_available,
        'Cost per Day' => $cost_per_day
    ];

    foreach ($fields as $field_name => $value) {
        if (contains_invalid_characters($value)) {
            $errors = false;
            break;
        }
    }

    if ($errors) {
        //Ensure the strings are stored with a capitalized first letter for ease of use
        $model_name = ucfirst($model_name);
        $drive = ucfirst(strtolower($drive));
        $brand_name = ucfirst(strtolower($brand_name));

        // Get brand_id
        $sql_check_brand = "SELECT brand_id FROM car_brands WHERE brand_name = '$brand_name'";
        $result = mysqli_query($conn, $sql_check_brand);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $brand_id = $row['brand_id'];
        }

        // Check if model exists, if not insert it and get model_id
        $sql_check_model = "SELECT model_id FROM car_models WHERE model_name = '$model_name'";
        $result = mysqli_query($conn, $sql_check_model);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $model_id = $row['model_id'];
        } else {
            $sql_insert_model = "INSERT INTO car_models (model_name, brand_id) VALUES ('$model_name', '$brand_id')";
            if (mysqli_query($conn, $sql_insert_model)) {
                $model_id = mysqli_insert_id($conn);
            } else {
                die("Error inserting model: " . mysqli_error($conn));
            }
        }

        // Insert the new vehicle into the cars table
        $sql_insert_vehicle = "INSERT INTO cars (model_id, brand_id, release_year, battery_capacity, range_max, charge_time, fastcharge_time, drive, cargo_vol, number_of_seats, number_available, cost_per_day)
                           VALUES ('$model_id', '$brand_id', '$release_year', '$battery_capacity', '$range_max', '$charge_time', '$fastcharge_time', '$drive', '$cargo_vol', '$number_of_seats', '$number_available', '$cost_per_day')";

        if (mysqli_query($conn, $sql_insert_vehicle)) {
            // Handle file upload
            $upload_result = handle_file_upload($_FILES['car_image'], $model_name);
            if ($upload_result !== "Success!") {
                echo "<script>alert('$upload_result');</script>";
                exit();
            }
            echo "<script>
                alert('Vehicle added successfully.');
                window.location.href = 'vehiclesAdmin.php';
              </script>";
        } else {
            die("Error inserting vehicle: " . mysqli_error($conn));
        }
    } else {
        echo "<script>alert('Please check input formatting.');</script>";
    }
}

if (isset($_POST['deleteVehicle'])) {
// Handle delete vehicle form submission
    $model_id = $_POST['model_id'];

// Delete SQL statement
    $sql_delete = "DELETE FROM cars WHERE model_id = '$model_id'";
    $sql_delete_rentals = "DELETE FROM rentals WHERE carmodel_id = '$model_id'";

    if ($conn->query($sql_delete) === TRUE && $conn->query($sql_delete_rentals) === TRUE) {
        echo "<script>
            alert('Vehicle deleted successfully.');
            window.location.href = 'vehiclesAdmin.php';
          </script>";
    } else {
        echo "<script>alert('Error deleting record:  . $conn->error');</script>";
    }
}

// Handle form submission for editing details
if (isset($_POST['editDetails'])) {
    $car_id = $_POST['car_id'];
    $battery_capacity = sanitize_input($_POST['battery_capacity']);
    $range_min = sanitize_input($_POST['range_min']);
    $range_max = sanitize_input($_POST['range_max']);
    $accel_0_100 = sanitize_input($_POST['accel_0_100']);
    $horsepower = sanitize_input($_POST['horsepower']);
    $charge_time = sanitize_input($_POST['charge_time']);
    $fastcharge_time = sanitize_input($_POST['fastcharge_time']);
    $drive = sanitize_input($_POST['drive']);
    $cargo_vol = sanitize_input($_POST['cargo_vol']);
    $car_body = sanitize_input($_POST['car_body']);
    $number_of_seats = sanitize_input($_POST['number_of_seats']);
    $number_available = sanitize_input($_POST['number_available']);
    $cost_per_day = sanitize_input($_POST['cost_per_day']);

    $errors = true;

    // Verify numeric fields
    if (!is_numeric($release_year) || !is_numeric($battery_capacity) || !is_numeric($range_min) || !is_numeric($range_max) || !is_numeric($charge_time) || !is_numeric($charge_time) || !is_numeric($fastcharge_time) || !is_numeric($cargo_vol) || !is_numeric($number_of_seats) || !is_numeric($cost_per_day) || !is_numeric($number_available)) {
        $errors = false;
    }

    // Check for invalid characters
    $fields = [
        'Model Name' => $model_name,
        'Brand Name' => $brand_name,
        'Release Year' => $release_year,
        'Battery Capacity' => $battery_capacity,
        'Range Max' => $range_max,
        'Charge Time' => $charge_time,
        'Fast Charge Time' => $fastcharge_time,
        'Drive' => $drive,
        'Cargo Volume' => $cargo_vol,
        'Number of Seats' => $number_of_seats,
        'Number Available' => $number_available,
        'Cost per Day' => $cost_per_day
    ];

    foreach ($fields as $field_name => $value) {
        if (contains_invalid_characters($value)) {
            $errors = false;
            break;
        }
    }

    if ($errors) {
        // Update SQL statement
        $sql_update = "UPDATE cars SET
        battery_capacity = '$battery_capacity',
        range_min = '$range_min',
        range_max = '$range_max',
        accel_0_100 = '$accel_0_100',
        horsepower = '$horsepower',
        charge_time = '$charge_time',
        fastcharge_time = '$fastcharge_time',
        drive = '$drive',
        cargo_vol = '$cargo_vol',
        car_body = '$car_body',
        number_of_seats = '$number_of_seats',
        number_available = '$number_available',
        cost_per_day = '$cost_per_day'
        WHERE model_id = '$car_id'";

        if ($conn->query($sql_update) === TRUE) {
// Handle file upload if a file is provided
            if (!empty($_FILES['car_image']['name'])) {
                $model_name_query = "SELECT model_name FROM car_models WHERE model_id = '$car_id'";
                $result = $conn->query($model_name_query);
                $model_name = $result->fetch_assoc()['model_name'];
                $upload_result = handle_file_upload($_FILES['car_image'], $model_name);
                if ($upload_result !== "Success!") {
                    echo "<script> alert('$upload_result');
                window.location.href = 'vehiclesAdmin.php';
                </script>";
                    exit();
                }
            }
            // Redirect to the same page to avoid resubmission
            echo "<script> window.location.href = 'vehiclesAdmin.php';
                </script>";
        } else {
            echo "<script>alert('Error updating record: . $conn->error');</script>";
        }
    } else {
        echo "<script>alert('Please check input formatting.');</script>";
    }
}

// Determine sorting criteria
$sort_criteria = isset($_GET['sort']) ? $_GET['sort'] : 'battery_capacity';

// Determine filter criteria
$filter_brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$filter_drive = isset($_GET['drive']) ? $_GET['drive'] : '';
$filter_body = isset($_GET['body']) ? $_GET['body'] : '';

// SQL query to fetch details of all cars sorted by selected criteria
$sql = "SELECT car_brands.brand_id, car_brands.brand_name, car_models.model_id, car_models.model_name, cars.release_year, cars.battery_capacity, cars.range_min, cars.range_max, cars.accel_0_100, cars.horsepower, cars.charge_time, cars.fastcharge_time, cars.drive, cars.cargo_vol, cars.car_body, cars.number_of_seats, cars.number_available, cars.cost_per_day 
        FROM cars 
        JOIN car_models ON cars.model_id = car_models.model_id
        JOIN car_brands ON cars.brand_id = car_brands.brand_id";

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

// Fetch brands
$sql_brands = "SELECT * FROM car_brands";
$result_brands = $conn->query($sql_brands);

$car_brands = array();
if ($result_brands->num_rows > 0) {
    while ($row = $result_brands->fetch_assoc()) {
        $car_brands[] = $row;
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
    <title>ElectraDrive - Administrator</title>
    <link rel="stylesheet" href="../css/vehiclesAdminStyle.css">
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
    <h1>Vehicle information</h1>
</div>

<div class="container-fluid mt-4 c">
    <!-- Add Vehicle Form -->
    <div class="row text-end"><a href="#" class="addVehicleLink">Add Vehicle</a></div>
    <form method="post" enctype="multipart/form-data" class="addVehicleForm" style="display: none;">
        <input type="hidden" name="addVehicle" value="1">
        <p>Input must contain only letters or digits (depending on the data type). <br> Special characters are not
            allowed except <b>. , - , +</b>.</p>
        <div class="mb-3">
            <label for="model_name" class="form-label">Model Name:</label>
            <input type="text" class="form-control" id="model_name" name="model_name" required>
        </div>
        <div class="mb-3">
            <label for="brand_id" class="form-label">Brand:</label>
            <select class="form-select" id="brand_name" name="brand_name" required>
                <option value="">Select Brand</option>
                <?php foreach ($car_brands as $brands) { ?>
                    <option value="<?php echo $brands['brand_name']; ?>"><?php echo $brands['brand_name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="release_year" class="form-label">Release Year:</label>
            <input type="text" class="form-control" id="release_year" name="release_year" required>
        </div>
        <div class="mb-3">
            <label for="battery_capacity" class="form-label">Battery Capacity (kWh):</label>
            <input type="text" class="form-control" id="battery_capacity" name="battery_capacity" required>
        </div>
        <div class="mb-3">
            <label for="range_min" class="form-label">Minimum Range (km):</label>
            <input type="text" class="form-control" id="range_min" name="range_min" required>
        </div>
        <div class="mb-3">
            <label for="range_max" class="form-label">Maximum Range (km):</label>
            <input type="text" class="form-control" id="range_max" name="range_max" required>
        </div>
        <div class="mb-3">
            <label for="charge_time" class="form-label">Charge Time (hrs):</label>
            <input type="text" class="form-control" id="charge_time" name="charge_time" required>
        </div>
        <div class="mb-3">
            <label for="fastcharge_time" class="form-label">Fastcharge Time (min):</label>
            <input type="text" class="form-control" id="fastcharge_time" name="fastcharge_time" required>
        </div>
        <div class="mb-3">
            <label for="drive" class="form-label">Drive:</label>
            <input type="text" class="form-control" id="drive" name="drive" required>
        </div>
        <div class="mb-3">
            <label for="cargo_vol" class="form-label">Cargo Volume (L):</label>
            <input type="text" class="form-control" id="cargo_vol" name="cargo_vol" required>
        </div>
        <div class="mb-3">
            <label for="number_of_seats" class="form-label">Number of Seats:</label>
            <input type="text" class="form-control" id="number_of_seats" name="number_of_seats" required>
        </div>
        <div class="mb-3">
            <label for="number_available" class="form-label">Number Available:</label>
            <input type="text" class="form-control" id="number_available" name="number_available" required>
        </div>
        <div class="mb-3">
            <label for="cost_per_day" class="form-label">Cost Per Day (€):</label>
            <input type="text" class="form-control" id="cost_per_day" name="cost_per_day" required>
        </div>
        <div class="mb-3">
            <label for="car_image_add">Car Image (PNG only):</label>
            <input type="file" class="form-control" id="car_image_add" name="car_image" accept=".png" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Vehicle</button>
    </form>

    <!-- Delete Vehicle Form -->
    <div class="row text-end"><a href="#" class="deleteVehicleLink">Delete Vehicle</a></div>
    <form method="post" class="deleteVehicleForm" style="display: none;">
        <input type="hidden" name="deleteVehicle" value="1">
        <div class="mb-3">
            <p><b>!</b> All rentals associated with this car model will be deleted.</p>
            <label for="car_id_delete" class="form-label">Select Vehicle to Delete:</label>
            <select class="form-select" id="car_id_delete" name="model_id" required>
                <option value="">Select Vehicle</option>
                <?php foreach ($car_details as $car) { ?>
                    <option value="<?php echo $car['model_id']; ?>"><?php echo $car['brand_name'] . ' ' . $car['model_name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <button type="submit" class="btn btn-danger">Delete Vehicle</button>
    </form>
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
            <li><a class="dropdown-item" href="?sort=cost_per_day">Cost per day</a></li>
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
                                <p class="text-end mt-3 nba">Number
                                    available: <?php echo $car["number_available"]; ?></p>
                                <p class="text-center mt-4 mx-auto pr">Price: <?php echo $car["cost_per_day"]; ?>
                                    €/day</p>

                                <!-- Edit details link -->
                                <a href="#" class="editDetailsLink" data-car-id="<?php echo $car['model_id']; ?>">Edit
                                    details</a>

                                <!-- Edit details form -->
                                <form method="post" class="editDetailsForm"
                                      enctype="multipart/form-data" data-car-id="<?php echo $car['model_id']; ?>"
                                      style="display: none;">
                                    <input type="hidden" name="editDetails" value="1">
                                    <input type="hidden" name="car_id" value="<?php echo $car['model_id']; ?>">
                                    <div class="mb-3">
                                        <label for="battery_capacity" class="form-label">Battery Capacity (kWh):</label>
                                        <input type="text" class="form-control" id="battery_capacity"
                                               name="battery_capacity"
                                               value="<?php echo $car['battery_capacity']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="range_min" class="form-label">Minimum range (km):</label>
                                        <input type="text" class="form-control" id="range_min" name="range_min"
                                               value="<?php echo $car['range_min']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="range_max" class="form-label">Maximum Range (km):</label>
                                        <input type="text" class="form-control" id="range_max" name="range_max"
                                               value="<?php echo $car['range_max']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="accel_0_100" class="form-label">Acceleration 0-100 km/h:</label>
                                        <input type="text" class="form-control" id="accel_0_100" name="accel_0_100"
                                               value="<?php echo $car['accel_0_100']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="horsepower" class="form-label">Horsepower:</label>
                                        <input type="text" class="form-control" id="horsepower" name="horsepower"
                                               value="<?php echo $car['horsepower']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="charge_time" class="form-label">Charge Time (hrs):</label>
                                        <input type="text" class="form-control" id="charge_time" name="charge_time"
                                               value="<?php echo $car['charge_time']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="fastcharge_time" class="form-label">Fastcharge Time (min):</label>
                                        <input type="text" class="form-control" id="fastcharge_time"
                                               name="fastcharge_time"
                                               value="<?php echo $car['fastcharge_time']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="drive" class="form-label">Drive:</label>
                                        <input type="text" class="form-control" id="drive" name="drive"
                                               value="<?php echo $car['drive']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cargo_vol" class="form-label">Cargo Volume (L):</label>
                                        <input type="text" class="form-control" id="cargo_vol" name="cargo_vol"
                                               value="<?php echo $car['cargo_vol']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="car_body" class="form-label">Car body:</label>
                                        <input type="text" class="form-control" id="car_body" name="car_body"
                                               value="<?php echo $car['car_body']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="number_of_seats" class="form-label">Number of Seats:</label>
                                        <input type="text" class="form-control" id="number_of_seats"
                                               name="number_of_seats"
                                               value="<?php echo $car['number_of_seats']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="number_available" class="form-label">Number available::</label>
                                        <input type="text" class="form-control" id="number_available"
                                               name="number_available"
                                               value="<?php echo $car['number_available']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cost_per_day" class="form-label">Cost per day (€):</label>
                                        <input type="text" class="form-control" id="cost_per_day" name="cost_per_day"
                                               value="<?php echo $car['cost_per_day']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="car_image_edit_<?php echo $car['model_id']; ?>" class="form-label">Car
                                            Image (PNG only):</label>
                                        <input type="file" class="form-control"
                                               id="car_image_edit_<?php echo $car['model_id']; ?>" name="car_image"
                                               accept=".png">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<div id="copyright" class="row text-center">
    <p>Administrator - 2024 © ElectraDrive</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

<script>
    // JavaScript to toggle edit form visibility
    document.addEventListener('DOMContentLoaded', function () {
        const editLinks = document.querySelectorAll('.editDetailsLink');
        editLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const carId = this.getAttribute('data-car-id');
                const form = document.querySelector(`form.editDetailsForm[data-car-id="${carId}"]`);
                if (form) {
                    form.style.display = form.style.display === 'none' ? 'block' : 'none';
                }
            });
        });
    });

    // Script to toggle add vehicle form visibility
    document.querySelector('.addVehicleLink').addEventListener('click', function (e) {
        e.preventDefault();
        const addForm = document.querySelector('.addVehicleForm');
        addForm.style.display = addForm.style.display === 'none' ? 'block' : 'none';
    });

    // Script to toggle delete vehicle form visibility
    document.querySelector('.deleteVehicleLink').addEventListener('click', function (e) {
        e.preventDefault();
        const deleteForm = document.querySelector('.deleteVehicleForm');
        deleteForm.style.display = deleteForm.style.display === 'none' ? 'block' : 'none';
    });

    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>

</body>
</html>

