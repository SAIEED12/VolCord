<?php

require_once "config.php";
session_start();

if (isset($_POST["login"])) {

    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $error = "";

    if ($email === "" || $password === "") {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $error = "No account found with that email.";
            $stmt->close();
        } else {
            $stmt->bind_result($user_id, $full_name, $user_email, $password_hash, $user_role);
            $stmt->fetch();
            $stmt->close();

            if (!password_verify($password, $password_hash)) {
                $error = "Invalid email or password.";
            }
        }
    }

    if ($error !== "") {
        $_SESSION["flash_error"] = $error;
        header("Location: login.php");
        exit();
    }

    $_SESSION["volunteer_name"]  = $full_name;
    $_SESSION["volunteer_email"] = $user_email;
    $_SESSION["volunteer_id"]    = $user_id;
    $_SESSION["user_role"]       = $user_role;

    setcookie("volunteer_name",  $full_name, time() + 3600, "/");
    setcookie("volunteer_email", $user_email, time() + 3600, "/");
    setcookie("user_role",       $user_role, time() + 3600, "/");

    $role_target = strtolower($user_role);
    if ($role_target === "customer") {
        header("Location: customerDashboard.php");
    } elseif ($role_target === "admin") {
        header("Location: adminDashboard.php");
    } else {
        header("Location: volunteerDashboard.php");
    }
    exit();

} else {
    header("Location: login.php");
    exit();
}