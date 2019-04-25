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
        header('Location: membersPage.php');
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

<!--Navigation Bar-->
<?php include 'bin/nav.php'; ?>

<section class="sec2">

    <h2>Booking History</h2><br/>

        <table class="bookingHistoryTable">
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
                    if ($date < $timestamp1):
                        ?>
                    <td><input type="button" onClick="deleteReservation(<?php echo $invoice['invoiceID']; ?>)" value="Cancel Reservation">
                    <?php endif;
                    ?>
                </tr>
            <?php endwhile;
            ?>

            <script language="javascript">
                function deleteReservation(delid)
                {
                    if(confirm("Are you sure you want to cancel this reservation?")){
                        window.location.href='delete.php?del_id=' +delid+'';
                        alert("Reservation cancel, please check your email for a confirmation.");
                        return true;
                    }
                }
            </script>

        </table>
    <br/><br/><br/><br/><br/><br/><br/>
</section>

<!-- Footer for the web page.-->
<?php include_once 'bin/footer.php'; ?>

</body>
</html>
