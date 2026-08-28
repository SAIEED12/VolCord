<?php
require_once "config.php";

$admin_email = "admin@volcord.local";
$admin_password = "Admin@1234";

$check = $conn->prepare("SELECT id FROM users WHERE role = 'Admin' LIMIT 1");
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->close();
    echo "An admin account already exists. No action taken.";
    exit;
}
$check->close();

$password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
$full_name = "System Administrator";
$role = "Admin";
$phone = "0000000000";
$gender = "Other";
$address = "VolCord HQ";

$stmt = $conn->prepare(
    "INSERT INTO users (full_name, email, password_hash, role, skills, phone, gender, address)
     VALUES (?, ?, ?, ?, NULL, ?, ?, ?)"
);
$stmt->bind_param("sssssss", $full_name, $admin_email, $password_hash, $role, $phone, $gender, $address);

if ($stmt->execute()) {
    echo "Admin account created successfully.<br>";
    echo "Email: <strong>" . htmlspecialchars($admin_email) . "</strong><br>";
    echo "Password: <strong>" . htmlspecialchars($admin_password) . "</strong><br>";
    echo "<br>Please delete or protect this file after first use, then <a href='login.php'>sign in</a>.";
} else {
    echo "Failed to create admin account: " . $conn->error;
}
$stmt->close();
?>
