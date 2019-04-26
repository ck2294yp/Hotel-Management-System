<?php

# Imports the required files needed to ensure program page works properly.
require_once "settings/settings.php";
require_once "bin/inputSanitization.php";
require_once 'vendor/autoload.php';


function accountActivate($userEmail)
{
    # Imports the required files needed to ensure program page works properly.
    $dependecy1 = "settings/settings.php";
    $dependecy2 = "bin/inputSanitization.php";
    $dependecy3 = "vendor/autoload.php";
    if (is_file($dependecy1) && is_file($dependecy2) && is_file($dependecy3)) {
        ob_start();
        include $dependecy1;
        #include $dependecy2;
        include $dependecy3;
    }

    // If user's Email address is not valid or administrators don't want emails to be sent return false.
    if (sanitizeEmail($userEmail) === false || $sendEmails === false) {
        return false;
    }

    # Connects to the SQL database.
    try {
        $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
        # Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        # Queries the database to get the username and the password of the user.
        $userInfoStmt = $conn->prepare('SELECT memEmail, memFname, memLname, memActivationLink FROM `Member` WHERE `memEmail`=:email');
        $userInfoStmt->bindParam(':email', $userEmail, PDO::PARAM_STR, 254);

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
        # Rollback any changes to the database and closes the connection (if possible).
        @$conn->rollBack();
        @$conn = null;

        # If database connection fails for whatever reason, return false.
        return false;
    }


    // Sets the body of the email
    $body = '
        <center> Welcome to TCI, ' . $memInfo['memFname'] . ' ' . $memInfo['memLname'] . '!
        <p> Click <a href="http://localhost:8080/activate.php?user=' . $memInfo['memEmail'] . '&activationId=' . $memInfo['memActivationLink'] . '">here</a> to activate your account.
        <p>
        <br>
        <br>
        <br>
        Although you probably won\'t need it your activation code is: <b>' . $memInfo['memActivationLink'] . '</b>.
        ';


    // Create the Transport
    $transport = (new Swift_SmtpTransport($emailSvrAddress, $emailSvrSMTPPort, 'ssl'))
        ->setUsername($adminEmailAddress)
        ->setPassword($adminEmailPassword);

    // Create the Mailer using your created Transport
    $mailer = new Swift_Mailer($transport);

    // Create a message
    $message = (new Swift_Message("Welcome to TCI, " . $memInfo['memFname'] . "!"))
        ->setFrom([$adminEmailAddress => 'TCI Account Manager'])
        ->setTo([$memInfo['memEmail'] => $memInfo['memFname'] . " " . $memInfo['memLname']])
        ->setBody($body, 'text/html');

    // Attempts to email the user and returns true if email was sent successfully. false if a failure occored.
    return $mailer->send($message);

}





