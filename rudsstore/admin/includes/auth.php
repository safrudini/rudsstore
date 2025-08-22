<?php
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

function redirect_if_not_logged_in() {
    if (!is_admin_logged_in()) {
        header("Location: login.php");
        exit();
    }
}
?>