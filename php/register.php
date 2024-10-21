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

// Function to validate email format
function validateEmail($user_email) {
    return filter_var($user_email, FILTER_VALIDATE_EMAIL);
}

// Function to validate name and surname
function validateNameSurname($name) {
    return preg_match('/^[A-Za-z-]+$/', $name);
}

// Function to validate Romanian phone number format
function validatePhoneNumber($phone_number) {
    // Romanian phone numbers should start with 07 and be 10 digits long
    return preg_match('/^07\d{8}$/', $phone_number);
}

// Handle registration
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $surname = $_POST["surname"];
    $user_email = $_POST["user_email"];
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];
    $phone_number = $_POST["phone_number"];

    // Validate name and/or surname
    if (!validateNameSurname($name) || !validateNameSurname($surname)) {
        echo "<script>alert('Invalid characters in the name or surname. Only letters and hyphens (-) are allowed.');</script>";
    }
    // Validate email
    elseif (!validateEmail($user_email)) {
        echo "<script>alert('Invalid email address.');</script>";
    }
    // Check if email is from electradrive.com domain
    elseif (strpos($user_email, '@electradrive.com') !== false) {
        echo "<script>alert('Registration with @electradrive.com email addresses is not allowed.');</script>";
    }
    // Validate password
    elseif (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        echo "<script>alert('Password must be at least 8 characters long, contain letters and at least a number.');</script>";
    }
    // Confirm password
    elseif ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match.');</script>";
    }
    // Validate phone number
    elseif (!validatePhoneNumber($phone_number)) {
        echo "<script>alert('Invalid phone number format. Please enter a valid Romanian phone number starting with (07) and containing 10 digits. No special characters or spaces.');</script>";
    } else {
        // Check if email is already registered
        $checkEmailQuery = "SELECT * FROM users WHERE user_email = '$user_email'";
        $checkEmailResult = $conn->query($checkEmailQuery);

        //Check if phone number is already registered
        $checkPhoneQuery = "SELECT * FROM users WHERE phone_number = '$phone_number'";
        $checkPhoneResult = $conn->query($checkPhoneQuery);

        if ($checkEmailResult->num_rows > 0) {
            echo "<script>alert('Email is already registered.');</script>";
        } elseif ($checkPhoneResult->num_rows > 0) {
            echo "<script>alert('Phone number has already been used.');</script>";
        } else {
            // Insert user data into the database with registration datetime
            $current_datetime = date("Y-m-d H:i:s");
            $insertQuery = "INSERT INTO users (name, surname, user_email, password, phone_number, registration_datetime) VALUES ('$name', '$surname', '$user_email', '$password', '$phone_number', '$current_datetime')";

            if ($conn->query($insertQuery) === TRUE) {
                echo "<script>alert('Registration successful. Redirecting to login page.')</script>";
                echo "<script>window.location.href = 'login.php';</script>";
                exit();
            } else {
                echo "<script>alert('Error: " . $insertQuery . "\\n" . $conn->error . "');</script>";
            }
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
    <title>Registration Form</title>
    <link rel="stylesheet" href="../css/registerStyle.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<div class="wrapper">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <h1>Registration Form</h1>
        <div class="input-box">
            <input type="text" placeholder="Name" name="name" required>
            <i class="bi bi-person-fill"></i>
        </div>
        <div class="input-box">
            <input type="text" placeholder="Surname" name="surname" required>
            <i class="bi bi-person"></i>
        </div>
        <div class="input-box">
            <input type="email" placeholder="Email" name="user_email" required>
            <i class="bi bi-envelope-fill"></i>
        </div>
        <div class="input-box">
            <input type="password" placeholder="Password" name="password" required>
            <i class="bi bi-lock"></i>
        </div>
        <div class="input-box">
            <input type="password" placeholder="Confirm password" name="confirm_password" required>
            <i class="bi bi-lock-fill"></i>
        </div>
        <div class="input-box">
            <input type="number" placeholder="Phone number (RO)" name="phone_number" required>
            <i class="bi bi-telephone"></i>
        </div>
        <button type="submit" class= "btn" value="Register">Register</button>
        <div class="back-login">
            <p>Back to <a href="login.php">login page</a></p>
        </div>
    </form>
</body>
</html>
