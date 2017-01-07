<?php 
include('./classes/Main.php');
$main = new Main();
$admin = $main->getAdminLoggedIn();
$player = new Player();
$player->login = Main::getString('login', $_POST);
$player->email = Main::parseEmail($_POST['email']);
$player->maxPerDay = Time::parseSeconds($_POST['maxPerDay']);
$player->maxPerWeek = Time::parseSeconds($_POST['maxPerWeek']);
$player->extraDay = Time::parseDate($_POST['extraDay']);
$player->extraMaxPerDay = Time::parseSeconds($_POST['extraMaxPerDay']);
$player->extraMaxPerWeek = Time::parseSeconds($_POST['extraMaxPerWeek']);
$player->extraComment = Main::getString('extraComment', $_POST, '');
$player->lockedUntil = Time::parseDate($_POST['lockedUntil']);
$main->savePlayer($player);
$main->redirectToAdmin();
?>