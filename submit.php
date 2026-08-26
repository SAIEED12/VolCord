<?php

session_start();

if (isset($_POST["submit"])) {

    // Collect fields (same pattern as the original demo).
    $first_name         = trim($_POST["first_name"]);
    $last_name          = trim($_POST["last_name"]);
    $nick_name          = trim($_POST["nick_name"]);
    $contract           = trim($_POST["contract"]);
    $emergency_contact  = trim($_POST["emergency_contact"]);
    $dob                = trim($_POST["dob"]);
    $email              = trim($_POST["email"]);
    $address            = trim($_POST["address"]);
    $permanent_address  = trim($_POST["permanent_address"]);
    $blood_group        = trim($_POST["blood_group"]);
    $gender             = trim($_POST["gender"]);
    $designation        = trim($_POST["designation"]);
    $password           = $_POST["password"];
    $confirm_password   = $_POST["confirm_password"];

    $required = [
        "first_name" => $first_name,
        "last_name" => $last_name,
        "contract" => $contract,
        "emergency_contact" => $emergency_contact,
        "dob" => $dob,
        "email" => $email,
        "address" => $address,
        "permanent_address" => $permanent_address,
        "blood_group" => $blood_group,
        "gender" => $gender,
        "password" => $password,
        "confirm_password" => $confirm_password,
    ];

    $error = "";

    foreach ($required as $field) {
        if ($field === "") {
            $error = "All fields are required.";
            break;
        }
    }

    if ($error === "" && strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    }

    if ($error === "" && $password !== $confirm_password) {
        $error = "Password and Confirm Password do not match.";
    }

    if ($error !== "") {
        $_SESSION["flash_error"] = $error;
        $_SESSION["flash_old"] = $_POST;
        header("Location: register.php");
        exit();
    }

    // ----- Session -----
    $_SESSION["volunteer_name"]        = $first_name . " " . $last_name;
    $_SESSION["volunteer_id"]          = $contract;
    $_SESSION["volunteer_email"]       = $email;
    $_SESSION["volunteer_nick_name"]   = $nick_name;
    $_SESSION["volunteer_dob"]         = $dob;
    $_SESSION["volunteer_gender"]      = $gender;
    $_SESSION["volunteer_blood_group"] = $blood_group;
    $_SESSION["volunteer_designation"] = $designation;
    $_SESSION["volunteer_address"]     = $address;

    // ----- Cookies -----
    // NOTE: this small demo has no database, so the cookies below double as
    // the only persisted "account" and let login.php recognise a returning
    // volunteer on the same browser. In a real system, register/login
    // would be checked against a database with a hashed password instead.
    $cookie_expiry = time() + (30 * 24 * 60 * 60); // 30 days

    setcookie("volunteer_name", $first_name . " " . $last_name, $cookie_expiry, "/");
    setcookie("volunteer_email", $email, $cookie_expiry, "/");
    setcookie("volunteer_id", $contract, $cookie_expiry, "/");
    setcookie("volunteer_pass", password_hash($password, PASSWORD_DEFAULT), $cookie_expiry, "/");

    header("Location: dashboard.php");
    exit();

} else {
    header("Location: register.php");
    exit();
}
