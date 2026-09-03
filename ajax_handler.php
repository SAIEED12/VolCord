<?php
require_once "config.php";
session_start();

header("Content-Type: application/json");

$action = $_GET["action"] ?? $_POST["action"] ?? "";

function json_response($success, $message = "", $redirect = "") {
    echo json_encode([
        "success"  => $success,
        "message"  => $message,
        "redirect" => $redirect
    ]);
    exit();
}

switch ($action) {

    case "login":
        $email    = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($email === "" || $password === "") {
            json_response(false, "All fields are required.");
        }

        $stmt = $conn->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $stmt->close();
            json_response(false, "No account found with that email.");
        }

        $stmt->bind_result($user_id, $full_name, $user_email, $password_hash, $user_role);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($password, $password_hash)) {
            json_response(false, "Invalid email or password.");
        }

        $_SESSION["volunteer_name"]  = $full_name;
        $_SESSION["volunteer_email"] = $user_email;
        $_SESSION["volunteer_id"]    = $user_id;
        $_SESSION["user_role"]       = $user_role;

        setcookie("volunteer_name",  $full_name,  time() + 3600, "/");
        setcookie("volunteer_email", $user_email, time() + 3600, "/");
        setcookie("user_role",       $user_role,  time() + 3600, "/");

        $role_target = strtolower($user_role);
        if ($role_target === "customer") {
            $redirect = "customerDashboard.php";
        } elseif ($role_target === "admin") {
            $redirect = "adminDashboard.php";
        } else {
            $redirect = "volunteerDashboard.php";
        }

        json_response(true, "Login successful.", $redirect);
        break;

    case "register":
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
            json_response(false, "All fields are required.");
        } 
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(false, "Please enter a valid email address.");
        } 
        elseif ($password !== $confirm) {
            json_response(false, "Passwords do not match.");
        } 
        elseif (strlen($password) < 8) {
            json_response(false, "Password must be at least 8 characters.");
        } 
        elseif (!in_array($role, $allowed_roles, true)) {
            json_response(false, "Please select a valid role.");
        } 
        elseif (!in_array($gender, $allowed_genders, true)) {
            json_response(false, "Please select a valid gender.");
        }

        if ($role === "Volunteer") {
            $skills = trim($_POST["skills"] ?? "");
            if ($skills === "") {
                json_response(false, "Please list your skills.");
            }
        }

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            json_response(false, "An account with that email already exists.");
        }
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
                $redirect = "customerDashboard.php";
            } else {
                $redirect = "volunteerDashboard.php";
            }

            json_response(true, "Account created successfully.", $redirect);
        }

        $stmt->close();
        json_response(false, "Something went wrong. Please try again.");
        break;

    case "apply":
        if (($_SESSION["user_role"] ?? "") !== "Volunteer") {
            json_response(false, "Unauthorized.", "login.php");
        }

        $volunteer_id  = $_SESSION["volunteer_id"];
        $opportunity_id = (int) ($_POST["opportunity_id"] ?? 0);
        $message       = trim($_POST["message"] ?? "");

        if ($opportunity_id <= 0) {
            json_response(false, "Invalid opportunity.");
        }

        $check = $conn->prepare("SELECT id FROM applications WHERE opportunity_id = ? AND volunteer_id = ?");
        $check->bind_param("ii", $opportunity_id, $volunteer_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $check->close();
            json_response(false, "You have already applied for this opportunity.");
        }
        $check->close();

        $stmt = $conn->prepare("INSERT INTO applications (opportunity_id, volunteer_id, message, status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param("iis", $opportunity_id, $volunteer_id, $message);

        if ($stmt->execute()) {
            $stmt->close();
            json_response(true, "Application submitted successfully.");
        }

        $stmt->close();
        json_response(false, "Could not submit application. Please try again.");
        break;

    case "post_opportunity":
        if (($_SESSION["user_role"] ?? "") !== "Customer") {
            json_response(false, "Unauthorized.", "login.php");
        }

        $customer_id     = $_SESSION["volunteer_id"];
        $title           = trim($_POST["title"] ?? "");
        $description     = trim($_POST["description"] ?? "");
        $location        = trim($_POST["location"] ?? "");
        $required_skills = trim($_POST["required_skills"] ?? "");
        $needed_date     = trim($_POST["needed_date"] ?? "");

        if ($title === "" || $description === "" || $location === "") {
            json_response(false, "Title, description and location are required.");
        }

        $needed_date_val = $needed_date === "" ? null : $needed_date;

        $stmt = $conn->prepare(
            "INSERT INTO opportunities (customer_id, title, description, location, required_skills, needed_date, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')"
        );
        $stmt->bind_param("isssss", $customer_id, $title, $description, $location, $required_skills, $needed_date_val);

        if ($stmt->execute()) {
            $stmt->close();
            json_response(true, "Opportunity posted and awaiting admin approval.");
        }

        $stmt->close();
        json_response(false, "Could not post opportunity. Please try again.");
        break;

    case "review_opportunity":
        if (($_SESSION["user_role"] ?? "") !== "Admin") {
            json_response(false, "Unauthorized.", "login.php");
        }

        $id     = (int) ($_POST["id"] ?? 0);
        $act    = $_POST["action_type"] ?? "";

        if ($id <= 0 || !in_array($act, ["approve", "reject"], true)) {
            json_response(false, "Invalid request.");
        }

        $status   = $act === "approve" ? "approved" : "rejected";
        $admin_id = $_SESSION["volunteer_id"];

        $stmt = $conn->prepare("UPDATE opportunities SET status = ?, approved_by = ?, approved_at = NOW() WHERE id = ?");
        $stmt->bind_param("sii", $status, $admin_id, $id);
        $stmt->execute();
        $stmt->close();

        json_response(true, "Opportunity " . $status . ".");
        break;

    case "review_application":
        if (($_SESSION["user_role"] ?? "") !== "Admin") {
            json_response(false, "Unauthorized.", "login.php");
        }

        $id  = (int) ($_POST["id"] ?? 0);
        $act = $_POST["action_type"] ?? "";

        if ($id <= 0 || !in_array($act, ["accept", "reject"], true)) {
            json_response(false, "Invalid request.");
        }

        $status = $act === "accept" ? "accepted" : "rejected";

        $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();

        json_response(true, "Application " . $status . ".");
        break;

    default:
        json_response(false, "Unknown action.");
        break;
}
