<?php
// Central DB connection (moved from root config.php)
$conn = new mysqli("localhost", "root", "", "volcord");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
