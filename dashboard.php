<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>VolCord | Dashboard</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>

<body>

    <div class="header">
        <h1>VolCord</h1>
    </div>

    <div class="page-wrap">

        <h2 class="page-title">Volunteer Dashboard</h2>
        <p class="page-subtitle">Session &amp; cookie status for this browser</p>

        <div class="dash-card">
            <h2>Session Data</h2>
            <?php if (isset($_SESSION["volunteer_name"])): ?>
                <div class="dash-row"><span>Status</span><span>Session exists</span></div>
                <div class="dash-row"><span>Name</span><span><?php echo htmlspecialchars($_SESSION["volunteer_name"]); ?></span></div>
                <div class="dash-row"><span>Volunteer ID</span><span><?php echo htmlspecialchars($_SESSION["volunteer_id"]); ?></span></div>
                <?php if (isset($_SESSION["volunteer_email"])): ?>
                    <div class="dash-row"><span>Email</span><span><?php echo htmlspecialchars($_SESSION["volunteer_email"]); ?></span></div>
                <?php endif; ?>
                <?php if (isset($_SESSION["volunteer_designation"])): ?>
                    <div class="dash-row"><span>Designation</span><span><?php echo htmlspecialchars($_SESSION["volunteer_designation"]); ?></span></div>
                <?php endif; ?>
            <?php else: ?>
                <div class="dash-row"><span>Status</span><span>No session.</span></div>
            <?php endif; ?>
        </div>

        <div class="dash-card">
            <h2>Cookie Data</h2>
            <?php if (isset($_COOKIE["volunteer_name"])): ?>
                <div class="dash-row"><span>Status</span><span>Cookie exists</span></div>
                <div class="dash-row"><span>Cookie: volunteer_name</span><span><?php echo htmlspecialchars($_COOKIE["volunteer_name"]); ?></span></div>
                <?php if (isset($_COOKIE["volunteer_email"])): ?>
                    <div class="dash-row"><span>Cookie: volunteer_email</span><span><?php echo htmlspecialchars($_COOKIE["volunteer_email"]); ?></span></div>
                <?php endif; ?>
            <?php else: ?>
                <div class="dash-row"><span>Status</span><span>No cookie.</span></div>
            <?php endif; ?>
        </div>

        <div class="dash-actions">
            <a href="logout.php" class="btn-secondary">Remove Session</a>
            <a href="remove_cookie.php" class="btn-secondary">Remove Cookie</a>
        </div>

    </div>

</body>

</html>
