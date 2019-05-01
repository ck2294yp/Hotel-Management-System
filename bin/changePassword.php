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
    $oldPassword = sanitizePassword($_REQUEST['oldPassword'], $_REQUEST['oldPassword'], $passwdHashAlgo, $beginingSalt, $endingSalt);
    $newPassword = sanitizePassword($_REQUEST['newPassword'], $_REQUEST['newPasswordConfirm'], $passwdHashAlgo, $beginingSalt, $endingSalt);

    # Checks if new password matches required complexity requirements.
    if ($newPassword === false ){
        echo'<script src="/displayError.js"></script>';
        echo("<script>unacceptibleNewPasswordMsg('http://localhost:8080/accountInformationPage.php'); </script>");
    }

    # Checks if old and new password hashes are the same.
    if ($oldPassword === $newPassword) {
        echo'<script src="/displayError.js"></script>';
        echo("<script>newPasswordMatchesOldMsg(); </script>");
    }

    // No need to check if new password matches confirmation password, sanitizePassword takes care of that on it's own.


    # Updates the database.
    try {
        $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
        # Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        # Updates the user's Password.
        $chPasswordStmt = $conn->prepare('UPDATE `Member` SET `memPasswd`=:newPassword  WHERE `memEmail`=:username');
        $chPasswordStmt->bindParam(':username', $_SESSION['username'], PDO::PARAM_STR, 254);
        $chPasswordStmt->bindParam(':newPassword', $newPassword, PDO::PARAM_STR, 254);
        $conn->beginTransaction();
        $chPasswordStmt->execute();
        $conn->commit();

        # Tells user that their password has been changed successfully
        echo'<script src="/displayError.js"></script>';
        echo("<script>passwordChangeSuccessfulMsg(); </script>");



    // Catch any sort of failure.
    } catch (PDOException $e) {
        # Sends user database error message.
        echo'<script src="/displayError.js"></script>';
        echo("<script> databaseError(); </script>");
    }


}
