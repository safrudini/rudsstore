<?php
function format_rupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function get_reseller_name($reseller_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT name FROM resellers WHERE id = ?");
    $stmt->execute([$reseller_id]);
    $reseller = $stmt->fetch(PDO::FETCH_ASSOC);
    return $reseller ? $reseller['name'] : 'Unknown Reseller';
}
?>