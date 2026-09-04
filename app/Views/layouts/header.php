<?php
// Shared dashboard header (expects $userName to be set by the calling View)
$homeUrl = "../home/index.php";
$logoutUrl = "../../Controllers/AuthController.php?action=logout";
?>
<div class="header">
    <a href="<?= $homeUrl ?>" class="header-brand"><h1>VolCord</h1></a>
    <div class="header-right">
        <span class="welcome">Hi, <?= htmlspecialchars($userName ?? "") ?></span>
        <a href="<?= $logoutUrl ?>" class="btn-signout">Sign Out</a>
    </div>
</div>
