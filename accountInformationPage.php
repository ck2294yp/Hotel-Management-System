<?php
# All of this runs before the HTML code is rendered.
session_start();

require_once "settings/settings.php";
require_once "bin/inputSanitization.php";

// Stops if no session exists.
if (array_key_exists('loggedIn', $_SESSION) === false) {
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
    $userInfoStmt = $conn->prepare('SELECT * FROM `Member` INNER JOIN `Address` USING(memID) WHERE `memEmail`=:email');
    $userInfoStmt->bindParam(':email', $memInfo['username'], PDO::PARAM_STR, 254);


    # Queries the database the Charge Card of the user.
    $getCard = $conn->prepare('select * from `ChargeCard` where `memID`=:memID');
    $getCard->bindParam(':memID', $memInfo['memID'], PDO::PARAM_STR, 254);


    # Begins a transaction, if there are any changes (which there shouldn't be) rollback the changes.
    $conn->beginTransaction();
    $userInfoStmt->execute();
    $conn->rollBack();

    # Closes the database connection.
    $conn = null;


    # Gets the member's account details from out of the database query.
    $userInfoStmt->setFetchMode(PDO::FETCH_ASSOC);
    $memInfo = $userInfoStmt->fetchAll(PDO::FETCH_ASSOC);

    $getCard->setFetchMode(PDO::FETCH_ASSOC);


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
            <button onclick="showChargeCard()">Manage Charge Card</button>
            <br/><br/><br/>
            <button onclick="deleteAccount()" style="background-color: red">Delete Account</button>
            <br/>
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

                <button class="makeChange" style="text-align: center;">Submit</button>
                <br/><br/>

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

                <button class="makeChange" style="text-align: center;">Submit</button>
                <br/><br/>

            </div>

            <div id="chargeCard" style="display: none">
                <table>
                    <h2 style="font-style: italic">Your TCI Wallet</h2>
                    <p>An overview of your charge cards.</p>
                    <tr>
                        <th>Your charge card</th>
                        <th>Expires</th>
                    </tr>

                    <?php
                    while ($card = $getCard->fetch( PDO::FETCH_ASSOC )):
                        ?>
                        <tr>
                            <td>Your card ending in
                                <?php echo $ccNum = $card['cardNum']; ?>
                                <?php echo $last4Digits = preg_replace( "#(.*?)(\d{4})$#", "$2", $ccNum); ?>
                            </td>
                            <td>
                                <?php echo $card['cardExpDate']; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
                <br/>
                <button>Add New Card</button>

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

    <script language="javascript">
        function deleteAccount()
        {
            if(confirm("Are you sure you want to delete your account?")){
                alert("Account deleted");
                return true;
            }
        }
    </script>

</section>

<!-- Footer for the web page.-->
<?php include_once 'bin/footer.php'; ?>

</body>
</html>