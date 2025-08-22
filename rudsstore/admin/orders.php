<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!is_admin_logged_in()) {
    header("Location: login.php");
    exit();
}

// Get all orders with reseller and product info
$orders_stmt = $pdo->query("SELECT o.*, r.name as reseller_name, p.product_name 
                           FROM orders o 
                           JOIN resellers r ON o.reseller_id = r.id 
                           JOIN products p ON o.product_id = p.id 
                           ORDER BY o.created_at DESC");
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Order - RUD'S STORE</title>
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
                        <li><a href="orders.php" class="active">Order</a></li>
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
                    <a href="resellers.php">Reseller</a>
                    <a href="orders.php" class="active">Order</a>
                    <a href="topups.php">Topup</a>
                    <a href="bank_accounts.php">Rekening</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>
            
            <div class="main-content">
                <h2>Kelola Order</h2>
                
                <?php if (count($orders) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Reseller</th>
                                <th>Produk</th>
                                <th>No. HP</th>
                                <th>Harga</th>
                                <th>Status</th>
                                <th>Kode Unik</th>
                                <th>Response API</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($order['reseller_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['phone_number']); ?></td>
                                    <td>Rp <?php echo number_format($order['price'], 0, ',', '.'); ?></td>
                                    <td>
                                        <span class="status-<?php echo $order['status']; ?>">
                                            <?php 
                                            if ($order['status'] == 'success') echo 'Sukses';
                                            elseif ($order['status'] == 'failed') echo 'Gagal';
                                            else echo 'Pending';
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['unique_code']); ?></td>
                                    <td>
                                        <?php if ($order['api_response']): ?>
                                            <button class="btn" onclick="showApiResponse(<?php echo $order['id']; ?>)">Lihat Response</button>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Belum ada order.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2023 RUD'S STORE - Jual Kuota Internet XL AXIS Termurah</p>
        </footer>
    </div>
    
    <!-- API Response Modal -->
    <div id="apiResponseModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Response API</h3>
            <pre id="api-response-content"></pre>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script>
        // Function to show API response
        function showApiResponse(orderId) {
            // In a real application, you would fetch this data via AJAX
            // For simplicity, we'll just show a placeholder
            document.getElementById("api-response-content").textContent = "Loading response...";
            
            // Simulate loading (in real app, use AJAX to fetch the response)
            setTimeout(function() {
                document.getElementById("api-response-content").textContent = "API response data for order #" + orderId;
            }, 500);
            
            // Show the modal
            document.getElementById("apiResponseModal").style.display = "block";
        }
        
        // Get the modal
        var modal = document.getElementById("apiResponseModal");
        
        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];
        
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