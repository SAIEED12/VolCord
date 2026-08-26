<?php

session_start();

if (isset($_POST["login"])) {

    $email    = trim($_POST["email"]);
    $password = $_POST["password"];
    $user_id  = trim($_POST["user_id"]);

    $error = "";

    if ($email === "" || $password === "" || $user_id === "") {
        $error = "All fields are required.";
    } elseif (!isset($_COOKIE["volunteer_email"]) || !isset($_COOKIE["volunteer_pass"])) {
        $error = "No account found on this browser. Please register first.";
    } elseif (
        $_COOKIE["volunteer_email"] !== $email ||
        $_COOKIE["volunteer_id"] !== $user_id ||
        !password_verify($password, $_COOKIE["volunteer_pass"])
    ) {
        $error = "Invalid email, user ID or password.";
    }

    if ($error !== "") {
        $_SESSION["flash_error"] = $error;
        header("Location: index.php");
        exit();
    }

    // Restore a session from the cookie data (see note in submit.php).
    $_SESSION["volunteer_name"]  = $_COOKIE["volunteer_name"];
    $_SESSION["volunteer_email"] = $_COOKIE["volunteer_email"];
    $_SESSION["volunteer_id"]    = $_COOKIE["volunteer_id"];

    header("Location: dashboard.php");
    exit();

} else {
    header("Location: index.php");
    exit();
}
