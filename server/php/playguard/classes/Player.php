<?php
include './classes/Time.php';

class Player {
  public $login;
  public $email;
  public $administrator;
  public $maxPerDay;
  public $maxPerWeek;
  public $extraDay;
  public $extraMaxPerDay;
  public $extraMaxPerWeek;
  public $extraComment;
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
  
  function getExtraComment() {
    if ($this->isExtraWeek()) {
      return htmlspecialchars($this->extraComment);
    }
    return '';
  }
  
  function getMaxToday() {
    if ($this->isLocked()) {
      return 0;
    }
    if ($this->extraMaxPerDay && $this->isExtraDay()) {
      return $this->extraMaxPerDay;
    }
    return $this->maxPerDay;
  }
  
  function getMaxThisWeek() {
    if ($this->isLocked()) {
      return 0;
    }
    if ($this->extraMaxPerWeek && $this->isExtraWeek()) {
      return $this->extraMaxPerWeek;
    }
    return $this->maxPerWeek;
  }

  function getPlayedToday() {
    global $config;
    $played = 0;
    if (Time::isToday($this->loginDate)) {
      if ($this->logoutDate == NULL) {
        $played = (time() - $this->loginDate - $config['loginLogoutDelay']);
        if ($played < 0) {
          $played = 0;
        }
      }
      $played = $played + $this->playedDay;
    }
    return $played;
  }
  
  function getPlayedThisWeek() {
    $played = 0;
    if (Time::isThisWeek($this->loginDate)) {
      $played = $this->playedWeek + $this->getPlayedToday();
      if (!Time::isToday($this->loginDate)) {
        $played = $played + $this->playedDay;
      }
    }
    return $played;
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

  public function getLoginSource() {
    return htmlspecialchars($this->loginSource);
  }

  public function getLoginIp() {
    return htmlspecialchars($this->loginIp);
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
