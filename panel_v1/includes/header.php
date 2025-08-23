<?php
if (!isset($pdo)) {
    require_once 'config.php';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1><?php echo SITE_NAME; ?></h1>
                    <p>JUAL KUOTA INTERNET XL AXIS TERMURAH</p>
                </div>
                <nav>
                    <ul>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="order.php">Order</a></li>
                        <li><a href="topup.php">Topup</a></li>
                        <li><a href="history.php">History</a></li>
                        <li><a href="check_area.php">Cek Area</a></li>
                    </ul>
                </nav>
                <div class="user-info">
                    <span>Hi, <?php echo $_SESSION['user_name']; ?></span>
                    <span>Saldo: <?php echo formatCurrency($_SESSION['user_saldo']); ?></span>
                    <a href="logout.php" class="btn btn-sm btn-outline">Logout</a>
                </div>
            </div>
        </div>
    </header>