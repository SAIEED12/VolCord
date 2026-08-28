<?php
require_once "config.php";
session_start();

if (($_SESSION["user_role"] ?? "") !== "Admin") {
    header("Location: login.php");
    exit;
}

$id = (int) ($_GET["id"] ?? 0);
$action = $_GET["action"] ?? "";

if ($id > 0 && ($action === "approve" || $action === "reject")) {
    $status = $action === "approve" ? "approved" : "rejected";
    $admin_id = $_SESSION["volunteer_id"];
    $stmt = $conn->prepare("UPDATE opportunities SET status = ?, approved_by = ?, approved_at = NOW() WHERE id = ?");
    $stmt->bind_param("sii", $status, $admin_id, $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: adminDashboard.php");
exit;
