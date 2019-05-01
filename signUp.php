<?php

# All of this runs before the HTML code is rendered.
session_start();

# Imports the required files needed to ensure program page works properly.
require_once "settings/settings.php";
require_once "bin/inputSanitization.php";
require_once "bin/sendEmail.php";
require_once "vendor/autoload.php";

// Creates minimum and maximum allowable birth dates.
$todayDate = date_create('now');
$youngestBirthDate = date_format(date_add($todayDate, date_interval_create_from_date_string('-18 years')), 'Y-m-d');
$oldestBirthDate = date_format(date_add($todayDate, date_interval_create_from_date_string('-100 years')), 'Y-m-d');

# If member is already logged in, send them to the member's page.
if (array_key_exists('loggedIn', $_SESSION)) {
    echo '<script src="/displayError.js"></script>';
    echo("<script> alreadyLoggedInMsg(); </script>");
}

if (sizeof($_REQUEST) > 0) {
    // Makes sure the userInput variable is empty first.
    $userInput = array();

    // Sanitizes all of the user input.
    $userInput['email'] = sanitizeEmail($_REQUEST['memEmail']);
    $userInput['password'] = sanitizePassword($_REQUEST['memPasswd'], $_REQUEST['confirmMemPasswd'], $passwdHashAlgo, $beginingSalt, $endingSalt);
    $userInput['fName'] = sanitizeAlphaString($_REQUEST['memFname']);
    $userInput['lName'] = sanitizeAlphaString($_REQUEST['memLname']);
    $userInput['dob'] = sanitizeDateString($_REQUEST['memDob']);
    $userInput['phoneNum'] = sanitizeNumString(str_replace(array("-", "(", ")", "+"), "", $_REQUEST['phoneNum']));
    $userInput['buildNum'] = sanitizeNumString($_REQUEST['buildNum']);
    $userInput['strName'] = sanitizeAlphaNumString($_REQUEST['strName']);
    $userInput['city'] = sanitizeAlphaString($_REQUEST['city']);
    $userInput['zip'] = sanitizeNumString($_REQUEST['zip']);
    $userInput['provence'] = sanitizeAlphaString($_REQUEST['provence']);
    $userInput['country'] = sanitizeAlphaString("United States");
    $userInput['aptNum'] = sanitizeAlphaNumString($_REQUEST['aptNum']);


    // Checks if selected password matches the confirmation password.
    if ($userInput['password'] === false) {
        echo '<script src="/displayError.js"></script>';
        echo("<script> unacceptibleNewPasswordMsg('http://localhost:8080/signUp.php'); </script>");
    }


    # If member has a SEPARATE billing address (the check box was NOT checked) then sanitize that input too.
    if (!empty($_REQUEST['billingMailingAddressIsSame'])) {
        $userInput['billBuildNum'] = sanitizeNumString($_REQUEST['billBuildNum']);
        $userInput['billStrName'] = sanitizeAlphaNumString($_REQUEST['billBuildNum']);
        $userInput['billCity'] = sanitizeAlphaString($_REQUEST['billCity']);
        $userInput['billZip'] = sanitizeNumString($_REQUEST['billZip']);
        $userInput['billProvence'] = sanitizeAlphaString($_REQUEST['billProvence']);
        $userInput['billCountry'] = sanitizeAlphaString("United States");
        $userInput['billAptNum'] = sanitizeAlphaNumString($_REQUEST['billAptNum']);

    # If member's mailing address is the SAME as their billing address. Simply use the same values as before.
    } else {
        $userInput['billBuildNum'] = $userInput['buildNum'];
        $userInput['billStrName'] = $userInput['strName'];
        $userInput['billCity'] = $userInput['city'];
        $userInput['billZip'] = $userInput['zip'];
        $userInput['billProvence'] = $userInput['provence'];
        $userInput['billCountry'] = $userInput['country'];
        $userInput['billAptNum'] = $userInput['aptNum'];
    }


    ###### Tries to connect to the MySQL database using PDO (rather then MySQLi) #####
    try {
        $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
        # Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        #Check if username/email address already exists.
        $checkUsernameStmt = $conn->prepare("SELECT memEmail FROM Member WHERE memEmail=:memEmail");
        $checkUsernameStmt->bindParam(':memEmail', $userInput['email'], PDO::PARAM_STR, 254);
        $checkUsernameStmt->execute();
        if ($checkUsernameStmt->rowCount() > 0) {
            echo '<script src="/displayError.js"></script>';
            echo("<script> usernameTakenMsg(); </script>");
        }


        # Begins transaction before inserting any new data into the database.
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
        $memStmt->execute();

        # Gets the memID of the newly created member.
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
        $mailAddrStmt->execute();

        # Gets the AddressID of the mailing Address.
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
        $billAddrStmt->execute();

        # Gets the AddressID of the billing address.
        $userInput['billAddressID'] = $conn->lastInsertId();

        # Commits the changes to the database and closes the database connection.
        $conn->commit();
        $conn = null;


        # Sends an email out to the customer (if administrators allow it).
        if (accountActivate($userInput['email'])) {
            echo '<script src="/displayError.js"></script>';
            echo("<script> emailAccountActivationMsg(); </script>");

        // If for some reason the email server fails to respond (or sendEmails is administratively set to "off") activate the user's account without it.
        } else {
            $email = $userInput['email'];
            $activationId = $userInput['activationLink'];
            $link = "http://localhost:8080/activate.php?user=$email&activationId=$activationId";
            echo '<script src="/displayError.js"></script>';
            echo("<script> noEmailAccountActivationMsg($link); </script>");
        }


    } catch (PDOException $e) {
        // If there is an error with the database tell user to try again.
        echo '<script src="/displayError.js"></script>';
        echo("<script> databaseError(); </script>");
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

<!--Navigation Bar-->
<?php include 'bin/nav.php'; ?>

<!-- Makes a JavaScript function to check for invalid input. -->
<script type="text/javascript" language="JavaScript">
    function checkInput(input, message) {
        const nameInput = document.querySelector(input);

        nameInput.addEventListener('input', () => {
            nameInput.setCustomValidity('');
            nameInput.checkValidity();
        });

        nameInput.addEventListener('invalid', () => {
            if (nameInput.value === '') {
                nameInput.setCustomValidity('');
                nameInput.checkValidity();
            } else {
                nameInput.setCustomValidity(message);
            }
        });
    }
</script>


<section class="sec1"></section>

<section class="sec2">
    <form action="signUp.php" method="post">
        <!-- Email/username and password -->
        <h2><font size="5">Login Information</font></h2>
        Enter your email address (this email address will also function as the username for your TCI account).<br>
        * <input type="email"
                 name="memEmail"
                 id="memEmail"
                 required
                 minlength="3"
                 maxlength="254"
                 autocomplete="email"
                 autocorrect="on"
                 title="Enter a email address."
                 placeholder="Email/Username">
        <script>checkInput('memEmail', 'Please enter a valid Email address!');</script>
        <br>
        <br>

        Passwords must be at LEAST 8 characters long, and must contain one: <b>capital letter, lowercase letter, number,
            and special character</b>.<br>
        Users are STRONGLY encouraged to use more complicated passwords then just this minimum. <br>
        * <input type="password"
                 name="memPasswd"
                 id="memPasswd"
                 required
                 minlength="8"
                 maxlength="254"
                 placeholder="Desired Password"
                 title='
                 Invalid password characters detected! Passwords must be: Between 8 - 254 characters long, Contain at least ONE capital letter, Contain at least ONE lowercase letter, Contain at least ONE number, and Contain at least ONE special character.'
                 pattern=<?php echo ($passwordComplexityRequirements); ?>>
        <script>
            checkInput('memPasswd', 'Passwords must be:\n' +
                '- Between 8 at 254 characters long.\n' +
                '- Contain at least ONE capital letter.\n' +
                '- Contain at least ONE lowercase letter.\n' +
                '- Contain at least ONE number.\n' +
                '- Contain at least ONE special character.');
        </script>
        <br>
        <br>

        Type your password in again to confirm: <br>
        * <input type="password"
                 name="confirmMemPasswd"
                 id="confirmMemPasswd"
                 required
                 minlength="8"
                 maxlength="254"
                 placeholder="Confirm Password"
                 title="Passwords must match!"
                 pattern=<?php echo ($passwordComplexityRequirements); ?>>
        <br>
        <br>

        <!-- Personal Details (Name, DOB, etc.)-->
        <br>
        <h2><font size="5">Personal Information</font></h2>
        Enter your first name. <br>
        * <input type="text"
                 name="memFname"
                 placeholder="First Name"
                 autocomplete="given-name"
                 minlength="2"
                 maxlength="64"
                 autocorrect="on"
                 pattern="[A-Za-z\-\h]{2,64}"
                 required>
        <script>checkInput('memFname', 'Please enter a valid first name!');</script>
        <br>
        <br>

        Enter your last name. <br>
        * <input type="text"
                 name="memLname"
                 autocomplete="family-name"
                 required
                 minlength="2"
                 maxlength="64"
                 autocorrect="on"
                 placeholder="Last Name"
                 pattern="[A-Za-z\-\h]{2,64}">
        <script>checkInput('memLname', 'Please enter a valid last name!');</script>
        <br>
        <br>

        <!--
        var dateControl = document.querySelector('input[type="date"]');
        dateControl.value = '2017-06-01';
        -->
        Enter your date of birth (DOB). <br>
        * <input type="date"
                 name="memDob"
                 required
                 autocomplete="bday"
                 min=<?php echo ($oldestBirthDate); ?>
                 max=<?php echo ($youngestBirthDate); ?>
                 placeholder="Date Of Birth">
        <script>checkInput('memDob', 'Please enter a valid date of birth!');</script>
        <br>
        <br>

        Enter Your Phone Number: <br>
        * <input type="text"
                 name="phoneNum"
                 required
                 min="10"
                 maxlength="14"
                 placeholder="Phone Number"
                 autocomplete="tel"
                 pattern="^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\./0-9]*$">
        <script>checkInput('phoneNum', 'Please enter a valid phone number!');</script>
        <br>
        <br>

        <!-- Mailing/Billing Information -->
        <br>
        <h2><font size="5">Mailing Information</font></h2>

        <br>
        Enter your preferred house or building number. <br>
        * <input type="number"
                 name="buildNum"
                 required
                 autocomplete="on"
                 min="1"
                 maxlength="8"
                 placeholder="Building/House Number"
                 pattern="[0-9]{1,8}">
        <script>checkInput('buildNum', 'Please enter a valid home or building number!');</script>
        <br>
        <br>

        Enter your street address. <br>
        * <input type="text"
                 name="strName"
                 required
                 autocomplete="on"
                 minlength="2"
                 maxlength="64"
                 spellcheck="true"
                 placeholder="Street Address"
                 pattern="[A-Za-z0-9\.\-\h]{2,64}">
        <script>checkInput('strName', 'Please enter a valid street name!');</script>
        <br>
        <br>

        Enter your city. <br>
        * <input type="text"
                 name="city"
                 required
                 minlength="2"
                 maxlength="64"
                 autocomplete="on"
                 spellcheck="true"
                 placeholder="City"
                 pattern="[A-Za-z\-\h]{2,64}">
        <script>checkInput('city', 'Please enter a valid city!');</script>
        <br>
        <br>

        Enter your zip code. <br>
        * <input
                type="number"
                name="zip"
                required
                min="1"
                maxlength="7"
                placeholder="Zip Code"
                pattern="[0-9]{1,7}">
        <script>checkInput('zip', 'Please enter a zip code!');</script>
        <br>
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
        <script>checkInput('provence', 'Please enter a state!');</script>
        <br>
        <br>

        Enter your apartment number, if needed (optional). <br>
        <input type="number"
               name="aptNum"
               min="1"
               maxlength="7"
               placeholder="Apartment Number"
               pattern="[a-zA-Z0-9\-\h]{1,7}">
        <script>checkInput('aptNum', 'Please enter a valid apartment number!');</script>
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
                    text.required = "true";
                } else {
                    text.style.display = "none";
                    text.required = "false";
                }
            }
        </script>

        Mailing Address is different than my Billing Address. <br>
        <input type="checkbox"
               id="billingMailingAddressIsSame"
               name="billingMailingAddressIsSame"
               onclick="billingSection()">
        <br>


        <!-- Allows the member to add their billing information (if it is different then their mailing address). -->
        <section class="sec2" id="additionalBillingInfo" style="display:none;">
            <h2><font size="5">Billing Address</font></h2>
            <br>
            Enter the building number you'd like to use for billing. <br>
            * <input
                    type="number"
                    name="billBuildNum"
                    id="billBuildNum"
                    min="1"
                    maxlength="8"
                    autocomplete="on"
                    placeholder="Building/House Number"
                    pattern="[0-9]{1,8}">
            <script>checkInput('billBuildNum', 'Please enter a valid building number!');</script>
            <br>
            <br>

            Enter your billing street address. <br>
            * <input type="text"
                     name="billStrName"
                     id="billStrName"
                     minlength="2"
                     maxlength="64"
                     spellcheck="true"
                     autocomplete="on"
                     placeholder="Street Address"
                     pattern="[A-Za-z0-9\.\-\h]{2,64}">
            <script>checkInput('billStrName', 'Please enter a valid street name!');</script>
            <br>
            <br>

            Enter your billing city. <br>
            * <input type="text"
                     name="billCity"
                     minlength="2"
                     maxlength="64"
                     spellcheck="true"
                     autocomplete="on"
                     placeholder="City"
                     pattern="[A-Za-z\-\h]{2,64}">
            <script>checkInput('billCity', 'Please enter a valid city name!');</script>
            <br>
            <br>

            Enter your billing zip code. <br>
            * <input type="number"
                     name="billZip"
                     min="1"
                     maxlength="7"
                     autocomplete="on"
                     placeholder="Zip Code"
                     pattern="[0-9]{1,7}">
            <script>checkInput('billZip', 'Please enter a valid Zip code!');</script>
            <br>
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
            <script>checkInput('billProvence', 'Please enter a valid state!');</script>
            <br>
            <br>

            Enter your billing apartment number, if needed (optional). <br>
            <input type="number"
                   name="billAptNum"
                   min="1"
                   maxlength="7"
                   placeholder="Apartment Number"
                   pattern="[a-zA-Z0-9\-\h]{1,7}">
            <script>checkInput('billAptNum', 'Please enter a valid apartment number!');</script>
            <br>
            <br>
        </section>


        <input type="reset"/> <input type="submit" value="Sign Up!"/>
    </form>

    <br> *: Input is required.<br>
</section>

</body>
</html>
