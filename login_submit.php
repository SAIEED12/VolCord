<?php

session_start();

if (isset($_POST["login"])) {

    $email    = trim($_POST["email"]);
    $password = $_POST["password"];

    $error = "";

    if ($email === "" || $password === "") {
        $error = "All fields are required.";
    } elseif (!isset($_COOKIE["volunteer_email"]) || !isset($_COOKIE["volunteer_pass"])) {
        $error = "No account found on this browser. Please register first.";
    } elseif (
        $_COOKIE["volunteer_email"] !== $email ||
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

    header("Location: dashboard.php");
    exit();

} else {
    header("Location: index.php");
    exit();
}
