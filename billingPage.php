<?php


?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billing page</title>
    <link rel="stylesheet" href="style.css" type="text/css" />
</head>

<style>
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
<option value="">Select Payment Option </option>
<optgroup>

</optgroup>
<div class="row">
    <div class="column">

           <h1> Payment Method</h1>
           Drop Down Menu: PlaceHolder "Please Select Card"
           <form>
                <input type="checkbox">
                <input type="checkbox" name="billingAddress" value="Bike"> Use same Billing Address<br>
                Name: <input type="text" name="FirstName" value="First Name">
                Last name: <input type="text" name="LastName" value="Last Name"><br>

           </form>

          <!-- Add new Card Button window pop up with --->

        <h1>
            Test 1</h1>
    </div>
    <div class="column">
        <h1>
            Test 2
            <!---
            Room Information
            Room Type
            Check In Date - Check Out Date
            Amount Due

            -->
        </h1>
    </div>
</div>


</div>
<button class="button button2">Book Room</button>
<!-- When Button is clicked there will be a display message and a SQL query for DB -->
<!-- Thank You for booking with TCI! We Look forward to seeing you soon.
 -->
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

