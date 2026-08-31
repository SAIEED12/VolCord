<?php
require_once "config.php";
session_start();

$message = "";
$messageType = ""; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $password  = $_POST["password"] ?? "";
    $confirm   = $_POST["confirm_password"] ?? "";
    $role      = $_POST["role"] ?? "";
    $skills    = null;
    $phone     = trim($_POST["phone"] ?? "");
    $gender    = trim($_POST["gender"] ?? "");
    $address   = trim($_POST["address"] ?? "");

    $allowed_roles   = ["Volunteer", "Customer"];
    $allowed_genders = ["Male", "Female", "Other"];

    if ($full_name === "" || $email === "" || $password === "" || $confirm === "" || $role === ""
        || $phone === "" || $gender === "" || $address === "") {
        $message = "All fields are required.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
        $messageType = "error";
    } elseif (!in_array($role, $allowed_roles, true)) {
        $message = "Please select a valid role.";
        $messageType = "error";
    } elseif (!in_array($gender, $allowed_genders, true)) {
        $message = "Please select a valid gender.";
        $messageType = "error";
    } else {

        if ($role === "Volunteer") {
            $skills = trim($_POST["skills"] ?? "");
            if ($skills === "") {
                $message = "Please list your skills.";
                $messageType = "error";
            }
        }
    }

    if ($messageType !== "error") {

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "An account with that email already exists.";
            $messageType = "error";
            $stmt->close();
        } else {
            $stmt->close();

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (full_name, email, password_hash, role, skills, phone, gender, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("ssssssss", $full_name, $email, $password_hash, $role, $skills, $phone, $gender, $address);

            if ($stmt->execute()) {
                $stmt->close();

                $_SESSION["volunteer_name"]  = $full_name;
                $_SESSION["volunteer_email"] = $email;
                $_SESSION["volunteer_id"]    = $conn->insert_id;
                $_SESSION["user_role"]       = $role;

                setcookie("volunteer_name",  $full_name, time() + 3600, "/");
                setcookie("volunteer_email", $email,     time() + 3600, "/");
                setcookie("user_role",       $role,      time() + 3600, "/");

                if (strtolower($role) === "customer") {
                    header("Location: customerDashboard.php");
                } else {
                    header("Location: volunteerDashboard.php");
                }
                exit;
            }

            $message = "Something went wrong. Please try again.";
            $messageType = "error";
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VolCoord | Create Account</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>

<body>

    <div class="header">
        <a href="index.php" class="header-brand"><h1>VolCord</h1></a>
    </div>

    <div class="page-wrap">

        <h2 class="page-title">Create Account</h2>

        <?php if ($message): ?>
            <div class="<?= $messageType === "success" ? "flash-success" : "flash-error" ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="card-outer">
            <div class="form-panel">

                <form method="POST">

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
        })();
    </script>

</body>

</html>