<?php
session_start();

// If user is already logged in, redirect to home.php
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: home.php');
    exit();
}

// Retrieve repopulated form data if available
$reg_data = $_SESSION['register_data'] ?? [];
$first_name_val = htmlspecialchars($reg_data['first_name'] ?? '');
$last_name_val = htmlspecialchars($reg_data['last_name'] ?? '');
$email_val = htmlspecialchars($reg_data['email'] ?? '');
unset($_SESSION['register_data']);

$error_msg = $_SESSION['register_error'] ?? '';
unset($_SESSION['register_error']);

$success_msg = $_SESSION['register_success'] ?? '';
unset($_SESSION['register_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Job Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #9333ea 100%);
            --secondary-gradient: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
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

        /* Animated Glowing Orbs Background */
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
            width: 500px;
            height: 500px;
            background: #6366f1;
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 600px;
            height: 600px;
            background: #9333ea;
            bottom: -150px;
            right: -100px;
            animation-delay: -5s;
        }

        .orb-3 {
            width: 350px;
            height: 350px;
            background: #06b6d4;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -9s;
        }

        @keyframes orbFloat {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(60px, 40px) scale(1.1); }
            100% { transform: translate(-40px, 80px) scale(0.95); }
        }

        /* Main Registration Container */
        .register-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1100px;
            margin: auto;
        }

        .register-card {
            background: var(--card-bg);
            backdrop-filter: blur(25px);
            border-radius: 28px;
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(255, 255, 255, 0.4);
            overflow: hidden;
            display: flex;
            min-height: 680px;
            transition: all 0.3s ease;
        }

        /* Left Side: Brand Panel */
        .register-brand {
            flex: 0 0 38%;
            background: var(--primary-gradient);
            padding: 48px 36px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .register-brand::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.12) 0%, transparent 65%);
            animation: rotateBg 20s linear infinite;
        }

        @keyframes rotateBg {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .brand-top {
            position: relative;
            z-index: 2;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(10px);
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 24px;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }

        .brand-title {
            font-size: 2.3rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .brand-desc {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.88);
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .brand-features {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .brand-features li {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .brand-features li i {
            width: 26px;
            height: 26px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }

        .brand-footer {
            position: relative;
            z-index: 2;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Right Side: Form Panel */
        .register-form-panel {
            flex: 1;
            padding: 44px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .form-header {
            margin-bottom: 28px;
        }

        .form-header h2 {
            font-size: 1.85rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 0.92rem;
        }

        /* Form Inputs */
        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .form-group {
            margin-bottom: 18px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .form-group label .required {
            color: var(--error);
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
            transition: color 0.2s ease;
            pointer-events: none;
        }

        .input-box input {
            width: 100%;
            height: 48px;
            padding: 10px 42px 10px 44px;
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

        .input-box input:focus ~ .input-icon {
            color: var(--primary);
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
            transition: color 0.2s;
        }

        .toggle-pwd:hover {
            color: #475569;
        }

        .feedback-msg {
            font-size: 0.78rem;
            margin-top: 4px;
            display: none;
            align-items: center;
            gap: 5px;
        }

        .feedback-msg.invalid {
            color: var(--error);
        }

        .feedback-msg.valid {
            color: var(--success);
        }

        /* Password Strength Indicator */
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

        /* Password Requirements Tags */
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

        /* Checkbox */
        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 18px 0 24px;
            font-size: 0.86rem;
            color: #475569;
        }

        .terms-checkbox input[type="checkbox"] {
            width: 17px;
            height: 17px;
            accent-color: var(--primary);
            cursor: pointer;
            margin-top: 2px;
        }

        .terms-checkbox a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .terms-checkbox a:hover {
            text-decoration: underline;
        }

        /* Submit Button */
        .btn-register-submit {
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
        }

        .btn-register-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(99, 102, 241, 0.55);
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 50%, #7e22ce 100%);
        }

        .btn-register-submit:active {
            transform: translateY(0);
        }

        /* Footer Link */
        .card-bottom-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .card-bottom-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s;
        }

        .card-bottom-link a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .register-card {
                flex-direction: column;
            }
            .register-brand {
                padding: 32px 24px;
            }
            .register-form-panel {
                padding: 32px 24px;
            }
            .form-row-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- Animated Glow Orbs -->
<div class="bg-orbs">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<div class="register-container">
    
    <!-- Flash Messages -->
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3 rounded-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3 rounded-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="register-card">
        
        <!-- Left Side: Brand Panel -->
        <div class="register-brand">
            <div class="brand-top">
                <div class="brand-badge">
                    <i class="fas fa-sparkles"></i> Modern Job Portal
                </div>
                <h1 class="brand-title">Start Your Career Journey Today</h1>
                <p class="brand-desc">
                    Create an account to explore thousands of verified job listings, apply in 1-click, and get matched with top tech recruiters.
                </p>
                <ul class="brand-features">
                    <li>
                        <i class="fas fa-check"></i>
                        <span>Instant Access to 5,000+ Companies</span>
                    </li>
                    <li>
                        <i class="fas fa-bolt"></i>
                        <span>One-Click Application Tracking</span>
                    </li>
                    <li>
                        <i class="fas fa-shield-alt"></i>
                        <span>100% Verified Employer Profiles</span>
                    </li>
                    <li>
                        <i class="fas fa-bell"></i>
                        <span>Personalized Job Match Alerts</span>
                    </li>
                </ul>
            </div>
            
            <div class="brand-footer">
                <span>&copy; <?php echo date('Y'); ?> JobPortal</span>
                <span><i class="fas fa-lock me-1"></i> 256-bit SSL Secure</span>
            </div>
        </div>

        <!-- Right Side: Registration Form -->
        <div class="register-form-panel">
            <div class="form-header">
                <h2>Create Your Account 🚀</h2>
                <p>Please enter your details to sign up</p>
            </div>

            <form id="registerForm" method="POST" action="register_process.php" novalidate>
                
                <!-- Name Row (First Name & Last Name) -->
                <div class="form-row-2">
                    <div class="form-group">
                        <label for="firstName">First Name <span class="required">*</span></label>
                        <div class="input-box">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" 
                                   id="firstName" 
                                   name="first_name" 
                                   placeholder="Enter The First Name " 
                                   value="<?php echo $first_name_val; ?>"
                                   required 
                                   minlength="2" 
                                   maxlength="50"
                                   autofocus>
                        </div>
                        <div class="feedback-msg invalid" id="firstNameError">
                            <i class="fas fa-circle-exclamation"></i> <span>First name is required (min 2 letters)</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="lastName">Last Name <span class="required">*</span></label>
                        <div class="input-box">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" 
                                   id="lastName" 
                                   name="last_name" 
                                   placeholder="Enter The Last Name" 
                                   value="<?php echo $last_name_val; ?>"
                                   required 
                                   minlength="2" 
                                   maxlength="50">
                        </div>
                        <div class="feedback-msg invalid" id="lastNameError">
                            <i class="fas fa-circle-exclamation"></i> <span>Last name is required (min 2 letters)</span>
                        </div>
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <div class="input-box">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               placeholder="Enter The Email ID" 
                               value="<?php echo $email_val; ?>"
                               required>
                    </div>
                    <div class="feedback-msg invalid" id="emailError">
                        <i class="fas fa-circle-exclamation"></i> <span>Please enter a valid email address</span>
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <div class="input-box">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               placeholder="Create a strong password" 
                               required 
                               minlength="8">
                        <button type="button" class="toggle-pwd" id="togglePassword" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="feedback-msg invalid" id="passwordError">
                        <i class="fas fa-circle-exclamation"></i> <span>Password does not meet requirements</span>
                    </div>

                    <!-- Live Password Strength Bar -->
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

                    <!-- Requirements Indicator -->
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
                    <label for="confirmPassword">Confirm Password <span class="required">*</span></label>
                    <div class="input-box">
                        <i class="fas fa-shield-check input-icon"></i>
                        <input type="password" 
                               id="confirmPassword" 
                               name="confirm_password" 
                               placeholder="Re-enter your password" 
                               required>
                        <button type="button" class="toggle-pwd" id="toggleConfirmPassword" aria-label="Toggle confirm password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="feedback-msg invalid" id="confirmPasswordError">
                        <i class="fas fa-circle-exclamation"></i> <span>Passwords do not match</span>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="terms-checkbox">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">
                        I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a> <span class="required">*</span>
                    </label>
                </div>
                <div class="feedback-msg invalid" id="termsError" style="margin-top: -15px; margin-bottom: 15px;">
                    <i class="fas fa-circle-exclamation"></i> <span>You must agree to continue</span>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-register-submit" id="registerBtn">
                    <i class="fas fa-user-plus"></i>
                    <span id="btnText">Create Account</span>
                </button>

                <!-- Link to Login -->
                <div class="card-bottom-link">
                    Already have an account? <a href="login.php">Sign In</a>
                </div>

            </form>
        </div>

    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const firstName = document.getElementById('firstName');
    const lastName = document.getElementById('lastName');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');
    const terms = document.getElementById('terms');
    const registerBtn = document.getElementById('registerBtn');
    const btnText = document.getElementById('btnText');

    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

    // Toggle password visibility
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const isPassword = password.type === 'password';
            password.type = isPassword ? 'text' : 'password';
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

    // Validation Logic
    function validateFirstName() {
        const val = firstName.value.trim();
        const err = document.getElementById('firstNameError');
        const pattern = /^[a-zA-Z\s\-]+$/;
        if (!val || val.length < 2 || val.length > 50 || !pattern.test(val)) {
            firstName.classList.add('is-invalid');
            firstName.classList.remove('is-valid');
            err.style.display = 'flex';
            return false;
        }
        firstName.classList.remove('is-invalid');
        firstName.classList.add('is-valid');
        err.style.display = 'none';
        return true;
    }

    function validateLastName() {
        const val = lastName.value.trim();
        const err = document.getElementById('lastNameError');
        const pattern = /^[a-zA-Z\s\-]+$/;
        if (!val || val.length < 2 || val.length > 50 || !pattern.test(val)) {
            lastName.classList.add('is-invalid');
            lastName.classList.remove('is-valid');
            err.style.display = 'flex';
            return false;
        }
        lastName.classList.remove('is-invalid');
        lastName.classList.add('is-valid');
        err.style.display = 'none';
        return true;
    }

    function validateEmail() {
        const val = email.value.trim();
        const err = document.getElementById('emailError');
        const pattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!val || !pattern.test(val)) {
            email.classList.add('is-invalid');
            email.classList.remove('is-valid');
            err.style.display = 'flex';
            return false;
        }
        email.classList.remove('is-invalid');
        email.classList.add('is-valid');
        err.style.display = 'none';
        return true;
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

        [bar1, bar2, bar3, bar4].forEach(b => {
            b.style.background = '#e2e8f0';
        });

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

    function validatePassword() {
        const val = password.value;
        const err = document.getElementById('passwordError');
        const isValid = checkPasswordRules(val);

        if (!isValid) {
            password.classList.add('is-invalid');
            password.classList.remove('is-valid');
            err.style.display = 'flex';
            return false;
        }
        password.classList.remove('is-invalid');
        password.classList.add('is-valid');
        err.style.display = 'none';
        return true;
    }

    function validateConfirmPassword() {
        const val = confirmPassword.value;
        const pwdVal = password.value;
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

    function validateTerms() {
        const err = document.getElementById('termsError');
        if (!terms.checked) {
            err.style.display = 'flex';
            return false;
        }
        err.style.display = 'none';
        return true;
    }

    // Attach Input Listeners
    firstName.addEventListener('input', validateFirstName);
    lastName.addEventListener('input', validateLastName);
    email.addEventListener('input', validateEmail);
    password.addEventListener('input', () => {
        validatePassword();
        if (confirmPassword.value) validateConfirmPassword();
    });
    confirmPassword.addEventListener('input', validateConfirmPassword);
    terms.addEventListener('change', validateTerms);

    // Form Submission
    form.addEventListener('submit', function(e) {
        const isFirstValid = validateFirstName();
        const isLastValid = validateLastName();
        const isEmailValid = validateEmail();
        const isPasswordValid = validatePassword();
        const isConfirmValid = validateConfirmPassword();
        const isTermsValid = validateTerms();

        if (!isFirstValid || !isLastValid || !isEmailValid || !isPasswordValid || !isConfirmValid || !isTermsValid) {
            e.preventDefault();
            const firstErr = form.querySelector('.is-invalid, input:invalid');
            if (firstErr) {
                firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstErr.focus();
            }
            return false;
        }

        // Animate button without cancelling the submit
        btnText.textContent = 'Creating Account...';
        registerBtn.style.opacity = '0.85';
        registerBtn.style.pointerEvents = 'none';
    });
});
</script>

</body>
</html>