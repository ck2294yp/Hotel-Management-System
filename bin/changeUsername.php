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
    echo "<script> alert(\"Your session has timed out, please sign in again.\"); </script>";
    header('Location: signIn.php');
    exit;
}


// If there is a post request coming into this page then handle it. Otherwise display the card entry details.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    # Sanitizes session and request information.
    $_SESSION['username'] = sanitizeEmail($_SESSION['username']);
    $_SESSION['loggedIn'] = sanitizeNumString($_SESSION['loggedIn']);

    # Sanitize input.
    $oldUsername = ucfirst(sanitizeEmail($_REQUEST['oldUsername']));
    $newUsername = ucfirst(sanitizeEmail($_REQUEST['newUsername']));
    $newUsernameConfirm = ucfirst(sanitizeEmail($_REQUEST['newUsernameConfirm']));

    # Checks if old username matches the currently logged in account.
    if ($_SESSION['username'] !== $oldUsername){
        echo "<script> alert(\"Old username does not match currently logged in account!\"); </script>";
        exit;
    }

    # Checks if old and new usernames are the same.
    if ($oldUsername === $newUsername){
        echo "<script> alert(\"No new username provided!\"); </script>";
        exit;
    }

    # Updates the database.
    try {
        $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
        # Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        #Check if new username is already taken.
        $checkUsernameStmt = $conn->prepare('SELECT `memEmail` FROM `Member` WHERE `memEmail`=:oldUsername');
        $checkUsernameStmt->bindParam(':oldUsername', $oldUsername, PDO::PARAM_STR, 254);
        $checkUsernameStmt->execute();
        if($checkUsernameStmt->rowCount() > 0) {
            echo "<script> alert(\"Username is already taken!\"); </script>";
            exit;
        }


        # Updates the username.
        $chUsernameStmt = $conn->prepare('UPDATE `Member` SET `memEmail`=:newUsername  WHERE `memEmail`=:oldUsername');
        $chUsernameStmt->bindParam(':newUsername', $newUsername, PDO::PARAM_STR, 254);
        $chUsernameStmt->bindParam(':oldUsername', $oldUsername, PDO::PARAM_STR, 254);
        $conn->beginTransaction();
        $chUsernameStmt->execute();
        $conn->commit();

        # Tells the user that their email/username has been changed.
        echo "<script> alert(\"Username/email address successfully changed.\"); </script>";

        # Change Session variable over to reflect new change.
        $_SESSION['username'] = $newUsername;

    // Catch any sort of failure.
    } catch (PDOException $e) {
        # Sends a JavaScript alert message back to the user notifying them that there was an error processing their request.
        echo "<script> alert(\"We are sorry, there seems to be a problem with our systems. Please try again. If problems still persist, please notify TCI at 651-222-2020.\"); </script>";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }




}







