<?php
require_once __DIR__ . '/auth_check.php';

$ids = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($ids > 0) {
    // Get candidate details before deletion for flash message
    $cQ = mysqli_query($con, "SELECT name, email FROM jobregistration WHERE id = $ids");
    $cRow = mysqli_fetch_assoc($cQ);
    $cName = $cRow['name'] ?? "#$ids";

    $deletequery = "DELETE FROM jobregistration WHERE id = ?";
    $stmt = mysqli_prepare($con, $deletequery);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $ids);
        if (mysqli_stmt_execute($stmt)) {
            // Also clean up any associated entry in job_applications if applicable
            if (!empty($cRow['email'])) {
                $eSafe = mysqli_real_escape_string($con, $cRow['email']);
                mysqli_query($con, "DELETE FROM job_applications WHERE email = '$eSafe'");
            }

            $_SESSION['admin_success'] = "Candidate <strong>" . htmlspecialchars($cName) . "</strong> deleted successfully.";
        } else {
            $_SESSION['admin_error'] = "Failed to delete candidate record.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['admin_error'] = "Database error while preparing delete statement.";
    }
} else {
    $_SESSION['admin_error'] = "Invalid candidate ID specified.";
}

header('Location: candidates.php');
exit();
?>
