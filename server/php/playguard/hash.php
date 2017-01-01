<?php 
include('./classes/Main.php');
$password = $_POST['password'];
if ($password) {
  $hash = Player::getPasswordHash($password);    
}
?>
<!doctype html>
<html>
<head>
  <title><?php echo LABEL_PASSWORD . ' ' . LABEL_HASH ?></title>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="site.css">
</head>
<body>
<form action="hash.php" method="post">
 <h1><?php echo LABEL_PASSWORD . ' ' . LABEL_HASH ?></h1>
 <p><label for="password"><?php echo LABEL_PASSWORD ?></label>: <input type="text" id="password" name="password" value="<?php echo $password ?>"/></p>
 <p><label><?php echo LABEL_HASH ?></label>: <span><?php echo $hash ?></span></p>
 <p><input type="submit" value="<?php echo LABEL_SUBMIT ?>"/></p>
</form>
</body>
</html>
