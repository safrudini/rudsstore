<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (is_reseller_logged_in()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM resellers WHERE email = ?");
    $stmt->execute([$email]);
    $reseller = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($reseller && password_verify($password, $reseller['password'])) {
        $_SESSION['reseller_id'] = $reseller['id'];
        $_SESSION['reseller_email'] = $reseller['email'];
        $_SESSION['reseller_name'] = $reseller['name'];
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Reseller - RUD'S STORE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <div class="logo">RUD'S STORE</div>
            </div>
        </header>
        
        <div class="auth-container">
            <div class="auth-form">
                <h2>Login Reseller</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn">Login</button>
                </form>
                
                <p class="auth-link">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2023 RUD'S STORE - Jual Kuota Internet XL AXIS Termurah</p>
        </footer>
    </div>
</body>
</html>