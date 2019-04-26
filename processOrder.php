<?php
session_start();

require_once "settings/settings.php";
require_once "bin/inputSanitization.php";


#TODO DEBUG:
print_r($_REQUEST);


try {
    $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
    # Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    # Creates prepared SQL statement to enter the order information
    $orderStmt = $conn->prepare("INSERT INTO InvoiceReservation (cardNum, memID, roomTypeID, invoiceStartDate, invoiceEndDate) 
              VALUES (:cardNum, :memID, :roomTypeID, :invoiceStartDate, :invoiceEndDate)");
    $orderStmt->bindParam(':cardNum', sanitizeNumString($_REQUEST['cardNum']));
    $orderStmt->bindParam(':memID', sanitizeNumString($_REQUEST['memID']));
    $orderStmt->bindParam(':roomTypeID', sanitizeNumString($_REQUEST['roomTypeID']));
    $orderStmt->bindParam(':invoiceStartDate', sanitizeDateString($_REQUEST['checkInDate']));
    $orderStmt->bindParam(':invoiceEndDate', sanitizeDateString($_REQUEST['checkOutDate']));

    # Commit the changes.
    $conn->beginTransaction();
    $orderStmt->execute();
    $conn->commit();

    # Close the database connection.
    $conn = null;

    echo "<script> alert(\"Your order has been processed successfully! Thank you for booking with TCI!\"); </script>";
    echo"Order processed successfully! Thank you for booking with TCI!";


} catch (PDOException $e) {
    # Rollback any changes to the database (if possible/required).
    @$conn->rollBack();
    @$conn = null;

    # Sends a JavaScript alert message back to the user notifying them that there was an error processing their request.
    echo "<script> alert(\"We are sorry. There was a problem processing your request. Please try again, if problem persists please call TCI at 651-000-0000.\"); </script>";
    #TODO DEBUG:
    echo($e->getMessage());
    //header('Location: billingPage.php');
    //exit;
}














