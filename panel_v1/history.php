<?php
require_once 'includes/auth.php';

$user_id = $_SESSION['user_id'];

// Get all orders for this user
$stmt = $pdo->prepare("
    SELECT o.*, p.nama_produk, p.kode_unik 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    WHERE o.reseller_id = ? 
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

$page_title = 'History Order';
?>
<?php include 'includes/header.php'; ?>
    
<div class="container">
    <div class="page-header">
        <h1>History Order</h1>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
    <div class="alert success">Order berhasil!</div>
    <?php endif; ?>
    
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Produk</th>
                        <th>Nomor Tujuan</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Trx ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></td>
                        <td><?php echo $order['nama_produk']; ?> (<?php echo $order['kode_unik']; ?>)</td>
                        <td><?php echo $order['phone']; ?></td>
                        <td><?php echo formatCurrency($order['harga']); ?></td>
                        <td class="status-<?php echo $order['status']; ?>">
                            <?php 
                            if ($order['status'] == 'pending') echo 'Pending';
                            elseif ($order['status'] == 'success') echo 'Sukses';
                            else echo 'Gagal';
                            ?>
                        </td>
                        <td><?php echo $order['trxid'] ? $order['trxid'] : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Belum ada order</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>