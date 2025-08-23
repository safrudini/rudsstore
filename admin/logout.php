<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (is_admin_logged_in()) {
    session_destroy();
}

header("Location: login.php");
exit();
?>