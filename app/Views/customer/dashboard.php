<?php
require_once __DIR__ . '/../../Helpers/Auth.php';
require_once __DIR__ . '/../../Helpers/Flash.php';
require_once __DIR__ . '/../../Controllers/DashboardController.php';

Auth::requireRole("Customer", "../auth/login.php");

$customer_id = $_SESSION["volunteer_id"];
$userName = $_SESSION["volunteer_name"] ?? "";

$flash_error = Flash::getError();
$flash_success = Flash::getSuccess();

$data = CustomerController::data($customer_id);
$opps = $data["opportunities"];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VolCord | Customer Dashboard</title>
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
</head>

<body>

    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <div class="page-wrap">

        <h2 class="page-title">Customer Dashboard</h2>
        <p class="page-subtitle">Post volunteer opportunities and track their progress.</p>

        <?php if ($flash_error): ?><div class="flash-error"><?= htmlspecialchars($flash_error) ?></div><?php endif; ?>
        <?php if ($flash_success): ?><div class="flash-success"><?= htmlspecialchars($flash_success) ?></div><?php endif; ?>
        <div id="ajax-message" class="flash-success" style="display:none;"></div>

        <div class="dash-grid">

            <section class="dash-card wide">
                <h2>Post a New Opportunity</h2>
                <form method="POST" action="../../Controllers/OpportunityController.php" class="form-panel">
                    <div class="field">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    <div class="field">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" required>
                        </div>
                        <div class="field">
                            <label for="needed_date">Date Needed</label>
                            <input type="datetime-local" id="needed_date" name="needed_date">
                        </div>
                    </div>
                    <div class="field">
                        <label for="required_skills">Required Skills</label>
                        <input type="text" id="required_skills" name="required_skills" placeholder="e.g. First aid, teaching">
                    </div>
                    <button type="submit" class="btn-primary">Post Opportunity</button>
                </form>
            </section>

            <section class="dash-card wide">
                <h2>Your Opportunities</h2>
                <?php if (count($opps) === 0): ?>
                    <p class="empty-note">You have not posted any opportunities yet.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Location</th>
                                <th>Date Needed</th>
                                <th>Status</th>
                                <th>Applications</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($opps as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row["title"]) ?></td>
                                    <td><?= htmlspecialchars($row["location"]) ?></td>
                                    <td><?= $row["needed_date"] ? htmlspecialchars($row["needed_date"]) : "—" ?></td>
                                    <td><span class="badge badge-<?= strtolower($row["status"]) ?>"><?= htmlspecialchars(ucfirst($row["status"])) ?></span></td>
                                    <td><?= (int) $row["applications_count"] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

        </div>

    </div>

    <script>
        (function() {
            var form = document.querySelector("form.form-panel");
            var msgDiv = document.getElementById("ajax-message");
            var btn = form.querySelector("button[type=submit]");
            var origText = btn.textContent;

            form.addEventListener("submit", function(e) {
                e.preventDefault();
                btn.textContent = "Posting...";
                btn.disabled = true;
                msgDiv.style.display = "none";

                var data = new FormData(form);
                data.append("action", "post_opportunity");

                fetch("../../Controllers/AjaxController.php?action=post_opportunity", {
                    method: "POST",
                    body: data
                })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    btn.textContent = origText;
                    btn.disabled = false;
                    msgDiv.textContent = res.message;
                    msgDiv.className = res.success ? "flash-success" : "flash-error";
                    msgDiv.style.display = "block";
                    if (res.success) {
                        form.reset();
                    }
                })
                .catch(function() {
                    msgDiv.textContent = "Network error. Please try again.";
                    msgDiv.className = "flash-error";
                    msgDiv.style.display = "block";
                    btn.textContent = origText;
                    btn.disabled = false;
                });
            });
        })();
    </script>
</body>

</html>
