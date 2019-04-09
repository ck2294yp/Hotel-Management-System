<?php

# Sanitizes email addresses.
function sanitizeEmail($input) {
    if (isset($input) && $input !== "" && filter_var($input, FILTER_SANITIZE_EMAIL)) {
        $input = stripslashes($input);
        $input = htmlspecialchars($input);
        $input = ucfirst($input);
        $input = trim($input);
        return $input;
    } else {
        echo "<script> alert(\"Invalid username/email address specified, please try again.\"); </script>";
        return false;
    }
}

# Sanitizes passwords and checks to make sure they are the same and are protected properly (salted & hashed).
function sanitizePassword($password, $passwordConfirm, $passwdHashAlgo, $beginingSalt, $endingSalt) {
    if ($password !== $passwordConfirm){
        echo "<script> alert(\"Passwords do not match, please try again.\"); </script>";
        return false;
    } elseif ($password !== "" && filter_var($password, FILTER_SANITIZE_STRING)) {
        $password = hash($passwdHashAlgo,$beginingSalt.$password.$endingSalt);
        $password = stripslashes($password);
        $password = htmlspecialchars($password);
        return $password;
    } else {
        echo "<script> alert(\"Invalid password specified, please try again.\"); </script>";
        return false;
    }
}

# Sanitizes Alpha Strings (strings that should ONLY alphabetical characters).
function sanitizeAlphaString($input) {
    if (isset($input) && $input !== "" && preg_match('/[A-Za-z]/', $input)) {
        $input = stripslashes($input);
        $input = htmlspecialchars($input);
        $input = ucfirst($input);
        $input = trim($input);
        return $input;
    } else {
        echo "<script> alert(\"Invalid value \".$input.\"specified, please try again.\"); </script>";
        return false;
    }
}

# Sanitizes numerical Strings (strings that should ONLY be numerical characters).
function sanitizeNumString($input) {
    if (isset($input) && preg_match('/[0-9]/', $input)) {
        $input = stripslashes($input);
        $input = htmlspecialchars($input);
        $input = trim($input);
        return $input;
    } else {
        echo "<script> alert(\"Invalid numerical value \".$input.\"specified, please try again.\"); </script>";
        return false;
    }
}





