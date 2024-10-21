<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ElectraDrive - Home</title>
    <link rel="stylesheet" href="../css/aboutStyle.css">
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

<div class="container-fluid">
    <div class="row text-center">
        <pi id="title">About us</pi>
    </div>
    <div class="row">
        <div class="card-group">
            <div class="col mx-1">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Who We Are</h5>
                        <p class="card-text">ElectraDrive is a forward-thinking electric car rental company dedicated to
                            offering clean, efficient, and affordable transportation solutions. Founded by a team
                            of passionate environmentalists and automotive enthusiasts, we believe in the power of green
                            technology to shape a better future.</p>
                    </div>
                </div>
            </div>
            <div class="col mx-1">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Our Mission</h5>
                        <p class="card-text">At ElectraDrive, we are committed to revolutionizing the car rental
                            experience by providing eco-friendly, electric vehicles. <br> Our mission is to make
                            sustainable transportation accessible and convenient for everyone, reducing our
                            carbon footprint one ride at a time.</p>
                    </div>
                </div>
            </div>
            <div class="col mx-1">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Our Fleet</h5>
                        <p class="card-text">Our fleet consists exclusively of top-of-the-line electric vehicles,
                            ensuring you enjoy a smooth, quiet, and emission-free ride. <br> From compact cars perfect
                            for city driving to spacious SUVs ideal for family trips, we have a vehicle to meet your
                            needs.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="aboutsect2" class="container">
        <div class="row">
            <div class="col-xl-6">
                <h1 id="envtitle">Our Commitment to the Environment</h1>
                <p id="p2about"><br>At ElectraDrive, sustainability is at the core of everything we do. We recognize the
                    critical need to address climate change and are dedicated to playing our part in creating a greener
                    future. Our fleet of electric vehicles is powered by renewable energy sources, drastically reducing
                    harmful emissions compared to traditional gasoline-powered cars. </p>
                <p id="p3about">We believe that small actions can lead to
                    significant changes, and we encourage our customers to join us on this journey. By choosing
                    ElectraDrive, you are not only opting for a superior driving experience but also contributing to a
                    cleaner, healthier planet. Together, we can drive the change towards a more sustainable future!</p>
            </div>
            <div class="col-xl-6 g-0">
                <img id="img1" class="img-fluid" src="/images/aboutimg1.png" alt="" width="600" height="550">
            </div>
        </div>
    </div>
</div>
<div class="container">
    <div class="row text-center">
        <h1 id="contactinfotitle">Contact information</h1>
        <div id="contactinfocontent" class="col-xl-6 align-self-center">
            <h1>Headquarters</h1>
            <p>Bulevardul Griviței 53, Brașov, România</p>
            <h1>Open hours</h1>
            <p>Mon - Fri: 10:00 - 18:00</p>
            <p>Sat: 12:00 - 16:00</p>
            <p>Sun: Closed</p>
            <h1>E-mail</h1>
            <a href="mailto:electradrive@info.com">electradrive@info.com</a>
            <h1 class="my-3">Telephone</h1>
            <a href="tel:+40 268 123 456">+40 268 123 456</a> <br>
            <a href="tel:+40 268 654 321">+40 268 654 321</a>
        </div>
        <div class="col-xl-6 align-self-center">
            <iframe src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d696.9492839318933!2d25.58862256967259!3d45.674972698192384!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zNDXCsDQwJzI5LjkiTiAyNcKwMzUnMjEuNCJF!5e0!3m2!1sen!2sro!4v1716903116043!5m2!1sen!2sro"
                    width="550" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
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
