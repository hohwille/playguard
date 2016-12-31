<?php
include('./classes/Main.php');
$main = new Main();
$login = $_GET['login'];
$player = $main->getPlayer($login);
$monday = Time::getMondayOfThisWeek()->getTimestamp();
$sunday = Time::getSundayOfThisWeek()->getTimestamp();
$end = $sunday;
$start = $_GET['start'];
if ($start > 0) {
  if ($_GET['end'] > 0) {
    $end = $_GET['end'];
  }
} else {
  $start = $monday;
}
$playdays = $main->getDatabase()->getPlaydays($player->login, $start, $end);
?>
<!doctype html>
<html>
<head>
  <title><?php echo LABEL_PLAYTIME_DETAILS ?></title>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="site.css">
</head>
<body>
<h1><?php echo LABEL_PLAYTIME_DETAILS . ' (' . $player->login . ')' ?></h1>
<h2><?php echo LABEL_TODAY . ' (' . date('Y-m-d') . ')' ?></h2>
<label><?php echo LABEL_REMAINING ?>:</label> <?php echo Time::formatSeconds($player->getRest()) ?><br>
<label><?php echo LABEL_PLAYED ?>:</label> <?php echo Time::formatSeconds($player->getPlayedToday()) ?>/<?php echo Time::formatSeconds($player->getMaxToday()) ?><br>
<h2><?php echo LABEL_THIS_WEEK . ' (' . Time::formatDate($monday) . ' - ' . Time::formatDate($sunday) . ')' ?></h2>
<label><?php echo LABEL_REMAINING ?>:</label> <?php echo Time::formatSeconds($player->getRestThisWeek()) ?><br>
<label><?php echo LABEL_PLAYED ?>:</label> <?php echo Time::formatSeconds($player->getPlayedThisWeek()) ?>/<?php echo Time::formatSeconds($player->getMaxThisWeek()) ?><br>
<h2><?php echo LABEL_HISTORY . ' (' . Time::formatDate($start) . ' - ' . Time::formatDate($end) . ')' ?></h2>
<table>
<thead>
<tr>
  <th><?php echo LABEL_DAY ?></th>
  <th><?php echo LABEL_LOGIN ?></th>
  <th><?php echo LABEL_DURATION ?></th>
  <th><?php echo LABEL_LOGOUT ?></th>
  <th><?php echo LABEL_SOURCE ?></th>
  <th><?php echo LABEL_IP ?></th>
</tr>
</thead>
<tbody>
<?php
foreach($playdays->days as $playday) { ?>
<tr>
  <td rowspan="<?php echo (sizeof($playday->times) + 1) ?>"><?php echo Time::formatDate($playday->day) ?></td>
<?php
  foreach($playday->times as $playtime) { ?>
  <td><?php echo Time::formatTime($playtime->loginDate) ?></td>
  <td><?php echo Time::formatSeconds($playtime->getDuration()) ?></td>
  <td><?php echo Time::formatTime($playtime->logoutDate) ?></td>
  <td><?php echo $playtime->loginSource ?></td>
  <td><?php echo $playtime->loginIp ?></td>
</tr><?php 
  } ?>
<tr>
  <td><?php echo LABEL_TOTAL ?></td>
  <td><?php echo Time::formatSeconds($playday->total) ?></td>
  <td>-</td>
  <td>-</td>
  <td>-</td>
</tr><?php
} ?>
</tbody>
</table>
<label><?php echo LABEL_TOTAL ?>:</label> <?php echo Time::formatSeconds($playdays->total) ?><br>
</body>
</html>

