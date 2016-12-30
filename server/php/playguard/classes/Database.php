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
      $player->maxPerDay = $row->max_per_day;
      $player->maxPerWeek = $row->max_per_week;
      $player->extraDay = $row->extra_day;
      $player->maxExtraDay = $row->max_extra_day;
      $player->maxExtraWeek = $row->max_extra_week;
      $player->lockedUntil = $row->locked_until;
      $player->loginSource = $row->loginSource;
      $player->loginIp = $row->login_ip;
      $player->loginDate = $row->login_date;
      $player->logoutDate = $row->logout_date;
      $player->confirmDate = $row->confirm_date;
      $player->playedDay = $row->played_day;
      $player->playedWeek = $row->played_week;
      $players[$player->login] = $player;
    }
    $statement->close();
    return $players;
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
    $statement = $this->mysqli->prepare('UPDATE Player SET extra_day = FROM_UNIXTIME(?), max_extra_day = ?, max_extra_week = ? WHERE login = ?');
    if (!$statement) {
      $this->error();
    }
    $statement->bind_param('iiis', $player->extraDay, $player->maxExtraDay, $player->maxExtraWeek, $player->login);
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
      $playtime->loginDate = $row->login_date;
      $playtime->logoutDate = $row->logout_date;
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
