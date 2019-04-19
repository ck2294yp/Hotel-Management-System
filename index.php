<?php
// Starts a session with the user just as soon as they enter the page.
session_start();

// Creates minimum and maximum allowable dates upon booking.
$todayDate = date_create('now');
$minStartDate = date_format(date_add($todayDate, date_interval_create_from_date_string('+1 day')), 'Y-m-d');
$maxStartDate = date_format(date_add($todayDate, date_interval_create_from_date_string('+5 years')), 'Y-m-d');
$minEndDate = date_format(date_add($todayDate, date_interval_create_from_date_string('+2 days')), 'Y-m-d');
$maxEndDate = $maxStartDate = date_format(date_add($todayDate, date_interval_create_from_date_string('+5 years +1 day')), 'Y-m-d');


// If the user chooses a variable in the calenders. Sanitize and do a "Sanity check" on it. If all is well, store dates as a $_SESSION variable.
if (sizeof($_REQUEST) > 0) {
    # Imports a required library.
    require_once 'bin/inputSanitization.php';

    $checkInDate = @sanitizeDateString($_REQUEST['checkInDate']);
    $checkOutDate = @sanitizeDateString($_REQUEST['checkOutDate']);

    // If bad data is entered, stop it here.
    if ($checkInDate === false || $checkOutDate === false) {
        echo "<script> alert(\"Invalid date values entered. Please try again.\"); </script>";
        $_REQUEST = "";
        $checkInDate = "";
        $checkOutDate = "";

        // Throws out any ending dates that come BEFORE the starting dates (as that wouldn't make any sense).
    } elseif (strtotime($checkInDate) >= strtotime($checkOutDate)) {
        echo "<script> alert(\"Your ending reservation date must come AFTER the starting date!\"); </script>";
        $_REQUEST = "";
        $checkInDate = "";
        $checkOutDate = "";
        // If There are no problems. Then set the session to hold the values. Send the user to the "Searching rooms" page. (which will redirect
        // them to the sign in page if user is not already logged in).
    } else {
        $_SESSION['checkInDate'] = $_REQUEST['checkInDate'];
        $_SESSION['checkInDate'] = $_REQUEST['checkOutDate'];
        $_SESSION['stayDuration'] = $_REQUEST['checkInDate'] - $_REQUEST['checkInDate'];
        header('Location: searchRooms.php');
        exit;
    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <link rel="stylesheet" href="style.css" type="text/css"/>
    <style>

        .header li {
            display: inline-block;
            padding-bottom: 0px;
            padding-top: 0px;
        }

        .image {
            background-image: url("Lobby.jpg");
            background-repeat: no-repeat;
            height: 550px;
            background-size: cover;
        }

        .features {

            overflow: hidden;
        }

        .features ul {
            display: inline-block;
            float: left;
            padding-bottom: 5px;

        }

        .sr {
            overflow: hidden;
        }

        .sr li {
            display: inline-block;
            padding-bottom: 5px;
        }

        .sr2 li {
            display: inline-block;

        }

        .feat {

        }

        .contact {
            text-align: center;
            height: 100px;
            padding-bottom: 5px;
        }

        .footer {

            overflow: auto;
        }

        .footer ul {
            padding-bottom: 5px;
            list-style-type: none;
        }

        .footer li {
            display: inline-block;
            font-weight: bold;
        }

        body {
            background-color: white;

        }


        .details {
            position: absolute;
            width: 400px;
            height: 300px;
            display: flex;
            justify-content: center;
            font-family: serif;
            font-size: 20px;
            top: 70%;
            left: 70%;
        }


        .details input[type="submit"] {
            width: 100%;
            height: 45px;
            background-color: brown;
            color: white;
            display: flex;
            justify-content: center;
            box-shadow: 0 12px 16px 0 rgba(0, 0, 0, 0.24), 0 17px 50px 0 rgba(0, 0, 0, 0.19);
            font-size: 20px;

        }

        table tr td {
            border: 1px solid black;

        }


        .details input[type="number"] {
            width: 100%;
            height: 35px;
            font-size: 20px;
            font-family: Arial, Helvetica, sans-serif;
        }

        .detail input[type="text"] {
            width: 100%;
            height: 55px;
        }

        .details input[type="date"] {
            font-size: 20px;
            width: 100%;
            height: 35px;
            font-family: Arial, Helvetica, sans-serif;
        }


        .details select {
            width: 100%;
            height: 35px;
            font-size: 20px;
            font-family: Arial, Helvetica, sans-serif;
        }

        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
        }

    </style>
</head>
<body>
<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1 class="headOne"> Twin Cities Inn</h1>
</header>
<nav>
    <ul>
        <li><a href="index.php" class="active">Home</a></li>
        <li><a href="aboutUs.html">About</a></li>
        <li><a href="whyTci.html">Why TCI?</a></li>
        <li><a href="signIn.php">Sign In</a></li>
    </ul>
</nav>

<!-- testing check out date -->

<div class="details">
    <form action="index.php" method="post">

        <table>
            <tr>
                <th>Check-in Date</th>
                <th>Check-out Date</th>
            </tr>
            <tr>
                <th>
                    <input type="date" id="checkInDate" name="checkInDate"
                           min=<?php echo($minStartDate); ?> max=<?php echo($maxStartDate); ?> required>
                </th>
                <th>
                    <input type="date" id="checkOutDate" name="checkOutDate"
                           min=<?php echo($minStartDate); ?> max=<?php echo($maxEndDate); ?> required>
                </th>
            </tr>
        </table>
        <br>

        <input type="submit" value="Search Room">

    </form>
</div>

<!-- end testing -->

<!--homepage start -->

<div class="image">
    <div style="padding-left:400px">

    </div>
</div>

<div class="features">
    <div>
        <h2 style="text-align:center;">At TCI we offer different types of rooms, amenities, and our own exclusive
            rewards program.</h2>
        <ul>
            <ul style="padding-right:300px;padding-top:1px;" class="feat">
                <li style="list-style-type:none;"><h2>Specialty Rooms</h2></li>
                <li>Pets</li>
                <li>Chef</li>
                <li>Gaming</li>
            </ul>
            <ul>
                <li style="list-style-type:none;padding-right:280px;"><h2>Amenties</h2></li>
                <li>Valet</li>
                <li>Gym</li>
                <li>Bar</li>
                <li>Wifi</li>
                <li>Arcade</li>
                <li>Pool</li>
                <li>Pet Ground</li>
            </ul>
            <ul style="float:right;">
                <li style="list-style-type:none;"><h2>Rewards</h2></li>
                <li>Earn rewards for each stay</li>
                <li>Save money on your next stay</li>
            </ul>
        </ul>
    </div>
</div>

<!--start of specialy rooms section-->


<!--end of specialy rooms section-->
</div>
<!--start of amenties section-->

<!--end of amenties section-->
</div>
<div class="sr">
    <ul>
        <li>
            <img src="https://www.rd.com/wp-content/uploads/2016/01/06-13-things-your-hotel-desk-clerk-wont-tell-you-concierge.jpg"
                 style="width:500px;height:400px;" alt="Rewards photo"</li>
        <li>
            <div style="width:600px;height:400px;float:right;text-align:left;padding-left: 80px;">
                <h2 id="rewards"">Rewards</h2>
                <p>Twin Cities Inn has a rewards program!</p>
                <p>Earn rewards each time you book with Twins Cities Inn!</p>
                <p>Earn one point each night you stay.</p>
                <p>Use those points to save money on your next stay.</p></div>
        </li>
    </ul>
</div>

<div class="contact">
    <ul class="footer">
        <li>Contact Twin Cities Inn</li>
        <li>|</li>
        <li>Phone Number: 651-222-2020</li>
        <li>|</li>
        <li>Email: twincitiesinnsupport@twincitiesinn.com</li>
    </ul>
</div>

<script>

    function sRooms() {
        var x = document.getElementById('spr');
        if (x.style.display == "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }
    }

    function amenties() {
        var x = document.getElementById('amt');
        if (x.style.display == "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }
    }

</script>

<!--homepage end-->
<!-- <section class="sec3"></section> -->
<footer>
    <nav>
        <ul>
            <li><a href="#">Facebook</a href="#"></li>
            <li><a href="#">Twitter</a></li>
            <li><a href="#">Google+</a></li>
            <li><a href="#">© 2019 Twin Cities Inn</a></li>
        </ul>
    </nav>
</footer>
</body>
</html>