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

// Get bank accounts
$bank_stmt = $pdo->query("SELECT * FROM bank_accounts");
$bank_accounts = $bank_stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = $_POST['amount'];
    
    // Validasi jumlah
    if ($amount < 10000) {
        $error = "Minimum topup adalah Rp 10.000";
    } else {
        // Handle file upload
        $proof_image = null;
        if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] == UPLOAD_ERR_OK) {
            $file_ext = pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION);
            $proof_image = 'topup_' . time() . '_' . $reseller_id . '.' . $file_ext;
            $upload_path = '../assets/images/uploads/' . $proof_image;
            
            if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $upload_path)) {
                // File uploaded successfully
            } else {
                $error = "Gagal mengupload bukti transfer.";
            }
        } else {
            $error = "Harap upload bukti transfer.";
        }
        
        if (!$error) {
            // Save topup request
            $stmt = $pdo->prepare("INSERT INTO topups (reseller_id, amount, proof_image) VALUES (?, ?, ?)");
            if ($stmt->execute([$reseller_id, $amount, $proof_image])) {
                $success = "Permintaan topup berhasil dikirim. Menunggu persetujuan admin.";
            } else {
                $error = "Terjadi kesalahan. Silakan coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Topup Saldo - RUD'S STORE</title>
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
                        <li><a href="history.php">Histori</a></li>
                        <li><a href="topup.php" class="active">Topup</a></li>
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
                    <a href="history.php">Histori Order</a>
                    <a href="topup.php" class="active">Topup Saldo</a>
                    <a href="area.php" target="_blank">Cek Area</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>
            
            <div class="main-content">
                <h2>Topup Saldo</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="topup-container">
                    <div class="topup-form">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="amount">Jumlah Topup (Rp)</label>
                                <input type="number" id="amount" name="amount" min="10000" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="proof_image">Bukti Transfer</label>
                                <input type="file" id="proof_image" name="proof_image" accept="image/*" required>
                            </div>
                            
                            <button type="submit" class="btn">Kirim Permintaan Topup</button>
                        </form>
                    </div>
                    
                    <div class="bank-accounts">
                        <h3>Rekening Tujuan</h3>
                        <?php foreach ($bank_accounts as $bank): ?>
                            <div class="bank-account">
                                <h4><?php echo htmlspecialchars($bank['bank_name']); ?></h4>
                                <p>No. Rekening: <?php echo htmlspecialchars($bank['account_number']); ?></p>
                                <p>Atas Nama: <?php echo htmlspecialchars($bank['account_name']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
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