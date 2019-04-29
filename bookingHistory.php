<?php
# All of this runs before the HTML code is rendered.
session_start();

require_once "settings/settings.php";
require_once "bin/inputSanitization.php";


# If member is NOT already signed in (loggedIn is false) then redirect them to the SignIn page IMMEDIATELY.
if (array_key_exists('loggedIn', $_SESSION) === false)  {
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

        # Continues on to display the rest of the page (regardless if reservation is removed or not).
        # Gets the memId of the member.
        $userInfoStmt = $conn->prepare('SELECT memID FROM Member WHERE `memEmail`=:memEmail');
        $userInfoStmt->bindParam(':memEmail', $memInfo['username'], PDO::PARAM_STR, 254);
        $conn->beginTransaction();
        $userInfoStmt->execute();

        # Gets query and turns it into a variable.
        $userInfoStmt->setFetchMode(PDO::FETCH_ASSOC);
        $memInfo = $userInfoStmt->fetch(PDO::FETCH_ASSOC);



        # Removes undesired booking from the database.
        if (array_key_exists('removeResv', $_REQUEST) === true) {

            # SQL statement to remove the card from the database.
            $removeResvStmt = $conn->prepare('DELETE FROM `InvoiceReservation` WHERE `invoiceID`=:invoiceID AND `memID`=:memID');
            $removeResvStmt->bindParam(':invoiceID', $_REQUEST['removeResv']);
            $removeResvStmt->bindParam(':memID', $memInfo['memID']);
            $removeResvStmt->execute();
        }


        # Queries the database the Booking History of the user.
        $bookingHistoryStmt = $conn->prepare('SELECT * FROM InvoiceReservation WHERE `memId`=:memId ORDER BY invoiceStartDate DESC');
        $bookingHistoryStmt->bindParam(':memId', $memInfo['memID'], PDO::PARAM_STR, 254);
        $bookingHistoryStmt->execute();
        $conn->commit();
        # Gets the member's account details from out of the database query.
        $bookingHistoryStmt->setFetchMode(PDO::FETCH_ASSOC);


    } catch (PDOException $e) {
        # Sends a JavaScript alert message back to the user notifying them that there was an error processing their request.
        echo "<script> alert(\"We are sorry, there seems to be a problem with our systems. Please try again. If problems still persist, please notify TCI at 651-222-2020.\"); </script>";

        header('Location: membersPage.php');
    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking History</title>
    <link rel="stylesheet" href="style.css" type="text/css"/>
</head>

<body>
<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1> Twin Cities Inn</h1>
</header>

<!--Navigation Bar-->
<?php include 'bin/nav.php'; ?>

<section class="sec2">

    <script language="javascript">
        function deleteReservation(resvId) {
            var popup = confirm("Are you sure you want to cancel this reservation?");
            if (popup == true) {
                window.location = "http://localhost:8080/bookingHistory.php?removeResv=" + resvId;
                // If user clicks "Cancel" then don't do anything (except close the prompt).
            } else {

            }
        }
    </script>


    <h2>Booking History</h2><br/>

    <table class="bookingHistoryTable">
        <tr>
            <th>Invoice ID</th>
            <th>Card Number</th>
            <th style='display: none'>Member ID</th>
            <th>From</th>
            <th>To</th>
            <th>Cancel Reservation</th>
        </tr>
        <?php
        while ($invoice = $bookingHistoryStmt->fetch(PDO::FETCH_ASSOC)):
            ?>
            <tr>
                <td><?php echo $invoice['invoiceID']; ?></td>
                <td><?php $ccNum = $invoice['cardNum'];
                    echo $last4Digits = preg_replace("#(.*?)(\d{4})$#", "$2", $ccNum); ?>
                </td>
                <td style='display: none;'><?php echo $invoice['memID']; ?></td>
                <td><?php $timestamp1 = strtotime($invoice['invoiceEndDate']);
                    echo date('m-d-Y', $timestamp1); ?></td>
                <td><?php $timestamp2 = strtotime($invoice['invoiceStartDate']);
                    echo date('m-d-Y', $timestamp2); ?></td>
                <?php $date = time();
                if ($date < $timestamp1):
                ?>
                <td><input type="button" onClick="deleteReservation(<?php echo $invoice['invoiceID']; ?>)"
                           value="Cancel Reservation">
                    <?php endif;
                    ?>
            </tr>
        <?php endwhile;
        ?>
    </table>
    <br/><br/><br/><br/><br/><br/><br/>
</section>

<!-- Footer for the web page.-->
<?php include_once 'bin/footer.php'; ?>

</body>
</html>
