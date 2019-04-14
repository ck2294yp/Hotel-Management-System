<?php


?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billing page</title>
    <link rel="stylesheet" href="style.css" type="text/css" />
</head>

<style>
    {
        box-sizing: border-box;
    }

    /* Create two equal columns that floats next to each other */
    .column {
        float: left;
        width: 50%;
        padding: 10px;
        height: 300px; /* Should be removed. Only for demonstration */
    }

    /* Clear floats after the columns */
    .row:after {
        content: "";
        display: table;
        clear: both;
    }
    .button {
        background-color: #4CAF50;
        border: none;
        color: white;
        padding: 20px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        margin: 4px 2px;
    }
    .button2 {border-radius: 4px;}
    .column {
        float: left;
        width: 50%;
    }

    /* Clear floats after the columns */
    .row:after {
        content: "";
        display: table;
        clear: both;
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
    .container {
        width: 100px;
        clear: both;
        text-align: center;
        align-content: center;
        padding: 50px;
    }

    .container input {
        width: 100%;
        width: 100px;
        clear: both;
        text-align: center;
    }
    .showthis {
        display: none;
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

<div class="row">
    <div class="column container ">
        <select id = "myList">
            <label>Select Payment Option</label>
            <option > Select Payment Option</option>
            <option value = "1">one</option>
            <option value = "2">two</option>
            <option value = "3">three</option>
            <option value = "4">four</option>
        </select><br>

        <input type="checkbox" name="billingAddress" value="Bike"> Use same Billing Address
           <!-- Drop Down Menu: PlaceHolder "Please Select Card" -->

           <form>


                First Name: <input type="text" name="FirstName" value="">
                Last name: <input type="text" name="LastName" value=""><br>
                Address:<input type = "text" name = "Address" value = ""><br>
                City:<input type = "text" name = "City" value = "">
               State:<input type = "text" name = "State" value = "">
               Zip code :<input type = "text" name = "Zip Code" value = "">

           </form>

          <!-- Add new Card Button window pop up with --->


    </div>
    <div class="column ">

            <p>
                Room Type:<br>
                Price: <br>
                Room Information:<br>
                Check in Date: <br>
                Check out Date <br>
            </p>
            <!---
            <p>
            Room Type
            Check In Date - Check Out Date
            Amount Due: fetch price x date.
            -->

    </div>
</div>


</div>
<button onclick="bookRoom()" class="button button2">Book Room</button>
<!-- When Button is clicked there will be a display message and a SQL query for DB -->
<!-- Thank You for booking with TCI! We Look forward to seeing you soon.
 -->
<script>
    function bookRoom() {
        alert("Thank you for booking with TCI, We look forward to seeing you soon!");
    }
    function hideFields(){

    }
</script>
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

