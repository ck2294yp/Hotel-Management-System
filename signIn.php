<?php

# All of this runs before the HTML code is rendered.
session_start();

require_once "settings/settings.php";
require_once "bin/inputSanitization.php";

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


    # Otherwise, if the member has tried to login, sanitize the input.
    if (isset($_REQUEST['username']) && isset($_REQUEST['password'])) {

        // Create connection to the database.
        $userInput['username'] = sanitizeEmail($_REQUEST['username']);
        $userInput['password'] = sanitizePassword($_REQUEST['password'], $_REQUEST['password'], $passwdHashAlgo, $beginingSalt, $endingSalt);

        // If the username or password contain harmful strings, stop here!
        if ($userInput === false || $userInput['password'] === false) {
            # Remove values from request.
            $_REQUEST['username'] = null;
            $_REQUEST['password'] = null;
            $userInput['username'] = null;
            $userInput['password'] = null;

            # Log the IP of the user.
            logIP($clientIp, $conn);

            // Redirect user back to the same login page.
            header('Location: signIn.php');
            exit;
        }


        // If username and password are "sensible" check if the username and password are correct and match to the same account.
        $checkValidStmt = $conn->prepare('select `memEmail` from `Member` where `memEmail`=:email AND `memPasswd`=:password AND `isMember`=1');
        $checkValidStmt->bindParam(':email', $userInput['username'], PDO::PARAM_STR, 254);
        $checkValidStmt->bindParam(':password', $userInput['password'], PDO::PARAM_STR, 64);

        # Begins a transaction, if there are any changes (which there shouldn't be) "on" the changes.
        $conn->beginTransaction();
        $checkValidStmt->execute();
        $conn->rollBack();



        # Log the user in if input MATCHES an account. (value with the specified username and password DOES exist in the database).
        if ($checkValidStmt->rowCount() === 1) {

            # Sets the username and logged in session variables.
            $_SESSION['username'] = $userInput['username'];
            $_SESSION['loggedIn'] = true;

            # Since user has managed to login, remove all instances where his/her clientIP has failed.
            $numFailedStmt = $conn->prepare('DELETE FROM `FailedLogins` WHERE (failLoginIP=:IP)');
            $numFailedStmt->bindParam(':IP', $clientIp);
            $conn->beginTransaction();
            $numFailedStmt->execute();
            $conn->commit();
            # Closes the database connection.
            $conn = null;

            # Logs member in.
            echo'<script src="/displayError.js"></script>';
            echo("<script> loginSuccessMsg(); </script>");



            # If user enters an INVALID username and password. Log the user's IP Address and return them back to this login page.
        } else {
            # Record the client IP in the database.
            logIP($clientIp, $conn);

            # Closes the database connection.
            $conn = null;

            # Tells the user that their username/password combination was wrong.
            echo'<script src="/displayError.js"></script>';
            echo("<script> loginFailedMsg(); </script>");
        }
    }
} catch (PDOException $e) {
    # Sends user database error message.
    echo'<script src="/displayError.js"></script>';
    echo("<script> databaseError(); </script>");}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign In</title>
    <link rel="stylesheet" href="style.css" type="text/css"/>
</head>
<body>
<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1> Twin Cities Inn</h1>
</header>

<!--Navigation Bar-->
<?php include 'bin/nav.php'; ?>

<section class="sec2">
    <form action="signIn.php" method="post" style="padding-bottom: 200px;  width: 30%; z-index: 1; ">
        <h2>Sign In</h2><br/><br/>
        </p>

        <label>Username/Email Address</label><br/>
        <input type="email"
               placeholder="Enter Username"
               id="username"
               name="username"
               required
               minlength="3"
               maxlength="254">
        <br/>
        <br/>

        <label>Password</label><br/>
        <input type="password"
               placeholder="Enter Password"
               id="password"
               name="password"
               required
               maxlength="254">
        <br/>
        <br/>
        <a href="passwordReset.php" style="color: gray">Forgot Password?</a><br/>
        <br/>

        <button type="submit" style="width: 25%">Login</button>
        <br/>

        <p>
            <br/><br/>Not a member yet? <br/>
            <a href="signUp.php" style="color: gray"> Create Account </a><br/>
        </p>

    </form>
    <img src="signIn.png" style="width: 70%; height: auto">
</section>


</body>

</html>