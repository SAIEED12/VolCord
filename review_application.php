<?php
require_once "config.php";
session_start();

if (($_SESSION["user_role"] ?? "") !== "Admin") {
    header("Location: login.php");
    exit;
}

$id = (int) ($_GET["id"] ?? 0);
$action = $_GET["action"] ?? "";

if ($id > 0 && ($action === "accept" || $action === "reject")) {
    $status = $action === "accept" ? "accepted" : "rejected";
    $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: adminDashboard.php");
exit;
