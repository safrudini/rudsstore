<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Approve or reject topup
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $topup_id = $_POST['topup_id'];
    $action = $_POST['action'];
    
    $stmt = $pdo->prepare("SELECT * FROM topups WHERE id = ?");
    $stmt->execute([$topup_id]);
    $topup = $stmt->fetch();
    
    if ($topup) {
        if ($action == 'approve') {
            // Update topup status
            $stmt = $pdo->prepare("UPDATE topups SET status = 'approved' WHERE id = ?");
            if ($stmt->execute([$topup_id])) {
                // Add saldo to reseller
                $stmt2 = $pdo->prepare("UPDATE resellers SET saldo = saldo + ? WHERE id = ?");
                if ($stmt2->execute([$topup['jumlah'], $topup['reseller_id']])) {
                    $success = 'Topup disetujui dan saldo berhasil ditambahkan.';
                } else {
                    $error = 'Gagal menambahkan saldo.';
                }
            } else {
                $error = 'Gagal menyetujui topup.';
            }
        } elseif ($action == 'reject') {
            $stmt = $pdo->prepare("UPDATE topups SET status = 'rejected' WHERE id = ?");
            if ($stmt->execute([$topup_id])) {
                $success = 'Topup ditolak.';
            } else {
                $error = 'Gagal menolak topup.';
            }
        }
    } else {
        $error = 'Topup tidak ditemukan.';
    }
}

// Get all topups
$stmt = $pdo->prepare("
    SELECT t.*, r.name as reseller_name, r.email as reseller_email 
    FROM topups t 
    JOIN resellers r ON t.reseller_id = r.id 
    ORDER BY t.created_at DESC
");
$stmt->execute();
$topups = $stmt->fetchAll();

$page_title = 'Kelola Topup';
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
            <h1>Kelola Topup</h1>
        </div>
        
        <?php if ($error): ?>
        <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Daftar Request Topup</h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Reseller</th>
                            <th>Jumlah</th>
                            <th>Bukti Transfer</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($topups) > 0): ?>
                        <?php foreach ($topups as $topup): ?>
                        <tr>
                            <td><?php echo $topup['id']; ?></td>
                            <td>
                                <strong><?php echo $topup['reseller_name']; ?></strong><br>
                                <small><?php echo $topup['reseller_email']; ?></small>
                            </td>
                            <td><?php echo formatCurrency($topup['jumlah']); ?></td>
                            <td>
                                <?php if ($topup['bukti_transfer']): ?>
                                <a href="../uploads/<?php echo $topup['bukti_transfer']; ?>" target="_blank" class="btn btn-sm btn-outline">Lihat Bukti</a>
                                <?php else: ?>
                                <span>-</span>
                                <?php endif; ?>
                            </td>
                            <td class="status-<?php echo $topup['status']; ?>">
                                <?php 
                                if ($topup['status'] == 'pending') echo 'Pending';
                                elseif ($topup['status'] == 'approved') echo 'Disetujui';
                                else echo 'Ditolak';
                                ?>
                            </td>
                            <td><?php echo date('d M Y H:i', strtotime($topup['created_at'])); ?></td>
                            <td>
                                <?php if ($topup['status'] == 'pending'): ?>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="topup_id" value="<?php echo $topup['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-sm btn-primary">Setujui</button>
                                </form>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="topup_id" value="<?php echo $topup['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-sm btn-danger">Tolak</button>
                                </form>
                                <?php else: ?>
                                <span>-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Belum ada request topup</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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