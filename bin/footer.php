<html>

<style>

    footer {
        height: 30px;
        margin: 0;
        clear: both;
        width: 100%;
        position: fixed;
        bottom: 0;
    }

    footerBar {
        width: 100%;
        height:50px;
        background: rgba(0,0,0,.7);
        border-top: 1px solid rgba(255,255,255,.2);
        border-bottom: 1px solid rgba(255,255,255,.2);
        position: fixed;
        bottom:0;
    }

    footerBar ul {
        display: flex;
        margin: 0;
        padding: 0 100px;
        float: right;
    }

    footerBar ul li {
        list-style: none;
    }

    footerBar ul li a {
        display: block;
        color: #fff;
        padding: 0 20px;
        text-decoration: none;
        text-transform: uppercase;
        font-weight: bold;
        line-height: 50px;
    }

    footerBar ul li a:hover,
        footerBar ul li a.active {
        background: #ff0000;
    }
</style>



<footer>
    <footerBar>
        <ul>
            <li><a onclick="return false" href="">Facebook</a></li>
            <li><a onclick="return false" href="">Twitter</a></li>
            <li><a onclick="return false" href="">Google+</a></li>
            <li><a onclick="return false" href="">© 2019 Twin Cities Inn</a></li>
        </ul>
    </footerBar>
</footer>


<?php


?>

</html>
