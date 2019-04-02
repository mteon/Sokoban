<?php
/**
 * Created by PhpStorm.
 * User: teoma
 * Date: 02/04/2019
 * Time: 13:53
 */

session_start();

$logged = false;

if(isset($_SESSION['username'])){
    $logged = true;
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

echo json_encode($logged);