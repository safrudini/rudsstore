<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Add new product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $kode_unik = $_POST['kode_unik'];
    $nama_produk = $_POST['nama_produk'];
    $keterangan = $_POST['keterangan'];
    $harga = $_POST['harga'];
    
    $stmt = $pdo->prepare("INSERT INTO products (kode_unik, nama_produk, keterangan, harga) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$kode_unik, $nama_produk, $keterangan, $harga])) {
        $success = 'Produk berhasil ditambahkan.';
    } else {
        $error = 'Gagal menambahkan produk.';
    }
}

// Update product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $id = $_POST['id'];
    $kode_unik = $_POST['kode_unik'];
    $nama_produk = $_POST['nama_produk'];
    $keterangan = $_POST['keterangan'];
    $harga = $_POST['harga'];
    
    $stmt = $pdo->prepare("UPDATE products SET kode_unik = ?, nama_produk = ?, keterangan = ?, harga = ? WHERE id = ?");
    if ($stmt->execute([$kode_unik, $nama_produk, $keterangan, $harga, $id])) {
        $success = 'Produk berhasil diperbarui.';
    } else {
        $error = 'Gagal memperbarui produk.';
    }
}

// Delete product
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = 'Produk berhasil dihapus.';
    } else {
        $error = 'Gagal menghapus produk.';
    }
}

// Get all products
$stmt = $pdo->prepare("SELECT * FROM products ORDER BY id DESC");
$stmt->execute();
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>
    <?php include '../includes/admin-header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Kelola Produk</h1>
        </div>
        
        <?php if ($error): ?>
        <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Tambah Produk Baru</h2>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="kode_unik">Kode Unik</label>
                        <input type="text" id="kode_unik" name="kode_unik" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nama_produk">Nama Produk</label>
                        <input type="text" id="nama_produk" name="nama_produk" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="harga">Harga</label>
                    <input type="number" id="harga" name="harga" required>
                </div>
                
                <button type="submit" name="add_product" class="btn btn-primary">Tambah Produk</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Daftar Produk</h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kode Unik</th>
                            <th>Nama Produk</th>
                            <th>Keterangan</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo $product['kode_unik']; ?></td>
                            <td><?php echo $product['nama_produk']; ?></td>
                            <td><?php echo $product['keterangan']; ?></td>
                            <td><?php echo formatCurrency($product['harga']); ?></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline" onclick="editProduct(<?php echo $product['id']; ?>, '<?php echo $product['kode_unik']; ?>', '<?php echo $product['nama_produk']; ?>', '<?php echo addslashes($product['keterangan']); ?>', <?php echo $product['harga']; ?>)">Edit</a>
                                <a href="?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Edit Product Modal -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Produk</h2>
            <form method="POST" action="">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="form-group">
                    <label for="edit_kode_unik">Kode Unik</label>
                    <input type="text" id="edit_kode_unik" name="kode_unik" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_nama_produk">Nama Produk</label>
                    <input type="text" id="edit_nama_produk" name="nama_produk" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_keterangan">Keterangan</label>
                    <textarea id="edit_keterangan" name="keterangan" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_harga">Harga</label>
                    <input type="number" id="edit_harga" name="harga" required>
                </div>
                
                <button type="submit" name="update_product" class="btn btn-primary">Update Produk</button>
            </form>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    function editProduct(id, kode, nama, keterangan, harga) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_kode_unik').value = kode;
        document.getElementById('edit_nama_produk').value = nama;
        document.getElementById('edit_keterangan').value = keterangan;
        document.getElementById('edit_harga').value = harga;
        
        document.getElementById('editModal').style.display = 'block';
    }
    
    // Close modal
    document.querySelector('.close').addEventListener('click', function() {
        document.getElementById('editModal').style.display = 'none';
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target == document.getElementById('editModal')) {
            document.getElementById('editModal').style.display = 'none';
        }
    });
    </script>
</body>
</html>