<!doctype html>

<head>
    <meta charset="UTF-8">
    <title>Sokoban</title>
    <link rel="stylesheet" type="text/css" href="css/sokoban.css">
    <link rel="icon" href="favicon/favicon.ico" />

    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/cookies.js"></script>
    <script type="text/javascript">
        "use strict";

        $(document).ready(function() {
            isLogged();
            initlogin();

            function Sokoban () {
                this.level = 0;                 // L'id du niveau
                this.tableGrid = [];            // La grille de jeu
                this.playerPos = {x: 0, y: 0};  // Position actuelle du joueur
                this.direction = {x: 0, y: 0};  // La direction en fonction de ZQSD
                this.goals = [];                // Array d'objets de chaque position des points d'arrivée {x: x, y: y}
                this.won = false;               // Si le niveau est gagné ou non

                this.color = function (pos, color) {
                    let tr = $($("tr")[pos.y]);
                    let td = $($("td", tr)[pos.x]);
                    td.css('background-color', color)
                };

                this.initTable = function (obj, lvl) {
                    this.level = lvl;
                    $('h2').html('Niveau ' + this.level);

                    $('#game').empty(); // Vidage de la grille dans le DOM

                    let table = $('<table cellspacing="0"></table>').css({'border': '1px solid black','table-layout':' fixed'}).appendTo('#game'); // Style

                    let i = 0;
                    for (let ob of obj.cells) {
                        this.tableGrid[i] = []; // Initialisation du tableau en 2D
                        let tr = $('<tr />').appendTo(table); // Ajout de chaque ligne au DOM ...
                        for (let j = 0; j < ob.length; j++) {
                            $('<td />').appendTo(tr).css({'height': '30px','width': '30px', 'color': 'rgba(0, 0, 0, 0)'}); // ... ainsi que les colonnes
                            this.tableGrid[i][j] = ob[j] !== " " || undefined ? ob[j] : '+'; // Si case vide du tableau, on remplace par un + dans notre tableau

                            // Définition des couleurs à afficher + positions joueur & objectifs
                            switch (ob[j]) {
                                case '#': // mur
                                    this.color({x:j, y:i}, 'gray'); break;
                                case '@': // joueur
                                    this.playerPos.y = i;
                                    this.playerPos.x = j;
                                    this.color({x:j, y:i}, 'red'); break;
                                case '.': // point d'arrivée
                                    this.goals.push({x:j, y:i});
                                    this.color({x:j, y:i}, 'gold'); break;
                                case '*': // bloc sur un point d'arrivée
                                    this.goals.push({x:j, y:i});
                                    this.color({x:j, y:i}, 'darkcyan'); break;
                                case '$': // bloc
                                    this.color({x:j, y:i}, 'cyan'); break;
                            }
                        }
                        i++;
                    }
                };

                this.checkMove = function (move) {
                    switch (move) {
                        case 122: // Haut Z
                            this.direction = {x:0, y:-1};
                            break;
                        case 113: // Gauche Q
                            this.direction = {x:-1, y:0};
                            break;
                        case 115: // Bas S
                            this.direction = {x:0, y:1};
                            break;
                        case 100: // Droite D
                            this.direction = {x:1, y:0};
                            break;
                    }

                    // Code assez verbeux mais évite de le dupliquer 4 fois
                    let futurePos = this.tableGrid[this.playerPos.y + this.direction.y][this.playerPos.x + this.direction.x];
                    let futurePos2 = this.tableGrid[this.playerPos.y + (this.direction.y + this.direction.y)][this.playerPos.x + (this.direction.x + this.direction.x)];
                    let futurePosIsBlock = (futurePos === '$' || futurePos === '*');
                    let carryingBlockIntoWall = ((futurePos2 === '#' || futurePos2 === '$' || futurePos2 === '*') && futurePosIsBlock);

                    if (futurePos !== '#' && !carryingBlockIntoWall) {
                        if (futurePos === '$' || futurePos === '*') { // Déplacement d'un bloc si besoin
                            this.tableGrid[this.playerPos.y + (this.direction.y + this.direction.y)][this.playerPos.x + (this.direction.x + this.direction.x)] = '$';
                        }
                        // Ancienne position du joueur devient vide
                        this.color({x:this.playerPos.x, y:this.playerPos.y}, 'white');
                        this.tableGrid[this.playerPos.y][this.playerPos.x] = '+';

                        // Transition vers la nouvelle et affectation des couleurs
                        this.playerPos.x += this.direction.x;
                        this.playerPos.y += this.direction.y;
                        this.tableGrid[this.playerPos.y][this.playerPos.x] = '@';
                        this.color({x:this.playerPos.x, y:this.playerPos.y}, 'red');
                    }

                    if (!this.won) {
                        this.won = true;
                        for (let goal of this.goals) {
                            if (this.tableGrid[goal.y][goal.x] === '$') this.tableGrid[goal.y][goal.x] = '*';

                            // Après que le joueur soit passé dessus, on la redéfinit dans le tableau (aspect visuel)
                            if (this.tableGrid[goal.y][goal.x] === '+') this.tableGrid[goal.y][goal.x] = '.'

                            // Si un bloc n'est pas sur le point d'arrivée
                            if (this.tableGrid[goal.y][goal.x] !== '*') this.won = false;
                        }
                    }

                    // Redéfinition de certaines couleurs
                    for (let i = 0; i < this.tableGrid.length; ++i) {
                        for (let j = 0; j < this.tableGrid[i].length; ++j) {
                            if (this.tableGrid[i][j] === '$') this.color({y:i,x:j}, 'cyan');
                            if (this.tableGrid[i][j] === '.') this.color({y:i,x:j}, 'gold');
                            if (this.tableGrid[i][j] === '*') this.color({y:i,x:j}, 'darkcyan');
                        }
                    }
                }
            }

            function Builder(sokoban) {
                $('#game').empty(); // Vidage de la grille dans le DOM
                let table = $('<table id="builder" cellspacing="0"></table>').css({'border': '1px solid black','table-layout':' fixed'}).appendTo('#game'); // Style

                let btnJoueur = $('<button />').css('background', 'red').append('Joueur').appendTo('#game');
                let btnBloc = $('<button />').css('background', 'cyan').append('Bloc').appendTo('#game');
                let btnMur = $('<button />').css('background', 'gray').append('Mur').appendTo('#game');
                let btnGoal = $('<button />').css('background', 'gold').append('Objectif').appendTo('#game');
                let btnBlocGoal = $('<button />').css('background', 'darkcyan').append('Bloc sur objectif').appendTo('#game');
                let btnVide = $('<button />').css('background', 'white').append('Case vide').appendTo('#game');

                let start = $('<button />').css('border', '1px solid black').append('START');

                let current = 'gray', symbol = '#';

                let nbJoueur = 0;
                let nbGoals = 0;
                let nbBlocs = 0;

                let grid = [];

                for (let i = 0; i < 20; ++i) {
                    grid[i] = [];
                    let tr = $('<tr />').appendTo(table); // Ajout de chaque ligne au DOM ...
                    for (let j = 0; j < 25; ++j) {
                        if (i === 0 || i === 19 || j === 0 || j === 24) {
                            grid[i][j] = '#'
                        } else {
                            $('<td />').appendTo(tr).css({'height': '30px','width': '30px', 'color': 'rgba(0, 0, 0, 0)'}).css('background-color', 'white').data('color', 'white')// ... ainsi que les colonnes
                        }
                    }
                }

                btnJoueur.click(() => {current = 'red'; symbol = '@';});
                btnBloc.click(() => {current = 'cyan'; symbol = '$';});
                btnMur.click(() => {current = 'gray'; symbol = '#';});
                btnGoal.click(() => {current = 'gold'; symbol = '.';});
                btnBlocGoal.click(() => {current = 'darkcyan'; symbol = '*';});
                btnVide.click(() => {current = 'white'; symbol = '';});


                $('#builder tr td').hover(function() {
                    $(this).css('background-color', current);
                }, function() {
                    $(this).css('background-color', $(this).data('color'));
                });

                $('#builder tr td').click(function() {
                    let col = $(this).index();
                    let $tr = $(this).closest('tr');
                    let row = $tr.index();

                    if ($(this).data('color') === 'red'  && current !== 'red' ) nbJoueur-=1;
                    if ($(this).data('color') === 'cyan' && current !== 'cyan') nbBlocs-=1;
                    if ($(this).data('color') === 'gold' && current !== 'gold') nbGoals-=1;

                    if ($(this).data('color') !== 'red'  && current === 'red' ) nbJoueur+=1;
                    if ($(this).data('color') !== 'cyan' && current === 'cyan') nbBlocs+=1;
                    if ($(this).data('color') !== 'gold' && current === 'gold') nbGoals+=1;

                    $(this).data('color', current);
                    $(this).css('background-color', current);

                    grid[row][col+1] = symbol;

                    if (nbGoals !== 0 && nbGoals === nbBlocs && nbJoueur === 1) {
                        start.appendTo('#game');
                        start.click(function () {
                            sokoban.initTable({cells: grid}, '990');
                        });
                    } else {
                        start.remove()
                    }
                });
            }

            let json = {"copyright":"ASCII Corporation","description":"60 levels original MSX version of Sokoban - 1984","levels":[{"cells":["    #####","    #   #","    #$  #","  ###  $###","  #  $  $ #","### # ### #     ######","#   # ### #######  ..#","# $  $             ..#","##### #### #@####  ..#","    #      ###  ######","    ########"],"height":11,"id":"1","total_balls":6,"width":22},{"cells":["############","#..  #     ###","#..  # $  $  #","#..  #$####  #","#..    @ ##  #","#..  # #  $ ##","###### ##$ $ #","  # $  $ $ $ #","  #    #     #","  ############"],"height":10,"id":"2","total_balls":10,"width":14},{"cells":["        ########","        #     @#","        # $#$ ##","        # $  $#","        ##$ $ #","######### $ # ###","#....  ## $  $  #","##...    $  $   #","#....  ##########","########"],"height":10,"id":"3","total_balls":11,"width":17},{"cells":["              ########","              #  ....#","   ############  ....#","   #    #  $ $   ....#","   # $$$#$  $ #  ....#","   #  $     $ #  ....#","   # $$ #$ $ $########","####  $ #     #","#   # #########","#    $  ##","# $$#$$ @#","#   #   ##","#########"],"height":13,"id":"4","total_balls":20,"width":22},{"cells":["        #####","        #   #####","        # #$##  #","        #     $ #","######### ###   #","#....  ## $  $###","#....    $ $$ ##","#....  ##$  $ @#","#########  $  ##","        # $ $  #","        ### ## #","          #    #","          ######"],"height":13,"id":"5","total_balls":12,"width":17},{"cells":["######  ###","#..  # ##@##","#..  ###   #","#..     $$ #","#..  # # $ #","#..### # $ #","#### $ #$  #","   #  $# $ #","   # $  $  #","   #  ##   #","   #########"],"height":11,"id":"6","total_balls":10,"width":12},{"cells":["       #####"," #######   ##","## # @## $$ #","#    $      #","#  $  ###   #","### #####$###","# $  ### ..#","# $ $ $ ...#","#    ###...#","# $$ # #...#","#  ### #####","####"],"height":12,"id":"7","total_balls":11,"width":13},{"cells":["  ####","  #  ###########","  #    $   $ $ #","  # $# $ #  $  #","  #  $ $  #    #","### $# #  #### #","#@#$ $ $  ##   #","#    $ #$#   # #","##  $    $ $ $ #"," ####  #########","  ###  ###","  #      #","  #      #","  #......#","  #......#","  #......#","  ########"],"height":17,"id":"8","total_balls":18,"width":16},{"cells":["          #######","          #  ...#","      #####  ...#","      #      ...#","      #  ##  ...#","      ## ##  ...#","     ### ########","     # $$$ ##"," #####  $ $ #####","##   #$ $   #   #","#@ $  $    $  $ #","###### $$ $ #####","     # $    #","     #### ###","        #  #","        #  #","        #  #","        ####"],"height":18,"id":"9","total_balls":15,"width":17},{"cells":["              ####","         ######  #","         #       #","         #  #### ###"," ###  ##### ###    #","##@####   $$$ #    #","# $$   $$ $   #....##","#  $$$#    $  #.....#","# $   # $$ $$ #.....#","###   #  $    #.....#","  #   # $ $ $ #.....#","  # ####### ###.....#","  #   #  $ $  #.....#","  ### # $$ $ $#######","    # #  $      #","    # # $$$ $$$ #","    # #       # #","    # ######### #","    #           #","    #############"],"height":20,"id":"10","total_balls":34,"width":21},{"cells":["          ####","     #### #  #","   ###  ###$ #","  ##   @  $  #"," ##  $ $$## ##"," #  #$##     #"," # # $ $$ # ###"," #   $ #  # $ #####","####    #  $$ #   #","#### ## $         #","#.    ###  ########","#.. ..# ####","#...#.#","#.....#","#######"],"height":15,"id":"11","total_balls":14,"width":19},{"cells":["  #########","  #*.*#*.*#","  #.*.*.*.#","  #*.*.*.*#","  #.*.*.*.#","  #*.*.*.*#","  ###   ###","    #   #","###### ######","#           #","# $ $ $ $ $ #","## $ $ $ $ ##"," #$ $ $ $ $#"," #   $@$   #"," #  #####  #"," ####   ####"],"height":16,"id":"12","total_balls":34,"width":13},{"cells":["    #########","  ###   ##  #####","###      #  #   ####","#  $$ #$ #  #  ... #","# #  $#@$## # #.#. #","#  ## #$  #    ... #","# $#    $ # # #.#. #","#    ##  ##$ $ ... #","# $ ##   #  #$#.#. #","## $$  $   $  $... #"," #$  ######    ##  #"," #   #    ##########"," #####"],"height":13,"id":"13","total_balls":18,"width":20},{"cells":["################","#              #","# # ######     #","# #  $ $ $ $#  #","# #   $@$   ## ##","# # #$ $ $###...#","# #   $ $  ##...#","# ###$$$ $ ##...#","#     # ## ##...#","#####   ## ##...#","    #####     ###","        #     #","        #######"],"height":13,"id":"14","total_balls":15,"width":17},{"cells":["       ####","    ####  #","   ##  #  #","   #  $ $ #"," ### #$   ####"," #  $  ##$   #"," #  # @ $ # $#"," #  #      $ ####"," ## ####$##     #"," # $#.....# #   #"," #  $...*. $# ###","##  #.....#   #","#   ### #######","# $$  #  #","#  #     #","######   #","     #####"],"height":17,"id":"15","total_balls":15,"width":17},{"cells":["#####","#   ##","#    #  ####","# $  ####  #","#  $$ $   $#","###@ #$    ##"," #  ##  $ $ ##"," # $  ## ## .#"," #  #$##$  #.#"," ###   $..##.#","  #    #.*...#","  # $$ #.....#","  #  #########","  #  #","  ####"],"height":15,"id":"16","total_balls":15,"width":14},{"cells":["       #######"," #######     #"," #     # $@$ #"," #$$ #   #########"," # ###......##   #"," #   $......## # #"," # ###......     #","##   #### ### #$##","#  #$   #  $  # #","#  $ $$$  # $## #","#   $ $ ###$$ # #","#####     $   # #","    ### ###   # #","      #     #   #","      ########  #","             ####"],"height":16,"id":"17","total_balls":18,"width":18},{"cells":["      ############","      #  .  ##   #","      # #.     @ #"," ###### ##...# ####","##  ##...####     ####","# $ ##...    $ #  $  #","#     .. ## # ## ##  #","####$###$# $  #   # ##"," ###  #    ##$ $$ # #"," #   $$ # # $ # $## #"," #                  #"," #################  #","                 ####"],"height":13,"id":"18","total_balls":13,"width":22},{"cells":["        ######","        #   @####","      ##### $   #","      #   ##    ####","      # $##  ##    #","      #   #  ##### #","      # #$$ $    # #","      #  $ $ ### # #","      # #   $  # # #","      # #  #$#   # #","     ## ####   # # #","     #  $  ##### # # ####","    ##    $     $  ###  ####","#####  ### $ $# $ #   .....#","#     ##      #  ##  #.....#","# $$$$    ######$##   #.##.#","##    ##              #....#"," ##  ###############   ....#","  #  #             #####  ##","  ####                 ####"],"height":20,"id":"19","total_balls":20,"width":28},{"cells":["       ############","       #..........#","     ###.#.#.#.#..#","     #   .........#","     #@ $ $ $ *.*.#","    ####### #######"," ####   #    ##  #","##    $ #    # $ ##","#  #$# ### ###$   ##","# $  $ $   # $ $ $ #","#  # $ ##       #$ #","#   $####$####$##  #","####  ##   #    #  #","   #$ ##   # # $$  #","   #   # $ #  $    #","   ### # $$ #  $ ###","     # #    # $ ##","     # ######## #","     #          #","     ############"],"height":20,"id":"20","total_balls":29,"width":20},{"cells":["   ##########","   #..  #   #","   #..      #","   #..  #  ####","  #######  #  ##","  #            #","  #  #  ##  #  #","#### ##  #### ##","#  $  ##### #  #","# # $  $  # $  #","# @$  $   #   ##","#### ## #######","   #    #","   ######"],"height":14,"id":"21","total_balls":6,"width":16},{"cells":["            ####"," ############  #####"," #    #  #  $  #   ##"," # $ $ $  $ # $ $   #"," ##$ $   # @# $   $ #","###   ############ ##","#  $ $#  #......# $#","# #   #  #......## #","#  ## ## # .....#  #","# #      $...... $ #","# # $ ## #......#  #","#  $ $#  #......# $#","# $   #  ##$#####  #","# $ $ #### $ $  $ $#","## #     $ $ $ $   ###"," #  ###### $    $    #"," #         # ####### #"," ####### #$          #","       #   ###########","       #####"],"height":20,"id":"22","total_balls":35,"width":22},{"cells":["       #######","       #  #  ####","       # $#$ #  ##","########  #  #   ########","#....  # $#$ #  $#  #   #","#....# #     #$  #      #","#..#.    $#  # $    #$  #","#... @##  #$ #$  #  #   #","#.... ## $#     $########","########  #$$#$  #","       # $#  #  $#","       #  #  #   #","       ####  #####","          ####"],"height":14,"id":"23","total_balls":18,"width":25},{"cells":["   ##########","   #........####","   #.#.#....#  #","   #........$$ #","   #     .###  ####"," #########  $ #   #"," #     $   $ $  $ #"," #  #    #  $ $#  #"," ## #####   #  #  #"," # $     #   #### #","##  $#   # ##  #  #","#    ##$###    #  ##","# $    $ #  #  #   #","#####    # ## # ## ##","    #$# #  $  $ $   #","    #@#  $#$$$  #   #","    ###  $      #####","      ##  #  #  #","       ##########"],"height":19,"id":"24","total_balls":23,"width":21},{"cells":["               ####","          ######  #####","    #######       #   #","    #      $ $ ## # # #","    #  #### $  #     .#","    #      $ # # ##.#.#","    ##$####$ $ $ ##.#.#","    #     #    ####.###","    # $   ######  #.#.#","######$$$##      @#.#.#","#      #    #$#$###. .#","# #### #$$$$$    # ...#","# #    $     #   # ...#","# #   ## ##     ###...#","# ######$######  ######","#        #    #  #","##########    ####"],"height":17,"id":"25","total_balls":21,"width":23},{"cells":["#########","#       #","#       ####","## #### #  #","## #@##    #","# $$$ $  $$#","#  # ## $  #","#  # ##  $ ####","####  $$$ $#  #"," #   ##   ....#"," # #   # #.. .#"," #   # # ##...#"," ##### $  #...#","     ##   #####","      #####"],"height":15,"id":"26","total_balls":13,"width":15},{"cells":[" #################"," #...   #    #   ###","##.....  $## # # $ #","#......#  $  #  $  #","#......#  #  # # # ##","######### $  $ # #  ###","  #     #$##$ ## ##   #"," ##   $    # $  $   # #"," #  ## ### #  #####$# #"," # $ $$     $   $     #"," # $    $##$ ######## #"," #######  @ ##      ###","       ######"],"height":13,"id":"27","total_balls":20,"width":23},{"cells":["     #######","     #@ #  #","     # $   #","    ### ## #"," #### $  # ##"," #       #  ##"," # $ $#### $ #"," # $$ #  #  $#"," #$  $   #$  #","##  $$#   $$ ##","# $$  #  #  $ #","#     #### $  #","#  #$##..##   #","### .#....#####","  # .......##","  #....   ..#","  ###########"],"height":17,"id":"28","total_balls":20,"width":15},{"cells":["                #####","       ###### ###   ####","   #####    ### $ $  $ #","####  ## #$ $    $ #   #","#....   $$ $ $  $   #$##","#.. # ## #   ###$## #  #","#....    # ###    #    #","#....    # ##  $  ###$ #","#..######  $  #  #### ##","####    #   ###    @  #","        ###############"],"height":11,"id":"29","total_balls":16,"width":24},{"cells":[" #####"," #   #######"," # $ ###   #"," # $    $$ #"," ## ####   #","### #  # ###","#   #  #@##","# $$    $ #","#   # # $ ####","##### #   #  #"," #   $####   #"," #  $     $  #"," ##   ##### ##"," ##########  #","##....# $  $ #","#.....# $$#  #","#.. ..# $  $ #","#.....$   #  #","##  ##########"," ####"],"height":20,"id":"30","total_balls":18,"width":14},{"cells":[" #######"," #  #  #####","##  #  #...###","#  $#  #...  #","# $ #$$ ...  #","#  $#  #... .#","#   # $########","##$       $ $ #","##  #  $$ #   #"," ######  ##$$@#","      #      ##","      ########"],"height":12,"id":"31","total_balls":13,"width":15},{"cells":["  ####","  #  #########"," ##  ## @#   #"," #  $# $ $   ####"," #$  $  # $ $#  ##","##  $## #$ $     #","#  #  # #   $$$  #","# $    $  $## ####","# $ $ #$#  #  #","##  ###  ###$ #"," #  #....     #"," ####......####","   #....####","   #...##","   #...#","   #####"],"height":16,"id":"32","total_balls":20,"width":18},{"cells":["      ####","  #####  #"," ##     $#","## $  ## ###","#@$ $ # $  #","#### ##   $#"," #....#$ $ #"," #....#   $#"," #....  $$ ##"," #... # $   #"," ######$ $  #","      #   ###","      #$ ###","      #  #","      ####"],"height":15,"id":"33","total_balls":15,"width":13},{"cells":["############","##     ##  #","##   $   $ #","#### ## $$ #","#   $ #    #","# $$$ # ####","#   # # $ ##","#  #  #  $ #","# $# $#    #","#   ..# ####","####.. $ #@#","#.....# $# #","##....#  $ #","###..##    #","############"],"height":15,"id":"34","total_balls":15,"width":12},{"cells":["############  ######","#   #    #@####....#","#   $$#       .....#","#   # ###   ## ....#","## ## ###  #   ....#"," # $ $     # ## ####"," #  $ $##  #       #","#### #  #### ## ## #","#  # #$   ## ##    #","# $  $  # ## #######","# # $ $    # #","#  $ ## ## # #","# $$     $$  #","## ## ### $  #"," #    # #    #"," ###### ######"],"height":16,"id":"35","total_balls":17,"width":20},{"cells":["     ####","   ###  ##","####  $  #","#   $ $  ####","# $   # $   # ####","#  #  #   $ # #..#","##$#$ ####$####..#"," #   ##### ## ...#"," #$# ##@## ##  ..#"," # #    $     ...#"," #   #### ###  ..#"," ### ## #  ## ...#","  ##$ ####$ ###..#","  #   ##    # #..#"," ## $$##  $ # ####"," #     $$$$ #"," # $ ###    #"," #   # ######"," #####"],"height":19,"id":"36","total_balls":21,"width":18},{"cells":["###########","#......   #########","#......   #  ##   #","#..### $    $     #","#... $ $ #  ###   #","#...#$#####    #  #","###    #   #$  # $###","  #  $$ $ $  $##  $ #","  #  $   #$#  ##    #","  ### ## #  $ #######","   #  $ $ ## ##","   #    $  $  #","   ##   # #   #","    #####@#####","        ###"],"height":15,"id":"37","total_balls":20,"width":21},{"cells":[" #########"," #....   ##"," #.#.#  $ ##","##....# # @##","# ....#  #  ##","#     #$ ##$ #","## ###  $    #"," #$  $ $ $#  #"," # #  $ $ ## #"," #  ###  ##  #"," #    ## ## ##"," #  $ #  $  #"," ###$ $   ###","   #  #####","   ####"],"height":15,"id":"38","total_balls":14,"width":14},{"cells":["              ###","             ##.###","             #....#"," #############....#","##   ##     ##....#####","#  $$##  $ @##....    #","#      $$ $#  ....#   #","#  $ ## $$ # #....#  ##","#  $ ## $  # ## ###  #","## ##### ###         #","##   $  $ ##### ###  #","# $###  # ##### # ####","#   $   #       #","#  $ #$ $ $###  #","# $$$# $   # ####","#    #  $$ #","######   ###","     #####"],"height":18,"id":"39","total_balls":25,"width":23},{"cells":["      ####","####### @#","#     $  #","#   $## $#","##$#...# #"," # $...  #"," # #. .# ##"," #   # #$ #"," #$  $    #"," #  #######"," ####"],"height":11,"id":"40","total_balls":8,"width":11},{"cells":["           #####","          ##   ##","         ##     #","        ##  $$  #","       ## $$  $ #","       # $    $ #","####   #   $$ #####","#  ######## ##    #","#..           $$$@#","#.# ####### ##   ##","#.# #######. #$ $###","#........... #   $ #","##############  $  #","             ##  ###","              ####"],"height":15,"id":"41","total_balls":16,"width":20},{"cells":[" ########"," #@##   ####"," # $   $   #"," #  $ $ $$$#"," # $$# #   #","##$    $   #","#  $  $$$$$##","# $#### #   #","#  $....#   #","# ##....#$$ #","# ##....   ##","#   ....#  #","## #....#$$#"," # #....#  #"," #         #"," #### ##$###","    #    #","    ######"],"height":18,"id":"42","total_balls":24,"width":13},{"cells":["    ############","    #          ##","    #  # #$$ $  #","    #$ #$#  ## @#","   ## ## # $ # ##","   #   $ #$  # #","   #   # $   # #","   ## $ $   ## #","   #  #  ##  $ #","   #    ## $$# #","######$$   #   #","#....#  ########","#.#... ##","#....   #","#....   #","#########"],"height":16,"id":"43","total_balls":16,"width":17},{"cells":["      ######","   #####   #","   #   # # #####","   # $ #  $    ######","  ##$  ### ##       #","###  $$ $ $ #  ##   #####","#       $   ###### ##   #","#  ######## #@   # #  # #","## ###      #### #$# #  #"," # ### #### ##.. #   $ ##"," #  $  $  #$##.. #$##  ##"," #  # # #     ..## ## $ #"," ####   # ## #..#    $  #","    #####    #..# # #  ##","        ######..#   # ##","             #..#####  #","             #..       #","             ##  ###  ##","              #########"],"height":19,"id":"44","total_balls":16,"width":25},{"cells":["        #######","    #####  #  ####","    #   #   $    #"," #### #$$ ## ##  #","##      # #  ## ###","#  ### $#$  $  $  #","#...    # ##  #   #","#...#    @ # ### ##","#...#  ###  $  $  #","######## ##   #   #","          #########"],"height":11,"id":"45","total_balls":9,"width":19},{"cells":["    #########  ####","    #   ##  ####  #","    #   $   #  $  #","    #  # ## #     ####","    ## $   $ $$# #   #","    ####  #  # $ $   #","#####  ####    ###...#","#   #$ #  # ####.....#","#      #  # # ##.....#","###### #  #$   ###...#","   #   ## # $#   #...#","  ##       $  $# #####"," ## $$$##  # $   #"," #   #  # ###  ###"," #   $  #$ @####"," #####  #   #","     ########"],"height":17,"id":"46","total_balls":19,"width":22},{"cells":[" #####"," #   #"," # # ######"," #      $@######"," # $ ##$ ###   #"," # #### $    $ #"," # ##### #  #$ ####","##  #### ##$      #","#  $#  $  # ## ## #","#         # #...# #","######  ###  ...  #","     #### # #...# #","          # ### # #","          #       #","          #########"],"height":15,"id":"47","total_balls":9,"width":19},{"cells":["       ####","       #  ##","       #   ##","       # $$ ##","     ###$  $ ##","  ####    $   #","###  # #####  #","#    # #....$ #","# #   $ ....# #","#  $ # #.*..# #","###  #### ### #","  #### @$  ##$##","     ### $     #","       #  ##   #","       #########"],"height":15,"id":"48","total_balls":12,"width":16},{"cells":["      ############","     ##..    #   #","    ##..* $    $ #","   ##..*.# # #$ ##","   #..*.# # # $  #","####...#  #    # #","#  ## #          #","# @$ $ ###  # # ##","# $   $   # #   #","###$$   # # # # #","  #   $   # # #####","  # $# #####      #","  #$   #   #   #  #","  #  ###   ##     #","  #  #      #    ##","  ####      ######"],"height":16,"id":"49","total_balls":16,"width":19},{"cells":["     #############","     #    ###    #","     #     $ $  ####","   #### #   $ $    #","  ## $  #$#### $ $ #","###   # #   ###  $ #","# $  $  #  $  # ####","# ##$#### #$#  $  ###","# ##  ### # # #  $  #","#    @$   $   # $ # #","#####  #  ##  # $#  #","  #... #####$  #  # #","  #.......# $$ #$ # #","  #.......#         #","  #.......#######  ##","  #########     ####"],"height":16,"id":"50","total_balls":24,"width":21},{"cells":["##### ####","#...# #  ####","#...###  $  #","#....## $  $###","##....##   $  #","###... ## $ $ #","# ##    #  $  #","#  ## # ### ####","# $ # #$  $    #","#  $ @ $    $  #","#   # $ $$ $ ###","#  ######  ###","# ##    ####","###"],"height":14,"id":"51","total_balls":17,"width":16},{"cells":[" ####","##  #####","#       # #####","# $###  ###   #","#..#  $# #  # #","#..#      $$# ###","#.*# #  #$ $    #####","#..#  ##     ##$#   #","#.*$  $ # ##  $     #","#..##  $   #   ######","#.*##$##   #####","#..  $ #####","#  # @ #","########"],"height":14,"id":"52","total_balls":16,"width":21},{"cells":["   ##########","   #  ###   #","   # $   $  #","   #  ####$##","   ## #  #  #","  ##  #.*   #","  #  ##..#  #","  # @ #.*# ##","  # #$#..#$ #","  # $ #..#  #","  # # #**#  #","  # $ #..#$##","  #    .*#  #"," ###  #  #  #","##    ####  #","#  #######$##","# $      $  #","#  ##   #   #","#############"],"height":19,"id":"53","total_balls":16,"width":13},{"cells":[" #####################"," #   ##  #   #   #   #"," # $     $   $   $   ##","##### #  #   ### ##$###","#   # ##$######   #   #","# $   # ......#   # $ #","## #  # ......#####   #","## #########..#   # ###","#          #..# $   #","# ## ### ###..## #  ###","# #   #   ##..## ###  #","#   @      $..#       #","# #   #   ##  #   ##  #","##### ############## ##","#          #   #    $ #","# $  # $ $ $   # #    #","# #$## $#  ## ##    # #","#  $ $$ #### $  $ # # #","#          #   #      #","#######################"],"height":20,"id":"54","total_balls":22,"width":23},{"cells":[" #####################","##                   #","#    $ #      ## #   #","#  ###### ###  #$## ##","##$#   ##$#....   # #","#  #    $ #....## # #","# $ # # # #....##   #","# $ #$$   #....##$# #","# # $@$##$#....##   #","#   $$$   #....#    #","#  $#   # ###### $###","##  # ###$$  $   $ #","##     # $  $ ##   #"," #####   #   #######","     #########"],"height":15,"id":"55","total_balls":24,"width":22},{"cells":["##########","#        ####","# ###### #  ##","# # $ $ $  $ #","#       #$   #","###$  $$#  ###","  #  ## # $##","  ##$#   $ @#","   #  $ $ ###","   # #   $  #","   # ##   # #","  ##  ##### #","  #         #","  #.......###","  #.......#","  #########"],"height":16,"id":"56","total_balls":14,"width":14},{"cells":["         ####"," #########  ##","##  $      $ #####","#   ## ##   ##...#","# #$$ $ $$#$##...#","# #    @  #   ...#","#  $# ###$$   ...#","# $  $$  $ ##....#","###$       #######","  #  #######","  ####"],"height":11,"id":"57","total_balls":16,"width":18},{"cells":["              ######","          #####    #","          #  ## #  #####","          #   *.#..#   #"," ##### #### $#.#...    #"," #   ###  ## #*....## ##"," # $      ## #..#..## #","###### #   # #*.##### #","#   # $#$# # #..##### #","# $  $     # #*.    # #","## ##  $ ### #  ##  # #"," #  $  $ ### ##### ## #"," ###$###$###  #### ## #","#### #         ###  # #","#  $ #  $####  ###$$#@#####","#      $ # #  ####  #$#   #","#### #  $# #              #","   #  $  # ##  ##  ########","   ##  ###  ########","    ####"],"height":20,"id":"58","total_balls":23,"width":27},{"cells":["         ####","         #  #","         #  ########","   #######  #      #","   #   # # # # #   ##","   # $     $  ##  $ #","  ### $# #  # #     #########","  #  $  #  $# # $$ #   # #  #"," ## #   #     ###    $ # #  #"," #  #$   # ###  #  # $$# #  #"," #    $## $  #   ## $  # # ##","####$ $ #    ##  #   $    ..#","#  #    ### # $ $ ###  ###.*#","#     ##  $$ @  $     ##....#","#  ##  ##   $  #$#  ##....*.#","## #  $  # # $##  ##....*.###","## ##  $  # $ #  #....*.###","#    $ ####   # ....*.###","#   #  #  #  #  ..*.###","########  ###########"],"height":20,"id":"59","total_balls":36,"width":29},{"cells":["        #####","        #   ####","        # $    ####  ####","        #   # $#  ####  #","########### #   $   #   #","#..     # $  #### #  #  #","#..$  #   $  #  $ # $ .##","#.*# # $ $ ##  ##    #.#","#..#$ @ #   ##    $$ #.#","#..# $ $  $ $ ##   ## .#","#.*$$ # ##   $ #$# $ #.#","#..#      ##   #     #.#","#..#######  ### ######.##","# $$                  *.##","#  ##################  ..#","####                ######"],"height":16,"id":"60","total_balls":27,"width":26},{"cells":["######","#@ $ .#","######"],"height":3,"id":"61","total_balls":1,"width":7},{"cells":["######","#@ $ .#","######"],"height":3,"id":"62","total_balls":1,"width":7},{"cells":["######","#@ $ .#","######"],"height":3,"id":"63","total_balls":1,"width":7}],"max_height":20,"max_width":29,"title":"Spiros 05","total":60};
            let sokoban;
            let levelSelected = false;

            function updateButtons() {
                $('#btn').empty();
                for (let i = 0; i < json.levels.length; i++) {
                    let btn = $('<button />').css('border', '1px solid black').append('LVL ' + (i+1)).appendTo('#btn');

                    if (getCookieLvl(i+1)) btn.css('background-color', 'green');

                    btn.click(function () {
                        levelSelected = true;
                        sokoban = new Sokoban();
                        sokoban.initTable(json.levels[i], json.levels[i].id);
                    });
                }

                let btn = $('<button />').css('border', '1px solid black').append('LEVEL BUILDER').appendTo('#btn');
                btn.click(function () {
                    levelSelected = true;
                    sokoban = new Sokoban();
                    new Builder(sokoban);
                });
            }

            updateButtons();

            $(document).keypress(function(e) {
                if (levelSelected) {
                    sokoban.checkMove(e.which);
                    if (sokoban.won) {
                        alert('gg');
                        if (!getCookieLvl(sokoban.level)) {
                            setCookie(getCookie() + ',' + sokoban.level);
                            updateButtons();
                        }
                    }
                }
            });
        });

        function logout(){
            $.ajax({
                url: "php/logout.php",
                success: function () {
                    showForm();
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
                        if (data.logged === true) {
                            alert(data.message);
                            showGame();

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
         //   $("body").append("<div id=\"btn\"></div>\n" +
         //       "<div id=\"game\"></div>\n");
        }

        function showForm(){
            $("#logoutbutton").hide();
            $("#aboutli").hide()
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
            $("#btn").hide();
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

<div id="btn"></div>
<div id="game"></div>
</body>