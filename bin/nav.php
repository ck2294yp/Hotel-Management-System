<html>
<head>

    <style>
        .topnav {
            width: 100%;
            height:50px;
            background: rgba(0,0,0,.7);
            border-top: 1px solid rgba(255,255,255,.2);
            border-bottom: 1px solid rgba(255,255,255,.2);
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .topnav a {
            float: right;
            color: #f2f2f2;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
            font-size: 17px;
        }

        .topnav a:hover {
            background: #ff0000;
            color: white;
        }


        .topnav ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            display: flex;
            float: right;
        }

    </style>

    <?php

    @session_start();

    // If user IS logged in.
    if (array_key_exists('loggedIn', $_SESSION) === true) {
        ?>
        <div class="topnav">
            <ul>
                <li><a href="/index.php">Home</a></li>
                <li><a href="/aboutUs.php">About</a></li>
                <li><a href="/whyTci.php">Why TCI?</a></li>
                <li><a href="/membersPage.php">Member's Page</a></li>
                <li><a href="/accountInformationPage.php">Account Information</a></li>
                <li><a href="/bookingHistory.php">Booking History</a></li>
                <li><a href="/bin/signOut.php">Sign Out</a></li>
            </ul>
        </div>
        <?php
    } else {
        ?>
        <div class="topnav">
            <ul>
                <li><a href="/index.php">Home</a></li>
                <li><a href="/aboutUs.php">About</a></li>
                <li><a href="/whyTci.php">Why TCI?</a></li>
                <li><a href="/signIn.php">Sign In/Sign Up</a></li>
            </ul>
        </div>
        <?php
    }
    ?>

</head>


</html>