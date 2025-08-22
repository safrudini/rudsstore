<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!is_admin_logged_in()) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Add new product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $product_code = $_POST['product_code'];
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    
    if (empty($product_code) || empty($product_name) || empty($price)) {
        $error = "Kode produk, nama produk, dan harga harus diisi!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (product_code, product_name, description, price) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$product_code, $product_name, $description, $price])) {
            $success = "Produk berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan produk. Mungkin kode produk sudah ada.";
        }
    }
}

// Edit product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_product'])) {
    $product_id = $_POST['product_id'];
    $product_code = $_POST['product_code'];
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    
    if (empty($product_code) || empty($product_name) || empty($price)) {
        $error = "Kode produk, nama produk, dan harga harus diisi!";
    } else {
        $stmt = $pdo->prepare("UPDATE products SET product_code = ?, product_name = ?, description = ?, price = ? WHERE id = ?");
        if ($stmt->execute([$product_code, $product_name, $description, $price, $product_id])) {
            $success = "Produk berhasil diperbarui!";
        } else {
            $error = "Gagal memperbarui produk.";
        }
    }
}

// Delete product
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$product_id])) {
        $success = "Produk berhasil dihapus!";
    } else {
        $error = "Gagal menghapus produk. Mungkin produk sedang digunakan dalam order.";
    }
}

// Get all products
$products_stmt = $pdo->query("SELECT * FROM products ORDER BY product_name");
$products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    $product_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - RUD'S STORE</title>
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
                        <li><a href="products.php" class="active">Produk</a></li>
                        <li><a href="resellers.php">Reseller</a></li>
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
                    <a href="products.php" class="active">Produk</a>
                    <a href="resellers.php">Reseller</a>
                    <a href="orders.php">Order</a>
                    <a href="topups.php">Topup</a>
                    <a href="bank_accounts.php">Rekening</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>
            
            <div class="main-content">
                <h2>Kelola Produk</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="tabs">
                    <button class="tab-button active" onclick="openTab('product-list')">Daftar Produk</button>
                    <button class="tab-button" onclick="openTab('add-product')"><?php echo $edit_product ? 'Edit Produk' : 'Tambah Produk'; ?></button>
                </div>
                
                <div id="product-list" class="tab-content active">
                    <?php if (count($products) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Kode Produk</th>
                                    <th>Nama Produk</th>
                                    <th>Deskripsi</th>
                                    <th>Harga</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['product_code']); ?></td>
                                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                                        <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                                        <td>
                                            <a href="products.php?edit=<?php echo $product['id']; ?>" class="btn">Edit</a>
                                            <a href="products.php?delete=<?php echo $product['id']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Belum ada produk.</p>
                    <?php endif; ?>
                </div>
                
                <div id="add-product" class="tab-content">
                    <form method="POST" action="">
                        <?php if ($edit_product): ?>
                            <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="product_code">Kode Produk (API)</label>
                            <input type="text" id="product_code" name="product_code" value="<?php echo $edit_product ? htmlspecialchars($edit_product['product_code']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="product_name">Nama Produk</label>
                            <input type="text" id="product_name" name="product_name" value="<?php echo $edit_product ? htmlspecialchars($edit_product['product_name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Deskripsi Produk</label>
                            <textarea id="description" name="description" rows="3"><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Harga (Rp)</label>
                            <input type="number" id="price" name="price" value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>" required>
                        </div>
                        
                        <button type="submit" name="<?php echo $edit_product ? 'edit_product' : 'add_product'; ?>" class="btn">
                            <?php echo $edit_product ? 'Update Produk' : 'Tambah Produk'; ?>
                        </button>
                        
                        <?php if ($edit_product): ?>
                            <a href="products.php" class="btn btn-danger">Batal</a>
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
        <?php if ($edit_product): ?>
            document.addEventListener('DOMContentLoaded', function() {
                openTab('add-product');
                document.querySelector('.tab-button:nth-child(2)').classList.add('active');
            });
        <?php endif; ?>
    </script>
</body>
</html>