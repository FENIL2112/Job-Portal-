<?php
session_start();

$forgot_error = $_SESSION['forgot_error'] ?? '';
unset($_SESSION['forgot_error']);

$forgot_success = $_SESSION['forgot_success'] ?? '';
unset($_SESSION['forgot_success']);

$reset_link = $_SESSION['reset_link'] ?? '';
unset($_SESSION['reset_link']);

$entered_email = $_SESSION['forgot_email'] ?? '';
unset($_SESSION['forgot_email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Job Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --card-bg: rgba(255, 255, 255, 0.96);
            --text-main: #1e293b;
            --text-muted: #64748b;
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --success: #10b981;
            --error: #ef4444;
            --border-color: #e2e8f0;
            --shadow-card: 0 25px 60px -15px rgba(99, 102, 241, 0.25), 0 0 30px rgba(0, 0, 0, 0.05);
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

        /* Animated Glowing Orbs */
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
            right: -100px;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 500px;
            height: 500px;
            background: #a855f7;
            bottom: -120px;
            left: -100px;
            animation-delay: -6s;
        }

        @keyframes orbFloat {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(50px, 40px) scale(1.1); }
            100% { transform: translate(-30px, 70px) scale(0.95); }
        }

        /* Container */
        .forgot-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 520px;
            margin: auto;
        }

        .forgot-card {
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
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(168, 85, 247, 0.15) 100%);
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

        /* Info box */
        .info-badge {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 24px;
            font-size: 0.85rem;
            color: #475569;
        }

        .info-badge i {
            color: var(--primary);
            font-size: 1rem;
            margin-top: 2px;
        }

        .form-group {
            margin-bottom: 22px;
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
            transition: color 0.2s ease;
            pointer-events: none;
        }

        .input-box input {
            width: 100%;
            height: 50px;
            padding: 10px 16px 10px 46px;
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

        .feedback-msg {
            font-size: 0.78rem;
            margin-top: 4px;
            display: none;
            align-items: center;
            gap: 5px;
            color: var(--error);
        }

        /* Submit Button */
        .btn-reset-submit {
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

        .btn-reset-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(99, 102, 241, 0.55);
            background: linear-gradient(135deg, #4f46e5 0%, #9333ea 100%);
        }

        .btn-reset-submit:active {
            transform: translateY(0);
        }

        /* Success Reset Box with Direct Link for Local Dev */
        .reset-link-box {
            background: #f0fdf4;
            border: 1.5px solid #86efac;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 24px;
            text-align: center;
        }

        .reset-link-box .success-icon {
            font-size: 2.2rem;
            color: var(--success);
            margin-bottom: 10px;
        }

        .reset-link-box h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #166534;
            margin-bottom: 6px;
        }

        .reset-link-box p {
            font-size: 0.88rem;
            color: #15803d;
            margin-bottom: 16px;
        }

        .btn-proceed-reset {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--success);
            color: #ffffff;
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
            transition: all 0.2s ease;
        }

        .btn-proceed-reset:hover {
            background: #059669;
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* Back Link */
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
            transition: color 0.2s;
        }

        .card-bottom-link a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .forgot-card {
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>

<!-- Background Orbs -->
<div class="bg-orbs">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
</div>

<div class="forgot-container">
    
    <!-- Flash Error Message -->
    <?php if (!empty($forgot_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3 rounded-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $forgot_error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="forgot-card">
        
        <!-- Icon -->
        <div class="card-icon-wrapper">
            <i class="fas fa-key"></i>
        </div>

        <h1 class="header-title">Forgot Password?</h1>
        <p class="header-subtitle">No worries! Enter your registered email address and we'll help you reset your password.</p>

        <!-- If Reset Link Generated -->
        <?php if (!empty($forgot_success) && !empty($reset_link)): ?>
            <div class="reset-link-box">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>Reset Link Ready! 🎉</h3>
                <p><?php echo $forgot_success; ?></p>
                <a href="<?php echo htmlspecialchars($reset_link); ?>" class="btn-proceed-reset">
                    <i class="fas fa-unlock-alt"></i> Set New Password Now
                </a>
            </div>
        <?php endif; ?>

        <!-- Info Box -->
        <div class="info-badge">
            <i class="fas fa-shield-alt"></i>
            <span>For security reasons, the password reset link will expire in <strong>1 hour</strong>.</span>
        </div>

        <!-- Form -->
        <form id="forgotForm" method="POST" action="forgot_process.php" novalidate>
            
            <div class="form-group">
                <label for="email">Registered Email Address</label>
                <div class="input-box">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           placeholder="Enter your registered email" 
                           value="<?php echo htmlspecialchars($entered_email); ?>"
                           required
                           autofocus>
                </div>
                <div class="feedback-msg" id="emailError">
                    <i class="fas fa-circle-exclamation"></i> <span>Please enter a valid email address</span>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-reset-submit" id="resetBtn">
                <i class="fas fa-paper-plane"></i>
                <span id="btnText">Send Reset Link</span>
            </button>

            <!-- Bottom Back Links -->
            <div class="card-bottom-link">
                <a href="login.php"><i class="fas fa-arrow-left me-1"></i> Back to Sign In</a>
                <span class="mx-2">•</span>
                <a href="register.php">Create Account</a>
            </div>

        </form>

    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('forgotForm');
    const email = document.getElementById('email');
    const resetBtn = document.getElementById('resetBtn');
    const btnText = document.getElementById('btnText');

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

    email.addEventListener('input', validateEmail);

    form.addEventListener('submit', function(e) {
        if (!validateEmail()) {
            e.preventDefault();
            email.focus();
            return false;
        }

        btnText.textContent = 'Generating Link...';
        resetBtn.style.opacity = '0.85';
        resetBtn.style.pointerEvents = 'none';
    });
});
</script>

</body>
</html>