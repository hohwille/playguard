<?php 
include('./classes/Main.php');
$now=time();
$main = new Main();
$player = $main->getPlayerLoggedIn();
$command = $_GET['cmd'];
$source = $_GET['src'];
if ($command == 'login') {
  $main->login($player, $now, $source);
} else if ($command == 'logout') {
  $main->logout($player, $now, $source);
} else if ($command == 'confirm') {
  $player->verifySource($source);
  $player->confirmDate = $now;
  $main->getDatabase()->updatePlayerLoginData($player);
} else {
  header('HTTP/1.1 501 Not Implemented');
  echo 'Unsupported event command: ' . $command;
  exit;  
}
$remaining = $player->getRest();
$main->respondRemaintingTime($remaining);
?>
