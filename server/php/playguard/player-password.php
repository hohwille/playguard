<?php 
include('./classes/Main.php');
$now=time();
$main = new Main();
$admin = $main->getAdminLoggedIn();
$player = new Player();
$player->login = $_POST['login'];
$player->setPassword($_POST['password']);
$main->getDatabase()->updatePlayerPassword($player);
$main->redirectToAdmin();
?>
