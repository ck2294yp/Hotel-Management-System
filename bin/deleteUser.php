<?php

# Change Directory back to the main directory.
chdir('..');

// Starts the session with the user
session_start();

require_once "settings/settings.php";
require_once "bin/inputSanitization.php";
require_once "bin/sendEmail.php";


// Stops if user is not logged in.
if (array_key_exists('loggedIn', $_SESSION) === false) {
    echo '<script src="/displayError.js"></script>';
    echo("<script>sessionTimeoutError(); </script>");
}


try {
    $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
    # Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Queries the database to get the userID.
    $userIDStmt = $conn->prepare('SELECT memID FROM `Member` WHERE `memEmail`=:email');
    $userIDStmt->bindParam(':email', $_SESSION['username'], PDO::PARAM_STR, 254);
    $userIDStmt->execute();
    $userIDStmt->setFetchMode(PDO::FETCH_ASSOC);
    $userID = $userIDStmt->fetch(PDO::FETCH_ASSOC);

    // Deletes all of the data in the tables using a transaction statement.
    $conn->beginTransaction();
    $removeInvoicesStmt = $conn->prepare('DELETE FROM `InvoiceReservation` WHERE `memID`=:memID');
    $removeInvoicesStmt->bindParam(':memID', $userID['memID']);
    $removeChargeCardsStmt = $conn->prepare('DELETE FROM `ChargeCard` WHERE `memID`=:memID');
    $removeChargeCardsStmt->bindParam(':memID', $userID['memID']);
    $removeAddressStmt = $conn->prepare('DELETE FROM `Address` WHERE `memID`=:memID');
    $removeAddressStmt->bindParam(':memID', $userID['memID']);
    $removeMemberStmt = $conn->prepare('DELETE FROM `Member` WHERE `memID`=:memID');
    $removeMemberStmt->bindParam(':memID', $userID['memID']);
    $removeInvoicesStmt->execute();
    $removeChargeCardsStmt->execute();
    $removeAddressStmt->execute();
    $removeMemberStmt->execute();
    $conn->commit();

    // Tells the user that their account has been deleted (the script itself signs them out).
    echo '<script src="/displayError.js"></script>';
    echo("<script>accountDeletionCompletedMsg(); </script>");


// Catch any sort of failure.
} catch (PDOException $e) {
    # Sends user database error message.
    echo '<script src="/displayError.js"></script>';
    echo("<script> databaseError(); </script>");
}



