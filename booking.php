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

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js" type="text/javascript"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"
            type="text/javascript"></script>
    <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css"
          rel="Stylesheet"type="text/css" href="style.css"/>
    <script type="text/javascript">
        $(function () {
            $("#checkInDate").datepicker({
                minDate: 0,
                //numberOfMonths: 6,
                onSelect: function (selected) {
                    var dt = new Date(selected);
                    dt.setDate(dt.getDate() + 1);
                    $("#checkOutDate").datepicker("option", "minDate", dt);
                }
            });
            $("#checkOutDate").datepicker({
                //numberOfMonths: 6,
                onSelect: function (selected) {
                    var dt = new Date(selected);
                    dt.setDate(dt.getDate() - 1);
                    $("#checkInDate").datepicker("option", "maxDate", dt);
                }
            });
        });
    </script>


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

<!--Navigation Bar-->
<?php include 'bin/nav.php'; ?>

<h2>Welcome To Twin Cities Inn Booking Page</h2><hr>
<div class="details">

    <form action="searchRooms.php" METHOD="post">

        <table border="0" cellpadding="0" cellspacing="0">
            <tr>
                <th>*Checkin Date</th>
                <th>*Checkout Date</th>
            </tr>
            <tr>

                <th>
                    <input type="text" id="checkInDate" name="checkInDate" min="2019-03-24" required>
                </th>
                <th>
                    <input type="text" id="checkOutDate" name="checkOutDate" max="2021-03-24" required>

                </th>
            </tr>
        </table>
        <br>






        <input type="submit" value="Search" onclick="compareDates()">








    </form>
</div>

<!-- Footer for the web page.-->
<?php include_once 'bin/footer.php'; ?>

</body>
</html>