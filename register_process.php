<?php
session_start();
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim inputs
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);

    $errors = [];

    // ========== FIRST NAME VALIDATION ==========
    if (empty($first_name)) {
        $errors[] = 'First name is required.';
    } elseif (strlen($first_name) < 2) {
        $errors[] = 'First name must be at least 2 characters.';
    } elseif (strlen($first_name) > 50) {
        $errors[] = 'First name cannot exceed 50 characters.';
    } elseif (!preg_match('/^[a-zA-Z\s\-]+$/', $first_name)) {
        $errors[] = 'First name can only contain letters, spaces, and hyphens.';
    }

    // ========== LAST NAME VALIDATION ==========
    if (empty($last_name)) {
        $errors[] = 'Last name is required.';
    } elseif (strlen($last_name) < 2) {
        $errors[] = 'Last name must be at least 2 characters.';
    } elseif (strlen($last_name) > 50) {
        $errors[] = 'Last name cannot exceed 50 characters.';
    } elseif (!preg_match('/^[a-zA-Z\s\-]+$/', $last_name)) {
        $errors[] = 'Last name can only contain letters, spaces, and hyphens.';
    }

    // ========== EMAIL VALIDATION ==========
    if (empty($email)) {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email = ?";
        $check_stmt = mysqli_prepare($con, $check_query);
        if ($check_stmt) {
            mysqli_stmt_bind_param($check_stmt, "s", $email);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);
            
            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $errors[] = 'This email is already registered. Please login.';
            }
            mysqli_stmt_close($check_stmt);
        } else {
            $errors[] = 'Database error while checking email.';
        }
    }

    // ========== PASSWORD VALIDATION ==========
    $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif (!preg_match($password_pattern, $password)) {
        $errors[] = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).';
    }

    // ========== CONFIRM PASSWORD VALIDATION ==========
    if (empty($confirm_password)) {
        $errors[] = 'Please confirm your password.';
    } elseif ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // ========== TERMS VALIDATION ==========
    if (!$terms) {
        $errors[] = 'You must agree to the terms and conditions.';
    }

    // ========== PROCESS REGISTRATION ==========
    if (empty($errors)) {
        // Hash the password securely
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $full_name = $first_name . ' ' . $last_name;
        
        // Insert user with default role 'candidate'
        $insert_query = "INSERT INTO users (name, email, role, password, is_active, created_at) VALUES (?, ?, 'candidate', ?, 1, NOW())";
        $insert_stmt = mysqli_prepare($con, $insert_query);
        
        if ($insert_stmt) {
            mysqli_stmt_bind_param($insert_stmt, "sss", $full_name, $email, $hashed_password);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                $new_user_id = mysqli_insert_id($con);
                mysqli_stmt_close($insert_stmt);

                // Initialize candidate profile
                $prof_query = "INSERT INTO candidate_profiles (user_id, headline, degree) VALUES (?, 'Student / Job Seeker', 'Graduate / Student')";
                $prof_stmt = mysqli_prepare($con, $prof_query);
                if ($prof_stmt) {
                    mysqli_stmt_bind_param($prof_stmt, "i", $new_user_id);
                    mysqli_stmt_execute($prof_stmt);
                    mysqli_stmt_close($prof_stmt);
                }

                $_SESSION['register_success'] = 'Account created successfully! Please sign in as a Candidate to access your dashboard.';
                header('Location: login.php');
                exit();
            } else {
                mysqli_stmt_close($insert_stmt);
                $_SESSION['register_error'] = 'Registration failed. Please try again.';
                $_SESSION['register_data'] = [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email
                ];
                header('Location: register.php');
                exit();
            }
        } else {
            $_SESSION['register_error'] = 'Database preparation error.';
            header('Location: register.php');
            exit();
        }
    } else {
        // Store errors in session
        $_SESSION['register_error'] = implode(' ', $errors);
        // Store form data to repopulate
        $_SESSION['register_data'] = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email
        ];
        header('Location: register.php');
        exit();
    }
} else {
    header('Location: register.php');
    exit();
}