<?php

# All of this runs before the HTML code is rendered.
session_start();

require_once "settings/settings.php";
require_once "bin/inputSanitization.php";

// Stops if no session exists.
if (array_key_exists('loggedIn', $_SESSION) === false ) {
    echo "<script> alert(\"Your session has timed out, please sign in again.\"); </script>";
    header('Location: signIn.php');
    exit;
}

$memInfo['username'] = @sanitizeEmail($_SESSION['username']);
$memInfo['loggedIn'] = @sanitizeNumString($_SESSION['loggedIn']);

# Create array to hold the client's information.
$memInfo = array();
# Sanitize session data.
@$memInfo['username'] = sanitizeEmail($_SESSION['username']);


// Try to connect to the database.
try {
    $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
    # Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Queries the database to get the username and the password of the user.
    $selectAllStmt = $conn->prepare('SELECT * FROM Member INNER JOIN Address USING (memID) WHERE `memEmail`=:email');
    $selectAllStmt->bindParam(':email', $user, PDO::PARAM_STR, 254);

    # Begins a transaction, if there are any changes (which there shouldn't be) rollback the changes.
    $conn->beginTransaction();
    $selectAllStmt->execute();
    $conn->rollBack();

    # Gets the member's account details from out of the database query.
    $selectAllStmt->setFetchMode(PDO::FETCH_ASSOC);
    $memInfo = $selectAllStmt->fetch(PDO::FETCH_ASSOC);



} catch (PDOException $e) {
    # Rollback any changes to the database (if possible).
    @$conn->rollBack();
    @$conn = null;

    # Sends a JavaScript alert message back to the user notifying them that there was an error processing their request.
    echo "<script> alert(\"We are sorry, there seems to be a problem with our systems. Please try again. If problems still persist, please notify TCI at 651-000-0000.\"); </script>";
}


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Member Page</title>
    <link rel="stylesheet" href="style.css" type="text/css"/>

    <style>
        label:hover {
            background: #f2f5ff;
            border-radius:5px;
            padding:2px 4px;
        }
    </style>

</head>
<body>
<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1> Twin Cities Inn</h1>
</header>

<nav>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="aboutUs.html">About</a></li>
        <li><a href="whyTci.html">Why TCI?</a></li>
        <li><a href="bin/signOut.php">Sign Out</a></li>
    </ul>
</nav>
<nav style="top: 50px;">
    <ul>
        <li><a href="membersPage.php">Member's Page</a></li>
        <li><a href="profilePage.php" class="active">Profile</a></li>
        <li><a href="reservations.php">Reservations</a></li>
    </ul>
</nav>
<section class="sec1Member">
    <h3> Welcome back, <?php echo($memInfo['memFname']); ?>! </h3>
</section>


<section class="sec2Member">
    <div class="gridMember">
        <div class="memberName">
            <h2 style="font-style: italic">My Account</h2>

            Username/Email Address: <b> <?php echo($memInfo['memEmail']); ?> </b><br>
            <label class="pull-left">Edit </label>
            <input class="clickedit"
                   type="text"
                   name="memEmail"
                   id="memEmail"
                   minlength="3"
                   maxlength="254"
                   autocomplete="email"
                   autocorrect="on"
                   title="Enter a email address."
                   placeholder="Email/Username"/>
            <div class="clearfix"></div>

            Password:
            <label class="pull-left">Edit </label>
            <input class="clickedit"
                   type="text"
                   name="memPasswd"
                   id="memPasswd"
                   minlength="8"
                   maxlength="254"
                   placeholder="Password"
                   title="Passwords must be:
                 - Between 8 at 254 characters long.
                 - Contain at least ONE capital letter.
                 - Contain at least ONE lowercase letter.
                 - Contain at least ONE number.
                 - Contain at least ONE special character."
                   pattern="(?=.{8,256})(?=.*?[^\w\s])(?=.*?[0-9])(?=.*?[A-Z]).*?[a-z].*"/>
            <div class="clearfix"></div>








            <p>Hello, <?php echo($memInfo['memFname'] . " " . $memInfo['memLname'] . "!"); ?>  </p>
            <p>Member ID: <?php echo($memInfo['memID']); ?>  </p>
            <p>Email: <?php echo($memInfo['memEmail']); ?>  </p>
            <p>Member Since: <?php echo(date('M Y', strtotime($memInfo['createdAt']))); ?>  </p>
            <button class="editProfile">Edit Profile</button>
            <br/><br/>
            <br>
        </div>
        <div class="gridMemberReward">
            <h2 style="font-style: italic">Reward Points</h2>
            <p><?php echo($memInfo['memRewardPoints']) ?> points</p>
            <button class="redeemPoints">Redeem</button>
            <br/><br/>
            <a href="#" style="color: orange">Report Missing Points</a>
        </div>
        <div class="bookNow">
            <h2 style="font-style: italic">Make Reservation</h2>
            <p>Rooms can be booked from our website or in person.</p>
            <p>You can use reward points, cash, or credit when booking a room.</p>
            <button class="bookNow">Book Now</button><br/><br/>
        </div>
    </div>

</section>

<!-- <section class="sec3"></section> -->

<footer>
    <nav>
        <ul>
            <li><a onclick="return false" href="">Facebook</a></li>
            <li><a onclick="return false" href="">Twitter</a></li>
            <li><a onclick="return false" href="">Google+</a></li>
            <li><a onclick="return false" href="">© 2019 Twin Cities Inn</a></li>
        </ul>
    </nav>
</footer>
</body>
</html>
























