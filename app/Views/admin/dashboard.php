<?php
require_once __DIR__ . '/../../Helpers/Auth.php';
require_once __DIR__ . '/../../Controllers/DashboardController.php';
require_once __DIR__ . '/../../Models/Application.php';

Auth::requireRole("Admin", "../auth/login.php");

$userName = $_SESSION["volunteer_name"] ?? "";

$data = AdminController::data();
$pending = $data["pending"];
$approved = $data["approved"];
$total_users = $data["total_users"];
$total_volunteers = $data["total_volunteers"];
$opp_pending_count = $data["opp_pending_count"];
$opp_approved_count = $data["opp_approved_count"];
$opp_by_status = $data["opp_by_status"];
$apps_per_month = $data["apps_per_month"];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VolCord | Admin Dashboard</title>
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
</head>

<body>

    <?php include __DIR__ . '/../layouts/header.php'; ?>

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
            <?php if (count($pending) === 0): ?>
                <p class="empty-note">No opportunities awaiting approval.</p>
            <?php else: ?>
                <?php foreach ($pending as $op): ?>
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
                            <a class="btn-approve" href="../../Controllers/OpportunityController.php?id=<?= $op["id"] ?>&action=approve">Approve</a>
                            <a class="btn-reject" href="../../Controllers/OpportunityController.php?id=<?= $op["id"] ?>&action=reject">Reject</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <section class="dash-card wide">
            <h2>Approved Opportunities</h2>
            <?php if (count($approved) === 0): ?>
                <p class="empty-note">No approved opportunities yet.</p>
            <?php else: ?>
                <?php foreach ($approved as $op): ?>
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

                        <?php $app_rows = Application::getByOpportunity($op["id"]); ?>
                        <?php if (count($app_rows) === 0): ?>
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
                                    <?php foreach ($app_rows as $app): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($app["full_name"]) ?></td>
                                            <td><?= $app["skills"] ? htmlspecialchars($app["skills"]) : "—" ?></td>
                                            <td><?= htmlspecialchars($app["email"]) ?><br><?= htmlspecialchars($app["phone"]) ?></td>
                                            <td><span class="badge badge-<?= strtolower($app["status"]) ?>"><?= htmlspecialchars(ucfirst($app["status"])) ?></span></td>
                                            <td>
                                                <?php if ($app["status"] === "pending"): ?>
                                                    <a class="btn-approve-sm" href="../../Controllers/ApplicationController.php?id=<?= $app["id"] ?>&action=accept">Accept</a>
                                                    <a class="btn-reject-sm" href="../../Controllers/ApplicationController.php?id=<?= $app["id"] ?>&action=reject">Reject</a>
                                                <?php else: ?>
                                                    <span class="muted"><?= ucfirst($app["status"]) ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
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

        document.querySelectorAll(".opp-actions a").forEach(function(link) {
            link.addEventListener("click", function(e) {
                e.preventDefault();
                var btn = this;
                var isApprove = btn.classList.contains("btn-approve");
                var actionType = isApprove ? "approve" : "reject";
                var id = new URL(btn.href).searchParams.get("id");
                var origText = btn.textContent;
                btn.textContent = "...";
                btn.style.pointerEvents = "none";

                var formData = new FormData();
                formData.append("action", "review_opportunity");
                formData.append("id", id);
                formData.append("action_type", actionType);

                fetch("../../Controllers/AjaxController.php?action=review_opportunity", {
                    method: "POST",
                    body: formData
                })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        var card = btn.closest(".opp-item");
                        card.style.opacity = "0";
                        card.style.transition = "opacity 0.3s";
                        setTimeout(function() { card.remove(); }, 300);
                    } else {
                        alert(res.message);
                        btn.textContent = origText;
                        btn.style.pointerEvents = "";
                    }
                })
                .catch(function() {
                    alert("Network error. Please try again.");
                    btn.textContent = origText;
                    btn.style.pointerEvents = "";
                });
            });
        });

        document.querySelectorAll(".btn-approve-sm, .btn-reject-sm").forEach(function(link) {
            link.addEventListener("click", function(e) {
                e.preventDefault();
                var btn = this;
                var isAccept = btn.classList.contains("btn-approve-sm");
                var actionType = isAccept ? "accept" : "reject";
                var id = new URL(btn.href).searchParams.get("id");
                var origText = btn.textContent;
                btn.textContent = "...";
                btn.style.pointerEvents = "none";

                var formData = new FormData();
                formData.append("action", "review_application");
                formData.append("id", id);
                formData.append("action_type", actionType);

                fetch("../../Controllers/AjaxController.php?action=review_application", {
                    method: "POST",
                    body: formData
                })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        var td = btn.closest("td");
                        var newStatus = actionType === "accept" ? "Accepted" : "Rejected";
                        var badgeClass = actionType === "accept" ? "badge-accepted" : "badge-rejected";
                        td.innerHTML = '<span class="badge ' + badgeClass + '">' + newStatus + '</span>';
                    } else {
                        alert(res.message);
                        btn.textContent = origText;
                        btn.style.pointerEvents = "";
                    }
                })
                .catch(function() {
                    alert("Network error. Please try again.");
                    btn.textContent = origText;
                    btn.style.pointerEvents = "";
                });
            });
        });
    </script>

</body>

</html>
