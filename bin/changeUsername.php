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
    echo'<script src="/displayError.js"></script>';
    echo("<script>sessionTimeoutError(); </script>");
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
        echo'<script src="/displayError.js"></script>';
        echo("<script>changeUsernameVerificationFailedMsg(); </script>");
    }

    # Checks if old and new usernames are the same.
    if ($oldUsername === $newUsername){
        echo'<script src="/displayError.js"></script>';
        echo("<script>newUsernameConfrmationFailedMsg(); </script>");
    }

    # Updates the database.
    try {
        $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
        # Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        #Check if new username is already taken.
        $checkUsernameStmt = $conn->prepare('SELECT `memEmail` FROM `Member` WHERE `memEmail`=:newUsername');
        $checkUsernameStmt->bindParam(':newUsername', $newUsername, PDO::PARAM_STR, 254);
        $checkUsernameStmt->execute();
        if($checkUsernameStmt->rowCount() > 0) {
            echo'<script src="/displayError.js"></script>';
            echo("<script>usernameTakenMsg(); </script>");
        }


        # Updates the username.
        $chUsernameStmt = $conn->prepare('UPDATE `Member` SET `memEmail`=:newUsername  WHERE `memEmail`=:oldUsername');
        $chUsernameStmt->bindParam(':newUsername', $newUsername, PDO::PARAM_STR, 254);
        $chUsernameStmt->bindParam(':oldUsername', $oldUsername, PDO::PARAM_STR, 254);
        $conn->beginTransaction();
        $chUsernameStmt->execute();
        $conn->commit();

        # Tells the user that their email/username has been changed.
        echo'<script src="/displayError.js"></script>';
        echo("<script>successfulUsernameChangeMsg(); </script>");



    // Catch any sort of failure.
    } catch (PDOException $e) {
        # Sends user database error message.
        echo'<script src="/displayError.js"></script>';
        echo("<script> databaseError(); </script>");
    }




}







