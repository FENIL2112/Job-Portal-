<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy session cookie if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Start fresh session to pass logout notification
session_start();
$_SESSION['login_success'] = 'You have been logged out successfully.';

header('Location: login.php');
exit();
?>
