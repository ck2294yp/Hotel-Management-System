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


        # Queries the database the Booking History of the user.
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
    <title>Booking History</title>
    <link rel="stylesheet" href="style.css" type="text/css" />
</head>
<body>
<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1> Twin Cities Inn</h1>
</header>

<nav>
    <ul>
        <li><a href="index.php">Home</a> </li>
        <li><a href="aboutUs.html">About</a> </li>
        <li><a href="whyTci.html">Why TCI?</a> </li>
        <li><a href="bin/signOut.php">Sign Out</a> </li>
    </ul>
</nav>
<nav style="top: 50px;">
    <ul>
        <li><a href="membersPage.php">Member's Page</a></li>
        <li><a href="accountInformationPage.php">Account Information</a></li>
        <li><a href="bookingHistory.php" class="active">Booking History</a></li>
    </ul>
</nav>
<section class="reservation">

</section>

<section class="sec2">

    <h2>Booking History</h2><br/>

        <table>
        <tr> <th>Invoice ID</th> <th>Card Number</th> <th style='display: none'>Member ID</th> <th>From</th> <th>To</th> <th>Cancel Reservation</th> </tr>
            <?php
            while ($invoice = $getInvoiceStmt->fetch( PDO::FETCH_ASSOC )):
                ?>
                <tr>
                    <td><?php echo $invoice['invoiceID']; ?></td>
                    <td><?php $ccNum = $invoice['cardNum'];
                        echo $last4Digits = preg_replace( "#(.*?)(\d{4})$#", "$2", $ccNum); ?>
                    </td>
                    <td style='display: none;'><?php echo $invoice['memID']; ?></td>
                    <td><?php $timestamp1 = strtotime($invoice['invoiceStartDate']);
                        echo date('m-d-Y',$timestamp1); ?></td>
                    <td><?php $timestamp2 = strtotime($invoice['invoiceEndDate']);
                        echo date('m-d-Y',$timestamp2);?></td>
                    <?php $date = time();
                    if ($date < $timestamp1): ?>
                    <td><input class="cancel" type="button" id="btn-show-dialog" value="Cancel Reservation" /></td>
                    <?php endif; ?>
                </tr>
            <?php endwhile;
            ?>
        </table>

    <div class="overlay" id="dialog-container">
        <div class="popup">
            <p>Are you sure you want to cancel reservation?</p>
            <div class="text-right">
                <button class="dialog-btn btn-cancel" id="cancel">Cancel</button>
                <button class="dialog-btn btn-primary" id="confirm">Yes</button>
            </div>
        </div>
    </div>

    <div class="overlay" id="dialog-container1">
        <div class="popup">
            <p>Reservation is cancel, a confirmation will be send to your email.</p>
            <div class="text-right">
                <button class="dialog-btn btn-exit" id="stop">Ok</button>
            </div>
        </div>
    </div>

    <script
            src="https://code.jquery.com/jquery-3.4.0.min.js"
            integrity="sha256-BJeo0qm959uMBGb65z40ejJYGSgR7REI4+CW1fNKwOg="
            crossorigin="anonymous">

    </script>

    <script>
        $(document).ready(function () {
            $('#btn-show-dialog').on('click', function () {
                $('#dialog-container').show();
            });
            $('#cancel').on('click', function () {
                $('#dialog-container').hide();
            });
            $('#confirm').on('click', function () {
                $('#dialog-container1').show();
                $('#dialog-container').hide();
            });
            $('#stop').on('click', function () {
                $('#dialog-container1').hide();
                $('#btn-show-dialog').hide();
            });

        });

    </script>

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