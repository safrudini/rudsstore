<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!is_admin_logged_in()) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Add new bank account
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_bank'])) {
    $bank_name = $_POST['bank_name'];
    $account_number = $_POST['account_number'];
    $account_name = $_POST['account_name'];
    
    if (empty($bank_name) || empty($account_number) || empty($account_name)) {
        $error = "Semua field harus diisi!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO bank_accounts (bank_name, account_number, account_name) VALUES (?, ?, ?)");
        if ($stmt->execute([$bank_name, $account_number, $account_name])) {
            $success = "Rekening berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan rekening.";
        }
    }
}

// Edit bank account
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_bank'])) {
    $bank_id = $_POST['bank_id'];
    $bank_name = $_POST['bank_name'];
    $account_number = $_POST['account_number'];
    $account_name = $_POST['account_name'];
    
    if (empty($bank_name) || empty($account_number) || empty($account_name)) {
        $error = "Semua field harus diisi!";
    } else {
        $stmt = $pdo->prepare("UPDATE bank_accounts SET bank_name = ?, account_number = ?, account_name = ? WHERE id = ?");
        if ($stmt->execute([$bank_name, $account_number, $account_name, $bank_id])) {
            $success = "Rekening berhasil diperbarui!";
        } else {
            $error = "Gagal memperbarui rekening.";
        }
    }
}

// Delete bank account
if (isset($_GET['delete'])) {
    $bank_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM bank_accounts WHERE id = ?");
    if ($stmt->execute([$bank_id])) {
        $success = "Rekening berhasil dihapus!";
    } else {
        $error = "Gagal menghapus rekening.";
    }
}

// Get all bank accounts
$banks_stmt = $pdo->query("SELECT * FROM bank_accounts ORDER BY bank_name");
$banks = $banks_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get bank for editing
$edit_bank = null;
if (isset($_GET['edit'])) {
    $bank_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM bank_accounts WHERE id = ?");
    $stmt->execute([$bank_id]);
    $edit_bank = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Rekening - RUD'S STORE</title>
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
                        <li><a href="topups.php">Topup</a></li>
                        <li><a href="bank_accounts.php" class="active">Rekening</a></li>
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
                    <a href="topups.php">Topup</a>
                    <a href="bank_accounts.php" class="active">Rekening</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>
            
            <div class="main-content">
                <h2>Kelola Rekening Bank</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="tabs">
                    <button class="tab-button active" onclick="openTab('bank-list')">Daftar Rekening</button>
                    <button class="tab-button" onclick="openTab('add-bank')"><?php echo $edit_bank ? 'Edit Rekening' : 'Tambah Rekening'; ?></button>
                </div>
                
                <div id="bank-list" class="tab-content active">
                    <?php if (count($banks) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama Bank</th>
                                    <th>Nomor Rekening</th>
                                    <th>Atas Nama</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($banks as $bank): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($bank['bank_name']); ?></td>
                                        <td><?php echo htmlspecialchars($bank['account_number']); ?></td>
                                        <td><?php echo htmlspecialchars($bank['account_name']); ?></td>
                                        <td>
                                            <a href="bank_accounts.php?edit=<?php echo $bank['id']; ?>" class="btn">Edit</a>
                                            <a href="bank_accounts.php?delete=<?php echo $bank['id']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus rekening ini?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Belum ada rekening bank.</p>
                    <?php endif; ?>
                </div>
                
                <div id="add-bank" class="tab-content">
                    <form method="POST" action="">
                        <?php if ($edit_bank): ?>
                            <input type="hidden" name="bank_id" value="<?php echo $edit_bank['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="bank_name">Nama Bank</label>
                            <input type="text" id="bank_name" name="bank_name" value="<?php echo $edit_bank ? htmlspecialchars($edit_bank['bank_name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="account_number">Nomor Rekening</label>
                            <input type="text" id="account_number" name="account_number" value="<?php echo $edit_bank ? htmlspecialchars($edit_bank['account_number']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="account_name">Atas Nama</label>
                            <input type="text" id="account_name" name="account_name" value="<?php echo $edit_bank ? htmlspecialchars($edit_bank['account_name']) : ''; ?>" required>
                        </div>
                        
                        <button type="submit" name="<?php echo $edit_bank ? 'edit_bank' : 'add_bank'; ?>" class="btn">
                            <?php echo $edit_bank ? 'Update Rekening' : 'Tambah Rekening'; ?>
                        </button>
                        
                        <?php if ($edit_bank): ?>
                            <a href="bank_accounts.php" class="btn btn-danger">Batal</a>
                        <?php endif; ?>
                    </form>
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
        
        // If editing, open the edit tab
        <?php if ($edit_bank): ?>
            document.addEventListener('DOMContentLoaded', function() {
                openTab('add-bank');
                document.querySelector('.tab-button:nth-child(2)').classList.add('active');
            });
        <?php endif; ?>
    </script>
</body>
</html>