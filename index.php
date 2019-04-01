<!doctype html>

<head>
    <meta charset="UTF-8">
    <title>Sokoban</title>
    <link rel="stylesheet" type="text/css" href="css/sokoban.css">
    <link rel="icon" href="favicon/favicon.ico" />

    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/sokoban.js"></script>
    <script src="js/cookies.js"></script>

    <script>

        $(document).ready(function() {
            login();
            logout();
        });

        function logout() {
            $("#logoutbutton").click(function () {
                $.get("php/logout.php").done(function () {
                    location.href = "";
                    
                });
                return false;
            }
        }

        function login() {
            $("#loginform").submit(function () {
                $.ajax({
                    url: $(this).attr("action"),
                    type: $(this).attr("method"),
                    dataType: "json",
                    data: $(this).serialize()
                }).done(function(data) {
                    if (data === true) {
                        alert(data.message);
                        $("#navbar").append("<li id=\"aboutli\" style=\"float: right;\">\n" +
                            "<button id=\"aboutbutton\">About</button></li>\n" +
                            "<li id=\"logoutli\" style=\"float:right\">\n" +
                            "<button id=\"logoutbutton\" type=\"button\">Logout</button>\n" +
                            "</li>");
                        $("#logindiv").html("")
                    } else {
                        alert(data.message)
                    }
                });
                return false;
            });
    </script>
</head>

<body>
<header>
    <ul id="navbar">
        <li id="titleofwebsite"><p>Sokoban</p></li>
    </ul>
</header>


<div id="about">
    <p>Bienvenue sur le site du jeu Sokoban ! <br> Le principe du jeu est simple : le joueur (en rouge) doit pousser
        tous les blocs (en rouge) dans une zone délimitée (en orange) pour gagner. <br> Il y a une soixantaine de
        niveaux a passer, amusez-vous bien !</p>
</div>

<div id="logindiv">
    <form id="loginform" action="php/login.php" method="post">
        <h2 id="loginMessage">Please Log In to Play</h2>
        <label id="usernameLabel">Username</label><br>
        <input type="text" placeholder="dupont" name="username" required /><br><br>
        <label id="passwordLabel">Password</label><br>
        <input type="password" id="passwordInput" name="password" required /><br><br>
        <ul id="loginButtonContainer">
            <li><button id="loginbutton" type="submit">Log In</button></li>
        </ul>
    </form>
</div>

<div id="game">
    <h1>JE SUIS LA DIV CONTENANT SOKOBAN</h1>
</div>
</body>