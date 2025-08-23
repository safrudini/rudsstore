<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (is_reseller_logged_in()) {
    header("Location: dashboard.php");
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>