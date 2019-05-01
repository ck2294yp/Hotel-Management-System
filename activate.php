<?php

// Imports needed libraries.
require_once "settings/settings.php";
require_once "bin/inputSanitization.php";

// Sanitizes the input to make sure it's safe.
$user = sanitizeEmail($_REQUEST['user']);
$activationId = sanitizeNumString($_REQUEST['activationId']);

// Stops loading the page if any bad (or no) input is used.
if ($user === false || $activationId === false) {
    $user = null;
    $activationId = null;
    echo '<script src="/displayError.js"></script>';
    echo("<script> invalidActivationTokenMsg(); </script>");
}


// If all input is correct and valid, then begin activating the user's account.
// Try to connect to the database.
try {
    $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
    # Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Queries the database to get the username and the password of the user.
    $checkValidStmt = $conn->prepare('select memEmail from `Member` where `memEmail`=:email AND `memActivationLink`=:memActivationLink');
    $checkValidStmt->bindParam(':email', $user, PDO::PARAM_STR, 254);
    $checkValidStmt->bindParam(':memActivationLink', $activationId, PDO::PARAM_STR, 64);

    # Begins a transaction, if there are any changes (which there shouldn't be) rollback the changes.
    $conn->beginTransaction();
    $checkValidStmt->execute();
    $conn->rollBack();



    # If user has entered a valid activation credentials, activate their account.
    if ($checkValidStmt->rowCount() === 1) {
        # Makes the user a member of TCI and closes the database connection.
        $makeMemStmt = $conn->prepare('UPDATE Member SET `isMember`=1 WHERE `memEmail`=:email AND `memActivationLink`=:memActivationLink');
        $makeMemStmt->bindParam(':email', $user, PDO::PARAM_STR, 254);
        $makeMemStmt->bindParam(':memActivationLink', $activationId, PDO::PARAM_STR, 64);
        $conn->beginTransaction();
        $makeMemStmt->execute();
        $conn->commit();
        $conn = null;

        # Congratulates the member for becoming an official member of TCI.
        echo'<script src="/displayError.js"></script>';
        echo("<script> activationSuccessfulMsg(); </script>");

    # If activation credentials don't match the account. Display meesage and send them to the index page.
    } else {
        echo '<script src="/displayError.js"></script>';
        echo("<script> invalidActivationTokenMsg(); </script>");
    }


} catch (PDOException $e) {
    # Sends user database error message.
    echo'<script src="/displayError.js"></script>';
    echo("<script> databaseError(); </script>");}



