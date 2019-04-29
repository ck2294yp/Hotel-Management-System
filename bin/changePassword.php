<?php

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
    $oldPassword = sanitizePassword($_REQUEST['oldPassword'], $_REQUEST['oldPassword'], $passwdHashAlgo, $beginingSalt, $endingSalt);
    $newPassword = sanitizePassword($_REQUEST['newPassword'], $_REQUEST['newPasswordConfirm'], $passwdHashAlgo, $beginingSalt, $endingSalt);


    # Checks if old and new password hashes are the same.
    if ($oldPassword === $newPassword) {
        echo "<script> alert(\"Password is the same!\"); </script>";
        exit;
    }

    # Updates the database.
    try {
        $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
        # Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        # Updates the user's Password.
        $chPasswordStmt = $conn->prepare('UPDATE `Member` SET `memPasswd`=:memPasswd  WHERE `memEmail`=:username');
        $chPasswordStmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR, 254);
        $chPasswordStmt->bindParam(':memPasswd', $newPassword, PDO::PARAM_STR, 254);
        $conn->beginTransaction();
        $chPasswordStmt->execute();
        $conn->commit();

        # Tells the user that their email/username has been changed.
        echo "<script> alert(\"Username/email address successfully changed.\"); </script>";


        // Catch any sort of failure.
    } catch (PDOException $e) {
        # Sends a JavaScript alert message back to the user notifying them that there was an error processing their request.
        echo "<script> alert(\"We are sorry, there seems to be a problem with our systems. Please try again. If problems still persist, please notify TCI at 651-222-2020.\"); </script>";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }


}
