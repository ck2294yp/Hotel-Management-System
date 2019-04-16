<?php

# Imports the required files needed to ensure program page works properly.
require_once "settings/settings.php";
require_once "bin/inputSanitization.php";
require_once 'vendor/autoload.php';


$userInput['email'] = "jaherzog574@gmail.com";
$userInput['fName'] = "James";
$userInput['lName'] = "Herzog";
$userInput['activationLink'] = "test1234567";
$body='
        <center>Your Activation Code is <b>'.$userInput['activationLink'].'</b>
        <p> Please click <a href="http://localhost:8081/activate.php?user='.$userInput['email'].'&activationId='.$userInput['activationLink'].'">here</a> to activate your account.
        ';


// Create the Transport
$transport = (new Swift_SmtpTransport($emailSvrAddress, $emailSvrSMTPPort, 'ssl'))
    ->setUsername($adminEmailAddress)
    ->setPassword($adminEmailPassword);

// Create the Mailer using your created Transport
$mailer = new Swift_Mailer($transport);

// Create a message
$message = (new Swift_Message("Welcome to TCI, ".$userInput['fName']."!"))
    ->setFrom([$adminEmailAddress => 'TCI Account Manager'])
    ->setTo([$userInput['email'] => $userInput['fName'] . " " . $userInput['lName']])
    ->setBody($body, 'text/html');

// Send the message
$result = $mailer->send($message);


echo $result;