<?php
session_start();
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['forgot_error'] = 'Please enter a valid email address.';
        $_SESSION['forgot_email'] = $email;
        header('Location: forgot_password.php');
        exit();
    }

    // Check if email exists in users table
    $query = "SELECT id, name FROM users WHERE email = ?";
    $stmt = mysqli_prepare($con, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user) {
            // Generate a secure reset token
            $token = bin2hex(random_bytes(32));

            // Delete any existing reset tokens for this email
            $del_query = "DELETE FROM password_resets WHERE email = ?";
            $del_stmt = mysqli_prepare($con, $del_query);
            if ($del_stmt) {
                mysqli_stmt_bind_param($del_stmt, "s", $email);
                mysqli_stmt_execute($del_stmt);
                mysqli_stmt_close($del_stmt);
            }

            // Insert new reset token with 1 hour expiration
            $ins_query = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))";
            $ins_stmt = mysqli_prepare($con, $ins_query);
            if ($ins_stmt) {
                mysqli_stmt_bind_param($ins_stmt, "ss", $email, $token);
                mysqli_stmt_execute($ins_stmt);
                mysqli_stmt_close($ins_stmt);
            }

            $reset_link = "reset_password.php?token=" . $token;

            $_SESSION['forgot_success'] = 'Password reset instructions have been generated for <strong>' . htmlspecialchars($email) . '</strong>.';
            $_SESSION['reset_link'] = $reset_link;
            $_SESSION['forgot_email'] = $email;

            header('Location: forgot_password.php');
            exit();
        } else {
            $_SESSION['forgot_error'] = 'No account is registered with that email address.';
            $_SESSION['forgot_email'] = $email;
            header('Location: forgot_password.php');
            exit();
        }
    } else {
        $_SESSION['forgot_error'] = 'Database query error.';
        $_SESSION['forgot_email'] = $email;
        header('Location: forgot_password.php');
        exit();
    }
} else {
    header('Location: forgot_password.php');
    exit();
}
?>