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

// Get products
$products_stmt = $pdo->query("SELECT * FROM products ORDER BY product_name");
$products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $phone_number = $_POST['phone_number'];
    
    // Validasi nomor HP
    if (!preg_match('/^08[0-9]{9,12}$/', $phone_number)) {
        $error = "Format nomor HP tidak valid! Harus diawali dengan 08 dan panjang 10-13 digit.";
    } else {
        // Get product details
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            $error = "Produk tidak ditemukan!";
        } elseif ($reseller['balance'] < $product['price']) {
            $error = "Saldo tidak cukup! Silakan topup saldo terlebih dahulu.";
        } else {
            // Generate unique code (UUID)
            $unique_code = uniqid('RUD', true);
            
            // Prepare API request
            $time = date('His');
            $api_data = [
                'req' => 'topup',
                'kodereseller' => API_RESELLER_CODE,
                'produk' => $product['product_code'],
                'msisdn' => $phone_number,
                'reffid' => $unique_code,
                'time' => $time,
                'pin' => API_PIN,
                'password' => API_PASSWORD
            ];
            
            // Send request to API
            $ch = curl_init(API_URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Process response
            if ($response) {
                $api_response = json_decode($response, true);
                
                // Determine status based on API response
                $status = 'pending';
                if (isset($api_response['status_code'])) {
                    if ($api_response['status_code'] == '0') {
                        $status = 'success';
                    } elseif ($api_response['status_code'] == '1') {
                        $status = 'failed';
                    }
                }
                
                // Save order to database
                $stmt = $pdo->prepare("INSERT INTO orders (reseller_id, product_id, phone_number, unique_code, price, status, api_response) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$reseller_id, $product_id, $phone_number, $unique_code, $product['price'], $status, $response]);
                
                if ($status == 'success') {
                    // Deduct balance
                    $stmt = $pdo->prepare("UPDATE resellers SET balance = balance - ? WHERE id = ?");
                    $stmt->execute([$product['price'], $reseller_id]);
                    
                    $success = "Order berhasil! " . $api_response['msg'];
                } elseif ($status == 'failed') {
                    $error = "Order gagal! " . (isset($api_response['msg']) ? $api_response['msg'] : 'Terjadi kesalahan');
                } else {
                    $success = "Order sedang diproses. Silakan cek status secara berkala.";
                }
            } else {
                $error = "Tidak dapat terhubung ke server. Silakan coba lagi.";
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
    <title>Order - RUD'S STORE</title>
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
                        <li><a href="order.php" class="active">Order</a></li>
                        <li><a href="history.php">Histori</a></li>
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
                    <a href="order.php" class="active">Order</a>
                    <a href="history.php">Histori Order</a>
                    <a href="topup.php">Topup Saldo</a>
                    <a href="area.php" target="_blank">Cek Area</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>
            
            <div class="main-content">
                <h2>Order Produk</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="product_id">Pilih Produk</label>
                        <select id="product_id" name="product_id" required>
                            <option value="">-- Pilih Produk --</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['price']; ?>">
                                    <?php echo htmlspecialchars($product['product_name']); ?> - Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone_number">Nomor HP Tujuan</label>
                        <input type="text" id="phone_number" name="phone_number" placeholder="08xxxxxxxxxx" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Keterangan Produk</label>
                        <div id="product-description" class="product-description">
                            Pilih produk untuk melihat keterangan
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">Order Sekarang</button>
                </form>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2023 RUD'S STORE - Jual Kuota Internet XL AXIS Termurah</p>
        </footer>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script>
        // Update product description when selection changes
        document.getElementById('product_id').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var description = selectedOption.getAttribute('data-description') || 'Tidak ada keterangan';
            document.getElementById('product-description').textContent = description;
        });
        
        // Preload product descriptions
        var products = <?php echo json_encode($products); ?>;
        var select = document.getElementById('product_id');
        for (var i = 0; i < select.options.length; i++) {
            if (select.options[i].value) {
                for (var j = 0; j < products.length; j++) {
                    if (products[j].id == select.options[i].value) {
                        select.options[i].setAttribute('data-description', products[j].description);
                        break;
                    }
                }
            }
        }
    </script>
</body>
</html>