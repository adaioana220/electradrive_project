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

// Check if the user is logged in
if (isset($_SESSION['user_email'])) {
    $userEmail = $_SESSION['user_email'];

    // Retrieve user information from the database
    $userInfoQuery = "SELECT user_id, name, surname, user_email, phone_number FROM users WHERE user_email = '$userEmail'";
    $userInfoResult = $conn->query($userInfoQuery);

    if ($userInfoResult->num_rows > 0) {
        $userInfo = $userInfoResult->fetch_assoc();
        $userId = $userInfo['user_id'];
        $userName = $userInfo['name'];
        $userSurname = $userInfo['surname'];
        $userEmail = $userInfo['user_email'];
        $userPhone = $userInfo['phone_number'];
    } else {
        // Handle the case where user information is not found
        $userName = 'User not found';
        $userSurname = 'User not found';
        $userEmail = 'User not found';
        $userPhone = 'User not found';
    }
} else {
    echo "<script>alert('You are not logged in. Please log in or create an account.')</script>";
    // Redirect the user to the login page if the session variable is not set
    header("Location: login.php");
    exit();
}

$userEmail = $_SESSION['user_email'];

// Handle change password form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['currentPassword']) && isset($_POST['newPassword'])) {
        // Change password form submitted
        $currentPassword = $_POST['currentPassword'];
        $newPassword = $_POST['newPassword'];

        // Fetch the current password from the database
        $query = "SELECT password FROM users WHERE user_email = '$userEmail'";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $dbPassword = $row['password'];

            // Verify the current password
            if ($currentPassword === $dbPassword) {
                // Validate the new password complexity
                if (strlen($newPassword) >= 8 && preg_match('/[A-Za-z]/', $newPassword) && preg_match('/\d/', $newPassword)) {
                    // Update the password in the database
                    $updateQuery = "UPDATE users SET password = '$newPassword' WHERE user_email = ?";
                    $stmt = $conn->prepare($updateQuery);
                    $stmt->bind_param("s", $userEmail);
                    if ($conn->query($updateQuery) === TRUE) {
                        echo "<script>alert('Password successfully changed.')</script>";
                    } else {
                        echo "<script>alert('Error updating password.')</script>";
                    }
                } else {
                    echo "<script>alert('Password must be at least 8 characters long, contain letters and at least a number.');</script>";
                }
            } else {
                echo "<script>alert('Current password is incorrect.')</script>";
            }
        }
    } elseif (isset($_POST['editDetails'])) {
        // Edit details form submitted
        $newName = $_POST['name'];
        $newSurname = $_POST['surname'];
        $newEmail = $_POST['email'];
        $newPhone = $_POST['phone'];

        // Validate the inputs
        $namePattern = "/^[a-zA-Z-]+$/";
        $phonePattern = "/^07[0-9]{8}$/";

        $valid = true;
        $errorString = "";

        // Name validation
        if (!preg_match($namePattern, $newName) || !preg_match($namePattern, $newSurname)) {
            // Surname validation
            $valid = false;
            echo "<script>alert('Name and surname must contain only letters and hyphens.');</script>";
        } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            // Email validation
            $valid = false;
            echo "<script>alert('Enter a valid email address.');</script>";
        } elseif (strpos($newEmail, '@electradrive.com') !== false) {
            // Check if email is from electradrive.com domain
            echo "<script>alert('Email addresses containing @electradrive.com are not allowed.');</script>";
        } elseif (!preg_match($phonePattern, $newPhone)) {
            // Phone validation
            $valid = false;
            echo "<script>alert('Phone number must be in Romanian format (ex. 07XXXXXXXX).');</script>";
        } else {
            // Check if email is already registered
            $checkEmailQuery = "SELECT * FROM users WHERE user_email = '$newEmail'";
            $checkEmailResult = $conn->query($checkEmailQuery);

            //Check if phone number is already registered
            $checkPhoneQuery = "SELECT * FROM users WHERE phone_number = '$newPhone'";
            $checkPhoneResult = $conn->query($checkPhoneQuery);

            if ($checkEmailResult->num_rows > 0) {
                echo "<script>alert('Email is already registered.');</script>";
            } elseif ($checkPhoneResult->num_rows > 0) {
                echo "<script>alert('Phone number has already been used.');</script>";
            }
        }
        if ($valid) {
            $updateQuery = "UPDATE users SET name = ?, surname = ?, user_email = ?, phone_number = ? WHERE user_email = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("sssss", $newName, $newSurname, $newEmail, $newPhone, $userEmail);
            if ($stmt->execute()) {
                // Update session email if the email was changed
                $_SESSION['user_email'] = $newEmail;
                // Update variables with new values
                $userName = $newName;
                $userSurname = $newSurname;
                $userEmail = $newEmail;
                $userPhone = $newPhone;
                echo "<script>alert('Details successfully updated.')</script>";
            } else {
                echo "<script>alert('Error updating details.')</script>";
            }
        }
    }
}

// Fetch rental history
$rentalHistoryQuery = "
SELECT r.carbrand_id, r.carmodel_id, r.pickup_date, r.pickup_time, r.dropoff_date, r.dropoff_time, c.cost_per_day, cb.brand_name, cm.model_name
FROM rentals r
JOIN car_brands cb ON r.carbrand_id = cb.brand_id
JOIN car_models cm ON r.carmodel_id = cm.model_id
JOIN cars c ON r.carmodel_id = c.model_id AND r.carbrand_id = c.brand_id
WHERE r.user_id = '$userId' AND r.dropoff_date < CURDATE()";
$rentalHistoryResult = $conn->query($rentalHistoryQuery);

// Fetch current rentals
$currentRentalsQuery = "
SELECT r.carbrand_id, r.carmodel_id, r.pickup_date, r.pickup_time, r.dropoff_date, r.dropoff_time, c.cost_per_day, cb.brand_name, cm.model_name
FROM rentals r
JOIN car_brands cb ON r.carbrand_id = cb.brand_id
JOIN car_models cm ON r.carmodel_id = cm.model_id
JOIN cars c ON r.carmodel_id = c.model_id AND r.carbrand_id = c.brand_id
WHERE r.user_id = '$userId' AND CURDATE() BETWEEN r.pickup_date AND r.dropoff_date";
$currentRentalsResult = $conn->query($currentRentalsQuery);

// Fetch upcoming rentals
$upcomingRentalsQuery = "
SELECT r.carbrand_id, r.carmodel_id, r.pickup_date, r.pickup_time, r.dropoff_date, r.dropoff_time, c.cost_per_day, cb.brand_name, cm.model_name
FROM rentals r
JOIN car_brands cb ON r.carbrand_id = cb.brand_id
JOIN car_models cm ON r.carmodel_id = cm.model_id
JOIN cars c ON r.carmodel_id = c.model_id AND r.carbrand_id = c.brand_id
WHERE r.user_id = '$userId' AND r.pickup_date > CURDATE()";
$upcomingRentalsResult = $conn->query($upcomingRentalsQuery);

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ElectraDrive - Home</title>
    <link rel="stylesheet" href="../css/accountStyle.css">
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
                <?php if ($_SESSION['user_email'] == "admin@electradrive.com") { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="indexAdmin.php">Admin</a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>

<div class="div text-center mt-5">
    <h1>Account</h1>
</div>

<div class="container-fluid mt-4 c">
    <div class="row">
        <h1>> User details</h1>
        <p>First name: <?php echo $userName; ?></p>
        <p>Surname: <?php echo $userSurname; ?></p>
        <p>Email: <?php echo $userEmail; ?></p>
        <p>Phone number: <?php echo $userPhone; ?></p>
        <div class="container text-end">
            <script>
                function togglePasswordFields() {
                    var fields = document.getElementById("passwordFields");
                    fields.style.display = fields.style.display === "none" ? "block" : "none";
                }

                function toggleEditDetailsFields() {
                    var fields = document.getElementById("editDetailsFields");
                    fields.style.display = fields.style.display === "none" ? "block" : "none";
                }
            </script>
            <br>
            <div class="container text-end">
                <a href="#" onclick="togglePasswordFields()">Change password</a>
            </div>
            <div id="passwordFields" style="display: none;">
                <form method="post">
                    <label for="currentPassword">Current Password:</label>
                    <input type="password" id="currentPassword" name="currentPassword" required><br>
                    <label for="newPassword">New Password:</label>
                    <input type="password" id="newPassword" name="newPassword" required><br>
                    <button type="submit">Submit</button>
                </form>
            </div>
            <a id="editDetailsLink" href="#" onclick="toggleEditDetailsFields()">Edit details</a>
            <div id="editDetailsFields" style="display: none;">
                <form method="post">
                    <input type="hidden" name="editDetails" value="1">
                    <label for="name">First Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($userName); ?>"
                           required><br>
                    <label for="surname">Surname:</label>
                    <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($userSurname); ?>"
                           required><br>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>"
                           required><br>
                    <label for="phone">Phone Number:</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($userPhone); ?>"
                           required><br>
                    <button type="submit">Submit</button>
                </form>
            </div>
            <br>
            <a id="handleLogoutLink" href="logout.php">Log out</a>
        </div>
    </div>
    <div class="row">
        <h1>> Rental history</h1>
        <div class="table-responsive">
            <table class="table table-hover table-striped-columns text-center">
                <thead>
                <tr>
                    <th scope="col"></th>
                    <th scope="col">Car Brand</th>
                    <th scope="col">Car Model</th>
                    <th scope="col">Pickup date</th>
                    <th scope="col">Pickup time</th>
                    <th scope="col">Dropoff date</th>
                    <th scope="col">Dropoff time</th>
                    <th scope="col">Rental duration (Days)</th>
                    <th scope="col">Price/day</th>
                    <th scope="col">Total</th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                <?php
                if ($rentalHistoryResult->num_rows > 0) {
                    $i = 1;
                    while ($row = $rentalHistoryResult->fetch_assoc()) {
                        $rentalStart = new DateTime($row['pickup_date']);
                        $rentalEnd = new DateTime($row['dropoff_date']);
                        $rentalDuration = $rentalStart->diff($rentalEnd)->days + 1; // Include both start and end date
                        $totalPrice = $rentalDuration * $row['cost_per_day'];
                        echo "<tr>
                            <th scope='row'>{$i}</th>
                            <td>{$row['brand_name']}</td>
                            <td>{$row['model_name']}</td>
                            <td>{$row['pickup_date']}</td>
                            <td>{$row['pickup_time']}</td>
                             <td>{$row['dropoff_date']}</td>
                            <td>{$row['dropoff_time']}</td>
                            <td>{$rentalDuration}</td>
                            <td>{$row['cost_per_day']}</td>
                            <td>{$totalPrice}</td>
                        </tr>";
                        $i++;
                    }
                } else {
                    echo "<tr><td colspan='7'>No rental history found.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <h1>> Current rentals</h1>
        <div class="table-responsive">
            <table class="table table-hover table-striped-columns text-center">
                <thead>
                <tr>
                    <th scope="col"></th>
                    <th scope="col">Car Brand</th>
                    <th scope="col">Car Model</th>
                    <th scope="col">Pickup date</th>
                    <th scope="col">Pickup time</th>
                    <th scope="col">Dropoff date</th>
                    <th scope="col">Dropoff time</th>
                    <th scope="col">Rental duration (Days)</th>
                    <th scope="col">Price/day</th>
                    <th scope="col">Total</th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                <?php
                if ($currentRentalsResult->num_rows > 0) {
                    $i = 1;
                    while ($row = $currentRentalsResult->fetch_assoc()) {
                        $rentalStart = new DateTime($row['pickup_date']);
                        $rentalEnd = new DateTime($row['dropoff_date']);
                        $rentalDuration = $rentalStart->diff($rentalEnd)->days + 1; // Include both start and end date
                        $totalPrice = $rentalDuration * $row['cost_per_day'];
                        echo "<tr>
                            <th scope='row'>{$i}</th>
                            <td>{$row['brand_name']}</td>
                            <td>{$row['model_name']}</td>
                            <td>{$row['pickup_date']}</td>
                            <td>{$row['pickup_time']}</td>
                             <td>{$row['dropoff_date']}</td>
                            <td>{$row['dropoff_time']}</td>
                            <td>{$rentalDuration}</td>
                            <td>{$row['cost_per_day']}</td>
                            <td>{$totalPrice}</td>
                        </tr>";
                        $i++;
                    }
                } else {
                    echo "<tr><td colspan='7'>No current rentals found.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <h1>> Upcoming rentals</h1>
        <div class="table-responsive">
            <table class="table table-hover table-striped-columns text-center">
                <thead>
                <tr>
                    <th scope="col"></th>
                    <th scope="col">Car Brand</th>
                    <th scope="col">Car Model</th>
                    <th scope="col">Pickup date</th>
                    <th scope="col">Pickup time</th>
                    <th scope="col">Dropoff date</th>
                    <th scope="col">Dropoff time</th>
                    <th scope="col">Rental duration (Days)</th>
                    <th scope="col">Price/day</th>
                    <th scope="col">Total</th>
                </tr>
                </thead>
                <tbody class="table-group-divider">
                <?php
                if ($upcomingRentalsResult->num_rows > 0) {
                    $i = 1;
                    while ($row = $upcomingRentalsResult->fetch_assoc()) {
                        $rentalStart = new DateTime($row['pickup_date']);
                        $rentalEnd = new DateTime($row['dropoff_date']);
                        $rentalDuration = $rentalStart->diff($rentalEnd)->days + 1; // Include both start and end date
                        $totalPrice = $rentalDuration * $row['cost_per_day'];
                        echo "<tr>
                            <th scope='row'>{$i}</th>
                            <td>{$row['brand_name']}</td>
                            <td>{$row['model_name']}</td>
                            <td>{$row['pickup_date']}</td>
                            <td>{$row['pickup_time']}</td>
                             <td>{$row['dropoff_date']}</td>
                            <td>{$row['dropoff_time']}</td>
                            <td>{$rentalDuration}</td>
                            <td>{$row['cost_per_day']}</td>
                            <td>{$totalPrice}</td>
                        </tr>";
                        $i++;
                    }
                } else {
                    echo "<tr><td colspan='7'>No upcoming rentals found.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <h1>> Need assistance?</h1>
        <p>Please contact our customer support team <a href="contact.php">here</a>.</p>
    </div>
</div>

<div id="copyright" class="row text-center">
    <p>2024 © ElectraDrive</p>
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




