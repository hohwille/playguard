<?php 
  include('./classes/Main.php');
  #Main::redirect('login.php');
  if ($_SERVER['PHP_AUTH_USER'] != NULL) {
    Main::forbidden();
  }
?>
<html>
<body>
You are successfully logged out!
<ul>
<li><a href="index.php">Overview</a></li>
<li><a href="login.php">Login</a> (log in again)</li>
<li><a href="admin.php">Administration</a> (log in as admin)</li>
</ul>
</body>
</html>
