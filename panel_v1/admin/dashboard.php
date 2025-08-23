<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get statistics
$resellers_count = $pdo->query("SELECT COUNT(*) FROM resellers")->fetchColumn();
$products_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$orders_count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending_topups = $pdo->query("SELECT COUNT(*) FROM topups WHERE status = 'pending'")->fetchColumn();

// Get latest orders
$orders_stmt = $pdo->prepare("
    SELECT o.*, p.nama_produk, r.name as reseller_name 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    JOIN resellers r ON o.reseller_id = r.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$orders_stmt->execute();
$latest_orders = $orders_stmt->fetchAll();

// Get pending topups
$topups_stmt = $pdo->prepare("
    SELECT t.*, r.name as reseller_name 
    FROM topups t 
    JOIN resellers r ON t.reseller_id = r.id 
    WHERE t.status = 'pending' 
    ORDER BY t.created_at DESC 
    LIMIT 5
");
$topups_stmt->execute();
$pending_topups_list = $topups_stmt->fetchAll();

$page_title = 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title . ' - ' . SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1><?php echo SITE_NAME; ?></h1>
                    <p>Admin Panel</p>
                </div>
                <nav>
                    <ul>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="products.php">Produk</a></li>
                        <li><a href="resellers.php">Reseller</a></li>
                        <li><a href="orders.php">Order</a></li>
                        <li><a href="topups.php">Topup</a></li>
                    </ul>
                </nav>
                <div class="user-info">
                    <span>Hi, <?php echo $_SESSION['admin_name']; ?></span>
                    <a href="logout.php" class="btn btn-sm btn-outline">Logout</a>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="page-header">
            <h1>Dashboard Admin</h1>
        </div>
        
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>Total Reseller</h3>
                <div class="stat-number"><?php echo $resellers_count; ?></div>
                <a href="resellers.php" class="btn btn-link">Lihat Semua</a>
            </div>
            
            <div class="dashboard-card">
                <h3>Total Produk</h3>
                <div class="stat-number"><?php echo $products_count; ?></div>
                <a href="products.php" class="btn btn-link">Lihat Semua</a>
            </div>
            
            <div class="dashboard-card">
                <h3>Total Order</h3>
                <div class="stat-number"><?php echo $orders_count; ?></div>
                <a href="orders.php" class="btn btn-link">Lihat Semua</a>
            </div>
            
            <div class="dashboard-card">
                <h3>Pending Topup</h3>
                <div class="stat-number"><?php echo $pending_topups; ?></div>
                <a href="topups.php" class="btn btn-link">Lihat Semua</a>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>Order Terbaru</h3>
                <?php if (count($latest_orders) > 0): ?>
                <div class="order-list">
                    <?php foreach ($latest_orders as $order): ?>
                    <div class="order-item">
                        <div class="order-info">
                            <p><strong><?php echo $order['reseller_name']; ?></strong></p>
                            <p><?php echo $order['nama_produk']; ?></p>
                            <p><?php echo $order['phone']; ?></p>
                            <p class="status-<?php echo $order['status']; ?>">
                                <?php 
                                if ($order['status'] == 'pending') echo 'Pending';
                                elseif ($order['status'] == 'success') echo 'Sukses';
                                else echo 'Gagal';
                                ?>
                            </p>
                        </div>
                        <div class="order-price">
                            <?php echo formatCurrency($order['harga']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p>Belum ada order</p>
                <?php endif; ?>
                <a href="orders.php" class="btn btn-link">Lihat Semua</a>
            </div>
            
            <div class="dashboard-card">
                <h3>Topup Pending</h3>
                <?php if (count($pending_topups_list) > 0): ?>
                <div class="topup-list">
                    <?php foreach ($pending_topups_list as $topup): ?>
                    <div class="topup-item">
                        <div class="topup-info">
                            <p><strong><?php echo $topup['reseller_name']; ?></strong></p>
                            <p><?php echo formatCurrency($topup['jumlah']); ?></p>
                        </div>
                        <div class="topup-date">
                            <?php echo date('d M Y', strtotime($topup['created_at'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p>Tidak ada topup pending</p>
                <?php endif; ?>
                <a href="topups.php" class="btn btn-link">Lihat Semua</a>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

<style>
.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #3498db;
    text-align: center;
    margin: 1rem 0;
}
</style>