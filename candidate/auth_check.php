<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../connection.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['login_error'] = 'Please sign in to access your Candidate Dashboard.';
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'] ?? 'candidate/dashboard.php';
    header('Location: ../login.php');
    exit();
}

$candidate_user_id = (int)($_SESSION['user_id'] ?? 0);
$candidate_name = $_SESSION['user_name'] ?? 'Candidate';
$candidate_email = $_SESSION['user_email'] ?? '';
$user_role = strtolower($_SESSION['user_role'] ?? 'candidate');

// Ensure candidate profile exists in candidate_profiles
$profQ = mysqli_query($con, "SELECT * FROM candidate_profiles WHERE user_id = $candidate_user_id");
$candidate_profile = mysqli_fetch_assoc($profQ);

if (!$candidate_profile && $candidate_user_id > 0) {
    mysqli_query($con, "INSERT INTO candidate_profiles (user_id, headline, degree) VALUES ($candidate_user_id, 'Aspiring Professional', 'Bachelor Degree')");
    $profQ = mysqli_query($con, "SELECT * FROM candidate_profiles WHERE user_id = $candidate_user_id");
    $candidate_profile = mysqli_fetch_assoc($profQ);
}
?>
