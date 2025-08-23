<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Add saldo to reseller
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_saldo'])) {
    $reseller_id = $_POST['reseller_id'];
    $jumlah = $_POST['jumlah'];
    
    if ($jumlah <= 0) {
        $error = 'Jumlah saldo harus lebih dari 0';
    } else {
        $stmt = $pdo->prepare("UPDATE resellers SET saldo = saldo + ? WHERE id = ?");
        if ($stmt->execute([$jumlah, $reseller_id])) {
            $success = 'Saldo berhasil ditambahkan.';
        } else {
            $error = 'Gagal menambahkan saldo.';
        }
    }
}

// Get all resellers
$stmt = $pdo->prepare("SELECT * FROM resellers ORDER BY created_at DESC");
$stmt->execute();
$resellers = $stmt->fetchAll();

$page_title = 'Kelola Reseller';
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
            <h1>Kelola Reseller</h1>
        </div>
        
        <?php if ($error): ?>
        <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Daftar Reseller</h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Saldo</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($resellers) > 0): ?>
                        <?php foreach ($resellers as $reseller): ?>
                        <tr>
                            <td><?php echo $reseller['id']; ?></td>
                            <td><?php echo $reseller['name']; ?></td>
                            <td><?php echo $reseller['email']; ?></td>
                            <td><?php echo $reseller['phone']; ?></td>
                            <td><?php echo formatCurrency($reseller['saldo']); ?></td>
                            <td><?php echo date('d M Y', strtotime($reseller['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="showAddSaldoModal(<?php echo $reseller['id']; ?>, '<?php echo $reseller['name']; ?>')">Tambah Saldo</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Belum ada reseller</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Add Saldo Modal -->
    <div id="addSaldoModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Tambah Saldo</h2>
            <p>Reseller: <span id="modal-reseller-name"></span></p>
            <form method="POST" action="">
                <input type="hidden" id="modal-reseller-id" name="reseller_id">
                
                <div class="form-group">
                    <label for="jumlah">Jumlah Saldo (Rp)</label>
                    <input type="number" id="jumlah" name="jumlah" min="1000" required>
                </div>
                
                <button type="submit" name="add_saldo" class="btn btn-primary">Tambah Saldo</button>
            </form>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
    
    <script>
    function showAddSaldoModal(resellerId, resellerName) {
        document.getElementById('modal-reseller-id').value = resellerId;
        document.getElementById('modal-reseller-name').textContent = resellerName;
        document.getElementById('addSaldoModal').style.display = 'flex';
    }
    
    // Close modal
    document.querySelector('.close').addEventListener('click', function() {
        document.getElementById('addSaldoModal').style.display = 'none';
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target == document.getElementById('addSaldoModal')) {
            document.getElementById('addSaldoModal').style.display = 'none';
        }
    });
    </script>
</body>
</html>