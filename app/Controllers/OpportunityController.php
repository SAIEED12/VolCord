<?php
// Controller: opportunities (post + approve/reject)
// Endpoints: POST app/Controllers/OpportunityController.php (customer post),
//            GET  app/Controllers/OpportunityController.php?id=&action=approve|reject (admin review)
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Models/Opportunity.php';

class OpportunityController {
    public static function store($customer_id, $title, $description, $location, $required_skills, $needed_date) {
        $title = trim($title ?? "");
        $description = trim($description ?? "");
        $location = trim($location ?? "");
        $required_skills = trim($required_skills ?? "");
        $needed_date = trim($needed_date ?? "");
        if ($title === "" || $description === "" || $location === "") {
            return [false, "Title, description and location are required."];
        }
        $needed_date_val = $needed_date === "" ? null : $needed_date;
        $ok = Opportunity::create($customer_id, $title, $description, $location, $required_skills, $needed_date_val);
        return $ok
            ? [true, "Opportunity posted and awaiting admin approval."]
            : [false, "Could not post opportunity. Please try again."];
    }

    public static function review($id, $act, $admin_id) {
        $id = (int) $id;
        if ($id <= 0 || !in_array($act, ["approve", "reject"], true)) {
            return [false, "Invalid request."];
        }
        $status = $act === "approve" ? "approved" : "rejected";
        Opportunity::setStatus($id, $status, $admin_id);
        return [true, "Opportunity " . $status . "."];
    }
}

// --- Direct request handling (ex post_opportunity.php / review_opportunity.php) ---
if (basename($_SERVER["SCRIPT_FILENAME"] ?? "") === basename(__FILE__)) {
Auth::start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_GET["id"])) {
    if (($_SESSION["user_role"] ?? "") !== "Customer") {
        header("Location: ../Views/auth/login.php");
        exit;
    }
    [$ok, $msg] = OpportunityController::store(
        $_SESSION["volunteer_id"],
        $_POST["title"] ?? "",
        $_POST["description"] ?? "",
        $_POST["location"] ?? "",
        $_POST["required_skills"] ?? "",
        $_POST["needed_date"] ?? ""
    );
    if ($ok) { Flash::setSuccess($msg); } else { Flash::setError($msg); }
    header("Location: ../Views/customer/dashboard.php");
    exit;
}

if (isset($_GET["id"])) {
    if (($_SESSION["user_role"] ?? "") !== "Admin") {
        header("Location: ../Views/auth/login.php");
        exit;
    }
    [$ok, $msg] = OpportunityController::review($_GET["id"] ?? 0, $_GET["action"] ?? "", $_SESSION["volunteer_id"]);
    header("Location: ../Views/admin/dashboard.php");
    exit;
}
}
?>
