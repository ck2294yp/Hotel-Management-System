<?php
# Imports a required library.
require_once 'bin/inputSanitization.php';

// Starts a session with the user or grabs their current session.
session_start();

// Creates minimum and maximum allowable dates upon booking.
$todayDate = date_create('now');
$minStartDate = date_format(date_add($todayDate, date_interval_create_from_date_string('+1 day')), 'Y-m-d');
$maxStartDate = date_format(date_add($todayDate, date_interval_create_from_date_string('+1 year')), 'Y-m-d');
$minEndDate = date_format(date_add($todayDate, date_interval_create_from_date_string('+2 days')), 'Y-m-d');
$maxEndDate = date_format(date_add($todayDate, date_interval_create_from_date_string('+1 year +1 day')), 'Y-m-d');

// If the user chooses a variable in the calenders ON THIS PAGE. Sanitize and do a "Sanity check" on it. If all is well, store dates as a $_SESSION variable.
if (sizeof($_REQUEST) > 0) {

    // Sanitizes the inputs
    $checkInDate = @sanitizeDateString($_REQUEST['checkInDate']);
    $checkOutDate = @sanitizeDateString($_REQUEST['checkOutDate']);

    // Throws out any ending dates that come BEFORE the starting dates (as that wouldn't make any sense).
    if (strtotime($checkInDate) >= strtotime($checkOutDate)) {
        echo'<script src="/displayError.js"></script>';
        echo("<script> outOfOrderBookingDatesMsg(); </script>");



    // If There are no problems. Then set the session to hold the values. Send the user to the "Searching rooms" page. (which will redirect them to the sign in page if user is not already logged in).
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
    <title>Booking page</title>
    <link rel="stylesheet" href="style.css" type="text/css"/>
</head>

<style>


    .details {
        position: absolute;
        width: 400px;
        height: 300px;
        display: flex;
        justify-content: center;
        font-family: serif;
        font-size: 20px;
        top: 48%;
        left: 35%;

        background-color: white;
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

    h2 {
        text-align: center;
        color: brown;
        top: 45%;
        left: 35%;
    }

</style>
<body>
<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1> Twin Cities Inn</h1>
</header>

<!--Navigation Bar-->
<?php include 'bin/nav.php'; ?>

<hr>
<div class="details">
    <form action="booking.php" method="post">

        <table>
            <tr>
                <th>Check-in Date</th>
                <th>Check-out Date</th>
            </tr>
            <tr>
                <th>
                    <input type="date"
                           id="checkInDate"
                           name="checkInDate"
                           required
                           value=<?php
                                if (array_key_exists('checkInDate', $_SESSION) === true){
                                    echo $_SESSION['checkInDate'];
                                } else {
                                    echo "";
                                }
                           ?>
                           min=<?php echo($minStartDate); ?>
                           max=<?php echo($maxStartDate); ?>
                    >
                </th>
                <th>
                    <input type="date"
                           id="checkOutDate"
                           name="checkOutDate"
                           required
                           value=<?php
                           if (array_key_exists('checkOutDate', $_SESSION) === true){
                               echo $_SESSION['checkOutDate'];
                           } else {
                               echo "";
                           }
                           ?>
                           min=<?php echo($minStartDate); ?>
                           max=<?php echo($maxEndDate); ?>
                    >
                </th>
            </tr>
        </table>
        <br>

        <input type="submit" value="Search Room">

    </form>
</div>

</body>
</html>