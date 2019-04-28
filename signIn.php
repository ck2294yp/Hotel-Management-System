<?php

# All of this runs before the HTML code is rendered.
session_start();

require_once "settings/settings.php";
require_once "bin/inputSanitization.php";

# If member is already logged in, send them to the member's page.
if (key_exists('loggedIn', $_SESSION)) {
    echo"<script> alert(\"You are already logged in! Redirecting you to the membership page...\"); </script>";
    header('Location: membersPage.php');
    exit;
}


# If member has entered a username and password on this webpage check their validity.
if (isset($_REQUEST['username']) && isset($_REQUEST['password'])){

    // Create connection to the database.
    $userInput['username'] = sanitizeEmail($_REQUEST['username']);
    $userInput['password'] = sanitizePassword($_REQUEST['password'], $_REQUEST['password'], $passwdHashAlgo, $beginingSalt, $endingSalt);

    // If the username or password do not match the required format (or contains harmful strings) stop here!
    if ($userInput === false || $userInput['password'] === false){
        # Remove values from request.
        $_REQUEST['username'] = null;
        $_REQUEST['password'] = null;
        $userInput['username'] = null;
        $userInput['password'] = null;

        # Redirect user back to the same login page.
        header('Location: signIn.php');
        exit;
    }


    // Try to connect to the database.
    try {
        $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
        # Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        # Gets the IP address of the client.
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            //ip from share internet
            $clientIp = trim($_SERVER['HTTP_CLIENT_IP'], '`\'[]');
        }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            //ip pass from proxy
            $clientIp = trim($_SERVER['HTTP_X_FORWARDED_FOR'], '`\'[]');
        }else{
            $clientIp = trim($_SERVER['REMOTE_ADDR'],'`\'[]');
        }


        // Checks how many times this client IP has tried to connect.
        $numFailedStmt = $conn->prepare('SELECT failLoginIP, COUNT(*) FROM `FailedLogins` WHERE (failLoginIP=:IP) GROUP BY FailedLogins.failLoginIP;');
        $numFailedStmt->bindParam(':IP', $clientIp);
        $numFailedStmt->execute();
        $numFailedStmt->setFetchMode(PDO::FETCH_ASSOC);
        $numFailFromIP = $numFailedStmt->fetch(PDO::FETCH_ASSOC);

        # If clientIP has tried to login 10 (or more) times in a row. Block the IP (send them to another page).
        if ($numFailFromIP['COUNT(*)'] >= 10){
            header('Location: blockedIP.php');
            exit;
        }




        // Queries the database to try to get the username and the password of the user.
        $checkValidStmt = $conn->prepare('select memEmail from `Member` where `memEmail`=:email AND `memPasswd`=:password AND `isMember`=1');
        $checkValidStmt->bindParam(':email', $userInput['username'], PDO::PARAM_STR, 254);
        $checkValidStmt->bindParam(':password', $userInput['password'], PDO::PARAM_STR, 64);

        # Begins a transaction, if there are any changes (which there shouldn't be) rollback the changes.
        $conn->beginTransaction();
        $checkValidStmt->execute();
        $conn->rollBack();


        # If user has entered a VALID username and password. (value with the specified username and password DOES exist in the database).
        if ($checkValidStmt->rowCount() === 1 ){
            echo "<script> alert(\"Login successful! Logging you in...\"); </script>";

            # Sets the username and logged in session variables.
            $_SESSION['username'] = $userInput['username'];
            $_SESSION['loggedIn'] = true;

            # Since the user has managed to login, remove all instances where the clientIP has failed to login.
            $numFailedStmt = $conn->prepare('DELETE FROM `FailedLogins` WHERE (failLoginIP=:IP)');
            $numFailedStmt->bindParam(':IP', $clientIp);
            $conn->beginTransaction();
            $numFailedStmt->execute();
            $conn->commit();

            # Closes the database connection.
            $conn = null;

            # Redirects the user to members page after successful login
            header('Location: membersPage.php');
            exit;

        # If user enters an INVALID username and password. Log the user's IP Address and return them back to this login page.
        } else {

            # Record the client IP in the database.
            $recordIpStmt = $conn->prepare('INSERT INTO `FailedLogins` (failLoginIP) VALUES (:failLoginIP)');
            $recordIpStmt->bindParam(':failLoginIP', $clientIp);
            $conn->beginTransaction();
            $recordIpStmt->execute();
            $conn->commit();

            # Closes the database connection.
            $conn = null;

            # Tells the user that their username/password combination was wrong.
            echo "<script> alert(\"Incorrect username or password (or user is not a member), Your failed login attempt has been logged. Please try again.\"); </script>";
            header('Location: signIn.php');
        }

    } catch (PDOException $e) {
        # Rollback any changes to the database (if possible).
        @$conn->rollBack();
        $conn = null;

        echo $e->getMessage();

        # Sends a JavaScript alert message back to the user notifying them that there was an error processing their request.
        echo "<script> alert(\"We are sorry, there seems to be a problem with our systems. Please try again. If problems still persist, please notify TCI at 651-000-0000.\"); </script>";
    }

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign In</title>
    <link rel="stylesheet" href="style.css" type="text/css" />
</head>
<body>
<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1> Twin Cities Inn</h1>
</header>

<!--Navigation Bar-->
<?php include 'bin/nav.php'; ?>

<section class="sec2">
    <form action="signIn.php" method="post" style="padding-bottom: 200px">
        <p>
            <h2>Member Sign In</h2><br/><br/>
        </p>

        <label>Username</label><br/>
        <input type="email" placeholder="Enter Username" name="username" maxlength="254" required><br/><br/>

        <label>Password</label><br/>
        <input type="password" placeholder="Enter Password" name="password" maxlength="254" required><br/><br/>
        <a href="#" style="color: gray">Forgot Username</a> <br/>
        <a href="#" style="color: gray">Forgot Password</a><br/>
        <br/>

        <button type="submit" style="width: 12.8%">Login</button><br/>

        <p>
            <br/><br/>Not a member yet? <br/>
            <a href="signUp.php" style="color: gray">Create Account</a><br/>
        </p>

    </form>
    <img src="https://cdn.shopify.com/s/files/1/2379/3029/products/51oNZAKQomL._SL1500_0270363e-8918-419e-80c4-3866316ce410_1024x1024.jpg?v=1551237385">
</section>

<!-- Footer for the web page.-->
<?php include_once 'bin/footer.php'; ?>

</body>

</html>