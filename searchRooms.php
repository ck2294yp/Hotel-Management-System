<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Rooms</title>
    <link rel="stylesheet" href="style.css" type="text/css" />
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

        .sr li{
            display: inline-block;
            padding-bottom: 5px;
        }

        .sr2 {
        }

        .sr2 li{
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
            background-color: #DBDBDB;

        }

        .bord {

            overflow: auto;
        }

        .roomBox {
            display: flex;
            height: auto;

        }
        .pic {
            height:inherit;
            width: 100%;
        }
        .bButton {
            padding: 20px;
        }

    </style>
</head>

<?php
session_start();
require_once "settings/settings.php";
require_once "bin/inputSanitization.php";
// Stops if no session exists.
if (in_array('username', $_SESSION) === false || in_array('loggedIn', $_SESSION) === false) {
    echo "<script> alert(\"Your session has timed out, please sign in again.\"); </script>";
    header('Location: signIn.php');
    exit;
}

$memInfo['username'] = @sanitizeEmail($_SESSION['username']);
$memInfo['loggedIn'] = @sanitizeNumString($_SESSION['loggedIn']);

# Create array to hold the client's information.
$memInfo = array();
# Sanitize session data.
    @$memInfo['username'] = sanitizeEmail($_SESSION['username']);


# Connects to the SQL database.
    try {
    $conn = new PDO("mysql:host=$dbAddress;dbname=$dbLocation", $dbUsername, $dbPassword);
    # Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    # Queries the database to get the username and the password of the user.
        $userInfoStmt = $conn->prepare('SELECT * FROM `RoomType`');
    # Begins a transaction, if there are any changes (which there shouldn't be) rollback the changes.
    $conn->beginTransaction();
    $userInfoStmt->execute();
    $conn->rollBack();

    # Closes the database connection.
        $conn = null;


    # Gets the member's account details from out of the database query.
    $userInfoStmt->setFetchMode(PDO::FETCH_ASSOC);
    $memInfo = $userInfoStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    # Rollback any changes to the database (if possible).
    @$conn->rollBack();

    # Sends a JavaScript alert message back to the user notifying them that there was an error processing their request.
        echo "<script> alert(\"We are sorry, there seems to be a problem with our systems. Please try again. If problems still persist, please notify TCI at 651-000-0000.\"); </script>";
        header('Location: membersPage.php');
}




?>


<body>
<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1 class="headOne"> Twin Cities Inn</h1>
</header>
<nav>
    <ul>
        <li><a href="index.html">Home</a> </li>
        <li><a href="AboutUs.html" >About</a> </li>
        <li><a href="#">Why TCI</a> </li>
        <li><a href="#">Sign In</a> </li>
    </ul>
</nav>
<!--commnet-->
<!--search rooms section start-->
<div style="width: 100%;padding-left: 4px;">
<div style="float: left; height: auto; width: 20%;">
    <h2 style="text-align: left">Filter by:</h2>
    <button onclick="oneBedRoom()">One Bed</button><br><br>
    <button onclick="twoBedRoom()" >Two Bed</button><br><br>
    <button onclick="specialtyRoom()">Specialty Room</button><br><br>
    <button onclick="allRooms()">All</button>
</div>
<div style="float: right; height: 700px ;width:80%;">
    <h2 style="text-align: left;">Available Rooms</h2>
   <section id="twoBed" class="roomBox" style="display: inline-block;">
    <div style="border:solid 1px black;">

        <div style="width: 30%;height:inherit;float: left;"><img class="pic" src="https://upload.wikimedia.org/wikipedia/commons/5/56/Hotel-room-renaissance-columbus-ohio.jpg"
                                                                 alt="Two bed hotel picture"></div>
        <div style="float: left;text-align: center;padding-left: 100px;"><h2>Two Bed Room</h2><p>Two bed room hotel room with <br>comfortable beds and clean bathroom.<br>
            Has a desk to do work on. <br>Also has a 40 inch TV with cable.</p></div>
        <div style="float: left;text-align: center;padding-left: 100px;"><h2>Price</h2><p>$<?php  echo($memInfo[0]['pricePerNight']);?></p></div>
        <div style="float: left;text-align: center;padding-left: 70px;"><br><br><br><br><button id="twoBedButton"  class="bButton">Book</button></div>
    </div>
</section>

    <section id="twoBedPet" class="roomBox" style="display: inline-block;">
        <div style="border:solid 1px black;">

            <div style="width: 30%;height:inherit;float: left;"><img class="pic" src="http://petquarter.com/wp-content/themes/thesis_186/custom/rotator/Pet-friendly-best-western-hotel.jpg"
                                                                     alt="Two bed hotel picture"></div>
            <div style="float: left;text-align: center;padding-left: 100px;"><h2>Two Bed Pet Room</h2><p>Two bed room hotel room with <br>comfortable beds and clean bathroom.<br>
                Has a desk to do work on. <br>Also has a 40 inch TV with cable.</p></div>
            <div style="float: left;text-align: center;padding-left: 100px;"><h2>Price</h2><p>$<?php  echo($memInfo[1]['pricePerNight']);?></p></div>
            <div style="float: left;text-align: center;padding-left: 70px;"><br><br><br><br><button id="twoPetButton" onclick="confirmRoom()" class="bButton">Book</button></div>
        </div>
    </section>

    <section id="oneBed" class="roomBox" style="display: inline-block;">
        <div style="border:solid 1px black;">

            <div style="width: 30%;height:inherit;float: left;"><img class="pic" src="https://upload.wikimedia.org/wikipedia/commons/5/52/Hotel_Room_%289638499309%29.jpg"
                                                                     alt="One bed hotel picture"></div>
            <div style="float: left;text-align: center;padding-left: 100px;"><h2>One Bed Room</h2><p>One bed Hotel room with <br>comfortable beds and clean bathroom.<br>
                Has a desk to do work on. <br>Also has a 40 inch TV with cable.</p></div>
            <div style="float: left;text-align: center;padding-left: 100px;"><h2>Price</h2><p>$<?php  echo($memInfo[1]['pricePerNight']);?></p></div>
            <div style="float: left;text-align: center;padding-left: 70px;"><br><br><br><br><button id="oneBedButton" onclick="confirmRoom()" class="bButton">Book</button></div>
        </div>
    </section>

    <section id="oneBedPet" class="roomBox" style="display: inline-block;">
        <div style="border:solid 1px black;">

            <div style="width: 30%;height:inherit;float: left;"><img class="pic" src="https://media.defense.gov/2017/Jul/27/2001784176/780/780/0/110911-F-XX000-0001.JPG"
                                                                     alt="One bed hotel picture"></div>
            <div style="float: left;text-align: center;padding-left: 100px;"><h2>One Bed Pet Room</h2><p>One bed pet room with <br>comfortable beds and clean bathroom.<br>
                Has a desk to do work on. <br>Also has a 40 inch TV with cable.</p></div>
            <div style="float: left;text-align: center;padding-left: 100px;"><h2>Price</h2><p>$<?php  echo($memInfo[1]['pricePerNight']);?></p></div>
            <div style="float: left;text-align: center;padding-left: 70px;"><br><br><br><br><button id="onePetButton" onclick="confirmRoom()" class="bButton">Book</button></div>
        </div>
    </section>

    <section id="gamingRoom" class="roomBox" style="display: inline-block;">
        <div style="border:solid 1px black;">

            <div style="width: 30%;height:inherit;float: left;"><img class="pic" src="https://cdn.vox-cdn.com/uploads/chorus_asset/file/10686785/Hilton_Panama_Alienware_Room_Gaming_hotel_room_11.jpg"
                                                                     alt="Gaming room picture"></div>
            <div style="float: left;text-align: center;padding-left: 100px;"><h2>Gaming Room</h2><p>Gaming hotel room with <br>PS4, Xbox1, Nintendo Switch and PC.<br>
                Includes many games. <br>Has a 4K TV with cable.</p></div>
            <div style="float: left;text-align: center;padding-left: 100px;"><h2>Price</h2><p>$<?php  echo($memInfo[3]['pricePerNight']);?></p></div>
            <div style="float: left;text-align: center;padding-left: 70px;"><br><br><br><br><button id="gamingButton" onclick="confirmRoom()" class="bButton">Book</button></div>
        </div>
    </section>

   <!-- <section id="petRoom" class="roomBox" style="display: inline-block;">
        <div style="border:solid 1px black;">

            <div style="width: 30%;height:inherit;float: left;"><img class="pic" src="https://media.defense.gov/2017/Jul/27/2001784176/780/780/0/110911-F-XX000-0001.JPG"
                                                                     alt="Pet room picture"></div>
            <div style="float: left;text-align: center;padding-left: 100px;"><h2>Pet Room</h2><p>Hotel room that allows pets. <br>All house pets are allowed in this room<br>
                Two pet maximum <br>Pets are only allowed in pet rooms.</p></div>
            <div style="float: left;text-align: center;padding-left: 100px;"><h2>Price</h2><p>$300</p></div>
            <div style="float: left;text-align: center;padding-left: 70px;"><br><br><br><br><button class="bButton">Book</button></div>
        </div>
    </section>-->
    <form action="billingPage.php">
    <section id="chefRoom" class="roomBox" style="display: inline-block;">
        <div style="border-top:solid 1px black;">

            <div style="width: 30%;height:inherit;float: left;"><img class="pic" src="data:image/jpeg;base64,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"
                                                                     alt="Chef room picture"></div>
            <div style="float: left;text-align: center;padding-left: 100px;"><h2>Chef Room</h2><p>Hotel room with a chef kitchen. <br>Includes microwave, oven, <br>refridgerator, utinsels and cutting boards.<br>
                Comes with fruits and vegetables. <br>Includes meats such as<br> chicken, steak, and pork.</p></div>
            <div style="float: left;text-align: center;padding-left: 100px;"><h2>Price</h2><p>$<?php  echo($memInfo[1]['pricePerNight']);?></p></div>
            <div style="float: left;text-align: center;padding-left: 60px;"><br><br><br><br><button id="chefButton" onclick="confirmRoom()" class="bButton">Book</button></div>
        </div>
    </section>
    </form>
</div>
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
        $('#twoBedButton').on('click', function () {
            $('#dialog-container').show();
        });
        $('#twoPetButton').on('click', function () {
            $('#dialog-container').show();
        });
        $('#oneBedButton').on('click', function () {
            $('#dialog-container').show();
        });
        $('#onePetButton').on('click', function () {
            $('#dialog-container').show();
        });
        $('#chefButton').on('click', function () {
            $('#dialog-container').show();
        });
        $('#gamingButton').on('click', function () {
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
    var b = document.getElementById("twoBed");
    var a = document.getElementById("oneBed");
    var c = document.getElementById("chefRoom");
    var d = document.getElementById("gamingRoom");
   // var e = document.getElementById("petRoom");
    var onePet = document.getElementById("oneBedPet");
    var twoPet = document.getElementById("twoBedPet");

    function oneBedRoom() {


        if (a.style.display == "inline-block" && onePet.style.display == "inline-block") {
            a.style.display = "inline-block";
            onePet.style.display = "inline-block";
            b.style.display = "none";
            c.style.display = "none";
            d.style.display = "none";
           twoPet.style.display = "none";
        }

        if (a.style.display == "none" && onePet.style.display == "none") {
            a.style.display = "inline-block";
            onePet.style.display = "inline-block";
            b.style.display = "none";
            c.style.display = "none";
            d.style.display = "none";
            twoPet.style.display = "none";
        }
    }

    function twoBedRoom() {

        if (b.style.display == "inline-block" && twoPet.style.display == "inline-block") {
           b.style.display = "inline-block";
            twoPet.style.display = "inline-block";
            a.style.display = "none";
            c.style.display = "none";
            d.style.display = "none";
           onePet.style.display = "none";
        }

        if (b.style.display == "none" && twoPet.style.display == "none") {
            b.style.display = "inline-block";
            twoPet.style.display = "inline-block"
            a.style.display = "none";
            c.style.display = "none";
            d.style.display = "none";
            onePet.style.display = "none";
        }
    }

    function specialtyRoom() {

        if (c.style.display == "inline-block" && d.style.display == "inline-block") {
            c.style.display = "inline-block";
            d.style.display = "inline-block";
           // e.style.display = "inline-block";
            a.style.display = "none";
            b.style.display = "none";
        }

        if (c.style.display == "none" && d.style.display == "none") {
            c.style.display = "inline-block";
            d.style.display = "inline-block";
            //e.style.display = "inline-block";
            a.style.display = "none";
            b.style.display = "none";
        }
    }
    
    function allRooms() {
        if(c.style.display == "none" || d.style.display == "none"||
        a.style.display == "none" || b.style.display == "none"){
            c.style.display = "inline-block";
            d.style.display = "inline-block";
            //e.style.display = "inline-block";
            a.style.display = "inline-block";
            b.style.display = "inline-block";
            onePet.style.display = "inline-block";
        }
    }

    function confirmRoom(){
        var popup = confirm("Are you sure you want to book this room?");
        if(popup == true){
            <?php header('Location: billing.php');
            ?>
        }else{

        }
    }


</script>

</body>
</html>