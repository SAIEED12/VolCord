<?php
require_once "config.php";
session_start();

if (($_SESSION["user_role"] ?? "") !== "Volunteer") {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $volunteer_id = $_SESSION["volunteer_id"];
    $opportunity_id = (int) ($_POST["opportunity_id"] ?? 0);
    $message = trim($_POST["message"] ?? "");

    if ($opportunity_id > 0) {
        $check = $conn->prepare("SELECT id FROM applications WHERE opportunity_id = ? AND volunteer_id = ?");
        $check->bind_param("ii", $opportunity_id, $volunteer_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $check->close();
            $_SESSION["flash_error"] = "You have already applied for this opportunity.";
        } else {
            $check->close();
            $stmt = $conn->prepare("INSERT INTO applications (opportunity_id, volunteer_id, message, status) VALUES (?, ?, ?, 'pending')");
            $stmt->bind_param("iis", $opportunity_id, $volunteer_id, $message);
            if ($stmt->execute()) {
                $_SESSION["flash_success"] = "Application submitted successfully.";
            } else {
                $_SESSION["flash_error"] = "Could not submit application. Please try again.";
            }
            $stmt->close();
        }
    }
}

header("Location: volunteerDashboard.php");
exit;
