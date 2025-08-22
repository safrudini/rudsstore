<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!is_reseller_logged_in()) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Area - RUD'S STORE</title>
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
                        <li><a href="topup.php">Topup</a></li>
                        <li><a href="area.php" class="active">Cek Area</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </header>
        
        <div class="dashboard">
            <div class="sidebar">
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($_SESSION['reseller_name']); ?></h3>
                    <p>Saldo: Rp <?php 
                    $stmt = $pdo->prepare("SELECT balance FROM resellers WHERE id = ?");
                    $stmt->execute([$_SESSION['reseller_id']]);
                    $reseller = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo number_format($reseller['balance'], 0, ',', '.'); 
                    ?></p>
                </div>
                <nav>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="order.php">Order</a>
                    <a href="history.php">Histori Order</a>
                    <a href="topup.php">Topup Saldo</a>
                    <a href="area.php" class="active">Cek Area</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>
            
            <div class="main-content">
                <h2>Cek Area Jaringan</h2>
                
                <div class="area-check">
                    <p>Fitur cek area sedang dalam pengembangan. Silakan gunakan layanan pihak ketiga untuk saat ini.</p>
                    <p>Anda dapat menggunakan layanan seperti:</p>
                    <ul>
                        <li><a href="https://coveragemap.telkomsel.com/" target="_blank">Telkomsel Coverage Map</a></li>
                        <li><a href="https://www.indosatooredoo.com/portal/id/coverage" target="_blank">Indosat Ooredoo Coverage</a></li>
                        <li><a href="https://www.xl.co.id/id/support/coverage" target="_blank">XL Axiata Coverage</a></li>
                        <li><a href="https://www.three.co.id/coveragedirectory" target="_blank">3 (Three) Coverage</a></li>
                    </ul>
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