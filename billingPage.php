<?php


?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billing page</title>
    <link rel="stylesheet" href="style.css" type="text/css" />
</head>
<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1> Twin Cities Inn</h1>
</header>
<style>
    * {
        box-sizing: border-box;
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
    .payNow{
        margin:0 auto;
        display:block;
    }
    .align{
        text-align: right;
    }


</style>
<body>
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
    <div class="column align" >
        <h4>Checkout Summary</h4>

        <p>Room Type:</p>
        <label>Total Amount Due: </label>
    </div>
    <div class="column" ">
        <h4>Points available: </h4>
    <select id = "myList">
        <option value = ""> Select Payment Option</option>
        <option value = "1">one</option>
    </select>

        <form>
            <!--
            Name on card, Card number, Expiration date and cvv number
            -->
        </form>
    </div>
</div >
<div class = "payNow" style = "text-align: center;">
<button  onclick="bookRoom() " class="button button2 main">Pay Now</button>
</div>
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

