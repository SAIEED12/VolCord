<?php
session_start();

$flash_error = "";
if (isset($_SESSION["flash_error"])) {
    $flash_error = $_SESSION["flash_error"];
    unset($_SESSION["flash_error"]);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VolCoord | Sign In</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="login-page">

    <div class="login-shell">

       <div class="login-hero">
    <div class="login-hero-tint"></div>
    <a href="index.php" class="brand-link">
        <div class="brand-mark">
            <span class="brand-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="8" r="4" fill="#fff" />
                    <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#fff" stroke-width="2" stroke-linecap="round" />
                    <path d="M18 4l1.4 1.4L22 3" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>
            <span class="brand-word">VOLCORD</span>
        </div>
    </a>

    <div class="hero-copy">
        <h2 class="hero-heading">Where Volunteers Power the Game</h2>
        <p class="hero-subtext">Coordinate schedules, track shifts, and keep every event running smoothly all in one place. Built for the people who make it happen.</p>
    </div>
</div>

        <div class="login-form-side">
            <div class="login-form-inner">
                <?php if ($flash_error !== ""): ?>
                    <div class="flash-error"><?= htmlspecialchars($flash_error) ?></div>
                <?php endif; ?>
                <p style="font-weight: 600; color: #333;">Welcome to Volcord!</p>
                <h1 class="login-title">Sign in to your account</h1>

                <form action="login_submit.php" method="POST">

                    <div class="field">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <button type="submit" name="login" class="btn-signin">Sign In</button>

                </form>
                <div style="display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 12px;">
                    <p>New to Volcord?</p>
                    <a class="create-account" href="register.php">Create Account</a>
                </div>
            </div>
        </div>

    </div>

</body>

</html>