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
        padding-bottom: 0px;
        list-style-type: none;
    }
    .footer li {
        display: inline-block;
        font-weight: bold;
    }
    .column {
        float: left;
        margin:0px;
        text-align: center;
        /* Should be removed. Only for demonstration */
    }

    .left {
        width: 60%;
    }

    .right {
        width: 40%;
    }

    /* Clear floats after the columns */
    .row:after {
        content: "";
        display: table;
        clear: both;
        padding: 0px;
    }
    .addNewButtonPaddingTop{
        padding-top: -50px;
         }
    body,
    html {
        height: 100%;
        min-height: 100%;
    }

    body {
        font-family: 'Roboto',
        sans-serif;
        margin: 0;
        background-color: #e7e7e7;
    }
    .credit-card {
        width: 360px;
        height: 400px;
        margin: 60px auto 0;
        border: 1px solid #ddd;
        border-radius: 6px;
        background-color: #fff;
        box-shadow: 1px 2px 3px 0 rgba(0,0,0,.10);
    }
    .form-header {
        height: 60px;
        padding: 20px 30px 0;
        border-bottom: 1px solid #e1e8ee;
    }

    .form-body {
        height: 300px;
        padding: 30px 30px 20px;
    }
    .title {
        font-size: 18px;
        margin: 0;
        color: #5e6977;
    }
    form {
        margin: 0 auto;
        width:250px;
    }
    .card-number,
    .cvv-input input,
    .month select,
    .year select {
        font-size: 14px;
        font-weight: 100;
        line-height: 14px;
    }

    .card-number,
    .month select,
    .year select {
        font-size: 14px;
        font-weight: 100;
        line-height: 14px;
    }

    .card-number,
    .cvv-details,
    .cvv-input input,
    .month select,
    .year select {
        opacity: .7;
        color: #86939e;
    }
    .card-number {
        width: 100%;
        margin-bottom: 20px;
        padding-left: 20px;
        border: 2px solid #e1e8ee;
        border-radius: 6px;
    }
    .month select,
    .year select {
        width: 145px;
        margin-bottom: 20px;
        padding-left: 20px;
        border: 2px solid #e1e8ee;
        border-radius: 6px;
        background: url('caret.png') no-repeat;
        background-position: 85% 50%;
        -moz-appearance: none;
        -webkit-appearance: none;
    }

    .month select {
        float: left;
    }

    .year select {
        float: right;
    }
    .cvv-input input {
        float: left;
        width: 145px;
        padding-left: 20px;
        border: 2px solid #e1e8ee;
        border-radius: 6px;
        background: #fff;
    }

    .cvv-details {
        font-size: 12px;
        font-weight: 300;
        line-height: 16px;
        float: right;
        margin-bottom: 20px;
    }

    .cvv-details p {
        margin-top: 6px;
    }
    .paypal-btn,
    .proceed-btn {
        cursor: pointer;
        font-size: 16px;
        width: 100%;
        border-color: transparent;
        border-radius: 6px;
    }

    .proceed-btn {
        margin-bottom: 0px;
        background: #7dc855;
    }

    .paypal-btn a,
    .proceed-btn a {
        text-decoration: none;
    }

    .proceed-btn a {

    }
    .alignLeft{
        text-align: left;
    }

</style>
<body>
<nav>
    <ul>
        <li><a href="index.html">Home</a> </li>
        <li><a href="aboutUs.html">About</a> </li>
        <li><a href="whyTci.html">Why TCI?</a> </li>
        <li><a href="bin/signOut.php">Sign Out</a> </li>
    </ul>
</nav>

    <h2> Billing Information</h2>
    <div class="row">
        <div class="column left" >
            <h2>Checkout Details</h2>
            <p>Room Type:</p>
            <h4>Points available: </h4>
            <label>Total Amount Due: </label><br>
            <select id = "myList">
                <option value = ""> Select Payment Option</option>
            </select><br>
            <button type = "submit" >Add New Card</button>
                    </div>
                    <div class="column right" >
                        <form class="credit-card form">
                            <div class="form-header">
                                <h4 class="title">Credit card detail</h4>
                            </div>

                            <div class="form-body">
                                <!-- Card Number -->
                                <input type="text" class="card-number" placeholder="Card Number">

                                <!-- Date Field -->
                                <div class="date-field">
                                    <div class="month">
                                        <select name="Month">
                                            <option value="january">January</option>
                                            <option value="february">February</option>
                                            <option value="march">March</option>
                                            <option value="april">April</option>
                                            <option value="may">May</option>
                                            <option value="june">June</option>
                                            <option value="july">July</option>
                                            <option value="august">August</option>
                                            <option value="september">September</option>
                                            <option value="october">October</option>
                                            <option value="november">November</option>
                                            <option value="december">December</option>
                                        </select>
                                    </div>
                                    <div class="year">
                                        <select name="Year">
                                            <option value="2016">2016</option>
                                            <option value="2017">2017</option>
                                            <option value="2018">2018</option>
                                            <option value="2019">2019</option>
                                            <option value="2020">2020</option>
                                            <option value="2021">2021</option>
                                            <option value="2022">2022</option>
                                            <option value="2023">2023</option>
                                            <option value="2024">2024</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Card Verification Field -->
                                <div class="card-verification">
                                    <div class="cvv-input">
                                        <input type="text" placeholder="CVV">
                                    </div>
                                    <div class="cvv-details">
                                        <p>3 or 4 digits usually found <br> on the signature strip</p>
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <button type="submit" class="proceed-btn"><a href="#">Add Card</a></button
                            </div>
                        </form>
        </div>
    </div>
    <button onclick = "bookRoom()" class = "process-btn">Pay Now</button>
<script>
    function bookRoom() {
        alert("Thank you for booking with TCI, We look forward to seeing you soon!");

    }
</script>
<footer class = "footer" style = padding-bottom: 20px "text-align: center">
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

