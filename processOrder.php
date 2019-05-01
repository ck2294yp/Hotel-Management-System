<?php
session_start();

require_once "settings/settings.php";
require_once "bin/inputSanitization.php";
require_once "bin/sendEmail.php";

// Sanitizes input from the POST request.
$_REQUEST['cardNum'] = sanitizeNumString($_REQUEST['cardNum']);
$_REQUEST['memID'] = sanitizeNumString($_REQUEST['memID']);
$_REQUEST['roomTypeID'] = sanitizeNumString($_REQUEST['roomTypeID']);
$_REQUEST['checkInDate'] = sanitizeDateString($_REQUEST['checkInDate']);
$_REQUEST['checkOutDate'] = sanitizeDateString($_REQUEST['checkOutDate']);

// Stops if no session exists.
if (array_key_exists('loggedIn', $_SESSION) === false) {
    echo'<script src="/displayError.js"></script>';
    echo("<script> sessionTimeoutError(); </script>");
}


// Processes order (makes insert call to the database).
try {
    $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
# Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
# Begins a transaction right away (which as far as I can tell also locks the tables).
    $conn->beginTransaction();

# Checks if room has been taken before the order could be placed.
    $bookedRoomsStmt = $conn->prepare('SELECT `invoiceID` FROM `InvoiceReservation` WHERE `roomTypeID`=:roomTypeID AND (DATE(`invoiceStartDate` OR `invoiceEndDate`) BETWEEN DATE(:startDate) AND DATE(:endDate))');
    $bookedRoomsStmt->bindParam(':roomTypeID', $_REQUEST['roomTypeID']);
    $bookedRoomsStmt->bindParam(':startDate', $_REQUEST['checkInDate']);
    $bookedRoomsStmt->bindParam(':endDate', $_REQUEST['checkOutDate']);
    $bookedRoomsStmt->execute();
    $numRoomsBooked = $bookedRoomsStmt->fetchColumn();
    $roomInfoStmt = $conn->prepare('SELECT * FROM `RoomType` WHERE roomTypeID=:roomTypeID;');
    $roomInfoStmt->bindParam(':roomTypeID', $_REQUEST['roomTypeID']);
    $roomInfoStmt->execute();
    $roomInfoStmt->setFetchMode(PDO::FETCH_ASSOC);
    $roomInfo = $roomInfoStmt->fetch(PDO::FETCH_ASSOC);
    if ($numRoomsBooked > $roomInfo['numOfRooms']) {
        $conn = null;
        echo'<script src="/displayError.js"></script>';
        echo("<script> roomSnatchedMsg(); </script>");
    }

# Otherwise, Book the room, commit the changes and close connection with the database.
    $orderStmt = $conn->prepare("INSERT INTO InvoiceReservation (cardNum, memID, roomTypeID, invoiceStartDate, invoiceEndDate)
VALUES (:cardNum, :memID, :roomTypeID, :invoiceStartDate, :invoiceEndDate)");
    $orderStmt->bindParam(':cardNum', $_REQUEST['cardNum']);
    $orderStmt->bindParam(':memID', $_REQUEST['memID']);
    $orderStmt->bindParam(':roomTypeID', $_REQUEST['roomTypeID']);
    $orderStmt->bindParam(':invoiceStartDate', $_REQUEST['checkInDate']);
    $orderStmt->bindParam(':invoiceEndDate', $_REQUEST['checkOutDate']);
    $orderStmt->execute();
    $orderNumber = $conn->lastInsertId();       // Gets the last insert ID (the invoice number)


    # Adds 10% of the cost of the room to the reward points.
    $pointsAddition = floor(($roomInfo['pricePerNight'] * $_SESSION['stayDuration']) * 0.10);
    $addPointsStmt = $conn->prepare('UPDATE `Member` SET `memRewardPoints`=`memRewardPoints`+:pointsAddition WHERE `memID`=:memID');
    $addPointsStmt->bindParam(':memID', $_REQUEST['memID']);
    $addPointsStmt->bindParam(':pointsAddition', $pointsAddition);
    $addPointsStmt->execute();
    $conn->commit();
    $conn = null;

    // Send order notification email out to the user.
    orderProcess($_REQUEST['memID'], $orderNumber);


    # Notify user that the reservation has been processed successfully.
    echo'<script src="/displayError.js"></script>';
    echo("<script> bookingSuccessfulMsg(); </script>");


} catch (PDOException $e) {
# Sends user database error message.
    echo'<script src="/displayError.js"></script>';
    echo("<script> databaseError(); </script>");
}














