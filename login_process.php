<?php
session_start();
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $errors = [];

    // Validation
    if (empty($email)) {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        // Query users by email including role
        $query = "SELECT id, name, email, password, role, is_active FROM users WHERE email = ?";
        $stmt = mysqli_prepare($con, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($user && password_verify($password, $user['password'])) {
                // Check if account is active
                if (isset($user['is_active']) && (int)$user['is_active'] === 0) {
                    $_SESSION['login_error'] = 'Your account has been deactivated. Please contact support.';
                    $_SESSION['login_email'] = $email;
                    header('Location: login.php');
                    exit();
                }

                // Update last login timestamp
                $update_query = "UPDATE users SET last_login = NOW() WHERE id = ?";
                $update_stmt = mysqli_prepare($con, $update_query);
                if ($update_stmt) {
                    mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
                    mysqli_stmt_execute($update_stmt);
                    mysqli_stmt_close($update_stmt);
                }

                // Regenerate session ID for security
                session_regenerate_id(true);

                $role = !empty($user['role']) ? strtolower($user['role']) : 'candidate';

                // Set session variables
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $role;
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();

                // Manage Remember Me Cookie
                if ($remember) {
                    setcookie('user_email', $email, time() + (86400 * 30), "/");
                } else {
                    if (isset($_COOKIE['user_email'])) {
                        setcookie('user_email', '', time() - 3600, "/");
                    }
                }

                $_SESSION['login_success'] = 'Welcome back, ' . htmlspecialchars($user['name']) . '!';

                // Role-based redirection
                if (isset($_SESSION['redirect_url']) && !empty($_SESSION['redirect_url'])) {
                    $redirect = $_SESSION['redirect_url'];
                    unset($_SESSION['redirect_url']);
                    header('Location: ' . $redirect);
                    exit();
                }

                if ($role === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: candidate/dashboard.php');
                }
                exit();
            } else {
                $_SESSION['login_error'] = 'Invalid email or password. Please try again.';
                $_SESSION['login_email'] = $email;
                header('Location: login.php');
                exit();
            }
        } else {
            $_SESSION['login_error'] = 'Database query error.';
            $_SESSION['login_email'] = $email;
            header('Location: login.php');
            exit();
        }
    } else {
        $_SESSION['login_error'] = implode(' ', $errors);
        $_SESSION['login_email'] = $email;
        header('Location: login.php');
        exit();
    }
} else {
    header('Location: login.php');
    exit();
}