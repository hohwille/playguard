<?php 
include('./classes/Main.php');
$main = new Main();
$player = $main->getPlayerLoggedIn();
?>
<html>
<body>
<h2>Logged in as <?php echo $player->login ?> (to logout supply empty login data on <a href="logout.php">logout page</a>)</h2>
</body>
</html>
