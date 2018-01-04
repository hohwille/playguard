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
  
  function savePlayer($player) {
    $existing = $this->getPlayers()[$player->login];
    if ($existing == NULL) {
      $this->getDatabase()->createPlayer($player);
    } else {
      if ($player->email != $existing->email) {
        $this->getDatabase()->updatePlayerEmail($player);
      }
      if (($player->maxPerDay != $existing->maxPerDay) || ($player->maxPerWeek != $existing->maxPerWeek)) {
        $this->getDatabase()->updatePlayerQuota($player);
      }
      if (($player->extraDay != $existing->extraDay) || ($player->maxExtraDay != $existing->maxExtraDay) || ($player->maxExtraWeek != $existing->maxExtraWeek)) {
        $this->getDatabase()->updatePlayerExtra($player);
      }
      if ($player->lockedUntil != $existing->lockedUntil) {
        $this->getDatabase()->updatePlayerLock($player);
      }
    }
  }
  
  function redirectToAdmin() {
    $url = ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI'])  . '/admin.php';
    header('Location: ' . $url, true, 302);
  }

  public static function forbidden() {
    Main::loginFailure(403, 'Forbidden');
  }
  
  public static function authenticationRequired() {
    Main::loginFailure(401, 'Unauthorized');
  }
  
  public static function loginFailure($code, $status) {
    header('HTTP/1.1 ' . $code . ' ' . $status, true, $code);
    header('WWW-Authenticate: Basic realm="Playguard"');
    echo '<html><body><h1>' . $status . '</h1></body></html>';
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
  
  public function getAdminLoggedIn(){
    $player = $this->getPlayerLoggedIn();
    if (!$player->administrator) {
      if ($_GET['relogin']) {
        Main::authenticationRequired();        
      }
      $this->respond('403 Forbidden', '<a href="' . 'admin.php?relogin=true' . '">Administrator permission required</a>');
      exit;
    }
    return $player;
  }

  public function login($player, $loginDate, $source, $minSec) {
    global $config;
    if ($player->getRest() < $minSec) {
      return;
    }
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
  
  public function respondRemaintingTime($remaining, $minSec) {
    if ($remaining < $minSec) {
      $this->respond('908 Playtime Over', $remaining);
    } else {
      $this->respond('200 OK', $remaining);
    }
  }

  public function respond($status, $body) {
    header('HTTP/1.1 ' . $status);
    echo $body;
  }
  
  public static function getString($name, $array, $default = NULL) {
    $string = $array[$name];
    $string = trim($string);
    if (empty($string)) {
      if ($default === NULL) {
        exit('Missing required parameter: ' . $name);        
      } else  {
        return $default;
      }
    }
    return $string;
  }
  
  public static function parseEmail($email) {
    if ($email == NULL) {
      return NULL;
    }
    $email = trim($email);
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
      exit('Invalid Email: ' . $email);
    }
    return $email;
  }
}
?>
