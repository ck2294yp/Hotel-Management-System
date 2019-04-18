<?php
session_start();

require_once "settings/settings.php";
require_once "bin/inputSanitization.php";
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking page</title>
    <link rel="stylesheet" href="style.css" type="text/css" />
</head>

<style>


    .details{
        position: absolute;
        width: 400px;
        height: 300px;
        display: flex;
        justify-content:center;
        font-family: serif;
        font-size: 20px;
        top: 48%;
        left: 35%;

        background-color:white;
    }


    .details input[type="submit"]{
        width: 100%;
        height: 45px;
        background-color: brown;
        color: white;
        display: flex;
        justify-content: center;
        box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
        font-size: 20px;

    }
    table tr td{
        border: 1px solid black;

    }


    .details input[type="number"]{
        width: 100%;
        height: 35px;
        font-size: 20px;
        font-family:Arial, Helvetica, sans-serif;
    }
    .detail input[type="text"]{
        width: 100%;
        height: 55px;
    }

    .details input[type="date"]{
        font-size: 20px;
        width: 100%;
        height: 35px;
        font-family:Arial, Helvetica, sans-serif;
    }



    .details select{
        width: 100%;
        height: 35px;
        font-size: 20px;
        font-family:Arial, Helvetica, sans-serif;
    }
    footer{
        position: fixed;
        bottom: 0;
        width: 100%;
    }
    h2{
        text-align: center;
        color: brown;
        top: 45%;
        left: 35%;
    }

</style>
<body>
<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1> Twin Cities Inn</h1>
</header>
<nav>
    <ul>
        <li><a href="#">Home</a> </li>
        <li><a href="#" class="active">About</a> </li>
        <li><a href="#">Amenities</a> </li>
        <li><a href="#">Specialty Rooms</a> </li>
        <li><a href="#">Contact</a> </li>
        <li><a href="#">Sign In</a> </li>
    </ul>
</nav>
<h2>Welcome To Twin Cities Inn Booking Page</h2><hr>
<div class="details">

    <form action="searchRoom.html" METHOD="post">

        <table>
            <tr>
                <th>*Checkin Date</th>
                <th>*Checkout Date</th>
            </tr>
            <tr>
                <th>
                    <input type="date" id="checkInDate" name="checkInDate" min="2019-03-24" required>
                </th>
                <th>
                    <input type="date" id="checkOutDate" name="checkOutDate" max="2021-03-24" required>
                </th>
            </tr>
        </table>
        <br>




        <select name="roomType" required>


            <option value="">*Select Room Type </option>
            <optgroup label="Standard rooms">
                <option value="1-bed"> 1-bed</option>
                <option value="2-beds"> 2-beds</option>
                <option value="3-beds"> 3-beds</option>
            </optgroup>
            <optgroup label="Specialty rooms">
                <option value="gaming"> Gaming room </option>
                <option value="chef"> Chef room</option>
                <option value="luxury"> Luxury room</option>

            </optgroup>

        </select>
        <br><br>

        <input id="numberOfAdults" type="number" name="numberOfAdults" min="1" max="6" required placeholder="     *Select Number of Guests">
        <br><br>
        <input id="numberOfPets" type="number" name="numberOfPets" min="0" max="2" placeholder="     Select Number of Pets (optional)">
        <br><br>


        <input type="submit" value="Search">








    </form>
</div>

<footer>
    <nav>
        <ul>
            <li><a href="#">Facebook</a> </li>
            <li><a href="#">Twitter</a> </li>
            <li><a href="#">Google+</a> </li>
            <li><a href="#">© 2019 Twin Cities Inn</a> </li>
        </ul>
    </nav>
</footer>

</body>
</html>