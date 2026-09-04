<?php
// Model: opportunities table
require_once __DIR__ . '/Database.php';

class Opportunity {
    public static function create($customer_id, $title, $description, $location, $required_skills, $needed_date_val) {
        $conn = Database::conn();
        $stmt = $conn->prepare(
            "INSERT INTO opportunities (customer_id, title, description, location, required_skills, needed_date, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')"
        );
        $stmt->bind_param("isssss", $customer_id, $title, $description, $location, $required_skills, $needed_date_val);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public static function getPending() {
        return Database::conn()->query(
            "SELECT o.id, o.title, o.location, o.needed_date, o.description, o.required_skills, u.full_name AS customer
             FROM opportunities o
             JOIN users u ON u.id = o.customer_id
             WHERE o.status = 'pending'
             ORDER BY o.created_at DESC"
        );
    }

    public static function getApprovedWithCounts() {
        return Database::conn()->query(
            "SELECT o.id, o.title, o.location, o.needed_date, o.status,
                    (SELECT COUNT(*) FROM applications a WHERE a.opportunity_id = o.id AND a.status = 'accepted') AS assigned_count,
                    (SELECT COUNT(*) FROM applications a WHERE a.opportunity_id = o.id) AS total_apps
             FROM opportunities o
             WHERE o.status IN ('approved', 'completed')
             ORDER BY o.created_at DESC"
        );
    }

    public static function getOpen() {
        return Database::conn()->query(
            "SELECT o.id, o.title, o.location, o.needed_date, o.description, o.required_skills, u.full_name AS customer
             FROM opportunities o
             JOIN users u ON u.id = o.customer_id
             WHERE o.status = 'approved'
             ORDER BY o.needed_date ASC, o.created_at DESC"
        );
    }

    public static function getByCustomer($customer_id) {
        $conn = Database::conn();
        $stmt = $conn->prepare(
            "SELECT o.id, o.title, o.location, o.needed_date, o.status, o.created_at,
                    (SELECT COUNT(*) FROM applications a WHERE a.opportunity_id = o.id) AS applications_count
             FROM opportunities o
             WHERE o.customer_id = ?
             ORDER BY o.created_at DESC"
        );
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        // NOTE: caller must fetch all rows before closing statement; result remains valid after close on mysqlnd
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public static function setStatus($id, $status, $admin_id) {
        $conn = Database::conn();
        $stmt = $conn->prepare("UPDATE opportunities SET status = ?, approved_by = ?, approved_at = NOW() WHERE id = ?");
        $stmt->bind_param("sii", $status, $admin_id, $id);
        $stmt->execute();
        $stmt->close();
    }

    public static function countByStatus($status) {
        $conn = Database::conn();
        $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM opportunities WHERE status = ?");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int) $row["cnt"];
    }

    public static function groupedByStatus() {
        $rows = Database::conn()->query("SELECT status, COUNT(*) AS cnt FROM opportunities GROUP BY status");
        $out = [];
        while ($r = $rows->fetch_assoc()) {
            $out[$r["status"]] = (int) $r["cnt"];
        }
        return $out;
    }
}
?>
