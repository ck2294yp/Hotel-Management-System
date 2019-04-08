<?php
# This file defines the settings that TCI website should use in order to connect to the database server.


# MySQL Database connection settings.
$dbAddress = "aetacraft.tk";
$dbPort = "3306";
$dbUsername = "root";
$dbPassword = "JspP#2HaxUd^FDTQgAsr";
$dbLocation = "tci";


# Password complexity requirements (entered as a Regex expression).
$passwordComplexityRequirements = '/(?=.{8,256})(?=.*?[^\w\s])(?=.*?[0-9])(?=.*?[A-Z]).*?[a-z].*/';

# Password storage settings.
$passwdHashAlgo = "sha256";
$beginingSalt= "!4~G=Q";
$endingSalt = "%4d^36E*";