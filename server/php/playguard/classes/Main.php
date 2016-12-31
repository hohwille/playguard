<?php
include('./classes/Database.php');
date_default_timezone_set($config['timezone']);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
if ($_SERVER['HTTP_ACCEPT_LANGUAGE'] == NULL) {
  $lang = $config['locale'];
} else {
  $lang = preg_replace("/([a-z]{2}).*/", "$1", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
}
$i18n = './i18n/' . $lang . '.php';
if (!file_exists($i18n)) {
  $i18n = './i18n/en.php';
}
include($i18n);

class Main {

  private $database;
  
  private $players;
  
  function getDatabase() {
    if ($this->database == NULL) {
      $this->database = new Database();
    }
    return $this->database;
  }
  
  function getPlayers() {
    if ($this->players == NULL) {
      $this->players = $this->getDatabase()->getPlayers();
    }
    return $this->players;
  }
  
  function getPlayer($login) {
    $player = $this->getPlayers()[$login];
    if ($player == NULL) {
      $player = new Player();
      $player->login = $login;
    }
    return $player;
  }

  public static function authenticationRequired() {
    header('WWW-Authenticate: Basic realm="Playguard"');
    header('HTTP/1.1 401 Unauthorized');
    echo 'Unauthorized';
    exit;
  }
  
  public static function getLogin() {
    $login = $_SERVER['PHP_AUTH_USER'];
    if ($login == NULL) {
      Main::authenticationRequired();
    }
    return $login;    
  }

  public function getPlayerLoggedIn() {
    $login = Main::getLogin();
    $player = $this->getPlayers()[$login];
    if ($player == NULL) {
      Main::authenticationRequired();
    }
    if (!$player->checkPassword($_SERVER['PHP_AUTH_PW'])) {
      Main::authenticationRequired();
    }
    return $player;
  }
  
}
?>