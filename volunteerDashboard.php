<?php
require_once "config.php";
session_start();

if (($_SESSION["user_role"] ?? "") !== "Volunteer") {
    header("Location: login.php");
    exit;
}

$volunteer_id = $_SESSION["volunteer_id"];

$flash_error = "";
$flash_success = "";
if (isset($_SESSION["flash_error"])) { $flash_error = $_SESSION["flash_error"]; unset($_SESSION["flash_error"]); }
if (isset($_SESSION["flash_success"])) { $flash_success = $_SESSION["flash_success"]; unset($_SESSION["flash_success"]); }

$open = $conn->query(
    "SELECT o.id, o.title, o.location, o.needed_date, o.description, o.required_skills, u.full_name AS customer
     FROM opportunities o
     JOIN users u ON u.id = o.customer_id
     WHERE o.status = 'approved'
     ORDER BY o.needed_date ASC, o.created_at DESC"
);

$applied_ids = [];
$my_apps = $conn->prepare(
    "SELECT a.id, a.status, a.message, a.applied_at, o.title, o.location
     FROM applications a
     JOIN opportunities o ON o.id = a.opportunity_id
     WHERE a.volunteer_id = ?
     ORDER BY a.applied_at DESC"
);
$my_apps->bind_param("i", $volunteer_id);
$my_apps->execute();
$my_apps_result = $my_apps->get_result();
while ($r = $my_apps_result->fetch_assoc()) {
    $applied_ids[$r["id"]] = $r["status"];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VolCord | Volunteer Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="header">
        <a href="index.php" class="header-brand"><h1>VolCord</h1></a>
        <div class="header-right">
            <span class="welcome">Hi, <?= htmlspecialchars($_SESSION["volunteer_name"]) ?></span>
            <a href="logout.php" class="btn-signout">Sign Out</a>
        </div>
    </div>

    <div class="page-wrap">

        <h2 class="page-title">Volunteer Dashboard</h2>
        <p class="page-subtitle">Browse approved opportunities and apply to help.</p>

        <?php if ($flash_error): ?><div class="flash-error"><?= htmlspecialchars($flash_error) ?></div><?php endif; ?>
        <?php if ($flash_success): ?><div class="flash-success"><?= htmlspecialchars($flash_success) ?></div><?php endif; ?>

        <section class="dash-card wide">
            <h2>Open Opportunities</h2>
            <?php if ($open->num_rows === 0): ?>
                <p class="empty-note">No open opportunities available right now.</p>
            <?php else: ?>
                <?php while ($op = $open->fetch_assoc()): ?>
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
                            <form method="POST" action="apply.php" class="apply-form">
                                <input type="hidden" name="opportunity_id" value="<?= $op["id"] ?>">
                                <div class="field">
                                    <textarea name="message" placeholder="Why would you like to help? (optional)"></textarea>
                                </div>
                                <button type="submit" class="btn-primary">Apply</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </section>

        <section class="dash-card wide">
            <h2>My Applications</h2>
            <?php
            $my_apps->execute();
            $my_apps_result = $my_apps->get_result();
            if ($my_apps_result->num_rows === 0):
            ?>
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
                        <?php while ($app = $my_apps_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($app["title"]) ?></td>
                                <td><?= htmlspecialchars($app["location"]) ?></td>
                                <td><?= htmlspecialchars($app["applied_at"]) ?></td>
                                <td><span class="badge badge-<?= strtolower($app["status"]) ?>"><?= htmlspecialchars(ucfirst($app["status"])) ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

    </div>

</body>

</html>
