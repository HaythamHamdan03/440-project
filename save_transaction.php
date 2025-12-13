<?php
/**
 * Save transaction hash to product (JSON-based storage)
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
    
    // Debug logging
    error_log("save_transaction.php - productId: $productId, txHash: $txHash, blockchainProductId: $blockchainProductId");
    
    if (empty($productId) || empty($txHash)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields', 'debug' => ['productId' => $productId, 'txHash' => $txHash]]);
        exit;
    }
    
    $current_user = get_logged_in_user();
    error_log("save_transaction.php - user: " . $current_user['username']);
    
    // Update product with transaction hash and blockchain product ID
    $result = update_product($productId, $current_user['username'], [
        'status' => 'approved',
        'txHash' => $txHash,
        'blockchainProductId' => $blockchainProductId
    ]);
    
    error_log("save_transaction.php - update_product result: " . ($result ? 'true' : 'false'));
    
    if ($result) {
        echo json_encode(['success' => true, 'productId' => $productId, 'status' => 'approved']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update product - product not found or permission denied', 'productId' => $productId, 'user' => $current_user['username']]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
