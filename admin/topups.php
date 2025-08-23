<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!is_admin_logged_in()) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Process topup action
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $topup_id = $_GET['id'];
    $admin_id = $_SESSION['admin_id'];
    
    $stmt = $pdo->prepare("SELECT * FROM topups WHERE id = ?");
    $stmt->execute([$topup_id]);
    $topup = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$topup) {
        $error = "Topup tidak ditemukan!";
    } else {
        if ($action == 'approve') {
            // Update topup status
            $stmt = $pdo->prepare("UPDATE topups SET status = 'approved', processed_by = ? WHERE id = ?");
            if ($stmt->execute([$admin_id, $topup_id])) {
                // Add balance to reseller
                $stmt = $pdo->prepare("UPDATE resellers SET balance = balance + ? WHERE id = ?");
                if ($stmt->execute([$topup['amount'], $topup['reseller_id']])) {
                    $success = "Topup disetujui dan saldo telah ditambahkan.";
                } else {
                    $error = "Gagal menambahkan saldo reseller.";
                }
            } else {
                $error = "Gagal menyetujui topup.";
            }
        } elseif ($action == 'reject') {
            // Update topup status
            $stmt = $pdo->prepare("UPDATE topups SET status = 'rejected', processed_by = ? WHERE id = ?");
            if ($stmt->execute([$admin_id, $topup_id])) {
                $success = "Topup ditolak.";
            } else {
                $error = "Gagal menolak topup.";
            }
        }
    }
}

// Get all topups with reseller info
$topups_stmt = $pdo->query("SELECT t.*, r.name as reseller_name 
                           FROM topups t 
                           JOIN resellers r ON t.reseller_id = r.id 
                           ORDER BY t.created_at DESC");
$topups = $topups_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Topup - RUD'S STORE</title>
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
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="products.php">Produk</a></li>
                        <li><a href="resellers.php">Reseller</a></li>
                        <li><a href="orders.php">Order</a></li>
                        <li><a href="topups.php" class="active">Topup</a></li>
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
                    <a href="dashboard.php">Dashboard</a>
                    <a href="products.php">Produk</a>
                    <a href="resellers.php">Reseller</a>
                    <a href="orders.php">Order</a>
                    <a href="topups.php" class="active">Topup</a>
                    <a href="bank_accounts.php">Rekening</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>
            
            <div class="main-content">
                <h2>Kelola Topup Saldo</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (count($topups) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Reseller</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Bukti Transfer</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topups as $topup): ?>
                                <tr>
                                    <td><?php echo date('d M Y H:i', strtotime($topup['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($topup['reseller_name']); ?></td>
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
                                    <td>
                                        <?php if ($topup['status'] == 'pending'): ?>
                                            <a href="topups.php?action=approve&id=<?php echo $topup['id']; ?>" class="btn btn-success">Setujui</a>
                                            <a href="topups.php?action=reject&id=<?php echo $topup['id']; ?>" class="btn btn-danger">Tolak</a>
                                        <?php else: ?>
                                            <span>Telah diproses</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Belum ada permintaan topup.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2023 RUD'S STORE - Jual Kuota Internet XL AXIS Termurah</p>
        </footer>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>