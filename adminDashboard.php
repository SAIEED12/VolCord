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

</body>

</html>
