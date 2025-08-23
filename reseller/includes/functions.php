<?php
function format_rupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function validate_phone($phone) {
    return preg_match('/^08[0-9]{9,12}$/', $phone);
}

function get_product_name($product_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT product_name FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    return $product ? $product['product_name'] : 'Unknown Product';
}
?>