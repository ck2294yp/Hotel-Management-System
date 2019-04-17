<?php

// Imports needed libraries.
require_once "settings/settings.php";
require_once "bin/inputSanitization.php";

// Sanitizes the input to make sure it's safe.
$user = sanitizeEmail($_REQUEST['user']);
$activationId = sanitizeNumString($_REQUEST['activationId']);

// Stops loading the page if any bad input is used.
if ($user === false || $activationId === false) {
    $user = null;
    $activationId = null;
    echo "<script> alert(\"Invalid input detected. Please try again\"); </script>";
    header('Location: signIn.php');
    exit;
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
        echo "<script> alert(\"Congratulations! You are have successfully activated your account and have become member of TCI! Click OK to continue to your personalized membership page.\"); </script>";


        # Creates a new session with the user.
        $_SESSION['username'] = $user;
        $_SESSION['loggedIn'] = true;

        # Redirects user to the members page.
        header('Location: membersPage.php');

    } else {
        echo "<script> alert(\"Incorrect account credentials specified, account NOT activated!\"); </script>";

    }


} catch (PDOException $e) {
    # Rollback any changes to the database (if possible).
    @$conn->rollBack();
    @$conn = null;

    # Sends a JavaScript alert message back to the user notifying them that there was an error processing their request.
    echo "<script> alert(\"We are sorry, there seems to be a problem with our systems. Please try again. If problems still persist, please notify TCI at 651-000-0000.\"); </script>";
}



