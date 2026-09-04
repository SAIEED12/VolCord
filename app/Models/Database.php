<?php
// Model base: exposes shared mysqli connection from config/database.php
require_once __DIR__ . '/../../config/database.php';

class Database {
    public static function conn() {
        global $conn;
        return $conn;
    }
}
?>
