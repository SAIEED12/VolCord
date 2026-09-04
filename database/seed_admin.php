<?php
// Seeder: one-time admin creation (moved from root seed_admin.php)
// Visit: http://localhost/volcord/database/seed_admin.php then delete/protect this file.
require_once __DIR__ . '/../app/Models/User.php';

$admin_email = "admin@volcord.local";
$admin_password = "Admin@1234";

if (User::adminExists()) {
    echo "An admin account already exists. No action taken.";
    exit;
}

$password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
[$ok, $err] = User::createAdmin("System Administrator", $admin_email, $password_hash, "0000000000", "Male", "VolCord HQ");

if ($ok) {
    echo "Admin account created successfully.<br>";
    echo "Email: <strong>" . htmlspecialchars($admin_email) . "</strong><br>";
    echo "Password: <strong>" . htmlspecialchars($admin_password) . "</strong><br>";
    echo "<br>Please delete or protect this file after first use, then <a href='../app/Views/auth/login.php'>sign in</a>.";
} else {
    echo "Failed to create admin account: " . htmlspecialchars($err);
}
?>
