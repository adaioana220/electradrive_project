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

// Determine sorting criteria
$sort_criteria = isset($_GET['sort']) ? $_GET['sort'] : 'user_id';

$sql = "SELECT user_id, name, surname, user_email, phone_number, password, registration_datetime FROM users ORDER BY users.$sort_criteria ASC";

$result = $conn->query($sql);

// Query to fetch user information
$user_details = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $user_details[] = $row;
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
    <title>ElectraDrive - Home</title>
    <link rel="stylesheet" href="../css/usersAdmintyle.css">
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
    <h1>User information</h1>
</div>

<div class="container-fluid mt-4 c">
    <div class="row">
        <h2>> User data</h2>
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle mb-4" type="button" id="sortDropdown"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                Sort By
            </button>
            <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                <li><a class="dropdown-item" href="?sort=user_id">ID</a></li>
                <li><a class="dropdown-item" href="?sort=name">Name</a></li>
                <li><a class="dropdown-item" href="?sort=surname">Surname</a></li>
                <li><a class="dropdown-item" href="?sort=user_email">Email</a></li>
                <li><a class="dropdown-item" href="?sort=phone_number">Phone number</a></li>
                <li><a class="dropdown-item" href="?sort=password">Password</a></li>
                <li><a class="dropdown-item" href="?sort=registration_datetime">Password</a></li>
            </ul>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped-columns text-center">
                <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Surname</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone number</th>
                    <th scope="col">Password</th>
                    <th scope="col">Registration Date & Time</th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                <?php
                foreach ($user_details as $users) {
                        echo "<tr>
                            <td>{$users['user_id']}</td>
                            <td>{$users['name']}</td>
                            <td>{$users['surname']}</td>
                            <td><a href='mailto:{$users['user_email']}'>{$users['user_email']}</a></td>
                            <td>{$users['phone_number']}</td>
                            <td>{$users['password']}</td>
                            <td>{$users['registration_datetime']}</td>
                        </tr>";
                }
                if (empty($user_details)) {
                    echo "<tr><td colspan='6'>No users found.</td></tr>";
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
