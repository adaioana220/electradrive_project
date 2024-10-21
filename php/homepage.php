<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ElectraDrive - Home</title>
    <link rel="stylesheet" href="../css/homepageStyle.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
    />
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

<div class="container-fluid p-0">
    <div class="row">
        <img src="../images/header.png" alt="Header"/>
    </div>
    <div class="row text-center">
        <div class="container mx-auto my-3" id="p1div"><h1 id="p1">Embrace sustainability without sacrificing style or
                performance as you navigate the roads.
                Revolutionize your travel experience with our meticulously maintained fleet, designed to deliver both
                environmental responsibility and driving pleasure.</h1></div>
    </div>
    <div class="row">
        <div class="card-group p-4">
            <div class="card">
                <img src="../images/green-technology.png" class="card-img-top img-responsive mx-auto" alt="...">
                <div class="card-body">
                    <h5 class="card-title">Sustainable</h5>
                    <p class="card-text">By offering electric cars, we actively contribute to reducing carbon emissions
                        and promoting eco-friendly transportation solutions.</p>
                </div>
            </div>
            <div class="card">
                <img src="../images/quality.png" class="card-img-top img-responsive mx-auto" alt="...">
                <div class="card-body">
                    <h5 class="card-title">High quality</h5>
                    <p class="card-text">Our company ensures that customers receive <br> top-notch vehicles and
                        exceptional service. <br> <b>Excellence is non-negotiable</b>.</p>
                </div>
            </div>
            <div class="card">
                <img src="../images/customer-centric.png" class="card-img-top img-responsive mx-auto" alt="...">
                <div class="card-body">
                    <h5 class="card-title">Client first</h5>
                    <p class="card-text">From user-friendly booking processes to transparent pricing and reliable
                        vehicle performance, the client's satisfaction is at the forefront.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row text-center mb-4">
        <h1 id="slogan">EMPOWER YOUR JOURNEY</h1>
    </div>
    <div class="row text-center mt-3">
        <div class="col-xl-6 align-self-center">
            <iframe src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d696.9492839318933!2d25.58862256967259!3d45.674972698192384!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zNDXCsDQwJzI5LjkiTiAyNcKwMzUnMjEuNCJF!5e0!3m2!1sen!2sro!4v1716903116043!5m2!1sen!2sro"
                    width="550" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        <div class="col-xl-6 align-self-center mt-0">
            <i class="bi bi-ev-front"></i><i class="bi bi-ev-station-fill"></i>
            <p id="p2">Explore the city in style with our electric car rentals!</p>
            <p id="p3">Use the map to find our location and start your journey towards a greener future today!</p>
            <div class="rent-button">
                <button type="button" class="btn"><a href="vehicles.php">RENT NOW</a></button>
            </div>
        </div>
    </div>
    <div id="copyright" class="row text-center">
        <p>2024 © ElectraDrive | <a href="contact.php">Contact us</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>
</html>