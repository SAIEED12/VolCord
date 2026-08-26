<?php

$cookies = ["volunteer_name", "volunteer_email", "volunteer_id", "volunteer_pass"];

foreach ($cookies as $cookie) {
    setcookie($cookie, "", time() - 3600, "/");
}

header("Location: dashboard.php");
exit();
