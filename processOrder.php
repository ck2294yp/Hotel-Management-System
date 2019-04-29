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
if (in_array(false, $_REQUEST)){
    echo "<script> alert(\"Invalid data entered, please try again.\"); </script>";
    header('Location: billingPage.php');
    exit;
}

// Stops if no session exists.
if (array_key_exists('loggedIn', $_SESSION) === false) {
    echo "<script> alert(\"Your session has timed out, please sign in again.\"); </script>";
    header('Location: signIn.php');
    exit;
}



// Processes order (makes insert call to the database).
try {
    $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
    # Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    # Creates prepared SQL statement to enter the order information
    $orderStmt = $conn->prepare("INSERT INTO InvoiceReservation (cardNum, memID, roomTypeID, invoiceStartDate, invoiceEndDate) 
              VALUES (:cardNum, :memID, :roomTypeID, :invoiceStartDate, :invoiceEndDate)");
    $orderStmt->bindParam(':cardNum', $_REQUEST['cardNum']);
    $orderStmt->bindParam(':memID', $_REQUEST['memID']);
    $orderStmt->bindParam(':roomTypeID', $_REQUEST['roomTypeID']);
    $orderStmt->bindParam(':invoiceStartDate', $_REQUEST['checkInDate']);
    $orderStmt->bindParam(':invoiceEndDate', $_REQUEST['checkOutDate']);


    # Commit the changes.
    $conn->beginTransaction();
    $orderStmt->execute();
    $orderNumber = $conn->lastInsertId();       // Gets the last insert ID (the invoice number)
    $conn->commit();

    # Close the database connection.
    $conn = null;

    // Send order notification email out to user.
    orderProcess($_REQUEST['memID'], $orderNumber);


    # Notify user that order has been processed.
    echo "<script> alert(\"Your order has been processed successfully! Thank you for booking with TCI!\"); </script>";
    echo"Order processed successfully! Thank you for booking with TCI! <br>";
    echo "You should be redirected to the member's page. If that doesn't work, please click <a href=\"membersPage.php\">here</a>.";
    header("membersPage.php");


} catch (PDOException $e) {
    # Sends a JavaScript alert message back to the user notifying them that there was an error processing their request.
    echo "<script> alert(\"We are sorry. There was a problem processing your request. Please try again, if problem persists please call TCI at 651-222-2020.\"); </script>";
    header('Location: billingPage.php');
    exit;
}














