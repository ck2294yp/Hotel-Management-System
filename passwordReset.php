<?php

# All of this runs before the HTML code is rendered.
session_start();

require_once "settings/settings.php";
require_once "bin/inputSanitization.php";
require_once "bin/sendEmail.php";

function logIP($clientIP, $conn){
    # Record the client IP in the database.
    $recordIpStmt = $conn->prepare('INSERT INTO `FailedLogins` (failLoginIP) VALUES (:failLoginIP)');
    $recordIpStmt->bindParam(':failLoginIP', $clientIP);
    $conn->beginTransaction();
    $recordIpStmt->execute();
    $conn->commit();
}

# If member is already logged in, send them to the member's page.
if (array_key_exists('loggedIn', $_SESSION)) {
    echo'<script src="/displayError.js"></script>';
    echo("<script>alreadyLoggedInMsg(); </script>");
}

// Try to connect to the database.
try {
    $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
    # Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    # Gets the IP address of the client.
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        //ip from share internet
        $clientIp = trim($_SERVER['HTTP_CLIENT_IP'], '`\'[]');
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //ip pass from proxy
        $clientIp = trim($_SERVER['HTTP_X_FORWARDED_FOR'], '`\'[]');
    } else {
        $clientIp = trim($_SERVER['REMOTE_ADDR'], '`\'[]');
    }


    // Checks how many times this client IP has tried to connect.
    $numFailedStmt = $conn->prepare('SELECT failLoginIP, COUNT(*) FROM `FailedLogins` WHERE (failLoginIP=:IP) GROUP BY FailedLogins.failLoginIP;');
    $numFailedStmt->bindParam(':IP', $clientIp);
    $numFailedStmt->execute();
    $numFailedStmt->setFetchMode(PDO::FETCH_ASSOC);
    $numFailFromIP = $numFailedStmt->fetch(PDO::FETCH_ASSOC);

    # If clientIP has tried to login 10 (or more) times in a row. Block the IP (send them to another page).
    if ($numFailFromIP['COUNT(*)'] >= 10) {
        header('Location: blockedIP.php');
        exit;
    }


    # Otherwise, if member has entered a valid member email account.
    if (isset($_REQUEST['email'])) {

        // Create connection to the database.
        $userInput['username'] = ucfirst(sanitizeEmail($_REQUEST['email']));

        // If the username contain harmful strings, stop here!
        if ($userInput === false) {
            # Remove values from request.
            $_REQUEST['username'] = null;
            $userInput['username'] = null;

            # Log the IP of the user.
            logIP($clientIp, $conn);

            // Redirect user to the same page.
            header('refresh:0');
            exit;
        }


        // If username is "sensible" check if an account exists on this account.
        $checkValidStmt = $conn->prepare('select `memEmail` from `Member` where `memEmail`=:email AND `isMember`=1');
        $checkValidStmt->bindParam(':email', $userInput['username'], PDO::PARAM_STR, 254);
        $checkValidStmt->execute();


        // This code is complementary of Yoga on GitHub Gist: https://gist.github.com/yoga-/8c2c196173be3d4aff56
        # Reset password if input MATCHES an account.
        if ($checkValidStmt->rowCount() === 1) {

            //Enforce min length 8
            $len = 8;

            //define character libraries - remove ambiguous characters like iIl|1 0oO
            $sets = array();
            $sets[] = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
            $sets[] = '23456789';
            $sets[]  = '~!@#$%^&*(){}[]/?';

            $newPassword = '';

            //append a character from each set - gets first 4 characters
            foreach ($sets as $set) {
                $newPassword .= $set[array_rand(str_split($set))];
            }

            //use all characters to fill up to $len
            while(strlen($newPassword) < $len) {
                //get a random set
                $randomSet = $sets[array_rand($sets)];

                //add a random char from the random set
                $newPassword .= $randomSet[array_rand(str_split($randomSet))];
            }

            // Shuffle the newPassword and make a hash of it (kept in a separate variable so it can be read by the user).
            $newPassword = str_shuffle($newPassword);
            $hashNewPassword = sanitizePassword($newPassword, $newPassword, $passwdHashAlgo, $beginingSalt, $endingSalt);

            // Update the user's password and remove all instances where his/her IP has failed logon.
            $conn->beginTransaction();
            $updatePasswdStmt = $conn->prepare('UPDATE `Member` SET `memPasswd`=:hashPasswd WHERE `memEmail`=:email AND `isMember`=1');
            $updatePasswdStmt->bindParam(':email', $userInput['username'], PDO::PARAM_STR, 254);
            $updatePasswdStmt->bindParam(':hashPasswd', $hashNewPassword, PDO::PARAM_STR, 64);
            $numFailedStmt = $conn->prepare('DELETE FROM `FailedLogins` WHERE (failLoginIP=:IP)');
            $numFailedStmt->bindParam(':IP', $clientIp);
            $updatePasswdStmt->execute();
            $numFailedStmt->execute();
            $conn->commit();
            $conn = null;

            # Sends an email out to the customer (administrators MUST allow emails for this to work!).
            if (passwordReset($userInput['username'], $newPassword)) {
                echo '<script src="/displayError.js"></script>';
                echo("<script> passwordResetSuccessfulMsg(); </script>");
            }



        # If user enters an INVALID username. Log the user's IP Address and return them back to this login page.
        } else {
            # Record the client IP in the database.
            logIP($clientIp, $conn);

            # Closes the database connection.
            $conn = null;

            # Tells the user that their username combination was wrong.
            echo'<script src="/displayError.js"></script>';
            echo("<script> nonExistantUsernameMsg(); </script>");
        }
    }
} catch (PDOException $e) {
    echo($e->getMessage());

    # Sends user database error message.
    //echo'<script src="/displayError.js"></script>';
    //echo("<script> databaseError(); </script>");
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset</title>
    <link rel="stylesheet" href="style.css" type="text/css" />
</head>

<body>
<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1> Twin Cities Inn</h1>
</header>

<!--Navigation Bar-->
<?php include 'bin/nav.php'; ?>

<center>
<section class="sec2">
    <h2>Password Reset Request</h2>
    <p> Please enter the address of the email account that you have registered to your TCI account. An email will be sent to you with your temporary password enclosed. </p>
    <form action="passwordReset.php" method="post">
        <input
            type="email"
            name="email"
            id="email"
            required
            min="3"
            minlength="3"
            max="64"
            maxlength="64"
            title="Enter your email address.">
        <br>
        <br>
        <button type="submit">Reset Password</button>
    </form>
</section>
</center>

</body>


<br><b> WARNING: </b> This website is for educational and demonstration purposes ONLY! Do not enter any sensitive information into this website! The images on this website are the property of their respective owners. By using this website you are acknowledging that creators of the website are not liable for damages of any kind! </br>


</html>






