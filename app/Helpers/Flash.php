<?php
// Helper: flash messages (wraps $_SESSION flash_error / flash_success keys used by views)
class Flash {
    public static function setError($msg)   { $_SESSION["flash_error"] = $msg; }
    public static function setSuccess($msg) { $_SESSION["flash_success"] = $msg; }

    public static function getError() {
        $m = $_SESSION["flash_error"] ?? "";
        unset($_SESSION["flash_error"]);
        return $m;
    }

    public static function getSuccess() {
        $m = $_SESSION["flash_success"] ?? "";
        unset($_SESSION["flash_success"]);
        return $m;
    }
}
?>
