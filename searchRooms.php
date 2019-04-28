<?php
session_start();
require_once "settings/settings.php";
require_once "bin/inputSanitization.php";

// Stops if no session exists.
if (array_key_exists('loggedIn', $_SESSION) === false) {
    echo "<script> alert(\"Your session has timed out, please sign in again.\"); </script>";
    header('Location: signIn.php');
    exit;
}

// Checks if session (or POST request) contains checkindate/checkoutdate.
if ((array_key_exists('checkInDate', $_SESSION) === false) || (array_key_exists('checkOutDate', $_SESSION) === false)) {
    echo "<script> alert(\"Check in or check out dates not found, please try again.\"); </script>";
    header('Location: booking.php');
    exit;
}

// Create array to hold the client's information.
$roomInfo = array();
// Sanitize everything.
$roomInfo['username'] = @sanitizeEmail($_SESSION['username']);
$roomInfo['loggedIn'] = @sanitizeNumString($_SESSION['loggedIn']);
$roomInfo['checkInDate'] = @sanitizeDateString($_SESSION['checkInDate']);
$roomInfo['checkOutDate'] = @sanitizeDateString($_SESSION['checkOutDate']);


# Connects to the SQL database.
try {
    $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
    # Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    # Queries the database to get the number of .
    $bookedRoomsStmt = $conn->prepare('SELECT roomTypeID, COUNT(*) FROM `InvoiceReservation` WHERE (DATE(`invoiceStartDate`) BETWEEN DATE(:startDate) AND DATE(:endDate)) OR (DATE(invoiceEndDate) BETWEEN DATE(:startDate) AND DATE(:endDate)) GROUP BY InvoiceReservation.roomTypeID;');
    $bookedRoomsStmt->bindParam(':startDate', $roomInfo['checkInDate']);
    $bookedRoomsStmt->bindParam(':endDate', $roomInfo['checkOutDate']);
    $allRoomInfoStmt = $conn->prepare('SELECT * FROM `RoomType`;');

    # Begins a transaction, if there are any changes (which there shouldn't be) rollback the changes.
    $conn->beginTransaction();
    $bookedRoomsStmt->execute();
    $allRoomInfoStmt->execute();
    $conn->rollBack();

    # Closes the database connection.
    $conn = null;

    # Gets the member's account details from out of the database query.
    $bookedRoomsStmt->setFetchMode(PDO::FETCH_ASSOC);
    $allRoomInfoStmt->setFetchMode(PDO::FETCH_ASSOC);
    $bookedRooms = $bookedRoomsStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $roomInformation = $allRoomInfoStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    # Rollback any changes to the database (if possible).
    @$conn->rollBack();

    # Sends a JavaScript alert message back to the user notifying them that there was an error processing their request.
    echo "<script> alert(\"We are sorry, there seems to be a problem with our systems. Please try again. If problems still persist, please notify TCI at 651-000-0000.\"); </script>";
    header('Location: membersPage.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Rooms</title>
    <link rel="stylesheet" href="style.css" type="text/css"/>
    <style>
        .header {
            text-align: center;
            overflow: hidden;

        }

        .header li {
            display: inline-block;
            padding-bottom: 0px;
            padding-top: 0px;
        }

        .image {
            background-image: url("Lobby.jpg");
            background-repeat: no-repeat;
            height: 550px;
            background-size: cover;
        }

        .features {

            overflow: hidden;
        }

        .features ul {
            display: inline-block;
            float: left;
            padding-bottom: 5px;

        }

        .sr {
            overflow: hidden;
        }

        .sr li {
            display: inline-block;
            padding-bottom: 5px;
        }

        .sr2 {
        }

        .sr2 li {
            display: inline-block;

        }

        .feat {

        }

        .contact {
            text-align: center;
            height: 100px;
            padding-bottom: 5px;
        }

        .footer {

            overflow: auto;
        }

        .footer ul {
            padding-bottom: 5px;
            list-style-type: none;
        }

        .footer li {
            display: inline-block;
            font-weight: bold;
        }

        body {
            background-color: white;

        }

        .bord {

            overflow: auto;
        }

        .roomBox {
            display: flex;
            height: auto;

        }

        .pic {
            height: inherit;
            width: 100%;
        }

        .bButton {
            padding: 20px;
        }

    </style>
</head>

<body>
<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1 class="headOne"> Twin Cities Inn</h1>
</header>

<!--Navigation Bar-->
<?php include 'bin/nav.php'; ?>

<!--search rooms section start-->
<div style="width: 100%;padding-left: 4px;">
    <div style="float: left; height: auto; width: 20%;">
        <h2 style="text-align: left">Filter by:</h2>
        <button id="oneBedRoom" onclick="oneBedRoom()" style="width: 60%">One Bed</button>
        <br><br>
        <button onclick="twoBedRoom()" style="width: 60%">Two Bed</button>
        <br><br>
        <button onclick="specialtyRoom()" style="width: 60%">Specialty Room</button>
        <br><br>
        <button onclick="allRooms()" style="width: 60%">Show All</button>
    </div>
    <div style="float: right; height: 700px ;width:80%;">
        <h2 style="text-align: left;">Currently Available Rooms: </h2>

        <?php
        foreach ($roomInformation as $currentRoom) {
            // If the current room exists in the booked rooms array AND the current room type is either at (or somehow over) it's maximum allowed amount. DO NOT PRINT THE ROOM DETAILS!
            if ((array_key_exists($currentRoom['roomTypeID'], $bookedRooms) === true) && ($currentRoom['numOfRooms'] >= $bookedRooms[$currentRoom['roomTypeID']])) {

            } else { ?>
                <div class="gridMember roomBox" id="<?php echo($currentRoom['roomNumBeds'] . 'bed' . $currentRoom['roomCatagory']); ?>"   style="display: inline-block;">
                    <!-- Room details -->
                    <div style="float:left; text-align:left; padding-left:5%; ">
                        <br> <h2> <?php echo($currentRoom['roomNumBeds'] . "-bed " . ucfirst($currentRoom['roomCatagory']) . " Room"); ?> </h2>
                        <br> <b>Room Type: </b> <?php echo(ucfirst($currentRoom['roomCatagory'])); ?>
                        <br> <b>Number of Beds: </b> <?php echo($currentRoom['roomNumBeds']); ?>
                        <br> <b>Pets Allowed: </b> <?php
                            if ($currentRoom['roomAllowsPets'] === 0) {
                                echo("No");
                            } else {
                                echo("Yes");
                            }; ?>
                        <br> <b>Rooms Left: </b> <?php
                            if (array_key_exists($currentRoom['roomTypeID'], $bookedRooms) === true) {
                                echo($currentRoom['numOfRooms'] - $bookedRooms[$currentRoom['roomTypeID']]);
                            } else {
                                echo($currentRoom['numOfRooms']);
                            }
                            ?>
                        <br> <b>Rate: </b>$<?php echo(number_format($currentRoom['pricePerNight'], 2))."/day"; ?>
                    </div>


                    <!-- The great "Book It!" button. -->
                    <div style="float: right;text-align: center;padding-right: 5%;">
                        <button id="bookRoomButton" onclick="confirmRoomType(<?php echo($currentRoom['roomTypeID']); ?>) " class="button"> Book It! </button>
                    </div>
                </div>
                <?php
            }
        }
        ?>

        <div class="overlay" id="dialog-container">
            <div class="popup">
                <p>Are you sure you want to book reservation?</p>
                <div class="text-right">
                    <button class="dialog-btn btn-cancel" id="cancel">Cancel</button>
                    <button class="dialog-btn btn-primary" id="confirm">Yes</button>
                </div>
            </div>
        </div>

    </div>

    <script
            src="https://code.jquery.com/jquery-3.4.0.min.js"
            integrity="sha256-BJeo0qm959uMBGb65z40ejJYGSgR7REI4+CW1fNKwOg="
            crossorigin="anonymous"></script>

    <script>
        $(document).ready(function () {
            $('#bookRoomButton').on('click', function () {
                $('#dialog-container').show();
            });
            $('#cancel').on('click', function () {
                $('#dialog-container').hide();
            });
            $('#confirm').on('click', function () {
                $('#dialog-container1').show();
                $('#dialog-container').hide();
            });
            $('#stop').on('click', function () {
                $('#dialog-container1').hide();
            });

        });
        //
    </script>

    <!--search rooms section end-->
    <!--<section class="sec3" style="overflow: hidden;"></section>-->
    <!--<footer class="foot">
        <nav>
            <ul>
                <li><a href="#">Facebook</a> </li>
                <li><a href="#">Twitter</a> </li>
                <li><a href="#">Google+</a> </li>
                <li><a href="#">© 2019 Twin Cities Inn</a> </li>
            </ul>
        </nav>
    </footer>-->

    <script>
        var a = document.getElementById("1bednormal");
        var b = document.getElementById("2bednormal");
        var c = document.getElementById("1bedchef");
        var d = document.getElementById("2bedchef");
        var e = document.getElementById("1bedpet");
        var f = document.getElementById("2bedpet");
        var g = document.getElementById("2bedfamily");
        var h = document.getElementById("3bedfamily");
        var i = document.getElementById("1bedgaming");
        var j = document.getElementById("2bedgaming");
        var k = document.getElementById("3bedfamily");



        function oneBedRoom() {

            if (a.style.display === "inline-block"
                || c.style.display === "inline-block"
                || e.style.display === "inline-block"
                || i.style.display === "inline-block") {

                a.style.display = "inline-block";
                c.style.display = "inline-block";
                e.style.display = "inline-block";
                i.style.display = "inline-block";


                b.style.display = "none";
                d.style.display = "none";
                f.style.display = "none";
                g.style.display = "none";
                h.style.display = "none";
                j.style.display = "none";
                k.style.display = "none";

            }

            if (a.style.display === "none"
                || c.style.display === "none"
                || e.style.display === "none"
                || i.style.display === "none") {

                a.style.display = "inline-block";
                c.style.display = "inline-block";
                e.style.display = "inline-block";
                i.style.display = "inline-block";


                b.style.display = "none";
                d.style.display = "none";
                f.style.display = "none";
                g.style.display = "none";
                h.style.display = "none";
                j.style.display = "none";
                k.style.display = "none";
            }
        }

        function twoBedRoom(){

            if (b.style.display === "inline-block"
                || d.style.display === "inline-block"
                || f.style.display === "inline-block"
                || g.style.display === "inline-block" || h.style.display === "inline-block" || j.style.display === "inline-block") {

                b.style.display = "inline-block";
                d.style.display = "inline-block";
                f.style.display = "inline-block";
                g.style.display = "inline-block";
                h.style.display = "inline-block";
                j.style.display = "inline-block";

                a.style.display = "none";
                c.style.display = "none";
                e.style.display = "none";
                i.style.display = "none";
                h.style.display = "none";


            }

            if (b.style.display === "none"
                || d.style.display === "none"
                || f.style.display === "none"
                || g.style.display === "none" || h.style.display === "none" || j.style.display === "none") {

                b.style.display = "inline-block";
                d.style.display = "inline-block";
                f.style.display = "inline-block";
                g.style.display = "inline-block";
                h.style.display = "inline-block";
                j.style.display = "inline-block";

                a.style.display = "none";
                c.style.display = "none";
                e.style.display = "none";
                i.style.display = "none";
                h.style.display = "none";
            }
        }

        // b,d,f,g,h,j
        function oldtwoBedRoom() {

            if (b.style.display === "inline-block"
                && d.style.display === "inline-block"
                && f.style.display === "inline-block"
                && g.style.display === "inline-block"
                && h.style.display === "inline-block"
                && j.style.display === "inline-block") {

                b.style.display = "inline-block";
                d.style.display = "inline-block";
                f.style.display = "inline-block";
                g.style.display = "inline-block";
                h.style.display = "inline-block";
                j.style.display = "inline-block";

                a.style.display = "none";
                c.style.display = "none";
                e.style.display = "none";
                i.style.display = "none";
                h.style.display = "none";
            }

            if (b.style.display === "none"
                && d.style.display === "none"
                && f.style.display === "none"
                && g.style.display === "none"
                && h.style.display === "none"
                && j.style.display === "none") {

                b.style.display = "inline-block";
                d.style.display = "inline-block";
                f.style.display = "inline-block";
                g.style.display = "inline-block";
                h.style.display = "inline-block";
                j.style.display = "inline-block";

                b.style.display = "none";
                d.style.display = "none";
                f.style.display = "none";
                g.style.display = "none";
                h.style.display = "none";
                j.style.display = "none";
            }
        }


        //c,d,e,f,g,h,i,j
        function specialtyRoom() {

            if (c.style.display === "inline-block"
                || d.style.display === "inline-block"
                || e.style.display === "inline-block"
                || f.style.display === "inline-block"
                || g.style.display === "inline-block"
                || h.style.display === "inline-block"
                || i.style.display === "inline-block"
                || j.style.display === "inline-block") {

                c.style.display = "inline-block";
                d.style.display = "inline-block";
                e.style.display = "inline-block";
                f.style.display = "inline-block";
                g.style.display = "inline-block";
                h.style.display = "inline-block";
                i.style.display = "inline-block";
                j.style.display = "inline-block";

                /*c.style.display = "none";
                d.style.display = "none";
                e.style.display = "none";
                f.style.display = "none";
                g.style.display = "none";
                h.style.display = "none";
                i.style.display = "none";
                j.style.display = "none";*/
                a.style.display = "none";
                b.style.display = "none";
            }

            if (c.style.display === "none"
                || d.style.display === "none"
                || e.style.display === "none"
                || f.style.display === "none"
                || g.style.display === "none"
                || h.style.display === "none"
                || i.style.display === "none"
                || j.style.display === "none") {

                c.style.display = "inline-block";
                d.style.display = "inline-block";
                e.style.display = "inline-block";
                f.style.display = "inline-block";
                g.style.display = "inline-block";
                h.style.display = "inline-block";
                i.style.display = "inline-block";
                j.style.display = "inline-block";

                c.style.display = "none";
                d.style.display = "none";
                e.style.display = "none";
                f.style.display = "none";
                g.style.display = "none";
                h.style.display = "none";
                i.style.display = "none";
                j.style.display = "none";
            }
        }

        // a,b,c,d,e,f,g,h,i,j
        function allRooms() {
            if (a.style.display === "inline-block"
                || b.style.display === "inline-block"
                || c.style.display === "inline-block"
                || d.style.display === "inline-block"
                || e.style.display === "inline-block"
                || f.style.display === "inline-block"
                || g.style.display === "inline-block"
                || h.style.display === "inline-block"
                || i.style.display === "inline-block"
                || j.style.display === "inline-block") {

                a.style.display = "inline-block";
                b.style.display = "inline-block";
                c.style.display = "inline-block";
                d.style.display = "inline-block";
                e.style.display = "inline-block";
                f.style.display = "inline-block";
                g.style.display = "inline-block";
                h.style.display = "inline-block";
                i.style.display = "inline-block";
                j.style.display = "inline-block";

            }
        }

        // Find out what room the user wants and selects that as their desired room.
        function confirmRoomType(roomTypeID) {
            var popup = confirm("Are you sure you want to book this room?");
            if (popup == true) {
                window.location = "http://localhost:8080/billingPage.php?roomTypeID=" + roomTypeID;
                // If user clicks "Cancel" then don't do anything (except close the prompt).
            } else {

            }
        }

    </script>


</body>
</html>