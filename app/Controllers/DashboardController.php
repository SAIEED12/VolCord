<?php
// Controller: data providers for dashboards (keeps SQL out of Views)
require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Opportunity.php';
require_once __DIR__ . '/../Models/Application.php';

class VolunteerController {
    public static function data($volunteer_id) {
        $openResult = Opportunity::getOpen();
        $open = [];
        while ($row = $openResult->fetch_assoc()) { $open[] = $row; }
        $myApps = Application::getByVolunteer($volunteer_id);
        $appliedMap = [];
        foreach ($myApps as $r) { $appliedMap[$r["opportunity_id"]] = $r["status"]; }
        return ["open" => $open, "myApps" => $myApps, "appliedMap" => $appliedMap];
    }
}

class CustomerController {
    public static function data($customer_id) {
        return ["opportunities" => Opportunity::getByCustomer($customer_id)];
    }
}

class AdminController {
    public static function data() {
        $pendingResult = Opportunity::getPending();
        $pending = [];
        while ($row = $pendingResult->fetch_assoc()) { $pending[] = $row; }
        $approvedResult = Opportunity::getApprovedWithCounts();
        $approved = [];
        while ($row = $approvedResult->fetch_assoc()) { $approved[] = $row; }
        return [
            "pending" => $pending,
            "approved" => $approved,
            "total_users" => User::countAll(),
            "total_volunteers" => User::countVolunteers(),
            "opp_pending_count" => Opportunity::countByStatus("pending"),
            "opp_approved_count" => Opportunity::countByStatus("approved"),
            "opp_by_status" => Opportunity::groupedByStatus(),
            "apps_per_month" => Application::monthlyStats(),
        ];
    }
}
?>
