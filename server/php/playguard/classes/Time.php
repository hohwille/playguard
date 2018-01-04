<?php
class Time {

  public static function getSecondsOfDay() {
    return (date('H') * 60 + date('i')) * 60 + date('s');
  }

  public static function formatSeconds($seconds, $default = '-') {
    if ($seconds == NULL) {
      return $default;
    }
    $t = round($seconds);
    return sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60);
  }
  
  public static function parseSegment($segment, $max = 60) {
    if (ctype_digit($segment)) {
      $result = intval($segment);
      if ($result <= $max) {
        return $result;
      }
    }
    exit('Illegal duration segment: ' . $segment);
  }
  
  public static function parseSegmentHours($hours, $max = 168) {
      
    return Time::parseSegment($hours, $max) * 3600;
  }
  
  public static function parseSegmentMinutes($minutes) {
      
    return Time::parseSegment($minutes) * 60;
  }
  
  public static function parseSegmentSeconds($seconds) {
      
    return Time::parseSegment($seconds);
  }
  
  public static function parseSeconds($duration) {
    if ($duration == NULL) {
      return NULL;
    }
    $segments = explode(':', $duration);
    if ((sizeof($segments) >= 2) && (sizeof($segments) <= 3)) {
      $seconds = Time::parseSegmentHours($segments[0]) + Time::parseSegmentMinutes($segments[1]);
      if (sizeof($segments) == 3) {
        $seconds = $seconds + Time::parseSegmentSeconds($segments[2]);
      }
      return $seconds;
    }
    exit('Illegal duration: ' . $duration);
  }

  public static function getTimestamp($dateString) {
    if ($dateString == NULL) {
      return NULL;
    }
    return strtotime($dateString);
  }

  public static function parseDate($date) {
    if ($date == NULL) {
      return NULL;
    }
    $result = DateTime::createFromFormat('Y-m-d', $date);
    return $result->getTimestamp();
  }
  
  public static function formatDate($timestamp, $default = '-') {
    return Time::formatTimestamp($timestamp, 'Y-m-d', $default);
  }

  public static function formatDateTime($timestamp, $default = '-') {
    return Time::formatTimestamp($timestamp, 'Y-m-d H:i:s', $default);
  }

  public static function formatTime($timestamp, $default = '-') {
    return Time::formatTimestamp($timestamp, 'H:i:s', $default);
  }

  public static function formatTimestamp($timestamp, $pattern, $default = '-') {
    if ($timestamp == NULL) {
      return $default;
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