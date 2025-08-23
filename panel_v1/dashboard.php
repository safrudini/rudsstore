<?php
require_once 'includes/auth.php';

// Get user info
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM resellers WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get latest orders
$orders_stmt = $pdo->prepare("
    SELECT o.*, p.nama_produk 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    WHERE o.reseller_id = ? 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$orders_stmt->execute([$user_id]);
$latest_orders = $orders_stmt->fetchAll();

// Get latest topups
$topups_stmt = $pdo->prepare("
    SELECT * FROM topups 
    WHERE reseller_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$topups_stmt->execute([$user_id]);
$latest_topups = $topups_stmt->fetchAll();

$page_title = 'Dashboard';
?>
<?php include 'includes/header.php'; ?>
    
<div class="container">
    <div class="dashboard">
        <div class="welcome-card">
            <h2>Selamat Datang, <?php echo $_SESSION['user_name']; ?></h2>
            <div class="saldo-info">
                <h3>Saldo Anda: <?php echo formatCurrency($user['saldo']); ?></h3>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>Menu Cepat</h3>
                <div class="quick-menu">
                    <a href="order.php" class="btn btn-primary">Order Produk</a>
                    <a href="topup.php" class="btn btn-secondary">Topup Saldo</a>
                    <a href="history.php" class="btn btn-outline">Riwayat Order</a>
                    <a href="check_area.php" class="btn btn-outline">Cek Area</a>
                </div>
            </div>
            
            <div class="dashboard-card">
                <h3>Order Terbaru</h3>
                <?php if (count($latest_orders) > 0): ?>
                <div class="order-list">
                    <?php foreach ($latest_orders as $order): ?>
                    <div class="order-item">
                        <div class="order-info">
                            <p><strong><?php echo $order['nama_produk']; ?></strong></p>
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
                <a href="history.php" class="btn btn-link">Lihat Semua</a>
            </div>
            
            <div class="dashboard-card">
                <h3>Topup Terbaru</h3>
                <?php if (count($latest_topups) > 0): ?>
                <div class="topup-list">
                    <?php foreach ($latest_topups as $topup): ?>
                    <div class="topup-item">
                        <div class="topup-info">
                            <p><?php echo formatCurrency($topup['jumlah']); ?></p>
                            <p class="status-<?php echo $topup['status']; ?>">
                                <?php 
                                if ($topup['status'] == 'pending') echo 'Pending';
                                elseif ($topup['status'] == 'approved') echo 'Disetujui';
                                else echo 'Ditolak';
                                ?>
                            </p>
                        </div>
                        <div class="topup-date">
                            <?php echo date('d M Y', strtotime($topup['created_at'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p>Belum ada topup</p>
                <?php endif; ?>
                <a href="topup.php" class="btn btn-link">Lihat Semua</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>