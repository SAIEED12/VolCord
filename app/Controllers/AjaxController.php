<?php
// Controller: JSON API dispatcher (replaces root ajax_handler.php)
// Endpoint: app/Controllers/AjaxController.php?action=login|register|apply|post_opportunity|review_opportunity|review_application
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Opportunity.php';
require_once __DIR__ . '/../Models/Application.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/OpportunityController.php';
require_once __DIR__ . '/ApplicationController.php';

Auth::start();
header("Content-Type: application/json");

$action = $_GET["action"] ?? $_POST["action"] ?? "";

function json_response($success, $message = "", $redirect = "") {
    echo json_encode(["success" => $success, "message" => $message, "redirect" => $redirect]);
    exit();
}

switch ($action) {
    case "login": {
        [$ok, $msg, $user] = AuthController::attemptLogin($_POST["email"] ?? "", $_POST["password"] ?? "");
        if (!$ok) json_response(false, $msg);
        Auth::setLoginSession($user["id"], $user["full_name"], $user["email"], $user["role"]);
        $r = strtolower($user["role"]);
        $redirect = $r === "customer" ? "../customer/dashboard.php" : ($r === "admin" ? "../admin/dashboard.php" : "../volunteer/dashboard.php");
        json_response(true, "Login successful.", $redirect);
        break;
    }

    case "register": {
        [$ok, $msg, $clean] = AuthController::validateRegister($_POST);
        if (!$ok) json_response(false, $msg);
        $hash = password_hash($clean["password"], PASSWORD_DEFAULT);
        $id = User::create($clean["full_name"], $clean["email"], $hash, $clean["role"], $clean["skills"], $clean["phone"], $clean["gender"], $clean["address"]);
        if (!$id) json_response(false, "Something went wrong. Please try again.");
        Auth::setLoginSession($id, $clean["full_name"], $clean["email"], $clean["role"]);
        $redirect = strtolower($clean["role"]) === "customer" ? "../customer/dashboard.php" : "../volunteer/dashboard.php";
        json_response(true, "Account created successfully.", $redirect);
        break;
    }

    case "apply": {
        if (($_SESSION["user_role"] ?? "") !== "Volunteer") json_response(false, "Unauthorized.", "../Views/auth/login.php");
        [$ok, $msg] = ApplicationController::apply($_SESSION["volunteer_id"], $_POST["opportunity_id"] ?? 0, $_POST["message"] ?? "");
        json_response($ok, $msg);
        break;
    }

    case "post_opportunity": {
        if (($_SESSION["user_role"] ?? "") !== "Customer") json_response(false, "Unauthorized.", "../Views/auth/login.php");
        [$ok, $msg] = OpportunityController::store($_SESSION["volunteer_id"], $_POST["title"] ?? "", $_POST["description"] ?? "", $_POST["location"] ?? "", $_POST["required_skills"] ?? "", $_POST["needed_date"] ?? "");
        json_response($ok, $msg);
        break;
    }

    case "review_opportunity": {
        if (($_SESSION["user_role"] ?? "") !== "Admin") json_response(false, "Unauthorized.", "../Views/auth/login.php");
        [$ok, $msg] = OpportunityController::review($_POST["id"] ?? 0, $_POST["action_type"] ?? "", $_SESSION["volunteer_id"]);
        json_response($ok, $msg);
        break;
    }

    case "review_application": {
        if (($_SESSION["user_role"] ?? "") !== "Admin") json_response(false, "Unauthorized.", "../Views/auth/login.php");
        [$ok, $msg] = ApplicationController::review($_POST["id"] ?? 0, $_POST["action_type"] ?? "");
        json_response($ok, $msg);
        break;
    }

    default:
        json_response(false, "Unknown action.");
        break;
}
?>
