<?php
session_start();

// If user is already logged in, redirect based on role
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: candidate/dashboard.php');
    }
    exit();
}

$login_error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

$login_success = $_SESSION['login_success'] ?? '';
unset($_SESSION['login_success']);

$register_success = $_SESSION['register_success'] ?? '';
unset($_SESSION['register_success']);

$forgot_success = $_SESSION['forgot_success'] ?? '';
unset($_SESSION['forgot_success']);

$remembered_email = $_COOKIE['user_email'] ?? ($_SESSION['login_email'] ?? '');
unset($_SESSION['login_email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Job Portal - Admin & Candidate Hub</title>
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
        }

        .orb-2 {
            width: 600px;
            height: 600px;
            background: #9333ea;
            bottom: -150px;
            right: -100px;
            animation-delay: -5s;
        }

        @keyframes orbFloat {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(60px, 40px) scale(1.1); }
            100% { transform: translate(-40px, 80px) scale(0.95); }
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1020px;
            margin: auto;
        }

        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(25px);
            border-radius: 28px;
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(255, 255, 255, 0.4);
            overflow: hidden;
            display: flex;
            min-height: 620px;
        }

        /* Left Side: Brand Panel */
        .login-brand {
            flex: 0 0 42%;
            background: var(--primary-gradient);
            padding: 44px 36px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .login-brand::before {
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
            font-size: 2.1rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 14px;
            letter-spacing: -0.5px;
        }

        .brand-desc {
            font-size: 0.92rem;
            color: rgba(255, 255, 255, 0.88);
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .portal-role-badges {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }

        .role-pill {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 12px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.85rem;
        }

        .role-pill i {
            font-size: 1.1rem;
            color: #fbbf24;
        }

        .role-pill strong {
            display: block;
            font-size: 0.9rem;
            color: #ffffff;
        }

        .role-pill span {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.78rem;
        }

        .brand-footer {
            position: relative;
            z-index: 2;
            padding-top: 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Right Side: Form Panel */
        .login-form-panel {
            flex: 1;
            padding: 44px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .form-header {
            margin-bottom: 20px;
        }

        .form-header h2 {
            font-size: 1.8rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
            letter-spacing: -0.5px;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 0.88rem;
        }

        /* Quick Demo Switcher Buttons */
        .demo-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 20px;
        }

        .demo-box-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .demo-btns {
            display: flex;
            gap: 8px;
        }

        .btn-demo {
            flex: 1;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #334155;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-demo:hover {
            background: #4f46e5;
            color: #ffffff;
            border-color: #4f46e5;
        }

        .btn-demo i {
            font-size: 0.8rem;
        }

        .form-group {
            margin-bottom: 18px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 0.84rem;
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
            left: 14px;
            color: #94a3b8;
            font-size: 0.95rem;
            pointer-events: none;
        }

        .input-box input {
            width: 100%;
            height: 48px;
            padding: 10px 42px 10px 42px;
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            font-size: 0.9rem;
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

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 0.85rem;
        }

        .remember-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            cursor: pointer;
        }

        .remember-checkbox input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .forgot-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .btn-login-submit {
            width: 100%;
            height: 48px;
            background: var(--primary-gradient);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 0.98rem;
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

        .btn-login-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(99, 102, 241, 0.55);
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 50%, #7e22ce 100%);
        }

        .card-bottom-link {
            text-align: center;
            margin-top: 18px;
            font-size: 0.88rem;
            color: var(--text-muted);
        }

        .card-bottom-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
        }

        .quick-nav {
            margin-top: 14px;
            text-align: center;
            font-size: 0.82rem;
            color: #94a3b8;
        }

        .quick-nav a {
            color: #64748b;
            text-decoration: none;
            margin: 0 6px;
        }

        .quick-nav a:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        @media (max-width: 860px) {
            .login-card {
                flex-direction: column;
            }
            .login-brand, .login-form-panel {
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>

<div class="bg-orbs">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
</div>

<div class="login-container">
    
    <!-- Flash Messages -->
    <?php if (!empty($login_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3 rounded-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $login_error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($login_success)): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3 rounded-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $login_success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($register_success)): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3 rounded-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $register_success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="login-card">
        
        <!-- Left Side: Brand Panel -->
        <div class="login-brand">
            <div class="brand-top">
                <div class="brand-badge">
                    <i class="fas fa-briefcase"></i> Job Portal 2026
                </div>
                <h1 class="brand-title">Welcome to the Portal</h1>
                <p class="brand-desc">
                    Access dedicated interfaces for Admin Operations & Student / Candidate Careers.
                </p>

                <div class="portal-role-badges">
                    <div class="role-pill">
                        <i class="fas fa-user-shield"></i>
                        <div>
                            <strong>Admin Portal</strong>
                            <span>Candidate List, Job Posts & ATS Tracking</span>
                        </div>
                    </div>
                    <div class="role-pill">
                        <i class="fas fa-user-graduate"></i>
                        <div>
                            <strong>Student / Candidate Portal</strong>
                            <span>Job Search, Profile Builder & My Applications</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="brand-footer">
                <span>&copy; <?php echo date('Y'); ?> JobPortal</span>
                <span><i class="fas fa-lock me-1"></i> Role-Based Auth</span>
            </div>
        </div>

        <!-- Right Side: Form Panel -->
        <div class="login-form-panel">
            <div class="form-header">
                <h2>Sign In 👋</h2>
                <p>Log in to access your customized portal dashboard</p>
            </div>

            <!-- Demo Switcher -->
            <div class="demo-box">
                <div class="demo-box-title">
                    <i class="fas fa-bolt text-warning"></i> Quick Demo Auto-Fill:
                </div>
                <div class="demo-btns">
                    <button type="button" class="btn-demo" id="btnFillAdmin">
                        <i class="fas fa-user-shield text-danger"></i> Admin Demo
                    </button>
                    <button type="button" class="btn-demo" id="btnFillCandidate">
                        <i class="fas fa-user-graduate text-primary"></i> Candidate Demo
                    </button>
                </div>
            </div>

            <form id="loginForm" method="POST" action="login_process.php">
                
                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-box">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               placeholder="name@example.com" 
                               value="<?php echo htmlspecialchars($remembered_email); ?>"
                               required
                               autofocus>
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-box">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               placeholder="Enter your password" 
                               required>
                        <button type="button" class="toggle-pwd" id="togglePassword" aria-label="Toggle password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Options -->
                <div class="form-options">
                    <label class="remember-checkbox" for="remember">
                        <input type="checkbox" id="remember" name="remember" <?php echo !empty($remembered_email) ? 'checked' : ''; ?>>
                        <span>Remember me</span>
                    </label>
                    <a href="forgot_password.php" class="forgot-link">
                        <i class="fas fa-key me-1"></i> Forgot Password?
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-login-submit" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span id="btnText">Sign In to Dashboard</span>
                </button>

                <!-- Create Account Link -->
                <div class="card-bottom-link">
                    Don't have an account? <a href="register.php">Register as Candidate</a>
                </div>

                <div class="quick-nav">
                    <a href="index.php"><i class="fas fa-home me-1"></i> Home</a>
                    <span>•</span>
                    <a href="jobs.php"><i class="fas fa-search me-1"></i> Browse Jobs</a>
                </div>

            </form>
        </div>

    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            this.querySelector('i').className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
        });
    }

    // Demo Fill Buttons
    const btnFillAdmin = document.getElementById('btnFillAdmin');
    const btnFillCandidate = document.getElementById('btnFillCandidate');

    if (btnFillAdmin) {
        btnFillAdmin.addEventListener('click', function() {
            emailInput.value = 'admin@example.com';
            passwordInput.value = 'Admin@12345';
        });
    }

    if (btnFillCandidate) {
        btnFillCandidate.addEventListener('click', function() {
            emailInput.value = 'candidate@example.com';
            passwordInput.value = 'Candidate@12345';
        });
    }
});
</script>

</body>
</html>