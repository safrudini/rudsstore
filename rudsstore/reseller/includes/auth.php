<?php
function is_reseller_logged_in() {
    return isset($_SESSION['reseller_id']);
}

function redirect_if_not_logged_in() {
    if (!is_reseller_logged_in()) {
        header("Location: login.php");
        exit();
    }
}
?>