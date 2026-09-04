<?php
require_once __DIR__ . '/../../Helpers/Auth.php';
require_once __DIR__ . '/../../Helpers/Flash.php';
Auth::start();

$message = Flash::getError();
if ($message === "") {
    $message = Flash::getSuccess();
    $messageType = $message !== "" ? "success" : "";
} else {
    $messageType = "error";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VolCoord | Create Account</title>
    <link rel="stylesheet" href="../../../public/assets/css/style.css?v=2">
</head>

<body>

    <div class="header">
        <a href="../home/index.php" class="header-brand"><h1>VolCord</h1></a>
    </div>

    <div class="page-wrap">

        <h2 class="page-title">Create Account</h2>

        <?php if ($message): ?>
            <div class="<?= $messageType === "success" ? "flash-success" : "flash-error" ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <div id="ajax-message" class="flash-error" style="display:none;"></div>

        <div class="card-outer">
            <div class="form-panel">

                <form method="POST" action="../../Controllers/AuthController.php">

                    <div class="field">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name"
                               value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                    </div>

                    <div class="field">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="field">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                    </div>

                    <div class="field">
                        <label>Gender</label>
                        <div class="radio-group">
                            <?php foreach (["Male", "Female", "Other"] as $g): ?>
                                <label>
                                    <input type="radio" name="gender"
                                           value="<?= $g ?>" <?= ($_POST['gender'] ?? '') === $g ? 'checked' : '' ?> required>
                                    <?= $g ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="field">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="field">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div class="field">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <option value="" disabled <?= empty($_POST['role']) ? 'selected' : '' ?>>Select a role</option>
                            <option value="Volunteer" <?= ($_POST['role'] ?? '') === 'Volunteer' ? 'selected' : '' ?>>Volunteer</option>
                            <option value="Customer" <?= ($_POST['role'] ?? '') === 'Customer' ? 'selected' : '' ?>>Customer</option>
                        </select>
                    </div>

                    <div class="field" id="skills-field" style="display:none">
                        <label for="skills">Skills</label>
                        <textarea id="skills" name="skills"
                                  placeholder="e.g. First aid, teaching, logistics"><?= htmlspecialchars($_POST['skills'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn-primary">Create Account</button>
                    <a href="login.php" class="btn-secondary">Already have an account? Sign In</a>

                </form>

            </div>
        </div>

    </div>

    <script>
        (function () {
            var roleSelect = document.getElementById("role");
            var skillsField = document.getElementById("skills-field");

            function toggleSkills() {
                skillsField.style.display = roleSelect.value === "Volunteer" ? "" : "none";
            }

            roleSelect.addEventListener("change", toggleSkills);
            toggleSkills();

            var form = document.querySelector("form");
            var msgDiv = document.getElementById("ajax-message");
            var btn = form.querySelector("button[type=submit]");
            var origText = btn.textContent;

            form.addEventListener("submit", function(e) {
                e.preventDefault();
                btn.textContent = "Creating account...";
                btn.disabled = true;
                msgDiv.style.display = "none";

                var data = new FormData(form);
                data.append("action", "register");

                fetch("../../Controllers/AjaxController.php?action=register", {
                    method: "POST",
                    body: data
                })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        window.location.href = res.redirect;
                    } else {
                        msgDiv.textContent = res.message;
                        msgDiv.className = "flash-error";
                        msgDiv.style.display = "block";
                        btn.textContent = origText;
                        btn.disabled = false;
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
