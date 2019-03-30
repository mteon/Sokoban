<?php
/**
 * Created by PhpStorm.
 * User: teoma
 * Date: 28/03/2019
 * Time: 20:06
 */

session_start();
?>
<!doctype html>

<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Sokoban</title>

    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="sokoban.js"></script>
    <script src="cookies.js"></script>
    <script>
        "use strict";
        $(document).ready(function () {
            $("#loginForm").submit(function (event){
                event.preventDefault();
                $.ajax({
                    url: $(this).attr("action"),
                    type: $(this).attr("method"),
                    dataType : "json",
                    data: $(this).serialize()
                }).done(function (data) {
                    alert(data.message);
                    location.href = "";
                });
                return false;
            });
        })

        $("#logoutbutton").click(function () {
            $.get("php/logout.php").done(function (done) {
                location.href = "";
            })
        });


    </script>

</head>

<body>
    <header>
        <h2>Sokoban</h2>
        <div id="disconnect" style="display: <?php if(isset($_SESSION['username']))echo "none";else echo "block";?>">
            <form>
                <button id="logoutbutton" type="submit">Logout</button>
            </form>
        </div>
    </header>
    <div name="content" style="display: <?php if(isset($_SESSION['username']))echo "block";else echo "none";?>">
        <form id="loginForm" method="post" action="/is_connected.php">
            <label>Username</label>
            <input name="username">
            <label>Password</label>
            <input type="password" name="password">
            <button type="submit">Login</button>
        </form>
    </div>

    <div id="btn"></div>
    <div id="game"></div>
</body>
</html>