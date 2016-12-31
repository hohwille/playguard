<?php
class Time {

  public static function getSecondsOfDay() {
    return (date('H') * 60 + date('i')) * 60 + date('s');
  }

  public static function formatSeconds($seconds) {
    if (!$seconds) {
      return '-';
    }
    $t = round($seconds);
    return sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60);
  }

  public static function getTimestamp($dateString) {
    if ($dateString == NULL) {
      return NULL;
    }
    return strtotime($dateString);
  }
  
  public static function formatDate($timestamp) {
    return Time::formatTimestamp($timestamp, 'Y-m-d');
  }

  public static function formatDateTime($timestamp) {
    return Time::formatTimestamp($timestamp, 'Y-m-d H:i:s');
  }

  public static function formatTime($timestamp) {
    return Time::formatTimestamp($timestamp, 'H:i:s');
  }

  public static function formatTimestamp($timestamp, $pattern) {
    if ($timestamp == NULL) {
      return '-';
    }
    return date($pattern, $timestamp);
  }
  
  public static function getDay($timestamp) {
    $date = new DateTime();
    $date->setTimestamp($timestamp);
    $date->setTime(0, 0, 0);
    return $date;
  }
  
  public static function getDayTimestamp($timestamp) {
    return Time::getDay($timestamp)->getTimestamp();
  }
  
  public static function getMondayOfThisWeek() {
    $weekday = date('w')-1;
    if ($weekday == -1) {
      $weekday = 6;
    }
    $date = new DateTime();
    $date->setTimestamp(time() - ($weekday * 24 * 60 * 60));
    $date->setTime(0, 0, 0);
    return $date;    
  }
  
  public static function getSundayOfThisWeek() {
    $weekday = 8-date('w');
    if ($weekday == 8) {
      $weekday = 0;
    }
    $date = new DateTime();
    $date->setTimestamp(time() + ($weekday * 24 * 60 * 60));
    $date->setTime(0, 0, 0);
    return $date;    
  }
  
  public static function getWeekdays() {
    $date = Time::getMondayOfThisWeek();
    $day = new DateInterval('P1D');
    $weekdays = array();
    for ($i = 0; $i < 7; $i++) {
      $weekdays[$i] = $date->getTimestamp();
      $date->add($day);
    }
    return $weekdays;
  }

  public static function isToday($date) {
    if ($date != NULL) {
      $today = new DateTime();
      $today->setTime(0, 0, 0);
      $delta = $date - $today->getTimestamp();
      if (($delta >= 0) &&  ($delta < 24 * 60 * 60)) {
        return true;
      }
    }
    return false;
  }
 
  public static function isThisWeek($date) {
    if ($date != NULL) {
      $day = Time::getMondayOfThisWeek();
      if ($date >= $day->getTimestamp()) {
        $day->add(new DateInterval('P7D'));
        if ($date <= $day->getTimestamp()) {        
          return true;
        }
      }
    }
    return false;      
  }
}
?>
