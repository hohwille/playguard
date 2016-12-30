<?php

class Playtime {
  public $login;
  public $loginSource;
  public $loginIp;
  public $loginDate;
  public $logoutDate;
  public $logoutConfirmed;
  private $day;
  private $duration;

  function __construct() {
  }
  
  function fromPlayer($player) {
    $this->login = $player->login;
    $this->loginSource = $player->loginSource;
    $this->loginIp = $player->loginIp;
    $this->loginDate = $player->loginDate;
    $this->logoutDate = $player->logoutDate;
    $this->logoutConfirmed = true;
    return $this;
  }
  
  function getDay() {
    if ($this->day == NULL) {
      $this->day = Time::getDay($this->loginDate);
    }
    return $this->day;
  }
  
  function getDuration() {
    if ($this->duration == NULL) {
      $this->duration = $this->logoutDate - $this->loginDate;
    }
    return $this->duration;
  }
}

class Playday {
  public $times;
  public $total;
  public $day;

  function __construct($day) {
    $this->times = array();
    $this->day = $day;
  }

  function add($playtime) {
    $times[] = $playtime;
    $this->total = $this->total + $playtime->getDuration();
  }
}

class Playdays {
  public $days;
  public $total;

  function __construct() {
    $this->days = array();
  }

  function add($playtime) {
    $day = $playtime->getDay();
    if ($this->days[$day] == NULL) {
      $this->days[$day] = new Playday($day);
    }
    $this->days[$day].add($playtime);
    $this->total = $this->total + $playtime->getDuration();
  }
}
