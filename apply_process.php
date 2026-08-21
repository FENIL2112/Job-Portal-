<?php
session_start();
require_once __DIR__ . '/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
    $job_id = (int)($_POST['job_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $refer = trim($_POST['refer'] ?? 'Direct');
    $skills = trim($_POST['skills'] ?? '');
    $experience = trim($_POST['experience'] ?? 'Fresher');
    $cover_note = trim($_POST['cover_note'] ?? '');
    $jobpost = trim($_POST['jobpost'] ?? 'Software Developer');

    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    $errors = [];

    if (empty($name) || strlen($name) < 2) {
        $errors[] = "Full name is required.";
    }

    if (empty($qualification)) {
        $errors[] = "Qualification / Degree is required.";
    }

    if (empty($mobile) || !preg_match('/^[0-9]{10}$/', $mobile)) {
        $errors[] = "Please enter a valid 10-digit mobile number.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // Lookup job title if job_id given
    if ($job_id > 0) {
        $jQ = mysqli_query($con, "SELECT title FROM jobs WHERE id = $job_id");
        if ($jRow = mysqli_fetch_assoc($jQ)) {
            $jobpost = $jRow['title'];
        }
    }

    if (empty($errors)) {
        // 1. Insert into job_applications (ATS)
        $insApp = "INSERT INTO job_applications 
            (job_id, user_id, name, email, mobile, degree, refer, jobpost, skills, experience, cover_note, status, applied_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Applied', NOW())";
        
        $stmtApp = mysqli_prepare($con, $insApp);
        if ($stmtApp) {
            mysqli_stmt_bind_param($stmtApp, "iisssssssss", 
                $job_id, $user_id, $name, $email, $mobile, $qualification, $refer, $jobpost, $skills, $experience, $cover_note
            );

            if (mysqli_stmt_execute($stmtApp)) {
                mysqli_stmt_close($stmtApp);

                // 2. Also keep jobregistration synchronized
                $checkReg = mysqli_query($con, "SELECT id FROM jobregistration WHERE email = '" . mysqli_real_escape_string($con, $email) . "'");
                if (mysqli_num_rows($checkReg) === 0) {
                    $stmtReg = mysqli_prepare($con, "INSERT INTO jobregistration (name, degree, mobile, email, refer, jobpost) VALUES (?, ?, ?, ?, ?, ?)");
                    if ($stmtReg) {
                        mysqli_stmt_bind_param($stmtReg, "ssssss", $name, $qualification, $mobile, $email, $refer, $jobpost);
                        mysqli_stmt_execute($stmtReg);
                        mysqli_stmt_close($stmtReg);
                    }
                }

                if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                    $_SESSION['candidate_success'] = "Your application for <strong>" . htmlspecialchars($jobpost) . "</strong> was submitted successfully! 🎉";
                    header('Location: candidate/applications.php');
                } else {
                    $_SESSION['home_success'] = "Your application for <strong>" . htmlspecialchars($jobpost) . "</strong> was received! Create an account to track your status.";
                    header('Location: job_details.php?id=' . $job_id . '&status=submitted');
                }
                exit();
            } else {
                $_SESSION['app_error'] = "Failed to submit application. Please try again.";
            }
            mysqli_stmt_close($stmtApp);
        } else {
            $_SESSION['app_error'] = "Database error while preparing application.";
        }
    } else {
        $_SESSION['app_error'] = implode(' ', $errors);
    }

    $redirectUrl = ($job_id > 0) ? "job_details.php?id=$job_id" : "jobs.php";
    header("Location: $redirectUrl");
    exit();
} else {
    header('Location: jobs.php');
    exit();
}
