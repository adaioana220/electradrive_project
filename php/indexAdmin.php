<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ElectraDrive - Administrator</title>
    <link rel="stylesheet" href="../css/indexAdminStyle.css">
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
    <h1>Administrator - Index</h1>
</div>

<div class="container-fluid mt-4 c">
    <div class="row">
        <h2>Welcome, admin</h2>
        <p>This is the index page of the administrator control panel.</p>
        <p>Please navigate through the navbar at the top of the page.</p>
        <a id="goToUserView" href="homepage.php">Go to user view</a>
        <a id="handleLogoutLink" href="logout.php">Log out</a>
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

