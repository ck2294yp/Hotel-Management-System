<?php

// Loads in the files that these functions need.
require_once "settings/settings.php";
require_once "bin/inputSanitization.php";

function getUserData($userEmail) {
    include "settings/settings.php";

    # Connects to the SQL database.
    try {
        $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
        # Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        # Queries the database to get all of the details of the member.
        $userInfoStmt = $conn->prepare('select * from `Member` where `memEmail`=sanitizeEmail($userEmail)');

        # Begins a transaction, if there are any changes (which there shouldn't be) rollback the changes.
        $conn->beginTransaction();
        $userInfoStmt->execute();
        $conn->rollBack();

        # Closes the database connection.
        $conn = null;


        # Gets the member's account details from out of the database query.
        $userInfoStmt->setFetchMode(PDO::FETCH_ASSOC);
        return $userInfoStmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
//        # Rollback any changes to the database (if possible).
//        $conn->rollBack();
//
//        # Sends a JavaScript alert message back to the user notifying them that there was an error processing their request.
//        echo "<script> alert(\"We are sorry, there seems to be a problem with our systems. Please try again. If problems still persist, please notify TCI at 651-000-0000.\"); </script>";
//        return false;
    }

}

// Function to grab the emailTemplate file.
function getTemplate($filename, $memInfo ) {
    if (is_file($filename)) {
        ob_start();
        include $filename;
        return ob_get_clean();
    } else {
        return false;
    }
}


// Sends activation email.
function sendActivationEmail($adminEmailAddress, $sendTo) {
    # Include the Autoloader (see "Libraries" for install instructions)
    require 'vendor/autoload.php';
    ##use Mailgun\Mailgun;
    # Instantiate the client.
    $mgClient = new Mailgun('YOUR_API_KEY');
    $domain = "YOUR_DOMAIN_NAME";
    # Make the call to the client.
    $result = $mgClient->sendMessage($domain, array(
        'from'	=> 'Excited User <mailgun@YOUR_DOMAIN_NAME>',
        'to'	=> 'Baz <YOU@YOUR_DOMAIN_NAME>',
        'subject' => 'Hello',
        'text'	=> 'Testing some Mailgun awesomness!'
    ));

    // Sends the email out to the customer.
    mail("$sendTo", "$subject", "$messageBody", "From:" . $adminEmailAddress);

}





