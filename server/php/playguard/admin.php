<?php 
include('./classes/Main.php');
$now=time();
$main = new Main();
$players=$main->getPlayers();
$admin = $main->getAdminLoggedIn();
$login = $_GET['login'];
if ($login != NULL) {
  $user = $players[$login];
}
if ($user == NULL) {
  $user = new Player();
  $user->maxPerDay = 3600;
  $user->maxPerWeek = 7200;
}
?>
<!doctype html>
<html>
<head>
  <title><?php echo LABEL_ADMINISTRATION ?></title>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="site.css">
</head>
<body>
<table>
<thead>
<tr>
  <th colspan="2"><?php echo LABEL_PLAYER ?></th>
  <th colspan="2"><?php echo LABEL_QUOTA ?></th>
  <th colspan="3"><?php echo LABEL_EXTRA ?></th>
  <th colspan="4"><?php echo LABEL_LAST_LOGIN ?></th>
  <th colspan="3"><?php echo LABEL_TIMES ?></th>
</tr>
<tr>
  <th><?php echo LABEL_LOGIN ?></th>
  <th><?php echo LABEL_EMAIL ?></th>
  <th><?php echo LABEL_MAXIMUM . '/' . LABEL_DAY ?></th>
  <th><?php echo LABEL_MAXIMUM . '/' . LABEL_WEEK ?></th>
  <th><?php echo LABEL_DAY ?></th>
  <th><?php echo LABEL_MAXIMUM . '/' . LABEL_DAY ?></th>
  <th><?php echo LABEL_MAXIMUM . '/' . LABEL_WEEK ?></th>
  <th><?php echo LABEL_LOGIN ?></th>
  <th><?php echo LABEL_SOURCE ?></th>
  <th><?php echo LABEL_IP ?></th>  
  <th><?php echo LABEL_LOGOUT ?></th>
  <th><?php echo LABEL_DAY ?></th>
  <th><?php echo LABEL_WEEK ?></th>
  <th><?php echo LABEL_REMAINING ?></th>
</tr>
</thead>
<tbody>
<?php
foreach($players as $player) { ?>
<tr>
  <td><a href="admin.php?login=<?php echo $player->login ?>"><?php echo $player->login ?></a></td>
  <td><?php echo $player->email ?></td>
  <td><?php echo Time::formatSeconds($player->maxPerDay) ?></td>
  <td><?php echo Time::formatSeconds($player->maxPerWeek) ?></td>
  <td><?php echo Time::formatDate($player->extraDay) ?></td>
  <td><?php echo Time::formatSeconds($player->extraMaxPerDay) ?></td>
  <td><?php echo Time::formatSeconds($player->extraMaxPerWeek) ?></td>
  <td><?php echo Time::formatDateTime($player->loginDate) ?></td>
  <td><?php echo $player->getLoginSource() ?></td>
  <td><?php echo $player->getLoginIp() ?></td>
  <td><?php echo Time::formatDateTime($player->logoutDate) ?></td>
  <td><?php echo Time::formatSeconds($player->playedDay) ?></td>
  <td><?php echo Time::formatSeconds($player->playedWeek) ?></td>
  <td><?php echo Time::formatSeconds($player->getRestThisWeek()) ?></td>
</tr><?php 
} ?>
</tbody>
</table>
<hr>
<form id="editPlayer" action="player-save.php" method="post">
  <h1><?php echo ($user->login == NULL) ? LABEL_CREATE_USER : LABEL_EDIT_USER ?></h1>
  <fieldset>
    <legend><?php echo LABEL_PLAYER ?></legend>
    <p><label for="login"><?php echo LABEL_USERNAME ?></label> <input type="text" id="login" name="login" value="<?php echo $user->login ?>" <?php echo ($user->login == NULL) ? 'placeholder="' . LABEL_USERNAME. '" required' : 'readonly' ?>/></p>
    <p><label for="email"><?php echo LABEL_EMAIL ?></label> <input type="text" id="email" name="email" value="<?php echo $user->email ?>" placeholder="<?php echo LABEL_EMAIL ?>"/></p>
  </fieldset>
  <fieldset>
    <legend><?php echo LABEL_QUOTA ?></legend>
    <p><label for="age"><?php echo LABEL_AGE ?></label>
    <select id="age" size="1"><?php
      foreach($config['age'] as $age => $times) { ?>
      <option<?php ($times == NULL) ? 'selected' : '' ?>><?php echo $age ?></option><?php
      } ?>
    </select></p>
    <p><label for="maxPerDay"><?php echo LABEL_MAXIMUM . '/' . LABEL_DAY ?></label> <input type="time" id="maxPerDay" name="maxPerDay" value="<?php echo Time::formatSeconds($user->maxPerDay, '') ?>" min="00:00:00" max="24:00:00" step="900" required/></p>
    <p><label for="maxPerWeek"><?php echo LABEL_MAXIMUM . '/' . LABEL_WEEK ?></label> <input type="time" id="maxPerWeek" name="maxPerWeek" value="<?php echo Time::formatSeconds($user->maxPerWeek, '') ?>" min="00:00:00" max="168:00:00" step="900" required/></p>
  </fieldset>
  <fieldset>
    <legend><?php echo LABEL_EXTRA ?></legend>
    <p><label for="bonus"><?php echo LABEL_BONUS ?></label>
    <select id="bonus" size="1">
      <option selected>-</option><?php
      foreach($config['bonus'] as $task => $bonus) { ?>
      <option><?php echo $task . ' (+' . $bonus . ')' ?></option><?php
      } ?>
    </select>
    <button id="addBonus" type="button"><?php echo LABEL_ADD ?></button>
    </p>
    <p><label for="extraDay"><?php echo LABEL_EXTRA_DAY ?></label> <input type="date" id="extraDay" name="extraDay" value="<?php echo Time::formatDate($user->extraDay, '') ?>"/></p>
    <p><label for="extraMaxPerDay"><?php echo LABEL_MAXIMUM . '/' . LABEL_DAY ?></label> <input type="time" id="extraMaxPerDay" name="extraMaxPerDay" value="<?php echo Time::formatSeconds($user->extraMaxPerDay, '') ?>" min="00:00:00" max="24:00:00" step="900"/></p>
    <p><label for="extraMaxPerWeek"><?php echo LABEL_MAXIMUM . '/' . LABEL_WEEK ?></label> <input type="time" id="extraMaxPerWeek" name="extraMaxPerWeek" value="<?php echo Time::formatSeconds($user->extraMaxPerWeek, '') ?>" min="00:00:00" max="168:00:00" step="900"/></p>
    <p><label for="extraComment"><?php echo LABEL_COMMENT ?></label> <textarea id="extraComment" name="extraComment"><?php echo $user->getExtraComment() ?></textarea></p>
  </fieldset>
 <p><input type="submit" value="<?php echo ($user->login == NULL) ? LABEL_CREATE : LABEL_SAVE ?>"/></p>
</form>
<script>
var ageSelect = document.getElementById('age');
var maxPerDayInput =  document.getElementById('maxPerDay');
var maxPerWeekInput =  document.getElementById('maxPerWeek');
var bonusSelect = document.getElementById('bonus');
var bonusButton = document.getElementById('addBonus');
var extraDayInput = document.getElementById('extraDay');
var extraMaxPerDayInput = document.getElementById('extraMaxPerDay');
var extraMaxPerWeekInput = document.getElementById('extraMaxPerWeek');
var extraCommentInput = document.getElementById('extraComment');

function ageSelected() {
  var ageValues = [<?php foreach ($config['age'] as $times) { echo ($times == NULL) ? "{}" : ",{'maxPerDay':'" . $times['maxPerDay'] . "', 'maxPerWeek':'" . $times['maxPerWeek'] . "'}"; } ?>];
  if (this.selectedIndex == 0) {
    maxPerDayInput.readOnly = false;
    maxPerWeekInput.readOnly = false;
  } else {
    maxPerDayInput.readOnly = true;
    maxPerWeekInput.readOnly = true;
    maxPerDayInput.value = ageValues[this.selectedIndex].maxPerDay;
    maxPerWeekInput.value = ageValues[this.selectedIndex].maxPerWeek;
  }
}
ageSelect.onchange = ageSelected;

function bonusClicked() {
  var bonusValues = [''<?php foreach ($config['bonus'] as $bonus) { echo ",'" . $bonus . "'"; } ?>];
  var bonus = bonusValues[bonusSelect.selectedIndex];
  var bonusText = bonusSelect.value;
  var extraPerWeek = bonus;
  if (!extraDayInput.value) {
    extraDayInput.value = new Date().toJSON().slice(0,10);
  }
  if (extraCommentInput.value) {
    extraCommentInput.value = extraCommentInput.value + ', ' + bonusText;
  } else {
    extraCommentInput.value = bonusText;
  }
  if (extraMaxPerWeekInput.value) {
    var extraSegments = extraMaxPerWeekInput.value.split(':');
    var bonusSegments = bonus.split(':');
    var segments = [0, 0, 0];
    for (var i = 0; i < extraSegments.length; i++) {
      segments[i] = parseInt(extraSegments[i]) + parseInt(bonusSegments[i]);
      if (isNaN(segments[i])) {
        segments[i]=0;
      }
      if (i > 0) {
        if (segments[i] >= 60) {
          segments[i-1]++;
          if ((i > 1) && (segments[i-1] > 60)) {
            segments[i-2]++;
            segments[i-1] = segments[i-1] - 60;
          }
          segments[i] = segments[i] - 60;
        }
      }
    }
    extraPerWeek = '';
    for (var i = 0; i < segments.length; i++) {
      var digits = String(segments[i]);
      if (digits.length == 1) {
        digits = '0' + digits;
      }
      if (i > 0) {
        extraPerWeek = extraPerWeek + ':';
      }
      extraPerWeek = extraPerWeek + digits;
    }
  }
  extraMaxPerWeekInput.value = extraPerWeek;
}
bonusButton.onclick = bonusClicked;
</script>
<?php
if ($user->login != NULL) { ?>
<hr>
<form id="changePassword" action="player-password.php" method="post">
 <h1><?php echo LABEL_CHANGE_PASSWORD ?></h1>
  <fieldset>
    <legend><?php echo LABEL_PLAYER ?></legend>
    <p><label for="login"><?php echo LABEL_USERNAME ?></label> <input type="text" id="login" name="login" value="<?php echo $user->login ?>" readonly/></p>
  </fieldset>
  <fieldset>
    <legend><?php echo LABEL_PASSWORD ?></legend>
    <p><label for="password"><?php echo LABEL_PASSWORD ?></label> <input type="password" id="password" name="password" required placeholder="<?php echo LABEL_PASSWORD ?>"/></p>
    <p><label for="confirm"><?php echo LABEL_CONFIRM_PASSWORD ?></label> <input type="password" id="confirm" name="confirm" required placeholder="<?php echo LABEL_CONFIRM_PASSWORD ?>"/></p>
    <p><input type="submit" value="<?php echo LABEL_CHANGE_PASSWORD ?>"/></p> 
  </fieldset>
</form>
<script>
var passwordInput = document.getElementById('password');
var confirmInput = document.getElementById('confirm');

function validatePassword() {
  if (passwordInput.value != confirmInput.value) {
    confirmInput.setCustomValidity('<?php echo LABEL_PASSWORD_MISMATCH ?>');
  } else {
    confirmInput.setCustomValidity('');
  }
}
passwordInput.onchange = validatePassword;
confirmInput.onkeyup = validatePassword;
</script>
<?php
} ?>
</body>
</html>
