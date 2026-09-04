<?php
// Controller: authentication (login / register / logout)
// Endpoint: app/Controllers/AuthController.php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Models/User.php';

class AuthController {
    public static function attemptLogin($email, $password) {
        $email = trim($email ?? "");
        if ($email === "" || $password === "") {
            return [false, "All fields are required.", null];
        }
        $user = User::findByEmail($email);
        if (!$user) {
            return [false, "No account found with that email.", null];
        }
        if (!User::verifyPassword($password, $user["password_hash"])) {
            return [false, "Invalid email or password.", null];
        }
        return [true, "Login successful.", $user];
    }

    public static function validateRegister($data) {
        $full_name = trim($data["full_name"] ?? "");
        $email     = trim($data["email"] ?? "");
        $password  = $data["password"] ?? "";
        $confirm   = $data["confirm_password"] ?? "";
        $role      = $data["role"] ?? "";
        $phone     = trim($data["phone"] ?? "");
        $gender    = trim($data["gender"] ?? "");
        $address   = trim($data["address"] ?? "");
        $skills    = null;

        $allowed_roles   = ["Volunteer", "Customer"];
        $allowed_genders = ["Male", "Female", "Other"];

        if ($full_name === "" || $email === "" || $password === "" || $confirm === "" || $role === ""
            || $phone === "" || $gender === "" || $address === "") {
            return [false, "All fields are required.", null];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [false, "Please enter a valid email address.", null];
        }
        if ($password !== $confirm) {
            return [false, "Passwords do not match.", null];
        }
        if (strlen($password) < 8) {
            return [false, "Password must be at least 8 characters.", null];
        }
        if (!in_array($role, $allowed_roles, true)) {
            return [false, "Please select a valid role.", null];
        }
        if (!in_array($gender, $allowed_genders, true)) {
            return [false, "Please select a valid gender.", null];
        }
        if ($role === "Volunteer") {
            $skills = trim($data["skills"] ?? "");
            if ($skills === "") {
                return [false, "Please list your skills.", null];
            }
        }
        if (User::emailExists($email)) {
            return [false, "An account with that email already exists.", null];
        }
        return [true, "", compact("full_name", "email", "password", "role", "skills", "phone", "gender", "address")];
    }

    public static function dashboardPath($role) {
        // Relative redirect from app/Controllers/ -> app/Views/...
        $r = strtolower($role);
        if ($r === "customer") return "../Views/customer/dashboard.php";
        if ($r === "admin") return "../Views/admin/dashboard.php";
        return "../Views/volunteer/dashboard.php";
    }
}

// --- Direct request handling (non-AJAX fallback, ex login_submit.php / register.php POST / logout.php) ---
// Only run when this file is the HTTP entry point (not when included by AjaxController).
if (basename($_SERVER["SCRIPT_FILENAME"] ?? "") === basename(__FILE__)) {
Auth::start();
$VIEW_LOGIN = "../Views/auth/login.php";

if (($_GET["action"] ?? "") === "logout") {
    Auth::logout($VIEW_LOGIN);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Login form posts `login` button (see Views/auth/login.php)
    if (isset($_POST["login"]) || (($_POST["action"] ?? "") === "login" && !isset($_POST["full_name"]))) {
        [$ok, $msg, $user] = AuthController::attemptLogin($_POST["email"] ?? "", $_POST["password"] ?? "");
        if (!$ok) {
            Flash::setError($msg);
            header("Location: " . $VIEW_LOGIN);
            exit;
        }
        Auth::setLoginSession($user["id"], $user["full_name"], $user["email"], $user["role"]);
        header("Location: " . AuthController::dashboardPath($user["role"]));
        exit;
    }

    // Register form posts `full_name` (see Views/auth/register.php)
    if (isset($_POST["full_name"])) {
        [$ok, $msg, $clean] = AuthController::validateRegister($_POST);
        if (!$ok) {
            Flash::setError($msg);
            header("Location: ../Views/auth/register.php");
            exit;
        }
        $hash = password_hash($clean["password"], PASSWORD_DEFAULT);
        $id = User::create($clean["full_name"], $clean["email"], $hash, $clean["role"], $clean["skills"], $clean["phone"], $clean["gender"], $clean["address"]);
        if (!$id) {
            Flash::setError("Something went wrong. Please try again.");
            header("Location: ../Views/auth/register.php");
            exit;
        }
        Auth::setLoginSession($id, $clean["full_name"], $clean["email"], $clean["role"]);
        header("Location: " . AuthController::dashboardPath($clean["role"]));
        exit;
    }
}
}
?>
