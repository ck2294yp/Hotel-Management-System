<?php

require_once "bin/sendEmail.php";

$userEmail = "Jaherzog574@gmail.com";

if (accountActivate($userEmail)){
    echo "Email sent successfully!";
}










