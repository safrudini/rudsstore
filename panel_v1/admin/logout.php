<?php
require_once '../includes/config.php';

// Destroy admin session
unset($_SESSION['admin_id']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_name']);

header('Location: login.php');
exit;
?>