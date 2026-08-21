<?php
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $role = strtolower($_SESSION['user_role'] ?? 'candidate');
    if ($role === 'admin') {
        header('Location: admin/candidates.php');
    } else {
        header('Location: candidate/dashboard.php');
    }
    exit();
} else {
    $_SESSION['login_error'] = 'Candidate list management is restricted to administrators. Please log in.';
    header('Location: login.php');
    exit();
}
?>