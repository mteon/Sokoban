<?php
/**
 * Created by PhpStorm.
 * User: teoma
 * Date: 28/03/2019
 * Time: 20:06
 */

session_start();
$data = new stdClass();
$data->logged = false;
$data->message = 'Identifiants incorrects';
if (isset($_POST['username']) && isset($_POST['password'])) {
    $userjson = json_decode(file_get_contents('json/users.json'));
    // Parcours de tous les utilisateurs stockés dans le JSON
    foreach ($userjson->users as $user) {
        // Si l'id et mdp match l'itération actuelle
        if ($user->username == $_POST['username'] && $user->password == $_POST['password']) {
            $_SESSION['username'] = $_POST['username'];
            $_SESSION['level'] = $user->role;
            $data->logged = true;
            $data->message = 'Connecté, Bienvenu '.$_SESSION['username'];
        }
    }
}
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode($data);
