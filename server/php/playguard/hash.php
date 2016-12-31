<?php 
include('./classes/Main.php');
$login = Main::getLogin();
$password = $_SERVER['PHP_AUTH_PW'];
echo Player::getPasswordHash($password);
?>
