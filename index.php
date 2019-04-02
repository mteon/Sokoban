<!doctype html>

<head>
    <meta charset="UTF-8">
    <title>Sokoban</title>
    <link rel="stylesheet" type="text/css" href="css/sokoban.css">
    <link rel="icon" href="favicon/favicon.ico" />

    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/sokoban.js"></script>
    <script src="js/cookies.js"></script>

    <script type="text/javascript">
        "use strict";

        var currentLevel;

        $(document).ready(function() {
            isLogged();
        });

        function logout(){
            $.ajax({
                url: "php/logout.php",
                success: function () {
                    showForm();
                    initlogin();
                }
            });
            return false;
        }

        function initlogin(){
            $("#loginform").submit(function () {
                $.ajax({
                    url: "php/login.php",
                    type: "POST",
                    dataType: "json",
                    data: $(this).serialize(),
                    success: function (data) {
                        if (data.logged === true) {7
                            currentLevel = data.level;
                            alert(data.message);
                            showGame();
                            loadCurrentLevel(currentLevel);

                        }
                    }
                });
                return false;
            })
        }

        function showGame(){
            $("#loginform").hide();
            $("#navbar").append("<li id=\"aboutli\" style=\"float: right;\">\n" +
                "<button id=\"aboutbutton\">About</button></li>\n" +
                "<li id=\"logoutli\" style=\"float:right\">\n" +
                "<button id=\"logoutbutton\" type=\"button\" onclick=\"logout();\">Logout</button>\n" +
                "</li>");
            $("#game").show();
        }

        function showForm(){
            console.log("disconnected");
            $("#logoutbutton").hide();
            $("#logindiv").empty()
                .append("<form id=\"loginform\">\n" +
                "        <h2 id=\"loginMessage\">Please Log In to Play</h2>\n" +
                "        <label id=\"usernameLabel\">Username</label><br>\n" +
                "        <input type=\"text\" placeholder=\"dupont\" name=\"username\" required /><br><br>\n" +
                "        <label id=\"passwordLabel\">Password</label><br>\n" +
                "        <input type=\"password\" id=\"passwordInput\" name=\"password\" required /><br><br>\n" +
                "        <ul id=\"loginButtonContainer\">\n" +
                "            <li><button id=\"loginbutton\" type=\"submit\">Log In</button></li>\n" +
                "        </ul>\n" +
                "    </form>");
            $("#game").hide();
        }

        function isLogged(){
            $.ajax({
                url: "php/is_connected.php",
                type: "GET",
                success: function (data) {
                    if(data === true){
                        showGame();
                    }
                    else {
                        showForm();
                        initlogin();
                    }
                }
            });
            return false;
        }
    </script>
</head>

<body>
<header>
    <ul id="navbar">
        <li id="titleofwebsite"><p>Sokoban</p></li>
    </ul>
</header>


<div id="about">
    <p>Bienvenue sur le site du jeu Sokoban ! <br> Le principe du jeu est simple : le joueur (en bleu) doit pousser
        tous les blocs (en rouge) dans une zone délimitée (en orange) pour gagner. <br> Il y a une soixantaine de
        niveaux a passer, amusez-vous bien !</p>
</div>

<div id="logindiv">
</div>

<div id="game"></div>

</body>