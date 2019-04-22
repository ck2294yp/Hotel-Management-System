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
    # Rollback any changes to the database (if possible).
    @$conn->rollBack();

    # Sends a JavaScript alert message back to the user notifying them that there was an error processing their request.
    echo "<script> alert(\"We are sorry, there seems to be a problem with our systems. Please try again. If problems still persist, please notify TCI at 651-000-0000.\"); </script>";
    header('Location: membersPage.php');
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
        <li><a href="bookingHistory.php">Booking History</a></li>
    </ul>
</nav>
<section class="sec1Member">
    <h3><?php echo($memInfo['memFname']); ?>'s Account Information </h3>
</section>


<section class="sec2Profile">
    <div class="gridMember">
        <div class="profileName">
            <h2 style="font-style: italic">Current Account Information</h2>

            Username/Email Address: <?php echo($memInfo['memEmail']); ?> <br>

            <p>Name: <?php echo($memInfo['memFname'] . " " . $memInfo['memLname'] . "!"); ?>  </p>
            <p>Member ID: <?php echo($memInfo['memID']); ?>  </p>
            <p>Mailing Address: <?php ?> </p>
            <p>Billing Address: <?php ?> </p>
            <p>Member Since: <?php echo(date('M Y', strtotime($memInfo['createdAt']))); ?>  </p>
            <br/><br/>
            <br>
        </div>

        <div class="navButton">
            <button onclick="showChangeUser()">Change Username/Email</button><br/>
            <button onclick="showChangePassword()">Change Password</button><br/>
            <button onclick="showChargeCard()">View Charge Card</button><br/><br/><br/>
            <button id="showDeleteAccount" style="background-color: red">Delete Account</button><br/>
        </div>
        <div class="changeSide">
            <div id="changeUser" style="display: none">
                <h2 style="font-style: italic">Change Username/Email</h2><br/><br/>
                <label class="pull-left">Current Username/Email: </label>
                <input class="clickedit"
                       type="text"
                       name="memEmail"
                       id="memEmail"
                       minlength="3"
                       maxlength="254"
                       autocomplete="email"
                       autocorrect="on"
                       title="Enter a email address."/><br/><br/>

                <label class="pull-left">New Username/Email: </label>
                <input class="clickedit"
                       type="text"
                       name="memEmail"
                       id="memEmail"
                       minlength="3"
                       maxlength="254"
                       autocomplete="email"
                       autocorrect="on"
                       title="Enter a email address."/><br/><br/>

                <label class="pull-left">Confirm Username/Email: </label>
                <input class="clickedit"
                       type="text"
                       name="memEmail"
                       id="memEmail"
                       minlength="3"
                       maxlength="254"
                       autocomplete="email"
                       autocorrect="on"
                       title="Enter a email address."/><br/><br/>

                <button class="makeChange" style="text-align: center;">Submit</button><br/><br/>

            </div>


            <div id="changePassword" style="display: none">
            <h2 style="font-style: italic">Change Password</h2><br/><br/>
            <label class="pull-left">Current password: </label>
            <input class="clickedit"
                   type="text"
                   name="memPasswd"
                   id="memPasswd"
                   minlength="8"
                   maxlength="254"
                   title="Passwords must be:
                 - Between 8 at 254 characters long.
                 - Contain at least ONE capital letter.
                 - Contain at least ONE lowercase letter.
                 - Contain at least ONE number.
                 - Contain at least ONE special character."
                   pattern="(?=.{8,256})(?=.*?[^\w\s])(?=.*?[0-9])(?=.*?[A-Z]).*?[a-z].*"/><br/><br/>

            <label class="pull-left">New Password: </label>
            <input class="clickedit"
                   type="text"
                   name="memPasswd"
                   id="memPasswd"
                   minlength="8"
                   maxlength="254"
                   title="Passwords must be:
                 - Between 8 at 254 characters long.
                 - Contain at least ONE capital letter.
                 - Contain at least ONE lowercase letter.
                 - Contain at least ONE number.
                 - Contain at least ONE special character."
                   pattern="(?=.{8,256})(?=.*?[^\w\s])(?=.*?[0-9])(?=.*?[A-Z]).*?[a-z].*"/><br/><br/>

            <label class="pull-left">Confirm Password: </label>
            <input class="clickedit"
                   type="text"
                   name="memPasswd"
                   id="memPasswd"
                   minlength="8"
                   maxlength="254"
                   title="Passwords must be:
                 - Between 8 at 254 characters long.
                 - Contain at least ONE capital letter.
                 - Contain at least ONE lowercase letter.
                 - Contain at least ONE number.
                 - Contain at least ONE special character."
                   pattern="(?=.{8,256})(?=.*?[^\w\s])(?=.*?[0-9])(?=.*?[A-Z]).*?[a-z].*"/><br/><br/>

                <button class="makeChange" style="text-align: center;">Submit</button><br/><br/>

            </div>

            <div id="chargeCard" style="display: none">
                <table>
                    <h2 style="font-style: italic">Your TCI Wallet</h2>
                    <p>An overview of your charge cards.</p>
                    <tr> <th>Your charge card</th> <th>Expires</th> </tr>
                    <tr>
                        <td>Your card ending in <?php ?></td>
                        <td><?php ?></td>
                    </tr>

                </table>
                <br/>
                <button>Add New Card</button>

            </div>

            <div class="overlay" id="dialog-container">
                <div class="popup">
                    <p style="color: black">Are you sure you want to delete your account?</p>
                    <div class="text-right">
                        <button class="dialog-btn2 btn-cancel" id="cancel" style="text-align: center">Cancel</button>
                        <button class="dialog-btn2 btn-primary" id="confirm" style="text-align: center">Confirm</button>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <script>
        var a = document.getElementById("changeUser");
        var b = document.getElementById("changePassword");
        var c = document.getElementById("chargeCard");


        function showChangeUser() {

            if (a.style.display === "none") {
                a.style.display = "block";
                b.style.display = "none";
                c.style.display = "none";
            }
        }

        function showChangePassword() {

            if (b.style.display === "none") {
                b.style.display = "block";
                a.style.display = "none";
                c.style.display = "none";
            }
        }

        function showChargeCard() {

            if (c.style.display === "none") {
                c.style.display = "block";
                a.style.display = "none";
                b.style.display = "none";
            }
        }

    </script>

    <script
            src="https://code.jquery.com/jquery-3.4.0.min.js"
            integrity="sha256-BJeo0qm959uMBGb65z40ejJYGSgR7REI4+CW1fNKwOg="
            crossorigin="anonymous">

    </script>

    <script>
        $(document).ready(function () {
            $('#showDeleteAccount').on('click', function () {
                $('#dialog-container').show();
            });
            $('#cancel').on('click', function () {
                $('#dialog-container').hide();
            });
            $('#confirm').on('click', function () {
                $('#dialog-container').hide();
            });
        });

    </script>

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
























