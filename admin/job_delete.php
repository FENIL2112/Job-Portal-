<?php
require_once __DIR__ . '/auth_check.php';

$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($job_id > 0) {
    $jQ = mysqli_query($con, "SELECT title FROM jobs WHERE id = $job_id");
    $jRow = mysqli_fetch_assoc($jQ);
    $jTitle = $jRow['title'] ?? "#$job_id";

    $delQuery = "DELETE FROM jobs WHERE id = ?";
    $stmt = mysqli_prepare($con, $delQuery);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $job_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['admin_success'] = "Job posting <strong>" . htmlspecialchars($jTitle) . "</strong> deleted successfully.";
        } else {
            $_SESSION['admin_error'] = "Failed to delete job.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['admin_error'] = "Database error while deleting job.";
    }
} else {
    $_SESSION['admin_error'] = "Invalid job ID.";
}

header('Location: jobs.php');
exit();
?>
