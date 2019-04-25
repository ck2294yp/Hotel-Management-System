<?php
session_start();

require_once "settings/settings.php";
require_once "bin/inputSanitization.php";

# Connects to the SQL database.
$conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);

# Set the PDO error mode to exception
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = $conn->prepare("DELETE from InvoiceReservation where invoiceID='".$_GET['del_id']."'");
$query->execute();

header ("Location: bookingHistory.php");
