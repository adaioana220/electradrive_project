<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assigning POST data to session variables
    $_SESSION['carbrand_id'] = $_POST['carbrand_id']; // Store carbrand_id in session
    $_SESSION['carmodel_id'] = $_POST['carmodel_id']; // Store carmodel_id in session
    $_SESSION['car_name'] = $_POST['car_name']; // Store car_name in session

    // Redirect to 'rental.php' using JavaScript
    echo "<script>window.location.href='rental.php';</script>";
    exit(); // Exit PHP script after redirection
}

