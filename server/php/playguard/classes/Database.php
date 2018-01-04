<?php
include './config/config.php';
include './classes/Player.php';
include './classes/Playtime.php';

class Database {
  public $mysqli;

  function __construct() {
    global $config;
    $this->mysqli = new mysqli($config['dbhost'], $config['dbuser'], $config['dbpassword'], $config['dbname']);
    if (!mysqli_select_db($this->mysqli, $config['dbname'])) {
      $this->error();
    }
  }
  
  function error() {
    printf('Datenbank-Fehler: Nummer %d, Meldung %s', $this->mysqli->errno, $this->mysqli->error);
    die;
  }

  function getPlayers() {
    $statement = $this->mysqli->prepare('SELECT * FROM Player');
    if (!$statement) {
      $this->error();
    }
    $statement->execute();
    $result = $statement->get_result();
    $players = array();
    while($row = $result->fetch_object()) {
      $player = new Player();
      $player->login = $row->login;
      $player->email = $row->email;
      $player->administrator = $row->administrator;
      $player->maxPerDay = $row->max_per_day;
      $player->maxPerWeek = $row->max_per_week;
      $player->extraDay = Time::getTimestamp($row->extra_day);
      $player->extraMaxPerDay = $row->extra_max_per_day;
      $player->extraMaxPerWeek = $row->extra_max_per_week;
      $player->extraComment = $row->extra_comment;
      $player->lockedUntil = $row->locked_until;
      $player->loginSource = $row->login_source;
      $player->loginIp = $row->login_ip;
      $player->loginDate = Time::getTimestamp($row->login_date);
      $player->logoutDate = Time::getTimestamp($row->logout_date);
      $player->confirmDate = Time::getTimestamp($row->confirm_date);
      $player->playedDay = $row->played_day;
      $player->playedWeek = $row->played_week;
      $player->passwordHash = $row->password_hash;
      $players[$player->login] = $player;
    }
    $statement->close();
    return $players;
  }
  
  function createPlayer($player) {
    $statement = $this->mysqli->prepare('INSERT INTO Player (login, max_per_day, max_per_week VALUES (?, ?, ?)');
    if (!$statement) {
      $this->error();
    }
    $statement->bind_param('sii', $player->login, $player->maxPerDay, $player->maxPerWeek);
    $statement->execute();
    $statement->close();    
  }
  
  function updatePlayerEmail($player) {
    $statement = $this->mysqli->prepare('UPDATE Player SET email = ? WHERE login = ?');
    if (!$statement) {
      $this->error();
    }
    $statement->bind_param('ss', $player->email, $player->login);
    $statement->execute();
    $statement->close();
  }
  
  function updatePlayerPassword($player) {
    $statement = $this->mysqli->prepare('UPDATE Player SET password_hash = ? WHERE login = ?');
    if (!$statement) {
      $this->error();
    }
    $statement->bind_param('ss', $player->passwordHash, $player->login);
    $statement->execute();
    $statement->close();
  }

  function updatePlayerQuota($player) {
    $statement = $this->mysqli->prepare('UPDATE Player SET max_per_day = ?, max_per_week = ? WHERE login = ?');
    if (!$statement) {
      $this->error();
    }
    $statement->bind_param('iis', $player->maxPerDay, $player->maxPerWeek, $player->login);
    $statement->execute();
    $statement->close();
  }

  function updatePlayerExtra($player) {
    $statement = $this->mysqli->prepare('UPDATE Player SET extra_day = FROM_UNIXTIME(?), extra_max_per_day = ?, extra_max_per_week = ?, extra_comment = ? WHERE login = ?');
    if (!$statement) {
      $this->error();
    }
    $statement->bind_param('iiiss', $player->extraDay, $player->extraMaxPerDay, $player->extraMaxPerWeek, $player->extraComment, $player->login);
    $statement->execute();
    $statement->close();
  }

  function updatePlayerLoginData($player) {
    $statement = $this->mysqli->prepare('UPDATE Player SET login_date = FROM_UNIXTIME(?), login_source = ?, login_ip = ?, confirm_date = FROM_UNIXTIME(?), logout_date = FROM_UNIXTIME(?), played_day = ?, played_week = ? WHERE login = ?');
    if (!$statement) {
      $this->error();
    }
    $statement->bind_param('issiiiis', $player->loginDate, $player->loginSource, $player->loginIp, $player->confirmDate, $player->logoutDate, $player->playedDay, $player->playedWeek, $player->login);
    $statement->execute();
    $statement->close();
  }

  function updatePlayerLock($player) {
    $statement = $this->mysqli->prepare('UPDATE Player SET locked_until = FROM_UNIXTIME(?) WHERE login = ?');
    if (!$statement) {
      $this->error();
    }
    $statement->bind_param('i', $player->lockedUntil, $player->login);
    $statement->execute();
    $statement->close();
  }
  function getPlaydays($login, $start, $end) {
    $statement = $this->mysqli->prepare('SELECT * FROM Playtime WHERE login = ? AND login_date >= FROM_UNIXTIME(?) AND login_date <= FROM_UNIXTIME(?)');
    if ( !$statement ) {
      $this->error();
    }
    $statement->bind_param('sii', $login, $start, $end);
    $statement->execute();
    $result = $statement->get_result();
    $playdays = new Playdays();
    while($row = $result->fetch_object()) {
      $playtime = new Playtime();
      $playtime->login = $row->login;
      $playtime->loginSource = $row->login_source;
      $playtime->loginIp = $row->login_ip;
      $playtime->loginDate = Time::getTimestamp($row->login_date);
      $playtime->logoutDate = Time::getTimestamp($row->logout_date);
      $playtime->logoutConfirmed = $row->logout_confirmed;
      $playdays->add($playtime);
    }
    $statement->close();
    return $playdays;
  }
  
  function savePlaytime($playtime) {
    $statement = $this->mysqli->prepare('INSERT INTO Playtime (login, login_source, login_ip, login_date, logout_date, logout_confirmed) VALUES (?, ?, ?, FROM_UNIXTIME(?), FROM_UNIXTIME(?), ?)');
    if ( !$statement ) {
      $this->error();
    }
    $statement->bind_param('sssiii', $playtime->login, $playtime->loginSource, $playtime->loginIp, $playtime->loginDate, $playtime->logoutDate, $playtime->logoutConfirmed);
    $statement->execute();
    $statement->close();
  }
}
?>