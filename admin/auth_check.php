<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure database connection is available
require_once __DIR__ . '/../connection.php';

// Strict Admin Verification Guard
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['login_error'] = 'Please sign in to access the Admin Panel.';
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'] ?? 'admin/dashboard.php';
    header('Location: ../login.php');
    exit();
}

if (!isset($_SESSION['user_role']) || strtolower($_SESSION['user_role']) !== 'admin') {
    $_SESSION['candidate_error'] = 'Access Denied: You do not have administrator permissions to view that page.';
    header('Location: ../candidate/dashboard.php');
    exit();
}

// Global active user variables
$admin_user_id = $_SESSION['user_id'] ?? 0;
$admin_user_name = $_SESSION['user_name'] ?? 'Administrator';
$admin_user_email = $_SESSION['user_email'] ?? 'admin@example.com';
?>
