<?php
// Model: applications table
require_once __DIR__ . '/Database.php';

class Application {
    public static function hasApplied($opportunity_id, $volunteer_id) {
        $conn = Database::conn();
        $check = $conn->prepare("SELECT id FROM applications WHERE opportunity_id = ? AND volunteer_id = ?");
        $check->bind_param("ii", $opportunity_id, $volunteer_id);
        $check->execute();
        $check->store_result();
        $exists = $check->num_rows > 0;
        $check->close();
        return $exists;
    }

    public static function create($opportunity_id, $volunteer_id, $message) {
        $conn = Database::conn();
        $stmt = $conn->prepare("INSERT INTO applications (opportunity_id, volunteer_id, message, status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param("iis", $opportunity_id, $volunteer_id, $message);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public static function getByVolunteer($volunteer_id) {
        $conn = Database::conn();
        $stmt = $conn->prepare(
            "SELECT a.opportunity_id, a.status, a.message, a.applied_at, o.title, o.location
             FROM applications a
             JOIN opportunities o ON o.id = a.opportunity_id
             WHERE a.volunteer_id = ?
             ORDER BY a.applied_at DESC"
        );
        $stmt->bind_param("i", $volunteer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public static function appliedMap($volunteer_id) {
        $map = [];
        foreach (self::getByVolunteer($volunteer_id) as $r) {
            $map[$r["opportunity_id"]] = $r["status"];
        }
        return $map;
    }

    public static function getByOpportunity($opportunity_id) {
        $conn = Database::conn();
        $stmt = $conn->prepare(
            "SELECT a.id, a.status, a.message, a.applied_at, u.full_name, u.email, u.skills, u.phone
             FROM applications a
             JOIN users u ON u.id = a.volunteer_id
             WHERE a.opportunity_id = ?
             ORDER BY a.applied_at DESC"
        );
        $stmt->bind_param("i", $opportunity_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public static function setStatus($id, $status) {
        $conn = Database::conn();
        $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();
    }

    public static function monthlyStats($months = 6) {
        // $months is internal only; kept fixed query shape from original dashboard
        $rows = Database::conn()->query(
            "SELECT DATE_FORMAT(applied_at, '%Y-%m') AS month, COUNT(*) AS cnt
             FROM applications
             WHERE applied_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY month ORDER BY month"
        );
        $out = [];
        while ($r = $rows->fetch_assoc()) {
            $out[] = $r;
        }
        return $out;
    }
}
?>
