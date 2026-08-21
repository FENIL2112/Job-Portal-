<?php
session_start();
require_once 'connection.php';

$token = trim($_GET['token'] ?? ($_POST['token'] ?? ''));
$errors = [];
$email = '';
$valid_token = false;

// Verify token
if (!empty($token)) {
    $query = "SELECT email, expires_at FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1";
    $stmt = mysqli_prepare($con, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $reset_data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($reset_data) {
            $email = $reset_data['email'];
            $valid_token = true;
        } else {
            $errors[] = 'This password reset link is invalid or has expired. Please request a new one.';
        }
    } else {
        $errors[] = 'Database error while verifying reset token.';
    }
} else {
    $errors[] = 'No reset token provided. Please request a reset link.';
}

// Process password reset POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    if (empty($new_password)) {
        $errors[] = 'New password is required.';
    } elseif (strlen($new_password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif (!preg_match($password_pattern, $new_password)) {
        $errors[] = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).';
    }

    if ($new_password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    if ($valid_token && empty($errors)) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update user's password
        $update_query = "UPDATE users SET password = ? WHERE email = ?";
        $update_stmt = mysqli_prepare($con, $update_query);
        if ($update_stmt) {
            mysqli_stmt_bind_param($update_stmt, "ss", $hashed_password, $email);
            $exec = mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);

            if ($exec) {
                // Delete used reset tokens
                $delete_query = "DELETE FROM password_resets WHERE email = ?";
                $delete_stmt = mysqli_prepare($con, $delete_query);
                if ($delete_stmt) {
                    mysqli_stmt_bind_param($delete_stmt, "s", $email);
                    mysqli_stmt_execute($delete_stmt);
                    mysqli_stmt_close($delete_stmt);
                }

                $_SESSION['login_success'] = 'Password reset successfully! Please sign in with your new password.';
                header('Location: login.php');
                exit();
            } else {
                $errors[] = 'Failed to update password. Please try again.';
            }
        } else {
            $errors[] = 'Database error while updating password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Job Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #9333ea 100%);
            --card-bg: rgba(255, 255, 255, 0.96);
            --text-main: #1e293b;
            --text-muted: #64748b;
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --success: #10b981;
            --error: #ef4444;
            --border-color: #e2e8f0;
            --shadow-card: 0 25px 60px -15px rgba(79, 70, 229, 0.25), 0 0 30px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #31104b 100%);
            padding: 24px;
            position: relative;
            overflow-x: hidden;
            color: var(--text-main);
        }

        .bg-orbs {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.35;
            animation: orbFloat 18s infinite alternate ease-in-out;
        }

        .orb-1 {
            width: 450px;
            height: 450px;
            background: #6366f1;
            top: -100px;
            left: -100px;
        }

        .orb-2 {
            width: 500px;
            height: 500px;
            background: #9333ea;
            bottom: -120px;
            right: -100px;
        }

        @keyframes orbFloat {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(50px, 40px) scale(1.1); }
            100% { transform: translate(-30px, 70px) scale(0.95); }
        }

        .reset-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 520px;
            margin: auto;
        }

        .reset-card {
            background: var(--card-bg);
            backdrop-filter: blur(25px);
            border-radius: 28px;
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(255, 255, 255, 0.4);
            padding: 44px 40px;
            transition: all 0.3s ease;
        }

        .card-icon-wrapper {
            width: 68px;
            height: 68px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(147, 51, 234, 0.15) 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--primary);
            font-size: 1.8rem;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .header-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f172a;
            text-align: center;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .header-subtitle {
            font-size: 0.92rem;
            color: var(--text-muted);
            text-align: center;
            line-height: 1.5;
            margin-bottom: 26px;
        }

        .user-pill {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 50px;
            padding: 6px 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 20px;
            width: 100%;
            justify-content: center;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .input-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-box .input-icon {
            position: absolute;
            left: 16px;
            color: #94a3b8;
            font-size: 1rem;
            pointer-events: none;
        }

        .input-box input {
            width: 100%;
            height: 50px;
            padding: 10px 44px 10px 46px;
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            font-size: 0.92rem;
            font-family: inherit;
            color: var(--text-main);
            background: #f8fafc;
            transition: all 0.25s ease;
        }

        .input-box input:focus {
            outline: none;
            border-color: var(--primary);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }

        .input-box input.is-valid {
            border-color: var(--success) !important;
            background-color: #f0fdf4 !important;
        }

        .input-box input.is-invalid {
            border-color: var(--error) !important;
            background-color: #fef2f2 !important;
        }

        .toggle-pwd {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            font-size: 0.95rem;
        }

        .feedback-msg {
            font-size: 0.78rem;
            margin-top: 4px;
            display: none;
            align-items: center;
            gap: 5px;
            color: var(--error);
        }

        .password-strength-container {
            margin-top: 8px;
        }

        .strength-bars {
            display: flex;
            gap: 5px;
            height: 4px;
            margin-bottom: 6px;
        }

        .strength-bar {
            flex: 1;
            background: #e2e8f0;
            border-radius: 2px;
            transition: background 0.3s;
        }

        .strength-info {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .strength-info span.score-text {
            font-weight: 600;
        }

        .pwd-rules {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 10px;
            margin-top: 8px;
            padding: 8px 12px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
        }

        .pwd-rule {
            font-size: 0.73rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .pwd-rule.met {
            color: var(--success);
            font-weight: 600;
        }

        .pwd-rule i {
            font-size: 0.65rem;
        }

        .btn-save-pwd {
            width: 100%;
            height: 50px;
            background: var(--primary-gradient);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.45);
            transition: all 0.25s ease;
            margin-top: 10px;
        }

        .btn-save-pwd:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(99, 102, 241, 0.55);
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 50%, #7e22ce 100%);
        }

        .card-bottom-link {
            text-align: center;
            margin-top: 24px;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .card-bottom-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
        }

        .card-bottom-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="bg-orbs">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
</div>

<div class="reset-container">
    
    <!-- Errors -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3 rounded-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo implode('<br>', $errors); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="reset-card">
        
        <div class="card-icon-wrapper">
            <i class="fas fa-lock"></i>
        </div>

        <h1 class="header-title">Reset Password</h1>
        <p class="header-subtitle">Create a strong, new password for your account.</p>

        <?php if ($valid_token): ?>
            
            <div class="user-pill">
                <i class="fas fa-user-check text-primary"></i> Resetting for: <strong><?php echo htmlspecialchars($email); ?></strong>
            </div>

            <form id="resetForm" method="POST" action="reset_password.php" novalidate>
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <!-- New Password -->
                <div class="form-group">
                    <label for="newPassword">New Password <span class="text-danger">*</span></label>
                    <div class="input-box">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" 
                               id="newPassword" 
                               name="new_password" 
                               placeholder="Enter new strong password" 
                               required 
                               minlength="8"
                               autofocus>
                        <button type="button" class="toggle-pwd" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="feedback-msg" id="newPasswordError">
                        <i class="fas fa-circle-exclamation"></i> <span>Password does not meet requirements</span>
                    </div>

                    <!-- Strength Meter -->
                    <div class="password-strength-container">
                        <div class="strength-bars">
                            <div class="strength-bar" id="bar1"></div>
                            <div class="strength-bar" id="bar2"></div>
                            <div class="strength-bar" id="bar3"></div>
                            <div class="strength-bar" id="bar4"></div>
                        </div>
                        <div class="strength-info">
                            <span>Password Strength:</span>
                            <span class="score-text" id="strengthText">None</span>
                        </div>
                    </div>

                    <!-- Rules -->
                    <div class="pwd-rules">
                        <div class="pwd-rule" id="rule-len"><i class="fas fa-circle"></i> 8+ characters</div>
                        <div class="pwd-rule" id="rule-upper"><i class="fas fa-circle"></i> 1 uppercase</div>
                        <div class="pwd-rule" id="rule-lower"><i class="fas fa-circle"></i> 1 lowercase</div>
                        <div class="pwd-rule" id="rule-num"><i class="fas fa-circle"></i> 1 number</div>
                        <div class="pwd-rule" id="rule-spec" style="grid-column: span 2;"><i class="fas fa-circle"></i> 1 special char (@$!%*?&)</div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password <span class="text-danger">*</span></label>
                    <div class="input-box">
                        <i class="fas fa-shield-check input-icon"></i>
                        <input type="password" 
                               id="confirmPassword" 
                               name="confirm_password" 
                               placeholder="Re-enter your new password" 
                               required>
                        <button type="button" class="toggle-pwd" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="feedback-msg" id="confirmPasswordError">
                        <i class="fas fa-circle-exclamation"></i> <span>Passwords do not match</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="reset_password" class="btn-save-pwd" id="resetBtn">
                    <i class="fas fa-check-circle"></i>
                    <span id="btnText">Save New Password</span>
                </button>

                <div class="card-bottom-link">
                    <a href="login.php"><i class="fas fa-arrow-left me-1"></i> Back to Sign In</a>
                </div>

            </form>

        <?php else: ?>
            
            <div class="text-center py-3">
                <a href="forgot_password.php" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                    <i class="fas fa-paper-plane me-2"></i> Request a New Reset Link
                </a>
                <div class="mt-3">
                    <a href="login.php" class="text-secondary text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i> Back to Sign In
                    </a>
                </div>
            </div>

        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resetForm');
    if (!form) return;

    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const resetBtn = document.getElementById('resetBtn');
    const btnText = document.getElementById('btnText');

    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const isPassword = newPassword.type === 'password';
            newPassword.type = isPassword ? 'text' : 'password';
            this.querySelector('i').className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
        });
    }

    if (toggleConfirmPassword) {
        toggleConfirmPassword.addEventListener('click', function() {
            const isPassword = confirmPassword.type === 'password';
            confirmPassword.type = isPassword ? 'text' : 'password';
            this.querySelector('i').className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
        });
    }

    function checkPasswordRules(val) {
        const rLen = val.length >= 8;
        const rUpper = /[A-Z]/.test(val);
        const rLower = /[a-z]/.test(val);
        const rNum = /\d/.test(val);
        const rSpec = /[@$!%*?&]/.test(val);

        updateRuleBadge('rule-len', rLen);
        updateRuleBadge('rule-upper', rUpper);
        updateRuleBadge('rule-lower', rLower);
        updateRuleBadge('rule-num', rNum);
        updateRuleBadge('rule-spec', rSpec);

        let score = 0;
        if (rLen) score++;
        if (rUpper && rLower) score++;
        if (rNum) score++;
        if (rSpec) score++;

        const bar1 = document.getElementById('bar1');
        const bar2 = document.getElementById('bar2');
        const bar3 = document.getElementById('bar3');
        const bar4 = document.getElementById('bar4');
        const strengthText = document.getElementById('strengthText');

        [bar1, bar2, bar3, bar4].forEach(b => { b.style.background = '#e2e8f0'; });

        if (val.length === 0) {
            strengthText.textContent = 'None';
            strengthText.style.color = '#64748b';
            return false;
        }

        if (score <= 1) {
            bar1.style.background = '#ef4444';
            strengthText.textContent = 'Weak';
            strengthText.style.color = '#ef4444';
        } else if (score === 2) {
            bar1.style.background = '#f59e0b';
            bar2.style.background = '#f59e0b';
            strengthText.textContent = 'Fair';
            strengthText.style.color = '#f59e0b';
        } else if (score === 3) {
            bar1.style.background = '#3b82f6';
            bar2.style.background = '#3b82f6';
            bar3.style.background = '#3b82f6';
            strengthText.textContent = 'Good';
            strengthText.style.color = '#3b82f6';
        } else if (score >= 4) {
            bar1.style.background = '#10b981';
            bar2.style.background = '#10b981';
            bar3.style.background = '#10b981';
            bar4.style.background = '#10b981';
            strengthText.textContent = 'Strong';
            strengthText.style.color = '#10b981';
        }

        return rLen && rUpper && rLower && rNum && rSpec;
    }

    function updateRuleBadge(id, isMet) {
        const el = document.getElementById(id);
        if (!el) return;
        if (isMet) {
            el.classList.add('met');
            el.querySelector('i').className = 'fas fa-check-circle';
        } else {
            el.classList.remove('met');
            el.querySelector('i').className = 'fas fa-circle';
        }
    }

    function validateNewPassword() {
        const val = newPassword.value;
        const err = document.getElementById('newPasswordError');
        const isValid = checkPasswordRules(val);

        if (!isValid) {
            newPassword.classList.add('is-invalid');
            newPassword.classList.remove('is-valid');
            err.style.display = 'flex';
            return false;
        }
        newPassword.classList.remove('is-invalid');
        newPassword.classList.add('is-valid');
        err.style.display = 'none';
        return true;
    }

    function validateConfirmPassword() {
        const val = confirmPassword.value;
        const pwdVal = newPassword.value;
        const err = document.getElementById('confirmPasswordError');

        if (!val || val !== pwdVal) {
            confirmPassword.classList.add('is-invalid');
            confirmPassword.classList.remove('is-valid');
            err.style.display = 'flex';
            return false;
        }
        confirmPassword.classList.remove('is-invalid');
        confirmPassword.classList.add('is-valid');
        err.style.display = 'none';
        return true;
    }

    newPassword.addEventListener('input', () => {
        validateNewPassword();
        if (confirmPassword.value) validateConfirmPassword();
    });

    confirmPassword.addEventListener('input', validateConfirmPassword);

    form.addEventListener('submit', function(e) {
        const isPwdValid = validateNewPassword();
        const isConfValid = validateConfirmPassword();

        if (!isPwdValid || !isConfValid) {
            e.preventDefault();
            const firstErr = form.querySelector('.is-invalid');
            if (firstErr) firstErr.focus();
            return false;
        }

        btnText.textContent = 'Saving Password...';
        resetBtn.style.opacity = '0.85';
        resetBtn.style.pointerEvents = 'none';
    });
});
</script>

</body>
</html>