<?php
// Controller: volunteer applications (apply + accept/reject)
// Endpoints: POST app/Controllers/ApplicationController.php (volunteer apply),
//            GET  app/Controllers/ApplicationController.php?id=&action=accept|reject (admin review)
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Models/Application.php';

class ApplicationController {
    public static function apply($volunteer_id, $opportunity_id, $message) {
        $opportunity_id = (int) $opportunity_id;
        $message = trim($message ?? "");
        if ($opportunity_id <= 0) {
            return [false, "Invalid opportunity."];
        }
        if (Application::hasApplied($opportunity_id, $volunteer_id)) {
            return [false, "You have already applied for this opportunity."];
        }
        $ok = Application::create($opportunity_id, $volunteer_id, $message);
        return $ok
            ? [true, "Application submitted successfully."]
            : [false, "Could not submit application. Please try again."];
    }

    public static function review($id, $act) {
        $id = (int) $id;
        if ($id <= 0 || !in_array($act, ["accept", "reject"], true)) {
            return [false, "Invalid request."];
        }
        $status = $act === "accept" ? "accepted" : "rejected";
        Application::setStatus($id, $status);
        return [true, "Application " . $status . "."];
    }
}

// --- Direct request handling (ex apply.php / review_application.php) ---
if (basename($_SERVER["SCRIPT_FILENAME"] ?? "") === basename(__FILE__)) {
Auth::start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_GET["id"])) {
    if (($_SESSION["user_role"] ?? "") !== "Volunteer") {
        header("Location: ../Views/auth/login.php");
        exit;
    }
    [$ok, $msg] = ApplicationController::apply($_SESSION["volunteer_id"], $_POST["opportunity_id"] ?? 0, $_POST["message"] ?? "");
    if ($ok) { Flash::setSuccess($msg); } else { Flash::setError($msg); }
    header("Location: ../Views/volunteer/dashboard.php");
    exit;
}

if (isset($_GET["id"])) {
    if (($_SESSION["user_role"] ?? "") !== "Admin") {
        header("Location: ../Views/auth/login.php");
        exit;
    }
    ApplicationController::review($_GET["id"] ?? 0, $_GET["action"] ?? "");
    header("Location: ../Views/admin/dashboard.php");
    exit;
}
}
?>
