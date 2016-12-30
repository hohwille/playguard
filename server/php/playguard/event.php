<?php 
include('./classes/Main.php');
$now=time();
$main = new Main();
$player = $main->getPlayerLoggedIn();
$command = $_GET['cmd'];
$source = $_GET['src'];
$ip = $_SERVER['REMOTE_ADDR'];
$rest = $player->getRest();
if ($command == 'login') {
  if (($player->loginDate != NULL) && ($player->logoutDate == NULL)) { // login without being logged-out?
    $logoutMin = $player->confirmDate + $config['reloginDelay'];
    if ($now < $logoutMin) {
      header('HTTP/1.1 903 Already Logged-In');
      echo '0';
      exit;
    }
    // auto-logout
    $logoutMax = $player->confirmDate + $config['reloginMaxPenalty'];
    if ($now > $logoutMax) {
      $player->logoutDate = $logoutMax;
    } else {
      $player->logoutDate = $now;
    }
    $playtime = new Playtime();
    $playtime->fromPlayer($player);
    $playtime->logoutConfirmed = false;
    $main->getDatabase()->savePlaytime($playtime);
  }
  if ($rest == 0) {
    header('HTTP/1.1 908 Playtime Over');
    echo '0';
    exit;
  }
  $player->loginDate = $now;
  $player->confirmDate = $now;
  $player->logoutDate = NULL;
  $player->loginSource = $source;
  $player->loginIp = $ip;
  $main->getDatabase()->updatePlayerLoginData($player);
} else if ($command == 'confirm') {
  if ($source != $player->loginSource) {
    header('HTTP/1.1 909 Source Missmatch');
    echo '0';
    exit;    
  }
  $player->confirmDate = $now;
  $playtime->getDatabase()->updatePlayerLoginData($player);
} else if ($command == 'logout') {
  if ($source != $player->loginSource) {
    header('HTTP/1.1 909 Source Missmatch');
    echo '0';
    exit;    
  }
  $player->logoutDate = $now;
  $playtime = new Playtime();
  $playtime->fromPlayer($player);
  $main->getDatabase()->savePlaytime($playtime);
  $main->getDatabase()->updatePlayerLoginData($player);
} else {
  header('HTTP/1.1 501 Not Implemented');
  echo 'Unsupported event command: ' . $command;
  exit;  
}
header('HTTP/1.1 200 OK');
echo $rest;  
?>
