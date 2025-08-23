<?php
require_once 'includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jumlah = $_POST['jumlah'];
    
    if ($jumlah < 10000) {
        $error = 'Minimum topup adalah Rp 10.000';
    } else {
        // Handle file upload
        if (isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] == 0) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $bukti_transfer = handleFileUpload($_FILES['bukti_transfer'], $upload_dir);
            
            if ($bukti_transfer) {
                // Insert topup request
                $stmt = $pdo->prepare("INSERT INTO topups (reseller_id, jumlah, bukti_transfer) VALUES (?, ?, ?)");
                
                if ($stmt->execute([$_SESSION['user_id'], $jumlah, $bukti_transfer])) {
                    $success = 'Request topup berhasil dikirim. Menunggu konfirmasi admin.';
                } else {
                    $error = 'Terjadi kesalahan. Silakan coba lagi.';
                }
            } else {
                $error = 'Gagal mengupload bukti transfer. Pastikan file adalah gambar (JPG, PNG, GIF) dan maksimal 5MB.';
            }
        } else {
            $error = 'Harap upload bukti transfer.';
        }
    }
}

// Get rekening info from config
global $rekening_info;

$page_title = 'Topup Saldo';
?>
<?php include 'includes/header.php'; ?>
    
<div class="container">
    <div class="page-header">
        <h1>Topup Saldo</h1>
        <p>Saldo Anda: <?php echo formatCurrency($_SESSION['user_saldo']); ?></p>
    </div>
    
    <?php if ($error): ?>
    <div class="alert error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="alert success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <h2>Transfer ke Rekening Berikut</h2>
        <div class="rekening-list">
            <?php foreach ($rekening_info as $rek): ?>
            <div class="rekening-item">
                <h3><?php echo $rek['bank']; ?></h3>
                <p>Atas Nama: <?php echo $rek['nama']; ?></p>
                <p>No. Rekening: <strong><?php echo $rek['norek']; ?></strong></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="card">
        <h2>Form Topup Saldo</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="jumlah">Jumlah Topup (Rp)</label>
                <input type="number" id="jumlah" name="jumlah" min="10000" required>
                <small>Minimum topup: Rp 10.000</small>
            </div>
            
            <div class="form-group">
                <label for="bukti_transfer">Bukti Transfer</label>
                <input type="file" id="bukti_transfer" name="bukti_transfer" accept="image/*" required>
                <small>Format: JPG, PNG, GIF (maksimal 5MB)</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Kirim Request Topup</button>
        </form>
    </div>
    
    <div class="card">
        <h2>History Topup</h2>
        <?php
        $stmt = $pdo->prepare("SELECT * FROM topups WHERE reseller_id = ? ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([$_SESSION['user_id']]);
        $topups = $stmt->fetchAll();
        ?>
        
        <?php if (count($topups) > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topups as $topup): ?>
                    <tr>
                        <td><?php echo date('d M Y H:i', strtotime($topup['created_at'])); ?></td>
                        <td><?php echo formatCurrency($topup['jumlah']); ?></td>
                        <td class="status-<?php echo $topup['status']; ?>">
                            <?php 
                            if ($topup['status'] == 'pending') echo 'Pending';
                            elseif ($topup['status'] == 'approved') echo 'Disetujui';
                            else echo 'Ditolak';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p>Belum ada history topup</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>