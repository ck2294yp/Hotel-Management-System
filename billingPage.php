<?php
// Checks to make sure user is actually logged in.
session_start();

require_once "settings/settings.php";
require_once "bin/inputSanitization.php";


// Stops if the room Type ID is not set.
if (array_key_exists('roomTypeID', $_REQUEST) === false) {
    echo "<script> alert(\"Invalid room type specified please try again.\"); </script>";
    header('Location: searchRooms.php');
    exit;
}

// Stops if user is not logged in.
if (array_key_exists('loggedIn', $_SESSION) === false) {
    echo "<script> alert(\"Your session has timed out, please sign in again.\"); </script>";
    header('Location: signIn.php');
    exit;
}

$_SESSION['username'] = @sanitizeEmail($_SESSION['username']);
$_SESSION['loggedIn'] = @sanitizeNumString($_SESSION['loggedIn']);
# Also sets the value of the roomTypeID to be one more then what is specified (because that is how the array from the SLQ statement will parse it).
$_SESSION['roomTypeID'] = @sanitizeNumString($_REQUEST['roomTypeID'] + 1);

# Create array to hold the client's information.
$memInfo = array();
# Sanitize session data.
@$memInfo['username'] = sanitizeEmail($_SESSION['username']);


# Connects to the SQL database.
try {
    $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
    # Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    # Queries the database to get all of the needed user information.
    $userInfoStmt = $conn->prepare('select memID, memEmail, memFname, memLname, memRewardPoints from `Member` where `memEmail`=:email');
    $userInfoStmt->bindParam(':email', $memInfo['username'], PDO::PARAM_STR, 254);
    $paymentInfoStmt = $conn->prepare('select cardNum, memID, cardCvv, cardExpDate, cardFname, cardMinitial, cardLname from `ChargeCard` INNER JOIN `Member` USING(memID) WHERE `memEmail`=:email');
    $paymentInfoStmt->bindParam(':email', $memInfo['username'], PDO::PARAM_STR, 254);
    # Queries the database to get all of needed room information.
    $roomInfoStmt = $conn->prepare('select roomTypeID, pricePerNight, roomCatagory, roomNumBeds, roomAllowsPets from `RoomType` where `roomTypeID`=:roomTypeID ORDER BY `roomTypeID`');
    $roomInfoStmt->bindParam(':roomTypeID', $_SESSION['roomTypeID'], PDO::PARAM_STR, 254);


    # Begins a transaction, if there are any changes (which there shouldn't be) rollback the changes.
    $conn->beginTransaction();
    $userInfoStmt->execute();
    $paymentInfoStmt->execute();
    $roomInfoStmt->execute();
    $conn->rollBack();

    # Closes the database connection.
    $conn = null;


    # Gets the member, payment, and room details from out of the database query.
    $userInfoStmt->setFetchMode(PDO::FETCH_ASSOC);
    $memInfo = $userInfoStmt->fetch(PDO::FETCH_ASSOC);
    # Payment info.
    $paymentInfoStmt->setFetchMode(PDO::FETCH_ASSOC);
    $paymentInfo = $paymentInfoStmt->fetchAll(PDO::FETCH_ASSOC);
    # Room info.
    $roomInfoStmt->setFetchMode(PDO::FETCH_ASSOC);
    $roomInfo = $roomInfoStmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    # Sends a JavaScript alert message back to the user notifying them that there was an error processing their request.
    echo "<script> alert(\"We are sorry, there seems to be a problem with our systems. Please try again. If problems still persist, please notify TCI at 651-000-0000.\"); </script>";
    header('Location: searchRooms.php');
    exit;
}

?>


<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billing page</title>
    <link rel="stylesheet" href="style.css" type="text/css"/>
</head>
<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1> Twin Cities Inn</h1>
</header>
<style>
    * {
        box-sizing: border-box;
    }

    .button {
        margin-bottom: 250px;
    }


    /* Clear floats after the columns */
    .row:after {
        content: "";
        display: table;
        clear: both;
    }

    .footer {
        overflow: auto;
    }

    .footer ul {
        padding-bottom: 0px;
        list-style-type: none;
    }

    .footer li {
        display: inline-block;
        font-weight: bold;
    }

    .column {
        float: left;
        margin: 0px;
        text-align: center;
    }

    .left {
        width: 70%;
    }

    .right {
        width: 30%;
    }

    /* Clear floats after the columns */
    .row:after {
        content: "";
        display: table;
        clear: both;
        padding: 0px;
    }

    body,
    html {
        height: 100%;
        min-height: 100%;
    }

    body {
        font-family: 'Roboto',
        sans-serif;
        margin: 0;
        background-color: #e7e7e7;
    }

    .credit-card {
        width: 360px;
        height: 400px;
        margin: -50px auto 0;
        border: 1px solid #ddd;
        border-radius: 6px;
        background-color: #fff;
        box-shadow: 1px 2px 3px 0 rgba(0, 0, 0, .10);
    }

    .form-header {
        height: 60px;
        padding: 20px 30px 0;
        border-bottom: 1px solid #e1e8ee;
    }

    .form-body {
        height: 300px;
        padding: 30px 30px 20px;
    }

    .title {
        font-size: 18px;
        margin: 0;
        color: #5e6977;
    }

    form {
        margin: 0 auto;
        width: 250px;
    }

    .card-number,
    .cvv-input input,
    .month select,
    .year select {
        font-size: 14px;
        font-weight: 100;
        line-height: 14px;
    }

    .card-number,
    .month select,
    .year select {
        font-size: 14px;
        font-weight: 100;
        line-height: 14px;
    }

    .card-number,
    .cvv-details,
    .cvv-input input,
    .month select,
    .year select {
        opacity: .7;
        color: #86939e;
    }

    .card-number {
        width: 100%;
        margin-bottom: 20px;
        padding-left: 20px;
        border: 2px solid #e1e8ee;
        border-radius: 6px;
    }

    .month select,
    .year select {
        width: 145px;
        margin-bottom: 20px;
        padding-left: 20px;
        border: 2px solid #e1e8ee;
        border-radius: 6px;
        background: url('caret.png') no-repeat;
        background-position: 85% 50%;
        -moz-appearance: none;
        -webkit-appearance: none;
    }

    .month select {
        float: left;
    }

    .year select {
        float: right;
    }

    .cvv-input input {
        float: left;
        width: 145px;
        padding-left: 20px;
        border: 2px solid #e1e8ee;
        border-radius: 6px;
        background: #fff;
    }

    .cvv-details {
        font-size: 12px;
        font-weight: 300;
        line-height: 16px;
        float: right;
        margin-bottom: 20px;
    }

    .cvv-details p {
        margin-top: 6px;
    }

    .paypal-btn,
    .proceed-btn {
        cursor: pointer;
        font-size: 16px;
        width: 100%;
        border-color: transparent;
        border-radius: 6px;
    }

    .proceed-btn {
        margin-bottom: 0px;
        background: #7dc855;
    }

    .paypal-btn a,
    .proceed-btn a {
        text-decoration: none;
    }

    .proceed-btn a {

    }

    .alignLeft {
        text-align: left;
    }

    #newCardEntry {
        padding: 50px 0;
        text-align: center;
        margin-bottom: 20px;
        display: none;
    }
</style>
<body>

<!--Navigation Bar-->
<?php include 'bin/nav.php'; ?>

<center><h2> Billing Information</h2></center>
<div class="row">
    <div class="column left">
        <h2>Checkout Details</h2>
        <form action="processOrder.php" method="post">
            <input type="hidden" name="memID" id="memID" value="<?php echo($memInfo['memID']); ?>" />
            <input type="hidden" name="roomTypeID" id="roomTypeID" value="<?php echo($roomInfo['roomTypeID']); ?>" />
            <input type="hidden" name="checkInDate" id="checkInDate" value="<?php echo($_SESSION['checkInDate']); ?>" />
            <input type="hidden" name="checkOutDate" id="checkOutDate" value="<?php echo($_SESSION['checkOutDate']); ?>" />

            <p>
                <br> Room Type: <?php echo(ucfirst($roomInfo['roomCatagory'])); ?>
                <br> Number of Beds: <?php echo($roomInfo['roomNumBeds']); ?>
                <br> Allows Pets: <?php if ($roomInfo['roomAllowsPets'] === 0){echo("No");} else {echo("Yes");}; ?>
                <br> Price Per Night: $<?php echo($roomInfo['pricePerNight']); ?>
            </p>
            <p>
                <br> Check-in Date: <?php echo($_SESSION['checkInDate']); ?>
                <br> Check-out Date: <?php echo($_SESSION['checkOutDate']); ?>
                <br> Total Number of Nights: <?php echo($_SESSION['stayDuration']); ?>
                <br> Points available: <?php echo($memInfo['memRewardPoints']); ?>
            </p>


            <label>Total Amount Due: $<?php echo($_SESSION['stayDuration'] * $roomInfo['pricePerNight']); ?> </label><br>



            <select name="cardNum" id="cardNum">
            <?php
            foreach ($paymentInfo as $row) {
                $last4Digits = preg_replace( '/[0-9]{12}/', "*", $row['cardNum']);
                echo "<option value='".$row['cardNum']."'>".$last4Digits."</option>";
            }
            ?>
            </select>

            <br>
            <button class="button" type="button" onclick="hideShowAddCardForm()">Add New Card</button>
            <button class="button" type="submit" class="process-btn" onclick="processingMessage()">Pay Now</button>
        </form>
    </div>
    <div class="column right" id="newCardEntry">
        <form class="chargeCardForm" action="addPayment.php" method="post">
            <div class="form-header"
            <h4 class="title">Add New Form of Payment</h4>

            <div class="form-body">
                <!-- Name on Card-->
                <input type="text" class="newCardFName" placeholder="Joe" pattern="[A-Za-z\-\h]{2,64}">
                <input type="text" class="newCardMInitial" placeholder="A" pattern="[A-Za-z]{1}">
                <input type="text" class="newCardLName" placeholder="Smith" pattern="[A-Za-z\-\h]{2,64}">

                <!-- Card Number-->
                <input type="text" class="newCardNum" placeholder="1111-2222-3333-4444" minlength="12" maxlength="23" pattern="[0-9 \-]">

                <!-- Expiration Date -->
                <div class="expiration-date">
                    <div class="month">
                        <select name="newCardExpMonth">
                            <option value="January">January</option>
                            <option value="February">February</option>
                            <option value="March">March</option>
                            <option value="April">April</option>
                            <option value="May">May</option>
                            <option value="June">June</option>
                            <option value="July">July</option>
                            <option value="August">August</option>
                            <option value="September">September</option>
                            <option value="October">October</option>
                            <option value="November">November</option>
                            <option value="December">December</option>
                        </select>
                    </div>
                    <div class="year">
                        <select name="newCardExpYear">
                            <option value="2019">2019</option>
                            <option value="2020">2020</option>
                            <option value="2021">2021</option>
                            <option value="2022">2022</option>
                            <option value="2023">2023</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                            <option value="2028">2028</option>
                            <option value="2029">2029</option>
                        </select>
                    </div>
                </div>

                <!-- Card Verification Field -->
                <div class="card-verification">
                    <div class="cvv-input">
                        <input type="text"
                               name="newCardCvv"
                               placeholder="CVV"
                               required
                               minlength="3"
                               maxlength="4"
                               pattern="[0-9\h]">
                    </div>
                    <div class="cvv-details">
                        <p>3 or 4 digits usually found <br> on the signature strip.</p>
                    </div>
                </div>

                <!-- Button -->
                <button type="submit" class="proceed-btn"><a href="billingPage.php">Add Card</a></button>
            </div>
        </form>
    </div>
</div>
<script
        src="https://code.jquery.com/jquery-3.4.0.min.js"
        integrity="sha256-BJeo0qm959uMBGb65z40ejJYGSgR7REI4+CW1fNKwOg="
        crossorigin="anonymous"></script>
<script>
    function processingMessage() {
        alert("Please wait patiently while your order processes. DO NOT NAVIGATE AWAY WHILE ORDER IS PROCESSING! It will be done soon. Please click 'OK' to continue...");

    }

    function hideShowAddCardForm() {

        var newCardEntry = document.getElementById("newCardEntry");

        if (newCardEntry.style.display === "block") {
            newCardEntry.style.display = "none";
            newCardEntry.required = false;

        } else {
            newCardEntry.style.display = "block";
            newCardEntry.required = true;
        }
    }

</script>
<footer class="footer" style=padding-bottom: 20px
"text-align: center">
<nav>
    <ul>
        <li><a href="#">Facebook</a></li>
        <li><a href="#">Twitter</a></li>
        <li><a href="#">Google+</a></li>
        <li><a href="#">© 2019 Twin Cities Inn</a></li>
    </ul>
</nav>
</footer>
</body>
</html>

