<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!is_reseller_logged_in()) {
    header("Location: login.php");
    exit();
}

// Get reseller info
$reseller_id = $_SESSION['reseller_id'];
$stmt = $pdo->prepare("SELECT * FROM resellers WHERE id = ?");
$stmt->execute([$reseller_id]);
$reseller = $stmt->fetch(PDO::FETCH_ASSOC);

// Get orders
$orders_stmt = $pdo->prepare("SELECT o.*, p.product_name FROM orders o 
                             JOIN products p ON o.product_id = p.id 
                             WHERE o.reseller_id = ? 
                             ORDER BY o.created_at DESC");
$orders_stmt->execute([$reseller_id]);
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get topups
$topups_stmt = $pdo->prepare("SELECT * FROM topups 
                             WHERE reseller_id = ? 
                             ORDER BY created_at DESC");
$topups_stmt->execute([$reseller_id]);
$topups = $topups_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histori - RUD'S STORE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <div class="logo">RUD'S STORE</div>
                <nav>
                    <ul>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="order.php">Order</a></li>
                        <li><a href="history.php" class="active">Histori</a></li>
                        <li><a href="topup.php">Topup</a></li>
                        <li><a href="area.php" target="_blank">Cek Area</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </header>
        
        <div class="dashboard">
            <div class="sidebar">
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($reseller['name']); ?></h3>
                    <p>Saldo: Rp <?php echo number_format($reseller['balance'], 0, ',', '.'); ?></p>
                </div>
                <nav>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="order.php">Order</a>
                    <a href="history.php" class="active">Histori Order</a>
                    <a href="topup.php">Topup Saldo</a>
                    <a href="area.php" target="_blank">Cek Area</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>
            
            <div class="main-content">
                <h2>Histori Order</h2>
                
                <div class="tabs">
                    <button class="tab-button active" onclick="openTab('orders')">Order Produk</button>
                    <button class="tab-button" onclick="openTab('topups')">Topup Saldo</button>
                </div>
                
                <div id="orders" class="tab-content active">
                    <?php if (count($orders) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Produk</th>
                                    <th>No. HP</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                    <th>Kode Unik</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></td>
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
                                        <td><?php echo htmlspecialchars($order['unique_code']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Belum ada order.</p>
                    <?php endif; ?>
                </div>
                
                <div id="topups" class="tab-content">
                    <?php if (count($topups) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Bukti Transfer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topups as $topup): ?>
                                    <tr>
                                        <td><?php echo date('d M Y H:i', strtotime($topup['created_at'])); ?></td>
                                        <td>Rp <?php echo number_format($topup['amount'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="status-<?php echo $topup['status']; ?>">
                                                <?php 
                                                if ($topup['status'] == 'approved') echo 'Disetujui';
                                                elseif ($topup['status'] == 'rejected') echo 'Ditolak';
                                                else echo 'Pending';
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($topup['proof_image']): ?>
                                                <a href="../assets/images/uploads/<?php echo $topup['proof_image']; ?>" target="_blank">Lihat Bukti</a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Belum ada topup saldo.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2023 RUD'S STORE - Jual Kuota Internet XL AXIS Termurah</p>
        </footer>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script>
        function openTab(tabName) {
            var i, tabContent, tabButtons;
            
            // Hide all tab content
            tabContent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabContent.length; i++) {
                tabContent[i].classList.remove("active");
            }
            
            // Remove active class from all buttons
            tabButtons = document.getElementsByClassName("tab-button");
            for (i = 0; i < tabButtons.length; i++) {
                tabButtons[i].classList.remove("active");
            }
            
            // Show the specific tab content and add active class to the button
            document.getElementById(tabName).classList.add("active");
            event.currentTarget.classList.add("active");
        }
    </script>
</body>
</html>