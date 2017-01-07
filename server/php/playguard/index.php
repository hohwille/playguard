<?php 
include('./classes/Main.php');
$main = new Main();
$players=$main->getPlayers();
?>
<!doctype html>
<html>
<head>
  <title><?php echo LABEL_PLAYTIME ?></title>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="site.css">
</head>
<body>
<h1><?php echo LABEL_PLAYTIME ?> (<?php echo date('c') ?>)</h1>
<?php echo LABEL_INDEX_HINT ?>
<table>
<thead>
<tr>
  <th rowspan="2"><?php echo LABEL_PLAYER ?></th>
  <th colspan="3"><?php echo LABEL_TODAY ?></th>
  <th colspan="3"><?php echo LABEL_THIS_WEEK ?></th>
  <th colspan="4"><?php echo LABEL_LAST_LOGIN ?></th>
</tr>
<tr>
  <th><?php echo LABEL_REMAINING ?></th>
  <th><?php echo LABEL_PLAYED ?></th>
  <th><?php echo LABEL_MAXIMUM ?></th>
  <th><?php echo LABEL_REMAINING ?></th>
  <th><?php echo LABEL_PLAYED ?></th>
  <th><?php echo LABEL_MAXIMUM ?></th>
  <th><?php echo LABEL_LOGIN ?></th>
  <th><?php echo LABEL_SOURCE ?></th>
  <th><?php echo LABEL_IP ?></th>  
  <th><?php echo LABEL_LOGOUT ?></th>
</tr>
</thead>
<tbody>
<?php
foreach($players as $player) { ?>
<tr>
  <td><a href="details.php?login=<?php echo $player->login ?>"><?php echo $player->login ?></a></td>
  <td><?php echo Time::formatSeconds($player->getRest()) ?></td>
  <td><?php echo Time::formatSeconds($player->getPlayedToday()) ?></td>
  <td><?php echo Time::formatSeconds($player->getMaxToday()) ?></td>
  <td><?php echo Time::formatSeconds($player->getRestThisWeek()) ?></td>
  <td><?php echo Time::formatSeconds($player->getPlayedThisWeek()) ?></td>
  <td><?php echo Time::formatSeconds($player->getMaxThisWeek()) ?></td>
  <td><?php echo Time::formatDateTime($player->loginDate) ?></td>
  <td><?php echo $player->getLoginSource() ?></td>
  <td><?php echo $player->getLoginIp() ?></td>
  <td><?php echo Time::formatDateTime($player->logoutDate) ?></td>
</tr><?php 
} ?>
</tbody>
</table>
</body>
</html>
