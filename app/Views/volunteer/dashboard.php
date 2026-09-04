<?php
require_once __DIR__ . '/../../Helpers/Auth.php';
require_once __DIR__ . '/../../Helpers/Flash.php';
require_once __DIR__ . '/../../Controllers/DashboardController.php';

Auth::requireRole("Volunteer", "../auth/login.php");

$volunteer_id = $_SESSION["volunteer_id"];
$userName = $_SESSION["volunteer_name"] ?? "";

$flash_error = Flash::getError();
$flash_success = Flash::getSuccess();

$data = VolunteerController::data($volunteer_id);
$open = $data["open"];
$myApps = $data["myApps"];
$applied_ids = $data["appliedMap"];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VolCord | Volunteer Dashboard</title>
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
</head>

<body>

    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <div class="page-wrap">

        <h2 class="page-title">Volunteer Dashboard</h2>
        <p class="page-subtitle">Browse approved opportunities and apply to help.</p>

        <?php if ($flash_error): ?><div class="flash-error"><?= htmlspecialchars($flash_error) ?></div><?php endif; ?>
        <?php if ($flash_success): ?><div class="flash-success"><?= htmlspecialchars($flash_success) ?></div><?php endif; ?>

        <section class="dash-card wide">
            <h2>Open Opportunities</h2>
            <?php if (count($open) === 0): ?>
                <p class="empty-note">No open opportunities available right now.</p>
            <?php else: ?>
                <?php foreach ($open as $op): ?>
                    <div class="opp-item">
                        <div class="opp-item-head">
                            <strong><?= htmlspecialchars($op["title"]) ?></strong>
                        </div>
                        <div class="opp-meta">
                            For <?= htmlspecialchars($op["customer"]) ?>
                            &middot; <?= htmlspecialchars($op["location"]) ?>
                            <?php if ($op["needed_date"]): ?>&middot; <?= htmlspecialchars($op["needed_date"]) ?><?php endif; ?>
                        </div>
                        <p class="opp-desc"><?= nl2br(htmlspecialchars($op["description"])) ?></p>
                        <?php if ($op["required_skills"]): ?>
                            <p class="opp-skills">Skills: <?= htmlspecialchars($op["required_skills"]) ?></p>
                        <?php endif; ?>

                        <?php if (isset($applied_ids[$op["id"]])): ?>
                            <p class="applied-note">Application: <span class="badge badge-<?= strtolower($applied_ids[$op["id"]]) ?>"><?= htmlspecialchars(ucfirst($applied_ids[$op["id"]])) ?></span></p>
                        <?php else: ?>
                            <form method="POST" action="../../Controllers/ApplicationController.php" class="apply-form">
                                <input type="hidden" name="opportunity_id" value="<?= $op["id"] ?>">
                                <div class="field">
                                    <textarea name="message" placeholder="Why would you like to help? (optional)"></textarea>
                                </div>
                                <button type="submit" class="btn-primary">Apply</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <section class="dash-card wide">
            <h2>My Applications</h2>
            <?php if (count($myApps) === 0): ?>
                <p class="empty-note">You have not applied for any opportunities yet.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Opportunity</th>
                            <th>Location</th>
                            <th>Applied</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myApps as $app): ?>
                            <tr>
                                <td><?= htmlspecialchars($app["title"]) ?></td>
                                <td><?= htmlspecialchars($app["location"]) ?></td>
                                <td><?= htmlspecialchars($app["applied_at"]) ?></td>
                                <td><span class="badge badge-<?= strtolower($app["status"]) ?>"><?= htmlspecialchars(ucfirst($app["status"])) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

    </div>

    <script>
        document.querySelectorAll(".apply-form").forEach(function(form) {
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                var f = this;
                var btn = f.querySelector("button[type=submit]");
                var origText = btn.textContent;
                btn.textContent = "Applying...";
                btn.disabled = true;

                var data = new FormData(f);
                data.append("action", "apply");

                fetch("../../Controllers/AjaxController.php?action=apply", {
                    method: "POST",
                    body: data
                })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        var badge = document.createElement("p");
                        badge.className = "applied-note";
                        badge.innerHTML = "Application: <span class=\"badge badge-pending\">Pending</span>";
                        f.replaceWith(badge);
                    } else {
                        alert(res.message);
                        btn.textContent = origText;
                        btn.disabled = false;
                    }
                })
                .catch(function() {
                    alert("Network error. Please try again.");
                    btn.textContent = origText;
                    btn.disabled = false;
                });
            });
        });
    </script>
</body>

</html>
