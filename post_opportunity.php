<?php
require_once "config.php";
session_start();

if (($_SESSION["user_role"] ?? "") !== "Customer") {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customer_id = $_SESSION["volunteer_id"];
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $required_skills = trim($_POST["required_skills"] ?? "");
    $needed_date = trim($_POST["needed_date"] ?? "");

    if ($title === "" || $description === "" || $location === "") {
        $_SESSION["flash_error"] = "Title, description and location are required.";
    } else {
        $needed_date_val = $needed_date === "" ? null : $needed_date;
        $stmt = $conn->prepare(
            "INSERT INTO opportunities (customer_id, title, description, location, required_skills, needed_date, status)
             VALUES (?, ?, ?, ?, ?, ?, 'pending')"
        );
        $stmt->bind_param("isssss", $customer_id, $title, $description, $location, $required_skills, $needed_date_val);

        if ($stmt->execute()) {
            $_SESSION["flash_success"] = "Opportunity posted and awaiting admin approval.";
        } else {
            $_SESSION["flash_error"] = "Could not post opportunity. Please try again.";
        }
        $stmt->close();
    }
}

header("Location: customerDashboard.php");
exit;
