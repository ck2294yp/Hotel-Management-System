<?php
# This file defines the settings for the TCI website.


# MySQL Database connection settings.
$dbAddress = "aetacraft.tk";
$dbPort = "3306";
$dbUsername = "root";
$dbPassword = "JspP#2HaxUd^FDTQgAsr";
$dbLocation = "tci";


# Password complexity requirements (entered as a Regex expression).
$passwordComplexityRequirements = '(?=.{8,256})(?=.*?[^\w\s])(?=.*?[0-9])(?=.*?[A-Z]).*?[a-z].*';

# Password storage settings.
$passwdHashAlgo = "sha256";
$beginingSalt= "i9_VE~xd!6G%:m%zDJ-MuWk)T&5={8<#>3p$}g.h";
$endingSalt = "z)^|u|lJPJ!(NwgE{bb#>fp>r@7HAFH#,#MWl0(f";


# Administrative Email Account Information (used to send emails OUT to customers).
$sendEmails = true;
$adminEmailAddress = "TCIHotelsMN@gmail.com";
$adminEmailPassword = "#Sny)-?Ni*:SitT8LTFjE{xm$#";
$emailSvrAddress = "smtp.gmail.com";
$emailSvrSMTPPort = "465";


