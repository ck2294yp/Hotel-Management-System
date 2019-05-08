<?php

// Sends user the disclaimer message.
echo'<script src="/displayError.js"></script>';
echo("<script> disclaimerMsg(); </script>");


// Starts a session with the user just as soon as they enter the page.
session_start();

require_once 'bin/inputSanitization.php';


// Creates minimum and maximum allowable dates upon booking.
$todayDate = date_create('now');
$minStartDate = date_format(date_add($todayDate, date_interval_create_from_date_string('+1 day')), 'Y-m-d');
$maxStartDate = date_format(date_add($todayDate, date_interval_create_from_date_string('+5 years')), 'Y-m-d');
$minEndDate = date_format(date_add($todayDate, date_interval_create_from_date_string('+2 days')), 'Y-m-d');
$maxEndDate = $maxStartDate = date_format(date_add($todayDate, date_interval_create_from_date_string('+5 years +1 day')), 'Y-m-d');

// If the user chooses a variable in the calenders. Sanitize and do a "Sanity check" on it. If all is well, store dates as a $_SESSION variable.
if (sizeof($_REQUEST) > 0) {

    // Sanitizes the inputs
    $checkInDate = sanitizeDateString($_REQUEST['checkInDate']);
    $checkOutDate = sanitizeDateString($_REQUEST['checkOutDate']);

    // Throws out any ending dates that come BEFORE the starting dates (as that wouldn't make any sense).
    if (strtotime($checkInDate) >= strtotime($checkOutDate)) {
        echo'<script src="/displayError.js"></script>';
        echo("<script> outOfOrderBookingDatesMsg(); </script>");



        // If There are no problems. Then set the session to hold the values. Send the user to the "Searching rooms" page. (which will redirect
        // them to the sign in page if user is not already logged in).
    } else {
        $_SESSION['checkInDate'] = $checkInDate;
        $_SESSION['checkOutDate'] = $checkOutDate;

        // Convert Date string back into date variables.
        $checkInDate = DateTime::createFromFormat('Y-m-d', $checkInDate);
        $checkOutDate = DateTime::createFromFormat('Y-m-d', $checkOutDate);

        // Get the stay duration.
        $_SESSION['stayDuration'] = (date_diff($checkInDate, $checkOutDate))->format("%a");
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
</head>

<body>
<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1 class="headOne"> Twin Cities Inn</h1>
</header>

<!--Navigation Bar-->
<?php include 'bin/nav.php'; ?>

<section class="sec1Member">
    <div class="details" style="padding-right: 10%">
        <form action="index.php" method="post">
            <table>
                <tr>
                    <th>Check-in Date</th>
                    <th>Check-out Date</th>
                </tr>
                <tr>
                    <th>
                        <input type= "date"
                               id= "checkInDate"
                               name= "checkInDate"
                               required
                               value=<?php
                               if (array_key_exists('checkInDate', $_SESSION) === true){
                                   echo $_SESSION['checkInDate'];
                               } else {
                                   echo "";
                               }
                               ?>
                               min= <?php echo($minStartDate); ?>
                               max= <?php echo($maxStartDate); ?>
                        >
                    </th>
                    <th>
                        <input type= "date"
                               id= "checkOutDate"
                               name= "checkOutDate"
                               required
                               value=<?php
                               if (array_key_exists('checkOutDate', $_SESSION) === true){
                                   echo $_SESSION['checkOutDate'];
                               } else {
                                   echo "";
                               }
                               ?>
                               min= <?php echo($minStartDate); ?>
                               max= <?php echo($maxEndDate); ?>
                        >
                    </th>
                </tr>
            </table>
            <br>

            <input type="submit" value="Search Room">

        </form>
    </div>

    <!--homepage start -->
    <div class="image">
        <div style="padding-left:400px">

        </div>
    </div>
</section>


<section class="sec2Member">
    <div class="features" style="padding-top: 10%; padding-bottom: 10%">
        <div>
            <h2 style="text-align:center;">At TCI we offer different types of rooms, amenities, and our own exclusive rewards program.</h2>
                <ul style="padding-left:10%; padding-right:10%;">
                    <h2>Specialty Rooms</h2>
                    <li>Pets</li>
                    <li>Chef</li>
                    <li>Gaming</li>
                </ul>
                <ul style="padding-left:10%; padding-right:10%;">
                    <h2>Amenities</h2>
                    <li>Valet</li>
                    <li>Gym</li>
                    <li>Bar</li>
                    <li>Wifi</li>
                    <li>Arcade</li>
                    <li>Pool</li>
                    <li>Pet Ground</li>
                </ul>
                <ul style="padding-left:10%; padding-right:10%;">
                    <h2>Rewards</h2>
                    <li>Earn rewards for each stay</li>
                    <li>Save money on your next stay</li>
                </ul>
        </div>
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
</section>

<div style="text-align: center">
    <p> Contact Twin Cities Inn | Phone Number: 651-222-2020 | Email: TCIsupport@TwinCitiesInn.com </p>
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

</body>

<br><b> WARNING: </b> This website is for educational and demonstration purposes ONLY! Do not enter any sensitive information into this website! The images on this website are the property of their respective owners. By using this website you are acknowledging that creators of the website are not liable for damages of any kind! </br>


</html>