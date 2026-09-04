<?php
// Model: users table
require_once __DIR__ . '/Database.php';

class User {
    public static function findByEmail($email) {
        $conn = Database::conn();
        $stmt = $conn->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user ?: null;
    }

    public static function emailExists($email) {
        $conn = Database::conn();
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    public static function create($full_name, $email, $password_hash, $role, $skills, $phone, $gender, $address) {
        $conn = Database::conn();
        $stmt = $conn->prepare(
            "INSERT INTO users (full_name, email, password_hash, role, skills, phone, gender, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssssss", $full_name, $email, $password_hash, $role, $skills, $phone, $gender, $address);
        $ok = $stmt->execute();
        $id = $ok ? $conn->insert_id : false;
        $stmt->close();
        return $id;
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function countAll() {
        $row = Database::conn()->query("SELECT COUNT(*) AS cnt FROM users")->fetch_assoc();
        return (int) $row["cnt"];
    }

    public static function countVolunteers() {
        $row = Database::conn()->query("SELECT COUNT(*) AS cnt FROM users WHERE role='Volunteer'")->fetch_assoc();
        return (int) $row["cnt"];
    }

    public static function adminExists() {
        $conn = Database::conn();
        $check = $conn->prepare("SELECT id FROM users WHERE role = 'Admin' LIMIT 1");
        $check->execute();
        $check->store_result();
        $exists = $check->num_rows > 0;
        $check->close();
        return $exists;
    }

    public static function createAdmin($full_name, $email, $password_hash, $phone, $gender, $address) {
        $conn = Database::conn();
        $role = "Admin";
        $stmt = $conn->prepare(
            "INSERT INTO users (full_name, email, password_hash, role, skills, phone, gender, address) VALUES (?, ?, ?, ?, NULL, ?, ?, ?)"
        );
        $stmt->bind_param("sssssss", $full_name, $email, $password_hash, $role, $phone, $gender, $address);
        $ok = $stmt->execute();
        $err = $conn->error;
        $stmt->close();
        return [$ok, $err];
    }
}
?>
