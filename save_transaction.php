<?php
/**
 * Save transaction hash to product
 */

session_start();
require_once 'config.php';
require_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $productId = $input['productId'] ?? '';
    $txHash = $input['transactionHash'] ?? '';
    $blockchainProductId = $input['blockchainProductId'] ?? '';
    
    if (empty($productId) || empty($txHash)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    $current_user = get_logged_in_user();
    
    // Update product with transaction hash
    update_product($productId, $current_user['username'], [
        'status' => 'approved',
        'updated_at' => date('Y-m-d\TH:i:s'),
        'tx_hash' => $txHash
    ]);
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

?>
