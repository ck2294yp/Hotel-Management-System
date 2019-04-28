<?php

require_once "settings/settings.php";
require_once "bin/inputSanitization.php";


#TODO DEBUG:
print_r($_REQUEST);



// If there is a post request coming into this page then handle it. Otherwise display the card entry details.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    # Sanitizes session and request information.
    $_SESSION['username'] = sanitizeEmail($_SESSION['username']);
    $_SESSION['loggedIn'] = sanitizeNumString($_SESSION['loggedIn']);

    # Sanitizes input from other pages (as well as this one).
    $_REQUEST['newCardFName'] = sanitizeAlphaString($_REQUEST['newCardFName']);
    $_REQUEST['newCardMInitial'] = sanitizeAlphaString($_REQUEST['newCardMInitial']);
    $_REQUEST['newCardLName'] = sanitizeAlphaString($_REQUEST['newCardLName']);
    $_REQUEST['newCardNum'] = preg_replace("/[^0-9]/", "", sanitizeNumString($_REQUEST['newCardNum']));
    $_REQUEST['newCardExpMonth'] = sanitizeAlphaString($_REQUEST['newCardExpMonth']);
    $_REQUEST['newCardExpYear'] = sanitizeNumString($_REQUEST['newCardExpYear']);
    $_REQUEST['newCardCvv'] = sanitizeNumString($_REQUEST['newCardCvv']);

    # Gets the last date of the selected month/year.
    $newCardExpDate = $_REQUEST['newCardExpYear']."-" .
        date("m",strtotime($_REQUEST['newCardExpMonth']))."-".
        cal_days_in_month(CAL_GREGORIAN,
            date("m",strtotime($_REQUEST['newCardExpMonth'])),
            $_REQUEST['newCardExpYear']);

    # Checks to make sure all of the values passed sanitation.
    if (in_array(false, $_REQUEST) === true || $_SESSION['username'] === true) {
        echo "<script> alert(\"Invalid card details entered. Please try again.\"); </script>";
        header('Location: membersPage.php');
        exit;
    }


    # Starts Database connection, begins sending queries.
    try {
        $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
        # Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        # Gets the user's member ID from the database (using the member's username session variable).
        $userInfoStmt = $conn->prepare('SELECT memID FROM `Member` WHERE `memEmail`=:email');
        $userInfoStmt->bindParam(':email', $_SESSION['username'], PDO::PARAM_STR, 254);
        $conn->beginTransaction();
        $userInfoStmt->execute();

        # Gets the needed userID after using the above SQL statement.
        $userInfoStmt->setFetchMode(PDO::FETCH_ASSOC);
        $memInfo = $userInfoStmt->fetch(PDO::FETCH_ASSOC);

        # Inputs the new card into the database.
        $addCardStmt = $conn->prepare('INSERT INTO `ChargeCard` (memID, cardFname, cardMinitial, cardLname, cardNum, cardExpDate, cardCvv) 
        VALUES (:memID, :cardFname, :cardMinitial, :cardLname, :cardNum, :cardExpDate, :cardCvv)');
        $addCardStmt->bindParam(':memID', $memInfo['memID']);
        $addCardStmt->bindParam(':cardFname', $_REQUEST['newCardFName']);
        $addCardStmt->bindParam(':cardMinitial', $_REQUEST['newCardMInitial']);
        $addCardStmt->bindParam(':cardLname', $_REQUEST['newCardLName']);
        $addCardStmt->bindParam(':cardNum', $_REQUEST['newCardNum']);
        $addCardStmt->bindParam(':cardExpDate', $newCardExpDate);
        $addCardStmt->bindParam(':cardCvv', $_REQUEST['newCardCvv']);
        $addCardStmt->execute();
        $conn->commit();
        $conn = null;


        # Reports that card has been created (since any SQL errors would have errored out at this point).


    } catch (PDOException $e) {
        # Sends a JavaScript alert message back to the user notifying them that there was an error processing their request.
        echo "<script> alert(\"We are sorry. There was a problem processing your request. Please try again, if problem persists please call TCI at 651-000-0000.\"); </script>";
    }
}



// Starts the session with the user
session_start();

// Stops if user is not logged in.
if (array_key_exists('loggedIn', $_SESSION) === false) {
    echo "<script> alert(\"Your session has timed out, please sign in again.\"); </script>";
    header('Location: signIn.php');
    exit;
}



?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            font-family: Arial;
            font-size: 17px;
            padding: 8px;
        }

        * {
            box-sizing: border-box;
        }

        .row {
            display: -ms-flexbox; /* IE10 */
            display: flex;
            -ms-flex-wrap: wrap; /* IE10 */
            flex-wrap: wrap;
            margin: 0 -16px;
        }

        .col-25 {
            -ms-flex: 25%; /* IE10 */
            flex: 25%;
        }

        .col-50 {
            -ms-flex: 50%; /* IE10 */
            flex: 50%;
        }

        .col-75 {
            -ms-flex: 75%; /* IE10 */
            flex: 75%;
        }

        .col-25,
        .col-50,
        .col-75 {
            padding: 0 16px;
        }

        .container {
            background-color: #f2f2f2;
            padding: 5px 20px 15px 20px;
            border: 1px solid lightgrey;
            border-radius: 3px;
        }

        input[type=text] {
            width: 100%;
            margin-bottom: 20px;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        label {
            margin-bottom: 10px;
            display: block;
        }

        .icon-container {
            margin-bottom: 20px;
            padding: 7px 0;
            font-size: 24px;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            margin: 10px 0;
            border: none;
            width: 100%;
            border-radius: 3px;
            cursor: pointer;
            font-size: 17px;
        }

        .btn:hover {
            background-color: #45a049;
        }

        a {
            color: #2196F3;
        }

        hr {
            border: 1px solid lightgrey;
        }

        span.price {
            float: right;
            color: grey;
        }

        /* Responsive layout - when the screen is less than 800px wide, make the two columns stack on top of each other instead of next to each other (also change the direction - make the "cart" column go on top) */
        @media (max-width: 800px) {
            .row {
                flex-direction: column-reverse;
            }

            .col-25 {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1> Twin Cities Inn</h1>
</header>
<?php include 'bin/nav.php'; ?>
<body>

<div class="row">
    <div class="col-75">
        <div class="container">
            <form action="addPayment.php" method="post">

                <div class="row">
                    <div class="col-50">
                        <h3>Name on Card</h3>

                        <label for="newCardFName"><i class="fa fa-user"></i> First Name</label>
                        <input type="text"
                               id="newCardFName"
                               name="newCardFName"
                               required
                               minlength="2"
                               maxlength="64"
                               placeholder="Joe"
                               pattern="[A-Za-z\-\h]">

                        <label for="newCardMInitial"><i class="fa fa-user"></i> Middle Initial</label>
                        <input type="text"
                               id="newCardMInitial"
                               name="newCardMInitial"
                               required
                               maxlength="1"
                               placeholder="A"
                               pattern="[A-Za-z]">

                        <label for="newCardLName"><i class="fa fa-user"></i> Last Name</label>
                        <input type="text"
                               id="newCardLName"
                               name="newCardLName"
                               required
                               minlength="2"
                               maxlength="64"
                               placeholder="Smith"
                               pattern="[A-Za-z\-\h]">
                    </div>

                    <div class="col-50">
                        <h3>Card Details</h3>
                        <label for="newCardNum">Accepted Cards</label>
                        <div class="icon-container">
                            <i class="fa fa-cc-visa" style="color:navy;"></i>
                            <i class="fa fa-cc-amex" style="color:blue;"></i>
                            <i class="fa fa-cc-mastercard" style="color:red;"></i>
                            <i class="fa fa-cc-discover" style="color:orange;"></i>
                        </div>
                        <label for="newCardNum">Credit card number</label>
                        <input type="text"
                               id="newCardNum"
                               name="newCardNum"
                               required
                               placeholder="1111-2222-3333-4444"
                               minlength="12"
                               maxlength="23"
                               pattern="[0-9\-\h]>

                        <label for=" newCardExpMonth">Expiration Month</label>
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
                        <div class="row">
                            <div class="col-50">
                                <label for="expyear">Exp Year</label>
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
                            <div class="col-50">
                                <label for="cvv">CVV</label>
                                <input type="text"
                                       name="newCardCvv"
                                       placeholder="CVV"
                                       required
                                       minlength="3"
                                       maxlength="4"
                                       pattern="[0-9\h]">
                            </div>
                        </div>
                    </div>

                </div>

                <label>
                    <input type="submit" value="Add Payment" class="btn">
            </form>
        </div>
    </div>

</div>
<?php include_once 'bin/footer.php'; ?>
</body>
</html>
