<?php

# Sanitizes email addresses.
function sanitizeEmail($input) {
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    $input = trim($input);
    $input = ucfirst($input);
    $input = preg_replace('/[^A-Za-z0-9\-\@\.]/', '', $input);
    return $input;
}

# Sanitizes passwords and checks to make sure they are the same and are protected properly (salted & hashed).
function sanitizePassword($password, $passwordConfirm, $passwdHashAlgo, $beginingSalt, $endingSalt) {
    if ($password !== $passwordConfirm) {
        return false;
    } else {
        $password = hash($passwdHashAlgo, $beginingSalt . $password . $endingSalt);
        return $password;
    }
}

# Sanitizes Alpha Strings (strings that should ONLY alphabetical characters).
function sanitizeAlphaString($input) {
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    $input = trim($input);
    $input = ucwords($input);
    $input = preg_replace('/[^A-Za-z-\.\- ]/', '', $input);
    return $input;
}

# Sanitizes numerical Strings (strings that should ONLY be numerical characters).
function sanitizeNumString($input) {
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    $input = trim($input);
    $input = preg_replace('/[^0-9-\.\- ]/', '', $input);
    return $input;
}


# Sanitizes both Alpha and Numeric Strings.
function sanitizeAlphaNumString($input) {
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    $input = ucwords($input);
    $input = trim($input);
    $input = preg_replace('/[^A-Za-z0-9-\.\-]/', '', $input);
    return $input;
}


# Sanitizes date values.
function sanitizeDateString($dateString) {
    $dateString = htmlspecialchars($dateString);
    $dateString = trim($dateString);
    return $dateString;
}

