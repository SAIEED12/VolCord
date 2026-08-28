<?php
session_start();
session_unset();
session_destroy();
setcookie("volunteer_name", "", time() - 3600, "/");
setcookie("volunteer_email", "", time() - 3600, "/");
setcookie("user_role", "", time() - 3600, "/");
header("Location: login.php");
exit;
