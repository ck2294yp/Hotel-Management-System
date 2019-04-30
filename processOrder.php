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

// Checks if there are any false (invalid) data coming into the database.
if (in_array(false, $_REQUEST)) {
    echo"";
    echo "<script> alert(\"Invalid data entered, please try again.\"); </script>";
    header('Location: billingPage.php');
    exit;
}

// Stops if no session exists.
if (array_key_exists('loggedIn', $_SESSION) === false) {
    echo"";
    echo "<script> alert(\"Your session has timed out, please sign in again.\"); </script>";
    header('Location: signIn.php');
    exit;
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
        echo "";
        echo "<script> alert(\"Sorry, Someone seems to have snatched this room before your order could be placed. Please choose another one and try again.\"); </script>";
        header('Location: searchRooms.php');
        exit;
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


# Notify user that reservation has been processed.
    echo"";
    echo "<script> alert(\"Your reservation has been placed successfully!\"); </script>";
    echo "Reservation processed successfully! Thank you for booking with TCI! <br>";
    echo "You should be automatically redirected to the member's page. If that doesn't work, please click <a
        href=\"membersPage.php\">here</a>.";
    header("Location: membersPage.php");


} catch (PDOException $e) {
# Sends a JavaScript alert message back to the user notifying them that there was an error processing their request.
    echo "<script> alert(\"We are sorry, there seems to be a problem with our systems. Please try again. If problems still persist, please notify TCI at 651-222-2020.\"); </script>";
    header('Location: billingPage.php');
    exit;
}














