<?php

# All of this runs before the HTML code is rendered.
session_start();

require_once "settings/settings.php";
require_once "bin/inputSanitization.php";

# If member is already logged in, send them to the member's page.
if (key_exists('loggedIn', $_SESSION)) {
    echo"<script> alert(\"You are already logged in! Redirecting you to the membership page...\"); </script>";
    header('Location: membersPage.php');
}


# If member has entered a username and password on this webpage check their validity.
if (isset($_REQUEST['username']) && isset($_REQUEST['password'])){

    // Create connection to the database.
    $userInput['username'] = sanitizeEmail($_REQUEST['username']);
    $userInput['password'] = sanitizePassword($_REQUEST['password'], $_REQUEST['password'], $passwdHashAlgo, $beginingSalt, $endingSalt);


    // Try to connect to the database.
    try {
        $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
        # Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Queries the database to get the username and the password of the user.
        $checkValidStmt = $conn->prepare('select memEmail from `Member` where `memEmail`=:email AND `memPasswd`=:password');
        $checkValidStmt->bindParam(':email', $userInput['username'], PDO::PARAM_STR, 254);
        $checkValidStmt->bindParam(':password', $userInput['password'], PDO::PARAM_STR, 64);

        # Begins a transaction, if there are any changes (which there shouldn't be) rollback the changes.
        $conn->beginTransaction();
        $checkValidStmt->execute();
        $conn->rollBack();

        # Closes the database connection.
        $conn = null;


        
        # If user has entered a valid username and password. (value with the specified username and password DOES exist in the database).
        if ($checkValidStmt->rowCount() === 1 ){
            echo "<script> alert(\"Login successful! Logging you in...\"); </script>";

            # Sets the username and logged in session variables.
            $_SESSION['username'] = $userInput['username'];
            $_SESSION['loggedIn'] = true;

            # Redirects the user to members page after successful login
            header('Location: membersPage.php');

        # If user has entered a INVALID username and password. Log the user's IP Address and return them back to this login page.
        } else {
//            # TODO: Fix this part of the code so that it works.
//            echo "Logging IP address of user for failed login attempt...";
//
//            # Gets the TRUE IP address of the (offending) client. Even if the client is using a proxy (may not work in all cases).
//            if(!empty($_SERVER['HTTP_CLIENT_IP'])){
//                //ip from share internet
//                $clientIp = trim($_SERVER['HTTP_CLIENT_IP'], '`\'[]');
//            }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
//                //ip pass from proxy
//                $clientIp = trim($_SERVER['HTTP_X_FORWARDED_FOR'], '`\'[]');
//            }else{
//                $clientIp = trim($_SERVER['REMOTE_ADDR'],'`\'[]');
//            }
//
//            $recordIpStmt = $conn->prepare("SELECT count(failedLoginIP) AS loginAttempts FROM FailedLogins WHERE failedLoginIP = ".$clientIp.' AND < '".date('Y-m-d H:i:s', strtotime('-30 minutes')));
//
//
//            # Binds the found IP address to the SQL statement and executes the SQL statement.
//            $conn->beginTransaction();
//            $recordIpStmt->execute();
//            $conn->commit();


            # Tells the user that their username/password combination was wrong.
            echo "<script> alert(\"Incorrect username or password. Please try again.\"); </script>";
            header('Location: signIn.php');
        }

    } catch (PDOException $e) {
        # Rollback any changes to the database (if possible).
        $conn->rollBack();

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

<nav>
    <ul>
        <li><a href="index.html">Home</a> </li>
        <li><a href="aboutUs.html" >About</a> </li>
        <li><a href="whyTci.html">Why TCI?</a> </li>
        <li><a href="signIn.php" class="active">Sign In</a></li>
    </ul>
</nav>

<section class="sec2">
    <form action="signIn.php" method="post" style="padding-bottom: 200px">
        <p>
            <h2>Member Sign In</h2><br>
            <br>
        </p>

        <label>Username</label><br>
        <input type="email" placeholder="Enter Username" name="username" maxlength="254" required><br>
        <br>

        <label>Password</label><br>
        <input type="password" placeholder="Enter Password" name="password" maxlength="254" pattern="(?=.{8,256})(?=.*?[^\w\s])(?=.*?[0-9])(?=.*?[A-Z]).*?[a-z].*" required><br>
        <a href="#">Forgot Username</a><br>
        <a href="#">Forgot Password</a><br>
        <br>

        <button type="submit">Login</button><br>

        <p>
            <br>
            <br>Not a member yet?<br>
            <a href="signUp.php">Create Account</a><br>
        </p>

    </form>

    <img src="https://cdn.shopify.com/s/files/1/2379/3029/products/51oNZAKQomL._SL1500_0270363e-8918-419e-80c4-3866316ce410_1024x1024.jpg?v=1551237385">
</section>


<footer>
    <nav>
        <ul>
            <li><a onclick="return false" href="">Facebook</a> </li>
            <li><a onclick="return false" href="">Twitter</a> </li>
            <li><a onclick="return false" href="">Google+</a> </li>
            <li><a onclick="return false" href="">© 2019 Twin Cities Inn</a> </li>
        </ul>
    </nav>
</footer>

</body>

</html>