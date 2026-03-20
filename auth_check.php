<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/dbcon.php';

function auth_check() {
    if (empty($_SESSION['user_id'])) {
        header("Location: login.php"); exit();
    }
}

function login_user($username, $password, $conn) {
    $stmt = $conn->prepare("SELECT user_id, username, password, full_name, role FROM users WHERE username = ? LIMIT 1");
    if (!$stmt) die("SQL Error: " . $conn->error);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row && password_verify($password, $row['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $row['user_id'];
        $_SESSION['username']  = $row['username'];
        $_SESSION['full_name'] = $row['full_name'];
        $_SESSION['role']      = $row['role'];
        
        return true;
    }
    return false;
}

function logout() {
    session_unset();
    session_destroy();
    header("Location: login.php"); exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') logout();
?>
