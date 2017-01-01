<?php
include './classes/Time.php';

class Player {
  public $login;
  public $maxPerDay;
  public $maxPerWeek;
  public $extraDay;
  public $maxExtraDay;
  public $maxExtraWeek;
  public $lockedUntil;
  public $loginSource;
  public $loginIp;
  public $loginDate;
  public $logoutDate;
  public $confirmDate;
  public $playedDay;
  public $playedWeek;
  public $passwordHash;

  function isLocked() {
    if ($this->lockedUntil) {
      return time() < $this->lockedUntil;
    }
    return false;
  }
  
  function isExtraDay() {
    return Time::isToday($this->extraDay);
  }
  
  function isExtraWeek() {
    return Time::isThisWeek($this->extraDay);
  }
  
  function getMaxToday() {
    if ($this->isLocked()) {
      return 0;
    }
    if ($this->maxExtraDay && $this->isExtraDay()) {
      return $this->maxExtraDay;
    }
    return $this->maxPerDay;
  }
  
  function getMaxThisWeek() {
    if ($this->isLocked()) {
      return 0;
    }
    if ($this->maxExtraWeek && $this->isExtraWeek()) {
      return $this->maxExtraWeek;
    }
    return $this->maxPerWeek;
  }

  function getPlayedToday() {
    global $config;
    if (Time::isToday($this->loginDate)) {
      $played = 0;
      if ($this->logoutDate == NULL) {
        $played = (time() - $this->loginDate - $config['loginLogoutDelay']);
        if ($played < 0) {
          $played = 0;
        }
      }
      return $this->playedDay + $played;
    }
    return 0;
  }
  
  function getPlayedThisWeek() {
    if (Time::isThisWeek($this->loginDate)) {
      return $this->playedWeek + $this->getPlayedToday();
    }
    return 0;
  }
  
  function getRestToday() {
    $rest = $this->getMaxToday() - $this->getPlayedToday();
    if ($rest < 0) {
      return 0;
    }
    return $rest;
  }

  function getRestThisWeek() {
    $rest = $this->getMaxThisWeek() - $this->getPlayedThisWeek();
    if ($rest < 0) {
      return 0;
    }
    return $rest;
  }

  function getRest() {
    $rest = $this->getRestToday();
    if ($rest == 0) {
      return 0;
    }
    $restThisWeek = $this->getRestThisWeek();
    if ($restThisWeek < $rest) {
      $rest = $restThisWeek;
    }
    return $rest;
  }
  
  public static function getPasswordHash($password) {
    return password_hash($password, PASSWORD_BCRYPT);
  }
  
  function setPassword($password) {
    $this->passwordHash = getPasswordHash($password);
  }
  
  function checkPassword($password) {
    return password_verify($password, $this->passwordHash);
  }
  
  function verifySource($source) {
    if ($source != $this->loginSource) {
      header('HTTP/1.1 909 Source Missmatch');
      echo  $source . ' vs ' . $this->loginSource;
      exit;    
    }
  }
}
?>
