<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Member Page</title>
    <link rel="stylesheet" href="style.css" type="text/css" />
</head>
<body>
<header>
    <img src="https://tbncdn.freelogodesign.org/4fc3ec1c-1e7a-4304-b812-4b6b0bdb6b68.png?1553471553913">
    <h1> Twin Cities Inn</h1>
</header>

<nav>
    <ul>
        <li><a href="#">Home</a> </li>
        <li><a href="aboutUs.html">About</a> </li>
        <li><a href="amenities.html">Amenities</a> </li>
        <li><a href="#">Specialty Room</a> </li>
        <li><a href="#">Contact</a> </li>
        <li><a href="#">Sign Out</a> </li>
    </ul>
</nav>
<nav style="top: 50px;">
    <ul>
        <li><a href="#" class="active">Member's Page</a></li>
        <li><a href="#">Profile</a></li>
        <li><a href="#">Reservations</a></li>
    </ul>
</nav>
<section class="sec1Member">
    <h3>Welcome to TCI Rewards Club</h3>
</section>

<section class="sec2Member">
    <div class="gridMember">
        <div class="memberName">
            <h2 style="font-style: italic">My Account</h2>
            <P>Hello, Bob</P>
            <p>Member ID: 2345678</p>
            <br/>
            <br/>
        </div>
        <div class="gridMemberReward">
            <h2 style="font-style: italic">Available to Redeem</h2>
            <p>500 points</p>
            <button class="redeemPoints">Redeem</button><br/><br/>
            <a href="#" style="color: orange">Report Missing Points</a>
        </div>
        <div class="bookNow">
            <h2 style="font-style: italic">Need to make a reservation?</h2>
            <p>Click below.</p>
            <button class="bookNow">Book Now</button><br/><br/>
        </div>
    </div>



</section>

<!-- <section class="sec3"></section> -->

<footer>
    <nav>
        <ul>
            <li><a onclick="return false" href="">Facebook</a> </li>
            <li><a onclick="return false" href="">Twitter</a> </li>
            <li><a onclick="return false" href="">Google+</a> </li>
            <li><a onclick="return false" href="">© 2019 Twin Cities Inn</a> </li>
        </ul>
    </nav>
</footer>
</body>
</html>