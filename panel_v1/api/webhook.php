<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Verify API key
if (!isset($_SERVER['HTTP_X_API_KEY']) || $_SERVER['HTTP_X_API_KEY'] !== API_KEY) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Process webhook based on type
if (isset($input['resp'])) {
    if ($input['resp'] == 'topup') {
        // Update order status based on API response
        $reffid = $input['reffid'];
        $status_code = $input['status_code'];
        
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE reffid = ?");
        $stmt->execute([$reffid]);
        $order = $stmt->fetch();
        
        if ($order) {
            $status = 'pending';
            if ($status_code == '0') {
                $status = 'success';
            } elseif ($status_code == '1') {
                $status = 'failed';
                
                // Refund balance if order failed
                $stmt2 = $pdo->prepare("UPDATE resellers SET saldo = saldo + ? WHERE id = ?");
                $stmt2->execute([$order['harga'], $order['reseller_id']]);
            }
            
            // Update order
            $stmt3 = $pdo->prepare("UPDATE orders SET status = ?, response_text = ?, trxid = ? WHERE reffid = ?");
            $stmt3->execute([
                $status,
                json_encode($input),
                isset($input['trxid']) ? $input['trxid'] : null,
                $reffid
            ]);
            
            echo json_encode(['status' => 'updated']);
        } else {
            echo json_encode(['status' => 'order_not_found']);
        }
    }
} else {
    echo json_encode(['status' => 'ignored']);
}
?>