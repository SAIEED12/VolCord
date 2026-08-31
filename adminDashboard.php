<?php
require_once "config.php";
session_start();

if (($_SESSION["user_role"] ?? "") !== "Admin") {
    header("Location: login.php");
    exit;
}

$pending = $conn->query(
    "SELECT o.id, o.title, o.location, o.needed_date, o.description, o.required_skills, u.full_name AS customer
     FROM opportunities o
     JOIN users u ON u.id = o.customer_id
     WHERE o.status = 'pending'
     ORDER BY o.created_at DESC"
);

$approved = $conn->query(
    "SELECT o.id, o.title, o.location, o.needed_date, o.status,
            (SELECT COUNT(*) FROM applications a WHERE a.opportunity_id = o.id AND a.status = 'accepted') AS assigned_count,
            (SELECT COUNT(*) FROM applications a WHERE a.opportunity_id = o.id) AS total_apps
     FROM opportunities o
     WHERE o.status IN ('approved', 'completed')
     ORDER BY o.created_at DESC"
);

$total_users = $conn->query("SELECT COUNT(*) AS cnt FROM users")->fetch_assoc()["cnt"];
$total_volunteers = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE role='Volunteer'")->fetch_assoc()["cnt"];

$opp_pending_count = $conn->query("SELECT COUNT(*) AS cnt FROM opportunities WHERE status='pending'")->fetch_assoc()["cnt"];
$opp_approved_count = $conn->query("SELECT COUNT(*) AS cnt FROM opportunities WHERE status='approved'")->fetch_assoc()["cnt"];

$opp_by_status_rows = $conn->query("SELECT status, COUNT(*) AS cnt FROM opportunities GROUP BY status");
$opp_by_status = [];
while ($row = $opp_by_status_rows->fetch_assoc()) {
    $opp_by_status[$row["status"]] = (int) $row["cnt"];
}

$apps_per_month_rows = $conn->query(
    "SELECT DATE_FORMAT(applied_at, '%Y-%m') AS month, COUNT(*) AS cnt
     FROM applications
     WHERE applied_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY month ORDER BY month"
);
$apps_per_month = [];
while ($row = $apps_per_month_rows->fetch_assoc()) {
    $apps_per_month[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VolCord | Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="header">
        <h1>VolCord</h1>
        <div class="header-right">
            <span class="welcome">Hi, <?= htmlspecialchars($_SESSION["volunteer_name"]) ?></span>
            <a href="logout.php" class="btn-signout">Sign Out</a>
        </div>
    </div>

    <div class="page-wrap">

        <h2 class="page-title">Admin Dashboard</h2>
        <p class="page-subtitle">Review opportunities and assign volunteers.</p>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= (int) $total_users ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= (int) $total_volunteers ?></div>
                <div class="stat-label">Volunteers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= (int) $opp_approved_count ?></div>
                <div class="stat-label">Active Opportunities</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= (int) $opp_pending_count ?></div>
                <div class="stat-label">Pending Approvals</div>
            </div>
        </div>

        <div class="chart-row">
            <div class="chart-card">
                <h3>Opportunities by Status</h3>
                <div class="chart-container">
                    <canvas id="oppStatusChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <h3>Applications (Last 6 Months)</h3>
                <div class="chart-container">
                    <canvas id="appsMonthlyChart"></canvas>
                </div>
            </div>
        </div>

        <section class="dash-card wide">
            <h2>Pending Approval</h2>
            <?php if ($pending->num_rows === 0): ?>
                <p class="empty-note">No opportunities awaiting approval.</p>
            <?php else: ?>
                <?php while ($op = $pending->fetch_assoc()): ?>
                    <div class="opp-item">
                        <div class="opp-item-head">
                            <strong><?= htmlspecialchars($op["title"]) ?></strong>
                            <span class="badge badge-pending">Pending</span>
                        </div>
                        <div class="opp-meta">
                            Posted by <?= htmlspecialchars($op["customer"]) ?> &middot;
                            <?= htmlspecialchars($op["location"]) ?>
                            <?php if ($op["needed_date"]): ?>&middot; <?= htmlspecialchars($op["needed_date"]) ?><?php endif; ?>
                        </div>
                        <p class="opp-desc"><?= nl2br(htmlspecialchars($op["description"])) ?></p>
                        <?php if ($op["required_skills"]): ?>
                            <p class="opp-skills">Skills: <?= htmlspecialchars($op["required_skills"]) ?></p>
                        <?php endif; ?>
                        <div class="opp-actions">
                            <a class="btn-approve" href="review_opportunity.php?id=<?= $op["id"] ?>&action=approve">Approve</a>
                            <a class="btn-reject" href="review_opportunity.php?id=<?= $op["id"] ?>&action=reject">Reject</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </section>

        <section class="dash-card wide">
            <h2>Approved Opportunities</h2>
            <?php if ($approved->num_rows === 0): ?>
                <p class="empty-note">No approved opportunities yet.</p>
            <?php else: ?>
                <?php while ($op = $approved->fetch_assoc()): ?>
                    <div class="opp-item">
                        <div class="opp-item-head">
                            <strong><?= htmlspecialchars($op["title"]) ?></strong>
                            <span class="badge badge-<?= strtolower($op["status"]) ?>"><?= htmlspecialchars(ucfirst($op["status"])) ?></span>
                        </div>
                        <div class="opp-meta">
                            <?= htmlspecialchars($op["location"]) ?>
                            <?php if ($op["needed_date"]): ?>&middot; <?= htmlspecialchars($op["needed_date"]) ?><?php endif; ?>
                            &middot; <?= (int) $op["assigned_count"] ?> assigned / <?= (int) $op["total_apps"] ?> applied
                        </div>

                        <?php
                        $apps = $conn->prepare(
                            "SELECT a.id, a.status, a.message, a.applied_at, u.full_name, u.email, u.skills, u.phone
                             FROM applications a
                             JOIN users u ON u.id = a.volunteer_id
                             WHERE a.opportunity_id = ?
                             ORDER BY a.applied_at DESC"
                        );
                        $apps->bind_param("i", $op["id"]);
                        $apps->execute();
                        $app_result = $apps->get_result();
                        ?>
                        <?php if ($app_result->num_rows === 0): ?>
                            <p class="empty-note">No applications yet.</p>
                        <?php else: ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Volunteer</th>
                                        <th>Skills</th>
                                        <th>Contact</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($app = $app_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($app["full_name"]) ?></td>
                                            <td><?= $app["skills"] ? htmlspecialchars($app["skills"]) : "—" ?></td>
                                            <td><?= htmlspecialchars($app["email"]) ?><br><?= htmlspecialchars($app["phone"]) ?></td>
                                            <td><span class="badge badge-<?= strtolower($app["status"]) ?>"><?= htmlspecialchars(ucfirst($app["status"])) ?></span></td>
                                            <td>
                                                <?php if ($app["status"] === "pending"): ?>
                                                    <a class="btn-approve-sm" href="review_application.php?id=<?= $app["id"] ?>&action=accept">Accept</a>
                                                    <a class="btn-reject-sm" href="review_application.php?id=<?= $app["id"] ?>&action=reject">Reject</a>
                                                <?php else: ?>
                                                    <span class="muted"><?= ucfirst($app["status"]) ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                        <?php $apps->close(); ?>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </section>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const oppStatusData = <?= json_encode($opp_by_status) ?>;
        const appsMonthlyData = <?= json_encode($apps_per_month) ?>;

        const statusLabels = Object.keys(oppStatusData);
        const statusValues = Object.values(oppStatusData);
        const statusColors = { pending: '#f59e0b', approved: '#10b981', rejected: '#ef4444', completed: '#1e3a5f' };

        new Chart(document.getElementById('oppStatusChart'), {
            type: 'doughnut',
            data: {
                labels: statusLabels.map(s => s.charAt(0).toUpperCase() + s.slice(1)),
                datasets: [{ data: statusValues, backgroundColor: statusLabels.map(s => statusColors[s] || '#94a3b8'), borderWidth: 2, borderColor: '#fff' }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { padding: 16, font: { size: 13 } } } } }
        });

        const monthLabels = appsMonthlyData.map(d => { const [y, m] = d.month.split('-'); return new Date(y, m - 1).toLocaleDateString('en-US', { month: 'short', year: '2-digit' }); });
        const monthValues = appsMonthlyData.map(d => d.cnt);

        new Chart(document.getElementById('appsMonthlyChart'), {
            type: 'bar',
            data: { labels: monthLabels, datasets: [{ label: 'Applications', data: monthValues, backgroundColor: '#1e3a5f', borderRadius: 6, maxBarThickness: 40 }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }, plugins: { legend: { display: false } } }
        });
    </script>

</body>

</html>
