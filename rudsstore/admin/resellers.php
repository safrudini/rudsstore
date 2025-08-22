<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!is_admin_logged_in()) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Update reseller balance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_balance'])) {
    $reseller_id = $_POST['reseller_id'];
    $amount = $_POST['amount'];
    $action = $_POST['action'];
    
    if (empty($amount) || $amount <= 0) {
        $error = "Jumlah harus lebih dari 0!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM resellers WHERE id = ?");
        $stmt->execute([$reseller_id]);
        $reseller = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reseller) {
            $error = "Reseller tidak ditemukan!";
        } else {
            if ($action == 'add') {
                $stmt = $pdo->prepare("UPDATE resellers SET balance = balance + ? WHERE id = ?");
            } else {
                $stmt = $pdo->prepare("UPDATE resellers SET balance = balance - ? WHERE id = ?");
            }
            
            if ($stmt->execute([$amount, $reseller_id])) {
                $success = "Saldo reseller berhasil diperbarui!";
            } else {
                $error = "Gagal memperbarui saldo reseller.";
            }
        }
    }
}

// Get all resellers
$resellers_stmt = $pdo->query("SELECT * FROM resellers ORDER BY name");
$resellers = $resellers_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Reseller - RUD'S STORE</title>
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
                        <li><a href="resellers.php" class="active">Reseller</a></li>
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
                    <a href="dashboard.php">Dashboard</a>
                    <a href="products.php">Produk</a>
                    <a href="resellers.php" class="active">Reseller</a>
                    <a href="orders.php">Order</a>
                    <a href="topups.php">Topup</a>
                    <a href="bank_accounts.php">Rekening</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>
            
            <div class="main-content">
                <h2>Kelola Reseller</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (count($resellers) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Telepon</th>
                                <th>Saldo</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resellers as $reseller): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reseller['name']); ?></td>
                                    <td><?php echo htmlspecialchars($reseller['email']); ?></td>
                                    <td><?php echo htmlspecialchars($reseller['phone']); ?></td>
                                    <td>Rp <?php echo number_format($reseller['balance'], 0, ',', '.'); ?></td>
                                    <td><?php echo date('d M Y', strtotime($reseller['created_at'])); ?></td>
                                    <td>
                                        <button class="btn" onclick="openBalanceModal(<?php echo $reseller['id']; ?>, '<?php echo htmlspecialchars($reseller['name']); ?>', <?php echo $reseller['balance']; ?>)">Edit Saldo</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Belum ada reseller.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2023 RUD'S STORE - Jual Kuota Internet XL AXIS Termurah</p>
        </footer>
    </div>
    
    <!-- Balance Modal -->
    <div id="balanceModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit Saldo Reseller</h3>
            <p>Reseller: <span id="modal-reseller-name"></span></p>
            <p>Saldo Saat Ini: Rp <span id="modal-current-balance"></span></p>
            
            <form method="POST" action="">
                <input type="hidden" id="modal-reseller-id" name="reseller_id">
                
                <div class="form-group">
                    <label for="amount">Jumlah</label>
                    <input type="number" id="amount" name="amount" min="1" required>
                </div>
                
                <div class="form-group">
                    <label for="action">Aksi</label>
                    <select id="action" name="action" required>
                        <option value="add">Tambah Saldo</option>
                        <option value="subtract">Kurangi Saldo</option>
                    </select>
                </div>
                
                <button type="submit" name="update_balance" class="btn">Update Saldo</button>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script>
        // Get the modal
        var modal = document.getElementById("balanceModal");
        
        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];
        
        // Function to open the modal
        function openBalanceModal(resellerId, resellerName, currentBalance) {
            document.getElementById("modal-reseller-id").value = resellerId;
            document.getElementById("modal-reseller-name").textContent = resellerName;
            document.getElementById("modal-current-balance").textContent = currentBalance.toLocaleString('id-ID');
            modal.style.display = "block";
        }
        
        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }
        
        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>