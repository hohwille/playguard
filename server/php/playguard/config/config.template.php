<?php
$config = array(
  'reloginDelay' => 300,
  'reloginMaxPenalty' => 3600,
  'loginLogoutDelay' => 60,
  'bonus' => array(
    'school mark A' => '00:30',
    'school mark B' => '00:10',
    'tidy up room' => '00:05'
  ),
  'age' => array(
    '-' => NULL,
    '4-6' => array(
      'maxPerDay' => '00:30',
      'maxPerWeek' => '01:00'
    ),
    '7-10' => array(
      'maxPerDay' => '00:45',
      'maxPerWeek' => '01:30'
    ),
    '11-13' => array(
      'maxPerDay' => '01:00',
      'maxPerWeek' => '03:00'
    ),
    '14-16' => array(
      'maxPerDay' => '02:00',
      'maxPerWeek' => '07:00'
    )
  ),
  'locale' => 'de',
  'timezone' => 'Europe/Berlin',
  'dbhost' => 'localhost',
  'dbname' => 'playguard',
  'dbuser' => 'playguard',
  'dbpassword' => '********'  
);
?>
