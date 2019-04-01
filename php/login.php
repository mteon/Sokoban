<?php
/**
 * Created by PhpStorm.
 * User: teoma
 * Date: 31/03/2019
 * Time: 00:58
 */

session_start();
$data = new stdClass();
$data->logged = false;
$data->message = "Identifiants incorrects, veuillez rééssayer ";
if (isset($_POST['username']) && isset($_POST['password'])) {
    $userjson = json_decode(file_get_contents('../json/users.json'));
    foreach ($userjson->users as $user) {
        if ($user->username == $_POST['username'] && $user->password == $_POST['password']) {
            $_SESSION['username'] = $_POST['username'];
            $_SESSION['level'] = $user->level;
            $data->logged = true;
            $data->message = 'Connecté, Bienvenu '.$_SESSION['username'];
        }
    }
}
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode($data);;