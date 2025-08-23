<?php
require_once 'includes/auth.php';

// Get products
$stmt = $pdo->prepare("SELECT * FROM products ORDER BY nama_produk");
$stmt->execute();
$products = $stmt->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $phone = $_POST['phone'];
    
    // Validate phone number
    if (!validatePhone($phone)) {
        $error = 'Format nomor HP tidak valid. Harus diawali dengan 08 dan 10-13 digit angka.';
    } else {
        // Get product details
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            $error = 'Produk tidak ditemukan.';
        } else {
            // Check if user has enough balance
            if ($_SESSION['user_saldo'] < $product['harga']) {
                $error = 'Saldo tidak cukup. Silakan topup saldo terlebih dahulu.';
            } else {
                // Generate UUID for reffid
                $reffid = generateUUID();
                $time = getCurrentTime();
                
                // Prepare API request
                $api_data = [
                    'req' => 'topup',
                    'kodereseller' => API_KODERESELLER,
                    'produk' => $product['kode_unik'],
                    'msisdn' => $phone,
                    'reffid' => $reffid,
                    'time' => $time,
                    'pin' => API_PIN,
                    'password' => API_PASSWORD
                ];
                
                // Send request to API
                $api_response = sendAPIRequest($api_data);
                
                // Insert order to database
                $stmt = $pdo->prepare("
                    INSERT INTO orders (reseller_id, product_id, phone, harga, status, reffid, response_text, trxid) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $status = 'pending';
                $response_text = json_encode($api_response);
                $trxid = null;
                
                if ($api_response && isset($api_response['status_code'])) {
                    if ($api_response['status_code'] == '0') {
                        $status = 'success';
                        $trxid = $api_response['trxid'];
                        
                        // Deduct balance
                        $new_saldo = $_SESSION['user_saldo'] - $product['harga'];
                        $stmt2 = $pdo->prepare("UPDATE resellers SET saldo = ? WHERE id = ?");
                        $stmt2->execute([$new_saldo, $_SESSION['user_id']]);
                        $_SESSION['user_saldo'] = $new_saldo;
                        
                        $success = 'Order berhasil! ' . $api_response['msg'];
                    } elseif ($api_response['status_code'] == '1') {
                        $status = 'failed';
                        $error = 'Order gagal: ' . $api_response['msg'];
                    } else {
                        $status = 'pending';
                        $success = 'Order dalam proses. Silakan cek status secara berkala.';
                    }
                } else {
                    $status = 'pending';
                    $success = 'Order dalam proses. Silakan cek status secara berkala.';
                }
                
                $stmt->execute([
                    $_SESSION['user_id'],
                    $product_id,
                    $phone,
                    $product['harga'],
                    $status,
                    $reffid,
                    $response_text,
                    $trxid
                ]);
                
                if ($status == 'success') {
                    header('Location: history.php?success=1');
                    exit;
                }
            }
        }
    }
}

$page_title = 'Order';
?>
<?php include 'includes/header.php'; ?>
    
<div class="container">
    <div class="page-header">
        <h1>Order Produk</h1>
        <p>Saldo Anda: <?php echo formatCurrency($_SESSION['user_saldo']); ?></p>
    </div>
    
    <?php if ($error): ?>
    <div class="alert error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="alert success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="order-form">
        <form method="POST" action="">
            <div class="form-group">
                <label for="product_id">Pilih Produk</label>
                <select id="product_id" name="product_id" required onchange="updateProductInfo()">
                    <option value="">-- Pilih Produk --</option>
                    <?php foreach ($products as $product): ?>
                    <option value="<?php echo $product['id']; ?>" 
                            data-kode="<?php echo $product['kode_unik']; ?>"
                            data-harga="<?php echo $product['harga']; ?>"
                            data-keterangan="<?php echo htmlspecialchars($product['keterangan']); ?>">
                        <?php echo $product['nama_produk']; ?> - <?php echo formatCurrency($product['harga']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="phone">Nomor HP Tujuan</label>
                <input type="text" id="phone" name="phone" placeholder="087847526737" required>
                <small>Format: 08xxxxxxxxxx (10-13 digit)</small>
            </div>
            
            <div id="product-info" class="product-info" style="display: none;">
                <h3>Informasi Produk</h3>
                <p id="product-kode"></p>
                <p id="product-harga"></p>
                <p id="product-keterangan"></p>
            </div>
            
            <button type="submit" class="btn btn-primary">Order Sekarang</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function updateProductInfo() {
    var select = document.getElementById('product_id');
    var selectedOption = select.options[select.selectedIndex];
    var productInfo = document.getElementById('product-info');
    
    if (select.value) {
        productInfo.style.display = 'block';
        document.getElementById('product-kode').innerHTML = '<strong>Kode:</strong> ' + selectedOption.getAttribute('data-kode');
        document.getElementById('product-harga').innerHTML = '<strong>Harga:</strong> ' + formatCurrency(selectedOption.getAttribute('data-harga'));
        document.getElementById('product-keterangan').innerHTML = '<strong>Keterangan:</strong> ' + selectedOption.getAttribute('data-keterangan');
    } else {
        productInfo.style.display = 'none';
    }
}

function formatCurrency(amount) {
    return 'Rp ' + Number(amount).toLocaleString('id-ID');
}
</script>