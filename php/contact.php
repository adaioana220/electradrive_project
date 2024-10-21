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

// Check if the session variable containing the email exists
if (isset($_SESSION['user_email'])) {
    $userEmail = $_SESSION['user_email'];

    // Retrieve name and surname from the database
    $userInfoQuery = "SELECT name, surname FROM users WHERE user_email = '$userEmail'";
    $userInfoResult = $conn->query($userInfoQuery);

    if ($userInfoResult->num_rows > 0) {
        $userInfo = $userInfoResult->fetch_assoc();
        $userName = $userInfo['name'];
        $userSurname = $userInfo['surname'];
    } else {
        // Handle the case where user information is not found
        $userName = 'User not found';
        $userSurname = 'User not found';
    }
} else {
    echo "<script>alert('You are not logged in. Please log in or create an account.')</script>";
    // Redirect the user to the login page if the session variable is not set
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and format the input
    $message = trim($_POST['message']);
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

    // Check if the message is within the length limits
    if (strlen($message) < 100) {
        echo "<script>alert('The message must be at least 100 characters long.')</script>";
    } elseif (strlen($message) > 700) {
        echo "<script>alert('The message cannot exceed 700 characters.')</script>";
    } else {
        // Process the form if the message length is within the allowed range
        echo "<script>alert('Message sent successfully! You will be contacted as soon as possible. Thank you.')</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Whitechapel Merchandise Store</title>
    <link rel="stylesheet" href="../css/contactStyle.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>


<nav class="navbar navbar-expand-xl">
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

<div class="wrapper">
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <i class="bi bi-envelope-fill"></i>
        <h1>Contact us</h1>

        <div class="name-box">
            <p id="p1">Name: <?php echo $userName; ?> <?php echo $userSurname; ?></p>
        </div>

        <div class="email">
            <p id="p2">Email: <?php echo $userEmail; ?></p>
        </div>

        <label id="message-label" for="message">Message (up to 700 characters):</label><br>
        <textarea id="message" name="message" rows="5" cols="50" maxlength="700"></textarea><br>

        <button type="submit" class="btn" value="Submit">Submit</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>
</html>