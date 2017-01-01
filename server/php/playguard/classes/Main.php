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

  public function login($player, $loginDate, $source) {
    global $config;
    if (($player->loginDate != NULL) && ($player->logoutDate == NULL)) { // implicit force logout (e.g. from new login)?
      $logoutMin = $player->confirmDate + $config['reloginDelay'];
      if ($loginDate < $logoutMin) {
        $this->respond('903 Already Logged-In', 0);
        exit;
      }
      // auto-logout
      $logoutMax = $player->confirmDate + $config['reloginMaxPenalty'];
      if ($loginDate > $logoutMax) {
        $logoutDate = $logoutMax;
      } else {
        $logoutDate = $loginDate;
      }
      $this->doLogout($player, $logoutDate, $source, false);
    }
    $remaining = $player->getRest();
    if ($remaining == 0) {
      $this->respondRemaintingTime($remaining);
      exit;
    }
    if ($player->loginDate != NULL) {
      $previousLoginDay = Time::getDay($player->loginDate);
      $currentLoginDay = Time::getDay($loginDate);
      $daysSincePreviousLogin = $currentLoginDay->diff($previousLoginDay)->format('%a');
      if ($daysSincePreviousLogin > 0) {
        if (($daysSincePreviousLogin < 8) && ($previousLoginDay->format('W') == $currentLoginDay->format('W'))) {
          $player->playedWeek = $player->playedWeek + $player->playedDay;
        } else {
          $player->playedWeek = 0;
        }       
        $player->playedDay = 0;
      }
    }
    $player->loginDate = $loginDate;
    $player->confirmDate = $loginDate;
    $player->logoutDate = NULL;
    $player->loginSource = $source;
    $player->loginIp = $_SERVER['REMOTE_ADDR'];
    $this->getDatabase()->updatePlayerLoginData($player);
  }

  public function logout($player, $logoutDate, $source) {
    $this->doLogout($player, $logoutDate, $source, true);
    $this->getDatabase()->updatePlayerLoginData($player);
  }
  
  private function doLogout($player, $logoutDate, $source, $logoutConfirmed) {
    if ($player->logoutDate != NULL) { // already logged out?
      $this->respond('904 Already Logged-Out', 0);
      exit;        
    }
    if ($logoutConfirmed) {
      $player->verifySource($source);
    }
    $player->logoutDate = $logoutDate;
    $playtime = new Playtime();
    $playtime->fromPlayer($player);
    $playtime->logoutConfirmed = $logoutConfirmed;
    $player->playedDay = $player->playedDay + $playtime->getDuration();
    $this->getDatabase()->savePlaytime($playtime);
  }
  
  public function respondRemaintingTime($remaining) {
    if ($remaining == 0) {
      $this->respond('908 Playtime Over', $remaining);
    } else {
      $this->respond('200 OK', $remaining);
    }
  }

  public function respond($status, $body) {
    header('HTTP/1.1 ' . $status);
    echo $body;
  }
}
?>