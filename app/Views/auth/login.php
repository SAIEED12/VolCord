<?php
require_once __DIR__ . '/../../Helpers/Auth.php';
require_once __DIR__ . '/../../Helpers/Flash.php';
Auth::start();

$flash_error = Flash::getError();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VolCoord | Sign In</title>
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
</head>

<body class="login-page">

    <div class="login-shell">

       <div class="login-hero">
    <div class="login-hero-tint"></div>
    <a href="../home/index.php" class="brand-link">
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
                <div id="ajax-error" class="flash-error" style="display:none;"></div>
                <?php if ($flash_error !== ""): ?>
                    <div class="flash-error"><?= htmlspecialchars($flash_error) ?></div>
                <?php endif; ?>
                <p style="font-weight: 600; color: #333;">Welcome to Volcord!</p>
                <h1 class="login-title">Sign in to your account</h1>

                <form action="../../Controllers/AuthController.php" method="POST">

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

    <script>
        document.querySelector("form").addEventListener("submit", function(e) {
            e.preventDefault();
            var form = this;
            var errDiv = document.getElementById("ajax-error");
            var btn = form.querySelector("button[type=submit]");
            var origText = btn.textContent;
            btn.textContent = "Signing in...";
            btn.disabled = true;
            errDiv.style.display = "none";

            var data = new FormData(form);
            data.append("action", "login");

            fetch("../../Controllers/AjaxController.php?action=login", {
                method: "POST",
                body: data
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    window.location.href = res.redirect;
                } else {
                    errDiv.textContent = res.message;
                    errDiv.style.display = "block";
                    btn.textContent = origText;
                    btn.disabled = false;
                }
            })
            .catch(function() {
                errDiv.textContent = "Network error. Please try again.";
                errDiv.style.display = "block";
                btn.textContent = origText;
                btn.disabled = false;
            });
        });
    </script>
</body>

</html>
