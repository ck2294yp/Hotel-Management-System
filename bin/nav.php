<!-- Load in the Font Awesome icon/font library (used to show a hamburger menu bar on small, mobile screens) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<style>
    /* Add a black background color to the top navigation */
    .topnav {
        background: rgba(0,0,0,.7);
        border-top: 1px solid rgba(255,255,255,.2);
        border-bottom: 1px solid rgba(255,255,255,.2);
        overflow: hidden;
        width; 100%;
        height: 10%;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    /* Style the links inside the navigation bar */
    .topnav a {
        float: right;
        display: block;
        color: #f2f2f2;
        text-align: center;
        padding: 14px 16px;
        text-decoration: none;
        font-size: 17px;

    }

    /* Change the color of links on hover */
    .topnav a:hover {
        background: #ff0000;
        /*color: black;*/
    }

    /* TODO: NOT used. Might use this later. */
    /* Add an active class to highlight the current page */
    /*.active {*/
    /*    background-color: #4CAF50;*/
    /*    color: white;*/
    /*}*/

    /* Hide the link that should open and close the topnav on small screens */
    .topnav .icon {
        display: none;
    }





    /* When the screen is less than 600 pixels wide, hide all links, except for the first one ("Home"). Show the link that contains should open and close the topnav (.icon) */
    @media screen and (max-width: 600px) {
        .topnav a:not(:first-child) {display: none;}
        .topnav a.icon {
            float: right;
            display: block;
        }
    }

    /* The "responsive" class is added to the topnav with JavaScript when the user clicks on the icon. This class makes the topnav look good on small screens (display the links vertically instead of horizontally) */
    @media screen and (max-width: 600px) {
        .topnav.responsive {position: relative;}
        .topnav.responsive a.icon {
            position: absolute;
            right: 0;
            top: 0;
        }
        .topnav.responsive a {
            float: none;
            display: block;
            text-align: left;
        }
    }

</style>




<!--Navigation Bar-->
<div class="topnav" id="topNavbar">
    <a href="/index.php">Home</a>
    <a href="/aboutUs.html">About</a>
    <a href="/whyTci.html">Why TCI?</a>
    <a href="signIn.php">Members</a>
    <a href="javascript:void(0);" class="icon" onclick="mobileDevice()">
        <i class="fa fa-bars"></i>
    </a>
</div>



<!-- Script to allow webpage to function on a small screen (such as a smartphone). -->
<script>
    function mobileDevice() {
        var x = document.getElementById("topNavbar");

        if (x.className === "topnav") {
            x.className += " responsive";

        } else {
            x.className = "topnav";
        }

    }
</script>






