<?php
# All of this runs before the HTML code is rendered.
session_start();

require_once "settings/settings.php";
require_once  "bin/inputSanitization.php";


# If member is NOT already signed in (loggedIn is false) then redirect them to the SignIn page IMMEDIATELY.
if ($_SESSION['loggedIn'] === 0){
    echo "<script> alert(\"Your session has timed out, please sign in again.\"); </script>";
    header('Location: signIn.php');

# Otherwise (loggedIn is true) continue on with the code and the creation of the webpage.
} else {
    # Create array to hold the client's information.
    $memInfo = array();
    # Sanitize session data.
    $memInfo['username'] = sanitizeEmail($_SESSION['username']);

    # Connects to the SQL database.
    try {
        $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);

        # Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        # Gets the memId of the member.
        $userInfoStmt = $conn->prepare('select memId from Member where `memEmail`=:memEmail');
        $userInfoStmt->bindParam(':memEmail', $memInfo['username'], PDO::PARAM_STR, 254);
        # Gets query and turns it into a variable.
        $userInfoStmt->execute();
        $userInfoStmt->setFetchMode(PDO::FETCH_ASSOC);
        $memInfo = $userInfoStmt->fetch(PDO::FETCH_ASSOC);


        # Queries the database the reservations of the user.
        $getInvoiceStmt = $conn->prepare('select * from InvoiceReservation where `memId`=:memId order by invoiceStartDate DESC');
        $getInvoiceStmt->bindParam(':memId', $memInfo['memId'], PDO::PARAM_STR, 254);
        $getInvoiceStmt->execute();
        # Gets the member's account details from out of the database query.
        $getInvoiceStmt->setFetchMode(PDO::FETCH_ASSOC);



    } catch (PDOException $e) {
        # Sends a JavaScript alert message back to the user notifying them that there was an error processing their request.
        echo "<script> alert(\"We are sorry, there seems to be a problem with our systems. Please try again. If problems still persist, please notify TCI at 651-000-0000.\"); </script>";
        #header('Location: membersPage.php');
    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reservations</title>
    <link rel="stylesheet" href="style.css" type="text/css" />
</head>
<body>
<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1> Twin Cities Inn</h1>
</header>

<nav>
    <ul>
        <li><a href="index.html">Home</a> </li>
        <li><a href="aboutUs.html">About</a> </li>
        <li><a href="whyTci.html">Why TCI?</a> </li>
        <li><a href="bin/signOut.php">Sign Out</a> </li>
    </ul>
</nav>
<nav style="top: 50px;">
    <ul>
        <li><a href="membersPage.php">Member's Page</a></li>
        <li><a href="#">Profile</a></li>
        <li><a href="reservations.php" class="active">Reservations</a></li>
    </ul>
</nav>
<section class="reservation">

</section>

<section class="sec2">

    <h2>Your Reservations History</h2><br/>
    <form method="post" action="reservations.php">
        <!--
        <input type="hidden" name="submitted" value="true">
        <label>Search for:
            <select name="dates">
                <option value="30days">last 30 days</option>
                <option value="60days">past 60 days</option>
                <option value="2019">2019</option>
                <option value="2018">2018</option>
            </select>
        </label>

        <label>Search</label>

        <input type="submit" />
        -->
        <?php

            echo "<table>";
            echo"<tr> <th>Invoice ID</th> <th>Card Number</th> <th style='display: none'>Member ID</th> <th>From</th> <th>To</th> <th>Room Number</th> <th>Paid in full</th> <th style='visibility: hidden'>Cancel Reservation</th> </tr>";

            while ($invoice = $getInvoiceStmt->fetch( PDO::FETCH_ASSOC )) {
                echo "<tr><td style='text-align: center'>";
                echo $invoice['invoiceID'];
                echo "</td><td style='text-align: center'>";
                echo $invoice['cardNum'];
                echo "</td><td style='display: none;'>";
                echo $invoice['memID'];
                echo "</td><td style='text-align: center'>";
                $timestamp = strtotime($invoice['invoiceStartDate']);
                echo date('m-d-Y',$timestamp);
                echo "</td><td style='text-align: center'>";
                $timestamp = strtotime($invoice['invoiceEndDate']);
                echo date('m-d-Y',$timestamp);
                echo "</td><td style='text-align: center'>";
                echo $invoice['roomNum'];
                echo "</td><td style='text-align: center'>";
                if ($invoice['paidInFull'] == 0) {
                    echo 'No';
                }
                else {
                    echo 'Yes';
                }
                echo "</td><td>";
                $date = time();
                if ($timestamp > $date) {
                    echo "<button class='cancel'>Cancel Reservation</button>";
                }
                echo "</td></tr>";
            }
            echo "</table>";

        # Closes the database connection.
        $conn = null;



        ?>

    </form>
    <br/>
    <br/>
    <div>

    </div>
</section>

<!-- <section class="sec3"></section> -->

<footer>
    <nav>
        <ul>
            <li><a onclick="return false" href="">Facebook</a> </li>
            <li><a onclick="return false" href="">Twitter</a> </li>
            <li><a onclick="return false" href="">Google+</a> </li>
            <li><a onclick="return false" href="">© 2019 Twin Cities Inn</a> </li>
        </ul>
    </nav>
</footer>
</body>
</html>
