<?php

# All of this runs before the HTML code is rendered.
session_start();

require_once "settings/settings.php";
require_once "bin/inputSanitization.php";
require_once "bin/sendEmail.php";


# If member is already logged in, send them to the member's page.
if (key_exists('loggedIn', $_SESSION)) {
    echo "<script> alert(\"You are already logged in! Redirecting you to the membership page...\"); </script>";
    header('Location: membersPage.php');
}

if (sizeof($_REQUEST) > 0) {

    $userInput = array();
    $isError = false;

    # Sanitizes and creates variable for the memEmail.
    $userInput['email'] = sanitizeEmail($_REQUEST['memEmail']);
    if ($userInput['email'] === false) {
        $isError = false;
    }

    # Sanitizes and creates variable for the password.
    $userInput['password'] = sanitizePassword($_REQUEST['memPasswd'], $_REQUEST['confirmMemPasswd'], $passwdHashAlgo, $beginingSalt, $endingSalt);
    if ($userInput['password'] === false) {
        $isError = true;
    }

    # Sanitizes and creates variable for the firstName.
    $userInput['fName'] = sanitizeAlphaString($_REQUEST['memFname']);
    if ($userInput['fName'] === false) {
        $isError = true;
    }

    # Sanitizes and creates variable for the lName.
    $userInput['lName'] = sanitizeAlphaString($_REQUEST['memLname']);
    if ($userInput['lName'] === false) {
        $isError = true;
    }

    # Adds the member's DOB to the userInput array (doesn't need to be sanitized).
    $userInput['dob'] = $_REQUEST['memDob'];

    # Sanitizes and creates variable for the member's phone number.
    $userInput['phoneNum'] = sanitizeNumString(str_replace(array("-", "(", ")"), "", $_REQUEST['phoneNum']));
    if ($userInput['phoneNum'] === false) {
        $isError = true;
    }

    ######## Address Entry ########

    # Sanitizes and creates variable for the addressBuildNum.
    $userInput['buildNum'] = sanitizeNumString($_REQUEST['buildNum']);
    if ($userInput['buildNum'] === false) {
        $isError = true;
    }

    # Sanitizes and creates variable for the addrStrName.
    $userInput['strName'] = sanitizeAlphaString($_REQUEST['strName']);
    if ($userInput['strName'] === false) {
        $isError = true;
    }

    # Sanitizes and creates variable for the addrCity.
    $userInput['city'] = sanitizeAlphaString($_REQUEST['city']);
    if ($userInput === false) {
        $isError = true;
    }

    # Sanitizes and creates variable for the addrZip.
    $userInput['zip'] = sanitizeNumString($_REQUEST['zip']);
    if ($userInput['zip'] === false) {
        $isError = true;
    }

    # Sanitizes and creates variable for the Provence/State.
    $userInput['provence'] = sanitizeAlphaString($_REQUEST['provence']);
    if ($userInput['provence'] === false) {
        $isError = true;
    }

    # Sets the country of the user. (currently set to ONLY the US!)
    $userInput['country'] = "United States";

    # Sanitizes and creates variable for the optional apartment number.
    $userInput['aptNum'] = sanitizeNumString($_REQUEST['aptNum']);
    if ($userInput['aptNum'] === false) {
        $isError = true;
    }


    ######### Checks if billing address is the same. ###########

    # If the check box was NOT checked (Member has a separate billing address).
    if (!empty($_REQUEST['billingMailingAddressIsSame'])) {

        # Sanitizes and creates variable for the addressBuildNum.
        $userInput['billBuildNum'] = sanitizeNumString($_REQUEST['billBuildNum']);
        if ($userInput['billBuildNum'] === false) {
            $isError = true;
        }

        # Sanitizes and creates variable for the addrStrName.
        $userInput['billStrName'] = sanitizeAlphaString($_REQUEST['billStrName']);
        if ($userInput['billStrName'] === false) {
            $isError = true;
        }

        # Sanitizes and creates variable for the addrCity.
        $userInput['billCity'] = sanitizeAlphaString($_REQUEST['billCity']);
        if ($userInput['billCity'] === false) {
            $isError = true;
        }

        # Sanitizes and creates variable for the addrZip.
        $userInput['billZip'] = sanitizeNumString($_REQUEST['billZip']);
        if ($userInput['billZip'] === false) {
            $isError = true;
        }

        # Sanitizes and creates variable for the Provence/State.
        $userInput['billProvence'] = sanitizeAlphaString($_REQUEST['billProvence']);
        if ($userInput['billProvence'] === false) {
            $isError = true;
        }

        # Sets the country of the user. (currently set to ONLY the US!)
        $userInput['billCountry'] = "United States";

        # Sanitizes and creates variable for the optional apartment number.
        $userInput['billAptNum'] = sanitizeNumString($_REQUEST['billAptNum']);
        if ($userInput['billAptNum'] === false) {
            $isError = true;
        }

        # If member's mailing address is the same as their billing address. Simply use the same values as before.
    } else {
        $userInput['billBuildNum'] = $userInput['buildNum'];
        $userInput['billStrName'] = $userInput['strName'];
        $userInput['billCity'] = $userInput['city'];
        $userInput['billZip'] = $userInput['zip'];
        $userInput['billProvence'] = $userInput['provence'];
        $userInput['billCountry'] = $userInput['country'];
        $userInput['billAptNum'] = $userInput['aptNum'];
    }


    # Checks if there where any errors while parsing though the user input.
    if ($isError === true) {
        echo "<script> alert(\"Incomplete or Incorrect information specified Please Try again.\"); </script>";

    } else {
        ###### Tries to connect to the MySQL database using PDO (rather then MySQLi) #####
        try {
            $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
            # Set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            # Begins transaction before anything happens.
            $conn->beginTransaction();

            # Creates prepared SQL statement for the member.
            $userInput['activationLink'] = random_int(1, 999999999);
            $memStmt = $conn->prepare("INSERT INTO Member (memEmail, memPasswd, memFname, memLname, memDob, memPhone, memActivationLink) 
              VALUES (:memEmail, :memPasswd, :memFname, :memLname, :memDob, :memPhone, :memActivationLink)");
            $memStmt->bindParam(':memEmail', $userInput['email'], PDO::PARAM_STR, 254);
            $memStmt->bindParam(':memPasswd', $userInput['password'], PDO::PARAM_STR, 64);
            $memStmt->bindParam(':memFname', $userInput['fName'], PDO::PARAM_STR, 64);
            $memStmt->bindParam(':memLname', $userInput['lName'], PDO::PARAM_STR, 64);
            $memStmt->bindParam(':memDob', $userInput['dob']);
            $memStmt->bindParam(':memPhone', $userInput['phoneNum']);
            $memStmt->bindParam(':memActivationLink', $userInput['activationLink']);

            # Gets the memID of the newly created member.
            $memStmt->execute();
            $userInput['memID'] = $conn->lastInsertId();

            # Creates prepared SQL statement for the Member's mailing address.
            $mailAddrStmt = $conn->prepare("INSERT INTO Address (addressType, memID, addressBuildNum, addressStreetName, addressCity, addressZip, addressProvence, addressCountry, addressAptNum) 
              VALUES ('mailing', :memID, :addressBuildNum, :addressStreetName, :addressCity, :addressZip, :addressProvence, :addressCountry, :addressAptNum)");
            $mailAddrStmt->bindParam(':memID', $userInput['memID']);
            $mailAddrStmt->bindParam(':addressBuildNum', $userInput['buildNum'], PDO::PARAM_INT, 8);
            $mailAddrStmt->bindParam(':addressStreetName', $userInput['strName'], PDO::PARAM_STR, 64);
            $mailAddrStmt->bindParam(':addressCity', $userInput['city'], PDO::PARAM_STR, 64);
            $mailAddrStmt->bindParam(':addressZip', $userInput['zip'], PDO::PARAM_INT, 7);
            $mailAddrStmt->bindParam(':addressProvence', $userInput['provence'], PDO::PARAM_STR, 32);
            $mailAddrStmt->bindParam(':addressCountry', $userInput['country'], PDO::PARAM_STR, 64);
            $mailAddrStmt->bindParam(':addressAptNum', $userInput['aptNum'], PDO::PARAM_INT, 7);

            # Gets the AddressID of the mailing Address.
            $mailAddrStmt->execute();
            $userInput['mailAddressID'] = $conn->lastInsertId();

            # Creates prepared SQL statement for the Member's billing address.
            $billAddrStmt = $conn->prepare("INSERT INTO Address (addressType, memID, addressBuildNum, addressStreetName, addressCity, addressZip, addressProvence, addressCountry, addressAptNum) 
              VALUES ('billing', :memID, :addressBuildNum, :addressStreetName, :addressCity, :addressZip, :addressProvence, :addressCountry, :addressAptNum)");
            $billAddrStmt->bindParam(':memID', $userInput['memID']);
            $billAddrStmt->bindParam(':addressBuildNum', $userInput['billBuildNum'], PDO::PARAM_INT, 8);
            $billAddrStmt->bindParam(':addressStreetName', $userInput['billStrName'], PDO::PARAM_STR, 64);
            $billAddrStmt->bindParam(':addressCity', $userInput['billCity'], PDO::PARAM_STR, 64);
            $billAddrStmt->bindParam(':addressZip', $userInput['billZip'], PDO::PARAM_INT, 7);
            $billAddrStmt->bindParam(':addressProvence', $userInput['billProvence'], PDO::PARAM_STR, 32);
            $billAddrStmt->bindParam(':addressCountry', $userInput['billCountry'], PDO::PARAM_STR, 64);
            $billAddrStmt->bindParam(':addressAptNum', $userInput['billAptNum'], PDO::PARAM_INT, 7);

            # Gets the AddressID of the billing address.
            $billAddrStmt->execute();
            $userInput['billAddressID'] = $conn->lastInsertId();

            # Commits the changes to the database and closes the database connection.
            $conn->commit();
            $conn = null;

            # Sends an email out to the customer (if administrators allow it).
            if ($sendEmails === true) {
                sendActivationEmail($adminEmailAddress, $userInput['email']);
            }

            # Sends a JavaScript alert message back to the user notifying them of successful account creation.
            echo "<script> alert(\"Account created successfully!\"); </script>";


        } catch (PDOException $e) {
            # Rollback any changes to the database (if possible/required).
            $conn->rollBack();

            #DEBUG:
            echo "<br>" . $e->getMessage() . '\n<br>';
            echo $e->errorInfo() . '\n<br>';

            # Sends a JavaScript alert message back to the user notifying them that there was an error processing their request.
            echo "<script> alert(\"We are sorry. There was a problem processing your request. Please try again, if problem persists please call TCI at 651-000-0000.\"); </script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css" type="text/css"/>
</head>
<body>

<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1> Twin Cities Inn </h1>
</header>
<nav>
    <ul>
        <li><a href="index.html">Home</a></li>
        <li><a href="aboutUs.html">About</a></li>
        <li><a href="whyTci.html">Why TCI?</a></li>
        <li><a href="signIn.php" class="active">Sign In</a></li>
    </ul>
</nav>

<section class="sec1"></section>

<section class="sec2">
    <form action="signUp.php" method="post">
        <!-- Email/username and password -->
        <h2><font size="5">Login Information</font></h2>
        Enter your email address (this email address will also function as the username for your TCI account).<br>
        * <input type="email" name="memEmail" maxlength="254" required placeholder="Email/Username"><br>

        <br>
        Passwords must be at LEAST 8 characters long, and must contain one: <b>capital letter, lowercase letter, number,
            and special character</b>.<br>
        Users are STRONGLY encouraged to use more complicated passwords then just this minimum. <br>
        * <input type="password" name="memPasswd" maxlength="254" placeholder="Desired Password" required
                 pattern="(?=.{8,256})(?=.*?[^\w\s])(?=.*?[0-9])(?=.*?[A-Z]).*?[a-z].*"><br>

        <br>
        Type your password in again to confirm: <br>
        * <input type="password" name="confirmMemPasswd" maxlength="254" placeholder="Confirm Password" required
                 pattern="?=.{8,256})(?=.*?[^\w\s])(?=.*?[0-9])(?=.*?[A-Z]).*?[a-z].*"><br>


        <!-- Personal Details (Name, DOB, etc.)-->
        <br>
        <br>
        <h2><font size="5">Personal Information</font></h2>
        Enter your first name. <br>
        * <input type="text" name="memFname" placeholder="First Name" maxlength="64" pattern="[A-Za-z\-\h]{2,64}"
                 required><br>

        <br>
        Enter your last name. <br>
        * <input type="text" name="memLname" maxlength="64" placeholder="Last Name" pattern="[A-Za-z\-\h]{2-64}"
                 required><br>

        <br>
        Enter your date of birth (DOB). <br>
        * <input type="date" min="1900-01-01" name="memDob" placeholder="Date Of Birth" required><br>

        <br>
        Enter Your Phone Number: <br>
        * <input type="number" name="phoneNum" min="10" maxlength="14" placeholder="Phone Number" pattern="[0-9\-]{14}">
        <br>

        <!-- Mailing/Billing Information -->
        <br>
        <br>
        <h2><font size="5">Mailing Information</font></h2>

        <br>
        Enter your preferred house or building number. <br>
        * <input type="number" name="buildNum" min="1" maxlength="8" placeholder="Building/House Number"
                 pattern="[0-9]{8}" required><br>

        <br>
        Enter your street address. <br>
        * <input type="text" name="strName" maxlength="64" placeholder="Street Address" pattern="[A-Za-z\.\-\h]{2,64}"
                 required><br>

        <br>
        Enter your city. <br>
        * <input type="text" name="city" maxlength="64" required placeholder="City" pattern="[A-Za-z\-\h]{2,64}"><br>

        <br>
        Enter your zip code. <br>
        * <input type="number" name="zip" min="1" maxlength="7" required placeholder="Zip Code" pattern="[0-9]{7}"><br>


        <br>
        Enter your state. <br>
        * <select required name="provence" autocomplete="on" size="1">
            <option value="AL">Alabama (AL)</option>
            <option value="AK">Alaska (AK)</option>
            <option value="AZ">Arizona (AZ)</option>
            <option value="AR">Arkansas (AR)</option>
            <option value="CA">California (CA)</option>
            <option value="CO">Colorado (CO)</option>
            <option value="CT">Connecticut (CT)</option>
            <option value="DE">Delaware (DE)</option>
            <option value="DC">District Of Columbia (DC)</option>
            <option value="FL">Florida (FL)</option>
            <option value="GA">Georgia (GA)</option>
            <option value="HI">Hawaii (HI)</option>
            <option value="ID">Idaho (ID)</option>
            <option value="IL">Illinois (IL)</option>
            <option value="IN">Indiana (IN)</option>
            <option value="IA">Iowa (IA)</option>
            <option value="KS">Kansas (KS)</option>
            <option value="KY">Kentucky (KY)</option>
            <option value="LA">Louisiana (LA)</option>
            <option value="ME">Maine (ME)</option>
            <option value="MD">Maryland (MD)</option>
            <option value="MA">Massachusetts (MA)</option>
            <option value="MI">Michigan (MI)</option>
            <option value="MN">Minnesota (MN)</option>
            <option value="MS">Mississippi (MS)</option>
            <option value="MO">Missouri (MO)</option>
            <option value="MT">Montana (MT)</option>
            <option value="NE">Nebraska (NE)</option>
            <option value="NV">Nevada (NV)</option>
            <option value="NH">New Hampshire (NH)</option>
            <option value="NJ">New Jersey (NJ)</option>
            <option value="NM">New Mexico (NM)</option>
            <option value="NY">New York (NY)</option>
            <option value="NC">North Carolina (NC)</option>
            <option value="ND">North Dakota (ND)</option>
            <option value="OH">Ohio (OH)</option>
            <option value="OK">Oklahoma (OK)</option>
            <option value="OR">Oregon (OR)</option>
            <option value="PA">Pennsylvania (PA)</option>
            <option value="RI">Rhode Island (RI)</option>
            <option value="SC">South Carolina (SC)</option>
            <option value="SD">South Dakota (SD)</option>
            <option value="TN">Tennessee (TN)</option>
            <option value="TX">Texas (TX)</option>
            <option value="UT">Utah (UT)</option>
            <option value="VT">Vermont</option>
            <option value="VA">Virginia</option>
            <option value="WA">Washington</option>
            <option value="WV">West Virginia</option>
            <option value="WI">Wisconsin</option>
            <option value="WY">Wyoming</option>
        </select>
        <br>
        <br>

        Enter your apartment number, if needed (optional). <br>
        <input type="number" name="aptNum" min="1" maxlength="7" placeholder="Apartment Number"
               pattern="[a-zA-Z0-9\-\h]{7}"> <br>
        <br>
        <br>

        <!--JavaScript used to display additional user billing information (called below).-->
        <script>
            function billingSection() {
                // Get the checkbox
                var checkBox = document.getElementById("billingMailingAddressIsSame");
                // Get the output text
                var text = document.getElementById("additionalBillingInfo");

                // If the checkbox is checked, display the output text
                if (checkBox.checked === true) {
                    text.style.display = "block";
                } else {
                    text.style.display = "none";
                }
            }
        </script>

        Mailing Address is different than my Billing Address. <br>
        <input type="checkbox" id="billingMailingAddressIsSame" name="billingMailingAddressIsSame"
               onclick="billingSection()"> <br>


        <!-- Allows the member to add their billing information (if it is different then their mailing address). -->
        <section class="sec2" id="additionalBillingInfo" style="display:none">

            <h2><font size="5">Billing Address</font></h2>

            <br>
            Enter the building number you'd like to use for billing. <br>
            * <input type="number" name="billBuildNum" min="1" maxlength="8" placeholder="Building/House Number"
                     pattern="[0-9]{8}"><br>

            <br>
            Enter your billing street address. <br>
            * <input type="text" name="billStrName" maxlength="64" placeholder="Street Address"
                     pattern="[A-Za-z\.\-\h]{2,64}"><br>

            <br>
            Enter your billing city. <br>
            * <input type="text" name="billCity" maxlength="64" placeholder="City"
                     pattern="[A-Za-z\-\h]{2,64}"><br>

            <br>
            Enter your billing zip code. <br>
            * <input type="number" name="billZip" min="1" maxlength="7" placeholder="Zip Code"
                     pattern="[0-9]{7}"><br>


            <br>
            Enter your billing state. <br>
            * <select name="billProvence" autocomplete="on" size="1">
                <option value="AL">Alabama (AL)</option>
                <option value="AK">Alaska (AK)</option>
                <option value="AZ">Arizona (AZ)</option>
                <option value="AR">Arkansas (AR)</option>
                <option value="CA">California (CA)</option>
                <option value="CO">Colorado (CO)</option>
                <option value="CT">Connecticut (CT)</option>
                <option value="DE">Delaware (DE)</option>
                <option value="DC">District Of Columbia (DC)</option>
                <option value="FL">Florida (FL)</option>
                <option value="GA">Georgia (GA)</option>
                <option value="HI">Hawaii (HI)</option>
                <option value="ID">Idaho (ID)</option>
                <option value="IL">Illinois (IL)</option>
                <option value="IN">Indiana (IN)</option>
                <option value="IA">Iowa (IA)</option>
                <option value="KS">Kansas (KS)</option>
                <option value="KY">Kentucky (KY)</option>
                <option value="LA">Louisiana (LA)</option>
                <option value="ME">Maine (ME)</option>
                <option value="MD">Maryland (MD)</option>
                <option value="MA">Massachusetts (MA)</option>
                <option value="MI">Michigan (MI)</option>
                <option value="MN">Minnesota (MN)</option>
                <option value="MS">Mississippi (MS)</option>
                <option value="MO">Missouri (MO)</option>
                <option value="MT">Montana (MT)</option>
                <option value="NE">Nebraska (NE)</option>
                <option value="NV">Nevada (NV)</option>
                <option value="NH">New Hampshire (NH)</option>
                <option value="NJ">New Jersey (NJ)</option>
                <option value="NM">New Mexico (NM)</option>
                <option value="NY">New York (NY)</option>
                <option value="NC">North Carolina (NC)</option>
                <option value="ND">North Dakota (ND)</option>
                <option value="OH">Ohio (OH)</option>
                <option value="OK">Oklahoma (OK)</option>
                <option value="OR">Oregon (OR)</option>
                <option value="PA">Pennsylvania (PA)</option>
                <option value="RI">Rhode Island (RI)</option>
                <option value="SC">South Carolina (SC)</option>
                <option value="SD">South Dakota (SD)</option>
                <option value="TN">Tennessee (TN)</option>
                <option value="TX">Texas (TX)</option>
                <option value="UT">Utah (UT)</option>
                <option value="VT">Vermont</option>
                <option value="VA">Virginia</option>
                <option value="WA">Washington</option>
                <option value="WV">West Virginia</option>
                <option value="WI">Wisconsin</option>
                <option value="WY">Wyoming</option>
            </select>
            <br>
            <br>

            Enter your billing apartment number, if needed (optional). <br>
            <input type="number" name="billAptNum" min="1" maxlength="7" placeholder="Apartment Number"
                   pattern="[a-zA-Z0-9\-\h]{7}"> <br>
        </section>


        <input type="reset"/> <input type="submit" value="Sign Up!"/>
    </form>

    <br> *: Input is required.<br>
</section>

<section class="sec3"></section>
<footer>
    <nav>
        <ul>
            <li><a href="#">Facebook</a href="#"></li>
            <li><a href="#">Twitter</a></li>
            <li><a href="#">Google+</a></li>
            <li><a href="#">© 2019 Twin Cities Inn</a></li>
        </ul>
    </nav>
</footer>
</body>
</html>
