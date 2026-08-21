<?php
session_start();
require_once __DIR__ . '/connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['login_error'] = 'Please sign in as a candidate to save jobs to your bookmarks.';
    header('Location: login.php');
    exit();
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
$job_id = (int)($_GET['id'] ?? (isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0));
$return_to = $_GET['return'] ?? 'jobs.php';

if ($user_id > 0 && $job_id > 0) {
    // Check if already saved
    $chk = mysqli_query($con, "SELECT id FROM saved_jobs WHERE user_id = $user_id AND job_id = $job_id");
    if (mysqli_num_rows($chk) === 0) {
        $ins = mysqli_prepare($con, "INSERT INTO saved_jobs (user_id, job_id) VALUES (?, ?)");
        if ($ins) {
            mysqli_stmt_bind_param($ins, "ii", $user_id, $job_id);
            mysqli_stmt_execute($ins);
            mysqli_stmt_close($ins);
            $_SESSION['candidate_success'] = "Job saved to your bookmarks!";
        }
    } else {
        mysqli_query($con, "DELETE FROM saved_jobs WHERE user_id = $user_id AND job_id = $job_id");
        $_SESSION['candidate_success'] = "Job removed from your bookmarks.";
    }
}

if ($return_to === 'details') {
    header('Location: job_details.php?id=' . $job_id);
} else {
    header('Location: ' . $return_to);
}
exit();
?>
