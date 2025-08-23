<?php
require_once 'config.php';

// Redirect to login if not authenticated
if (!isLoggedIn() && basename($_SERVER['PHP_SELF']) != 'login.php' && basename($_SERVER['PHP_SELF']) != 'register.php') {
    header('Location: login.php');
    exit;
}

// Redirect to dashboard if already authenticated
if (isLoggedIn() && (basename($_SERVER['PHP_SELF']) == 'login.php' || basename($_SERVER['PHP_SELF']) == 'register.php')) {
    header('Location: dashboard.php');
    exit;
}
?>