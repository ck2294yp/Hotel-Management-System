<?php

# Change Directory back to the main directory.
chdir('..');


// Starts the session with the user
session_start();

require_once "../settings/settings.php";
require_once "inputSanitization.php";


// Stops if no session exists.
if (array_key_exists('loggedIn', $_SESSION) === false) {
    echo'<script src="/displayError.js"></script>';
    echo("<script> sessionTimeoutError(); </script>");
}


// If there is changeUsername post request coming into this page then handle it. Otherwise display the card entry details.
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
    $newCardExpDate = $_REQUEST['newCardExpYear'] . "-" .
        date("m", strtotime($_REQUEST['newCardExpMonth'])) . "-" .
        cal_days_in_month(CAL_GREGORIAN,
            date("m", strtotime($_REQUEST['newCardExpMonth'])),
            $_REQUEST['newCardExpYear']);

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
        echo'<script src="/displayError.js"></script>';
        echo("<script> newPaymentFormAcceptedMsg(); </script>");

    # If there are any database failures, notify the user.
    } catch (PDOException $e) {
        echo'<script src="/displayError.js"></script>';
        echo("<script> databaseError(); </script>");
    }
}


