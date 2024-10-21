<?php

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

function sanitize_input($data) {
    $data = trim($data);
    $data = htmlspecialchars($data);
    $data = stripslashes($data);
    return $data;
}

function contains_invalid_characters($input)
{
    // This regex matches any character that is not a letter, space, hyphen, dot, or plus sign
    return preg_match('/[^a-zA-Z\s\-+.]/', $input);
}

if (isset($_POST['addBrand'])) {

    // Handle adding new brand
    $new_brand = sanitize_input($_POST['new_brand']);

    $error = true;

    if (contains_invalid_characters($new_brand)) {
        $error = false;
    }

    if($error) {

        //Ensure the strings are stored with a capitalized first letter for ease of use
        $new_brand = ucfirst(strtolower($new_brand));

        // Check if the brand already exists
        $check_brand_query = "SELECT * FROM car_brands WHERE brand_name = ?";
        $stmt = $conn->prepare($check_brand_query);
        $stmt->bind_param("s", $new_brand);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script> alert('Brand already exists.');
                window.location.href = 'brandsAdmin.php';
                </script>";
        } else {
            // Insert the new brand into car_brands table
            $insert_brand_query = "INSERT INTO car_brands (brand_name) VALUES (?)";
            $stmt = $conn->prepare($insert_brand_query);
            $stmt->bind_param("s", $new_brand);

            if ($stmt->execute()) {
                echo "<script> alert('Brand added successfully.');
                window.location.href = 'brandsAdmin.php';
                </script>";
            } else {
                echo "<script>alert('Error adding brand: . $conn->error');</script>";
            }
        }

        $stmt->close();
    } else {
        echo "<script>alert('Please check input formatting.')</script>";
    }
}

if (isset($_POST['deleteBrand'])) {
// Handle delete vehicle form submission
    $brand_id = $_POST['brand_id'];

// Delete SQL statement
    $sql_delete = "DELETE FROM car_brands WHERE brand_id = '$brand_id'";
    $sql_delete_rentals = "DELETE FROM rentals WHERE carbrand_id = '$brand_id'";

    if ($conn->query($sql_delete) === TRUE && $conn->query($sql_delete_rentals) === TRUE) {
        echo "<script>
            alert('Brand deleted successfully. Vehicles associated with this brand have also been deleted.');
            window.location.href = 'brandsAdmin.php';
          </script>";
    } else {
        echo "<script>alert('Error deleting record:  . $conn->error');</script>";
    }
}

// Determine sorting criteria
$sort_criteria = isset($_GET['sort']) ? $_GET['sort'] : 'brand_id';

$sql = "SELECT brand_id, brand_name FROM car_brands ORDER BY car_brands.$sort_criteria ASC";

$result = $conn->query($sql);

// Fetch brands
$sql_brands = "SELECT * FROM car_brands";
$result_brands = $conn->query($sql_brands);

$car_brands = array();
if ($result_brands->num_rows > 0) {
    while ($row = $result_brands->fetch_assoc()) {
        $car_brands[] = $row;
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
    <link rel="stylesheet" href="../css/brandsAdminStyle.css">
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
    <h1>Brand information</h1>
</div>

<div class="container-fluid mt-4 c">
    <!-- Add Brand Form-->
    <div class="row text-end r1"><a href="#" class="addBrandLink mt-5">Add Brand</a></div>
    <form method="post" class="addBrandForm" style="display: none;">
        <p>Input must contain only letters. <br> Special characters are not allowed except <b>. , - , +</b>.</p>
        <input type="hidden" name="addBrand" value="1">
        <div class="mb-3">
            <label for="brand_name" class="form-label">Brand name:</label>
            <input type="text" class="form-control" id="brand_name" name="new_brand" required>
        </div>
        <button type="submit" class="btn btn-primary">Add brand</button>
    </form>

    <!-- Delete Brand Form -->
    <div class="row text-end r1"><a href="#" class="deleteBrandLink">Delete Brand</a></div>
    <form method="post" class="deleteBrandForm" style="display: none;">
        <input type="hidden" name="deleteBrand" value="1">
        <div class="mb-3">
            <p><b>!</b> All vehicles and rentals associated with this brand will be deleted.</p>
            <label for="delete_brand_id" class="form-label">Select Brand to Delete:</label>
            <select class="form-select" id="delete_brand_id" name="brand_id" required>
                <option value="">Select Brand</option>
                <?php foreach ($car_brands as $brands) { ?>
                    <option value="<?php echo $brands['brand_id']; ?>"><?php echo $brands['brand_name'] ?></option>
                <?php } ?>
            </select>
        </div>
        <button type="submit" class="btn btn-danger">Delete Brand</button>
    </form>
    <div class="row">
        <h2>> Brand data</h2>
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle mb-4" type="button" id="sortDropdown"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                Sort By
            </button>
            <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                <li><a class="dropdown-item" href="?sort=brand_id">ID</a></li>
                <li><a class="dropdown-item" href="?sort=brand_name">Name</a></li>
            </ul>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped-columns text-center">
                <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                <?php
                foreach ($car_brands as $brand) {
                    echo "<tr>
                            <td>{$brand['brand_id']}</td>
                            <td>{$brand['brand_name']}</td>
                        </tr>";
                }
                if (empty($car_brands)) {
                    echo "<tr><td colspan='6'>No brands found.</td></tr>";
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
<script>
    // Script to toggle add brand form visibility
    document.querySelector('.addBrandLink').addEventListener('click', function (e) {
        e.preventDefault();
        const addForm = document.querySelector('.addBrandForm');
        addForm.style.display = addForm.style.display === 'none' ? 'block' : 'none';
    });

    // Script to toggle delete brand form visibility
    document.querySelector('.deleteBrandLink').addEventListener('click', function (e) {
        e.preventDefault();
        const deleteForm = document.querySelector('.deleteBrandForm');
        deleteForm.style.display = deleteForm.style.display === 'none' ? 'block' : 'none';
    });
</script>
</body>
</html>
