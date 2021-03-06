<?php
# All of this runs before the HTML code is rendered.
session_start();

require_once "settings/settings.php";
require_once "bin/inputSanitization.php";

// Stops if no session exists.
if (array_key_exists('loggedIn', $_SESSION) === false ) {
    echo'<script src="/displayError.js"></script>';
    echo("<script> sessionTimeoutError(); </script>");
}

$memInfo['username'] = @sanitizeEmail($_SESSION['username']);
$memInfo['loggedIn'] = @sanitizeNumString($_SESSION['loggedIn']);

# Create array to hold the client's information.
$memInfo = array();
# Sanitize session data.
@$memInfo['username'] = sanitizeEmail($_SESSION['username']);


# Connects to the SQL database.
try {
    $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
    # Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    # Queries the database to get the username and the password of the user.
    $userInfoStmt = $conn->prepare('select memID, memEmail, memFname, memLname, createdAt, updatedAt, memRewardPoints from `Member` where `memEmail`=:email');
    $userInfoStmt->bindParam(':email', $memInfo['username'], PDO::PARAM_STR, 254);

    # Begins a transaction, if there are any changes (which there shouldn't be) rollback the changes.
    $conn->beginTransaction();
    $userInfoStmt->execute();
    $conn->rollBack();

    # Closes the database connection.
    $conn = null;


    # Gets the member's account details from out of the database query.
    $userInfoStmt->setFetchMode(PDO::FETCH_ASSOC);
    $memInfo = $userInfoStmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    # Sends user database error message.
    echo'<script src="/displayError.js"></script>';
    echo("<script> databaseError(); </script>");
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Member Page</title>
    <link rel="stylesheet" href="style.css" type="text/css"/>
</head>

<body>
<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1> Twin Cities Inn</h1>
</header>

<!--Navigation Bar-->
<?php include 'bin/nav.php'; ?>


<section class="sec1Member">
    <h3> Welcome back, <?php echo($memInfo['memFname']); ?>! </h3>
</section>

<section class="sec2Member">
    <div class="gridMember">

        <div class="memberName">
            <h2 style="font-style: italic">My Account</h2>
            <p>Hello, <?php echo($memInfo['memFname'] . " " . $memInfo['memLname'] . "!"); ?>  </p>
            <p>Member ID: <?php echo($memInfo['memID']); ?>  </p>
            <p>Email: <?php echo($memInfo['memEmail']); ?>  </p>
            <p>Member Since: <?php echo(date('M Y', strtotime($memInfo['createdAt']))); ?>  </p>
            <button class="editProfile" onclick="window.location.href = 'accountInformationPage.php';">Edit Profile</button>
            <br/><br/>
            <br>
        </div>

        <div class="gridMemberReward">
            <h2 style="font-style: italic">Reward Points</h2>
            <p><?php echo($memInfo['memRewardPoints']) ?> points</p>
            <br/><br/>
            <a href="mailto:tcihotelsmn@gmail.com?Subject=Report%20Missing%20Points" style="color: orange">Report Missing Points</a>
        </div>

        <div class="bookNow">
            <h2 style="font-style: italic">Make Reservation</h2>
            <p>Rooms can be booked from our website or in person.</p>
            <p>You can use reward points or credit when booking a room.</p>
            <button class="bookNow"onclick="window.location.href = 'booking.php';">Book Now</button><br/><br/>
        </div>
    </div>


</section>

</body>

<br><b> WARNING: </b> This website is for educational and demonstration purposes ONLY! Do not enter any sensitive information into this website! The images on this website are the property of their respective owners. By using this website you are acknowledging that creators of the website are not liable for damages of any kind! </br>


</html>