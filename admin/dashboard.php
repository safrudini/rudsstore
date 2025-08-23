<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!is_admin_logged_in()) {
    header("Location: login.php");
    exit();
}

// Get stats for dashboard
$resellers_count = $pdo->query("SELECT COUNT(*) FROM resellers")->fetchColumn();
$orders_count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending_topups = $pdo->query("SELECT COUNT(*) FROM topups WHERE status = 'pending'")->fetchColumn();
$total_sales = $pdo->query("SELECT SUM(price) FROM orders WHERE status = 'success'")->fetchColumn();

// Get recent orders
$orders_stmt = $pdo->query("SELECT o.*, r.name as reseller_name, p.product_name 
                           FROM orders o 
                           JOIN resellers r ON o.reseller_id = r.id 
                           JOIN products p ON o.product_id = p.id 
                           ORDER BY o.created_at DESC 
                           LIMIT 10");
$recent_orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending topups
$topups_stmt = $pdo->query("SELECT t.*, r.name as reseller_name 
                           FROM topups t 
                           JOIN resellers r ON t.reseller_id = r.id 
                           WHERE t.status = 'pending' 
                           ORDER BY t.created_at DESC 
                           LIMIT 5");
$pending_topups_list = $topups_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - RUD'S STORE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <div class="logo">RUD'S STORE - Admin</div>
                <nav>
                    <ul>
                        <li><a href="dashboard.php" class="active">Dashboard</a></li>
                        <li><a href="products.php">Produk</a></li>
                        <li><a href="resellers.php">Reseller</a></li>
                        <li><a href="orders.php">Order</a></li>
                        <li><a href="topups.php">Topup</a></li>
                        <li><a href="bank_accounts.php">Rekening</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </header>
        
        <div class="dashboard">
            <div class="sidebar">
                <div class="user-info">
                    <h3>Admin</h3>
                </div>
                <nav>
                    <a href="dashboard.php" class="active">Dashboard</a>
                    <a href="products.php">Produk</a>
                    <a href="resellers.php">Reseller</a>
                    <a href="orders.php">Order</a>
                    <a href="topups.php">Topup</a>
                    <a href="bank_accounts.php">Rekening</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>
            
            <div class="main-content">
                <h2>Dashboard Admin</h2>
                
                <div class="stats">
                    <div class="stat-card">
                        <h3>Total Reseller</h3>
                        <p><?php echo $resellers_count; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Total Order</h3>
                        <p><?php echo $orders_count; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Pending Topup</h3>
                        <p><?php echo $pending_topups; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Total Penjualan</h3>
                        <p>Rp <?php echo number_format($total_sales, 0, ',', '.'); ?></p>
                    </div>
                </div>
                
                <div class="recent-activity">
                    <h3>Order Terbaru</h3>
                    <?php if (count($recent_orders) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Reseller</th>
                                    <th>Produk</th>
                                    <th>No. HP</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['reseller_name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['phone_number']); ?></td>
                                        <td>Rp <?php echo number_format($order['price'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="status-<?php echo $order['status']; ?>">
                                                <?php 
                                                if ($order['status'] == 'success') echo 'Sukses';
                                                elseif ($order['status'] == 'failed') echo 'Gagal';
                                                else echo 'Pending';
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Belum ada order.</p>
                    <?php endif; ?>
                </div>
                
                <div class="recent-activity">
                    <h3>Topup Pending</h3>
                    <?php if (count($pending_topups_list) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Reseller</th>
                                    <th>Jumlah</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_topups_list as $topup): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($topup['reseller_name']); ?></td>
                                        <td>Rp <?php echo number_format($topup['amount'], 0, ',', '.'); ?></td>
                                        <td><?php echo date('d M Y H:i', strtotime($topup['created_at'])); ?></td>
                                        <td>
                                            <a href="topups.php?action=approve&id=<?php echo $topup['id']; ?>" class="btn btn-success">Setujui</a>
                                            <a href="topups.php?action=reject&id=<?php echo $topup['id']; ?>" class="btn btn-danger">Tolak</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Tidak ada topup pending.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2023 RUD'S STORE - Jual Kuota Internet XL AXIS Termurah</p>
        </footer>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>