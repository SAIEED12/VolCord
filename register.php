<?php
session_start();

$flash_error = "";
if (isset($_SESSION["flash_error"])) {
    $flash_error = $_SESSION["flash_error"];
    unset($_SESSION["flash_error"]);
}

// Preserve previously entered values if validation failed on submit.
$old = isset($_SESSION["flash_old"]) ? $_SESSION["flash_old"] : [];
unset($_SESSION["flash_old"]);

function old($key, $old)
{
    return isset($old[$key]) ? htmlspecialchars($old[$key]) : "";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>VolCord | Create Account</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

    <div class="header">
        <h1>VolCord</h1>
    </div>

    <div class="page-wrap">

        <h2 class="page-title">Create Account</h2>
        <p class="page-subtitle">Join our volunteer community</p>

        <?php if ($flash_error): ?>
            <div class="flash-error"><?php echo htmlspecialchars($flash_error); ?></div>
        <?php endif; ?>

        <form action="submit.php" method="POST">

            <div class="card-outer">

                <!-- Left panel -->
                <div class="form-panel">

                    <div class="field">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" placeholder="Enter first name"
                            value="<?php echo old('first_name', $old); ?>" required>
                    </div>

                    <div class="field">
                        <label for="nick_name">Nick Name</label>
                        <input type="text" id="nick_name" name="nick_name" placeholder="Enter nick name"
                            value="<?php echo old('nick_name', $old); ?>">
                    </div>

                    <div class="field">
                        <label for="emergency_contact">Emergency - Contact</label>
                        <input type="text" id="emergency_contact" name="emergency_contact"
                            placeholder="Enter emergency contact number"
                            value="<?php echo old('emergency_contact', $old); ?>" required>
                    </div>

                    <div class="field">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter email address"
                            value="<?php echo old('email', $old); ?>" required>
                    </div>

                    <div class="field">
                        <label for="permanent_address">Permanent Address</label>
                        <input type="text" id="permanent_address" name="permanent_address"
                            placeholder="Enter permanent address"
                            value="<?php echo old('permanent_address', $old); ?>" required>
                    </div>

                    <div class="field">
                        <label for="blood_group">Blood Group</label>
                        <select id="blood_group" name="blood_group" required>
                            <option value="" disabled <?php echo old('blood_group', $old) === "" ? "selected" : ""; ?>>Select blood group</option>
                            <?php foreach (["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"] as $bg): ?>
                                <option value="<?php echo $bg; ?>" <?php echo old('blood_group', $old) === $bg ? "selected" : ""; ?>>
                                    <?php echo $bg; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password"
                            placeholder="Create password (min 6 chars)" minlength="6" required>
                    </div>

                    <button type="submit" name="submit" class="btn-primary">Register</button>

                </div>

                <!-- Right panel -->
                <div class="form-panel">

                    <div class="field">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Enter last name"
                            value="<?php echo old('last_name', $old); ?>" required>
                    </div>

                    <div class="field">
                        <label for="contract">Contract</label>
                        <input type="text" id="contract" name="contract" placeholder="Enter contract number"
                            value="<?php echo old('contract', $old); ?>" required>
                    </div>

                    <div class="field">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" value="<?php echo old('dob', $old); ?>" required>
                    </div>

                    <div class="field">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" placeholder="Enter address"
                            value="<?php echo old('address', $old); ?>" required>
                    </div>

                    <div class="field">
                        <label>Gender</label>
                        <div class="radio-group">
                            <label><input type="radio" name="gender" value="Male"
                                    <?php echo (old('gender', $old) === "Male" || old('gender', $old) === "") ? "checked" : ""; ?>>
                                Male</label>
                            <label><input type="radio" name="gender" value="Female"
                                    <?php echo old('gender', $old) === "Female" ? "checked" : ""; ?>> Female</label>
                            <label><input type="radio" name="gender" value="Others"
                                    <?php echo old('gender', $old) === "Others" ? "checked" : ""; ?>> Others</label>
                        </div>
                    </div>

                    <div class="field">
                        <label for="designation">Designation</label>
                        <select id="designation" name="designation">
                            <?php foreach (["Volunteer", "Coordinator", "Team Lead", "Admin"] as $d): ?>
                                <option value="<?php echo $d; ?>" <?php echo old('designation', $old) === $d || (old('designation', $old) === "" && $d === "Volunteer") ? "selected" : ""; ?>>
                                    <?php echo $d; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                            placeholder="Confirm your password" minlength="6" required>
                    </div>

                    <a href="index.php" class="btn-secondary">Back to Login</a>

                </div>

            </div>

        </form>

    </div>

</body>

</html>
