<?php

# All of this runs before the HTML code is rendered.
session_start();

require_once "settings/settings.php";
require_once "bin/inputSanitization.php";

if (sizeof($_REQUEST) > 0) {

    $userInput = array();
    $isError = false;

    # Sanitizes and creates variable for the memEmail.
    if (sanitizeEmail($_REQUEST['memEmail']) !== false) {
        $userInput['email'] = sanitizeEmail($_REQUEST['memEmail']);
    } else {$isError = true;}

    # Sanitizes and creates variable for the password.
    if (sanitizePassword($_REQUEST['memPasswd'], $_REQUEST['confirmMemPasswd'], $passwdHashAlgo, $beginingSalt, $endingSalt) !== false) {
        $userInput['password'] = sanitizePassword($_REQUEST['memPasswd'], $_REQUEST['confirmMemPasswd'], $passwdHashAlgo, $beginingSalt, $endingSalt);
    } else {$isError = true;}

    # Sanitizes and creates variable for the firstName.
    if (sanitizeAlphaString($_REQUEST['memFname']) !== false) {
        $userInput['fName'] = sanitizeAlphaString($_REQUEST['memFname']);
    } else {$isError = true;}

    # Sanitizes and creates variable for the lName.
    if (sanitizeAlphaString($_REQUEST['memLname']) !== false) {
        $userInput['lName'] = sanitizeAlphaString($_REQUEST['memLname']);
    } else {$isError = true;}

    # Adds the member's DOB to the userInput array (doesn't need to be sanitized).
    $userInput['dob'] = $_REQUEST['memDob'];

    ######## Address Entry ########

    # Sanitizes and creates variable for the addressBuildNum.
    if (sanitizeNumString($_REQUEST['buildNum']) !== false) {
        $userInput['buildNum'] = sanitizeNumString($_REQUEST['buildNum']);
    } else {$isError = true;}

    # Sanitizes and creates variable for the addrStrName.
    if (sanitizeAlphaString($_REQUEST['strName']) !== false) {
        $userInput['strName'] = sanitizeAlphaString($_REQUEST['strName']);
    } else {$isError = true;}

    # Sanitizes and creates variable for the addrCity.
    if (sanitizeAlphaString($_REQUEST['city']) !== false) {
        $userInput['city'] = sanitizeAlphaString($_REQUEST['city']);
    } else {$isError = true;}

    # Sanitizes and creates variable for the addrZip.
    if (sanitizeNumString($_REQUEST['zip']) !== false) {
        $userInput['zip'] = sanitizeNumString($_REQUEST['zip']);
    } else {$isError = true;}

    # Sanitizes and creates variable for the Provence/State.
    if (sanitizeAlphaString($_REQUEST['provence']) !== false) {
        $userInput['provence'] = sanitizeAlphaString($_REQUEST['provence']);
    } else {$isError = true;}

    # Sets the country of the user. (currently set to ONLY the US!)
    $userInput['country'] = "United States";

    # Sanitizes and creates variable for the optional apartment number.
    if (sanitizeNumString($_REQUEST['aptNum']) !== false) {
        $userInput['aptNum'] = sanitizeNumString($_REQUEST['aptNum']);
    }else {$isError = true;}


    # Checks if there where any when parsing though the user input.
    if ($isError === true){
        echo "<script> alert(\"Incomplete or Incorrect information specified Please Try again.\"); </script>";
    } else {
        ###### Tries to connect to the MySQL database using PDO (rather then MySQLi) #####
        try {
            $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
            # Set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            # Creates prepared SQL statement for the member.
            $memStmt = $conn->prepare("INSERT INTO Member (memEmail, memPasswd, memFname, memLname, memDob) 
              VALUES (:memEmail, :memPasswd, :memFname, :memLname, :memDob)");
            $memStmt->bindParam(':memEmail', $userInput['email'],PDO::PARAM_STR, 254);
            $memStmt->bindParam(':memPasswd', $userInput['password'], PDO::PARAM_STR, 64);
            $memStmt->bindParam(':memFname', $userInput['fName'], PDO::PARAM_STR, 64);
            $memStmt->bindParam(':memLname', $userInput['lName'],PDO::PARAM_STR, 64);
            $memStmt->bindParam(':memDob', $userInput['dob']);

            #TODO: Make the Address connect to the member's account.
            # Creates prepared SQL statement for the Member's address.
            $addrStmt = $conn->prepare("INSERT INTO Address (addressBuildNum, addressStreetName, addressCity, addressZip, addressProvence, addressCountry, addressAptNum) 
              VALUES (:addressBuildNum, :addressStreetName, :addressCity, :addressZip, :addressProvence, :addressCountry, :addressAptNum)");
            $addrStmt->bindParam(':addressBuildNum', $userInput['buildNum'], PDO::PARAM_INT,8);
            $addrStmt->bindParam(':addressStreetName', $userInput['strName'], PDO::PARAM_STR, 64);
            $addrStmt->bindParam(':addressCity', $userInput['city'],PDO::PARAM_STR, 64);
            $addrStmt->bindParam(':addressZip', $userInput['zip'], PDO::PARAM_INT, 7);
            $addrStmt->bindParam(':addressProvence', $userInput['provence'], PDO::PARAM_STR, 32);
            $addrStmt->bindParam(':addressCountry', $userInput['country'], PDO::PARAM_STR, 64);
            $addrStmt->bindParam(':addressAptNum', $userInput['aptNum'], PDO::PARAM_INT, 7);

            # Begins transaction and makes the two entries to the database and commits them.
            $conn->beginTransaction();
            $memStmt->execute();
            $addrStmt->execute();

            # Link the member to their entered address. (Done here because the lastInsertId is used).
            $userInput['addressID'] = $conn->lastInsertId();
            $linkStmt = $conn->prepare("UPDATE Member SET `addressID`=:addressID WHERE `memEmail`=:memEmail");
            $linkStmt->bindParam(':addressID',$userInput['addressID']);
            $linkStmt->bindParam(':memEmail', $userInput['email'],PDO::PARAM_STR, 254);
            # Executes the above link statement.
            $linkStmt->execute();
            # Commits the changes to the database.
            $conn->commit();

            # Closes the database connection.
            $conn = null;

            # Sends a JavaScript alert message back to the user notifying them of successful account creation.
            echo "<script> alert(\"Account created successfully!\"); </script>";



        } catch (PDOException $e) {
            # Rollback any changes to the database (if possible/required).
            $conn->rollBack();

            #DEBUG:
            echo "<br>".$e->getMessage().'\n<br>';
            echo $e->errorInfo().'\n<br>';

            # Sends a JavaScript alert message back to the user notifying them that there was an error processing their request.
            echo "<script> alert(\"Sorry an error occurred, Please try again. If problem persists, please notify TCI at 651-000-0000.\"); </script>";
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
        <li><a href="index.html">Home</a> </li>
        <li><a href="aboutUs.html" >About</a> </li>
        <li><a href="whyTci.html">Why TCI?</a> </li>
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
        Passwords must be at LEAST 8 characters long, and must contain one: <b>capital letter, lowercase letter, number, and special character</b>.<br>
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
    * <input type="text" name="memFname" placeholder="First Name" maxlength="64" pattern="[A-Za-z\-\h]{2,64}" required><br>

    <br>
    Enter your last name. <br>
    * <input type="text" name="memLname" maxlength="64" placeholder="Last Name" pattern="[A-Za-z\-\h]{2-64}" required><br>

    <br>
    Enter your date of birth (DOB). <br>
    * <input type="date" min="1900-01-01" name="memDob" placeholder="Date Of Birth" required><br>








    <!-- Mailing/Billing Information -->
    <br>
    <br>
    <h2><font size="5">Mailing & Billing Information</font></h2>

    <br>
    Enter your preferred house or building number. <br>
    * <input type="number" name="buildNum" min="1" maxlength="8" placeholder="Building/House Number" pattern="[0-9]{8}" required><br>

    <br>
    Enter your street address. <br>
    * <input type="text" name="strName" maxlength="64" placeholder="Street Address" pattern="[A-Za-z\.\-\h]{2,64}" required><br>

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
        </select><br>

    <br>
    Enter your apartment number, if needed (optional). <br>
    * <input type="number" name="aptNum" min="1" maxlength="7" placeholder="Apartment Number" pattern="[a-zA-Z0-9\-\h]{7}"> <br>
    <br>
    <br>
    <input type="reset"/>  <input type="submit" value="Sign Up!"/>
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
