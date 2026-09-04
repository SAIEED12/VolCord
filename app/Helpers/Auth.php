<?php
// Helper: session + role-based auth guard
class Auth {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function user() {
        return [
            "id"    => $_SESSION["volunteer_id"] ?? null,
            "name"  => $_SESSION["volunteer_name"] ?? null,
            "email" => $_SESSION["volunteer_email"] ?? null,
            "role"  => $_SESSION["user_role"] ?? null,
        ];
    }

    public static function requireRole($role, $loginUrl) {
        self::start();
        if (($_SESSION["user_role"] ?? "") !== $role) {
            header("Location: " . $loginUrl);
            exit;
        }
    }

    public static function setLoginSession($user_id, $full_name, $user_email, $user_role) {
        $_SESSION["volunteer_name"]  = $full_name;
        $_SESSION["volunteer_email"] = $user_email;
        $_SESSION["volunteer_id"]    = $user_id;
        $_SESSION["user_role"]       = $user_role;
        setcookie("volunteer_name",  $full_name,  time() + 3600, "/");
        setcookie("volunteer_email", $user_email, time() + 3600, "/");
        setcookie("user_role",       $user_role,  time() + 3600, "/");
    }

    public static function dashboardFor($role) {
        $r = strtolower($role);
        if ($r === "customer") return "customer/dashboard.php";
        if ($r === "admin") return "admin/dashboard.php";
        return "volunteer/dashboard.php";
    }

    public static function logout($loginUrl) {
        self::start();
        session_unset();
        session_destroy();
        setcookie("volunteer_name", "", time() - 3600, "/");
        setcookie("volunteer_email", "", time() - 3600, "/");
        setcookie("user_role", "", time() - 3600, "/");
        header("Location: " . $loginUrl);
        exit;
    }
}
?>
