<?php
// API Webhook untuk menerima notifikasi dari server pusat
header('Content-Type: application/json');

// Include config
require_once '../reseller/includes/config.php';

// Verify API key
if (!isset($_SERVER['HTTP_X_API_KEY']) || $_SERVER['HTTP_X_API_KEY'] !== API_KEY) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit();
}

// Process based on response type
if (isset($input['resp'])) {
    if ($input['resp'] === 'topup') {
        processTopupResponse($input);
    } elseif ($input['resp'] === 'cmd') {
        processCmdResponse($input);
    }
}

// Process topup response
function processTopupResponse($data) {
    global $pdo;
    
    // Find order by reffid
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE unique_code = ?");
    $stmt->execute([$data['reffid']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        // Update order status based on response
        $status = 'pending';
        if ($data['status_code'] == '0') {
            $status = 'success';
        } elseif ($data['status_code'] == '1') {
            $status = 'failed';
            
            // Refund balance if failed
            $refund_stmt = $pdo->prepare("UPDATE resellers SET balance = balance + ? WHERE id = ?");
            $refund_stmt->execute([$order['price'], $order['reseller_id']]);
        }
        
        // Update order
        $update_stmt = $pdo->prepare("UPDATE orders SET status = ?, api_response = ? WHERE id = ?");
        $update_stmt->execute([$status, json_encode($data), $order['id']]);
        
        echo json_encode(['status' => 'processed']);
    } else {
        echo json_encode(['error' => 'Order not found']);
    }
}

// Process command response
function processCmdResponse($data) {
    // For now, just log the response
    file_put_contents('../logs/api_cmd.log', date('Y-m-d H:i:s') . ' - ' . json_encode($data) . PHP_EOL, FILE_APPEND);
    echo json_encode(['status' => 'logged']);
}
?>