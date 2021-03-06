<?php
# All of this runs before the HTML code is rendered.
session_start();

require_once "settings/settings.php";
require_once "bin/inputSanitization.php";

// Stops if no session exists.
if (array_key_exists('loggedIn', $_SESSION) === false) {
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
    $userInfoStmt = $conn->prepare('SELECT * FROM `Member` INNER JOIN `Address` USING(memID)  WHERE `memEmail`=:email');
    $userInfoStmt->bindParam(':email', $memInfo['username'], PDO::PARAM_STR, 254);

    $cardInfoStmt = $conn->prepare('SELECT * FROM `Member` INNER JOIN `ChargeCard` USING(memID) WHERE `memEmail`=:email');
    $cardInfoStmt->bindParam(':email', $memInfo['username'], PDO::PARAM_STR, 254);

    # Begins a transaction, if there are any changes (which there shouldn't be) rollback the changes.
    $conn->beginTransaction();
    $userInfoStmt->execute();
    $cardInfoStmt->execute();
    $conn->rollBack();

    # Closes the database connection.
    $conn = null;

    # Gets the member's account details from out of the database query.
    $userInfoStmt->setFetchMode(PDO::FETCH_ASSOC);
    $memInfo = $userInfoStmt->fetchAll(PDO::FETCH_ASSOC);
    $cardInfoStmt->setFetchMode(PDO::FETCH_ASSOC);
    $cardInfo = $cardInfoStmt->fetchAll(PDO::FETCH_ASSOC);


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
    <title>Account Information Page</title>
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
    <h3><?php echo($memInfo[0]['memFname']); ?>'s Account Information </h3>
</section>


<section class="sec2Profile">
    <div class="gridMember">
        <div class="profileName">
            <h2 style="font-style: italic">Current Account Information</h2>

            <u>Username/Email Address:</u> <?php echo($memInfo[0]['memEmail']); ?> <br>

            <p><u>Name:</u> <?php echo($memInfo[0]['memFname'] . " " . $memInfo[0]['memLname'] . "!"); ?>  </p>
            <p><u>Member ID:</u> <?php echo($memInfo[0]['memID']); ?>  </p>
            <p><u>Mailing Address:</u>
                <?php
                echo('<br>' . $memInfo[0]['addressBuildNum'] . ' ' . $memInfo[0]['addressStreetName']);
                echo('<br>' . $memInfo[0]['addressCity'] . ', ' . $memInfo[0]['addressProvence'] . ' ' . $memInfo[0]['addressZip']);
                if ($memInfo[0]['addressAptNum'] != null) {
                    echo('<br> Apartment Number: ' . $memInfo[0]['addressAptNum']);
                }
                ?> </p>
            <p><u>Billing Address:</u>
                <?php
                echo('<br>' . $memInfo[1]['addressBuildNum'] . ' ' . $memInfo[1]['addressStreetName']);
                echo('<br>' . $memInfo[1]['addressCity'] . ', ' . $memInfo[1]['addressProvence'] . ' ' . $memInfo[1]['addressZip']);
                if ($memInfo[1]['addressAptNum'] != null) {
                    echo('<br> Apartment Number: ' . $memInfo[1]['addressAptNum']);
                }
                ?> </p>
            <p><u>Member Since:</u> <?php echo(date('M Y', strtotime($memInfo[0]['createdAt']))); ?>  </p>
            <br/><br/>
            <br>
        </div>

        <div class="navButton">
            <button onclick="showChangeUser()">Change Username/Email</button>
            <br/>
            <button onclick="showChangePassword()">Change Password</button>
            <br/>
            <button onclick="showChargeCard()">Manage Charge Cards</button>
            <br/><br/><br/>
            <button onclick="deleteAccount()" style="background-color: red">Delete Account</button>
            <br/>
        </div>
        <div class="changeSide">
            <form id="changeUser" action="bin/changeUsername.php" method="post" style="display: none">
                <h2 style="font-style: italic">Change Username/Email</h2><br/><br/>
                <label class="pull-left">Current Username/Email: </label>
                <input class="clickedit"
                       type="email"
                       required
                       name="oldUsername"
                       id="oldUsername"
                       minlength="3"
                       maxlength="254"
                       autocomplete="email"
                       autocorrect="on"
                       title="Enter a email address."/><br/><br/>

                <label class="pull-left">New Username/Email: </label>
                <input class="clickedit"
                       type="email"
                       required
                       name="newUsername"
                       id="newUsername"
                       minlength="3"
                       maxlength="254"
                       autocomplete="email"
                       autocorrect="on"
                       title="Enter a email address."/><br/><br/>

                <label class="pull-left">Confirm Username/Email: </label>
                <input class="clickedit"
                       type="email"
                       required
                       name="newUsernameConfirm"
                       id="newUsernameConfirm"
                       minlength="3"
                       maxlength="254"
                       autocomplete="email"
                       autocorrect="on"
                       title="Enter a email address."/><br/><br/>

                <button type="submit" class="makeChange" style="text-align: center;">Submit</button>
                <br/><br/>

            </form>


            <form id="changePassword" action="bin/changePassword.php" method="post" style="display: none">
                <h2 style="font-style: italic">Change Password</h2><br/><br/>
                <label class="pull-left">Current password: </label>
                <input class="clickedit"
                       type="password"
                       name="oldPassword"
                       id="oldPassword"
                       required
                       minlength="8"
                       maxlength="254"
                       pattern=<?php echo($passwordComplexityRequirements); ?> >
                <br>
                <br>

                <label class="pull-left">New Password: </label>
                <input class="clickedit"
                       type="password"
                       name="newPassword"
                       id="newPassword"
                       required
                       minlength="8"
                       maxlength="254"
                       title="Passwords must be:
                 - Between 8 at 254 characters long.
                 - Contain at least ONE capital letter.
                 - Contain at least ONE lowercase letter.
                 - Contain at least ONE number.
                 - Contain at least ONE special character."
                       pattern=<?php echo($passwordComplexityRequirements); ?>>
                 <br>
                 <br>

                <label class="pull-left">Confirm Password: </label>
                <input class="clickedit"
                       type="password"
                       name="newPasswordConfirm"
                       id="newPasswordConfirm"
                       required
                       minlength="8"
                       maxlength="254"
                       title="Passwords must be:
                 - Between 8 at 254 characters long.
                 - Contain at least ONE capital letter.
                 - Contain at least ONE lowercase letter.
                 - Contain at least ONE number.
                 - Contain at least ONE special character."
                       pattern=<?php echo($passwordComplexityRequirements); ?>>
                <br>
                <br>

                <button type="submit" class="makeChange" style="text-align: center;">Submit</button>
                <br>
                <br>

            </form>

            <div id="chargeCard" style="display: none">
                <table>
                    <h2 style="font-style: italic">Your TCI Wallet</h2>
                    <p>An overview of your charge cards.</p>
                    <tr>
                        <th>Card number</th>
                        <th>Expiration Date</th>
                    </tr>

                    <?php
                        for ($card = 0; $card < sizeof($cardInfo); $card++){
                        echo "<tr>";
                        echo "<th> *".preg_replace("#(.*?)(\d{4})$#", "$2",$cardInfo[$card]['cardNum'])."</th>";
                        echo "<th>".date('M/Y', strtotime($cardInfo[$card]['cardExpDate']))."</th>";
                        echo "</tr>";
                        }
                        ?>
                </table>
                <br/>
                <button onclick="window.location.href = 'addPayment.php';">Add New Card</button>

            </div>

        </div>
    </div>
</section>

<script>
    var changeUsername = document.getElementById("changeUser");
    var changePassword = document.getElementById("changePassword");
    var chargeCard = document.getElementById("chargeCard");


    function showChangeUser() {
        if (changeUsername.style.display === "none") {
            changeUsername.style.display = "block";
            changeUsername.required = true;
            changePassword.style.display = "none";
            changePassword.required = false;
            chargeCard.style.display = "none";
        }
    }

    function showChangePassword() {
        if (changePassword.style.display === "none") {
            changePassword.style.display = "block";
            changePassword.required = true;
            changeUsername.style.display = "none";
            changeUsername.required = false;
            chargeCard.style.display = "none";
        }
    }

    function showChargeCard() {
        if (chargeCard.style.display === "none") {
            chargeCard.style.display = "block";
            changeUsername.style.display = "none";
            changeUsername.required = false;
            changePassword.style.display = "none";
            changePassword.required = false;
        }
    }

</script>

<script language="javascript">
    function deleteAccount() {
        if (confirm("Are you sure you want to delete your account?")) {
            window.location.href="http://localhost:8080/bin/deleteUser.php";
        }
    }
</script>

</section>

<br><b> WARNING: </b> This website is for educational and demonstration purposes ONLY! Do not enter any sensitive information into this website! The images on this website are the property of their respective owners. By using this website you are acknowledging that creators of the website are not liable for damages of any kind! </br>


</body>
</html>