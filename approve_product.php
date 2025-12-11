<?php
/**
 * Approve product on blockchain via backend API
 */

session_start();
require_once 'config.php';
require_once 'api_client.php';
require_login();
require_role(['producer', 'admin']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $productId = $input['productId'] ?? '';
    $name = $input['name'] ?? '';
    $description = $input['description'] ?? $name;
    $price = $input['price'] ?? '0';
    
    if (empty($productId) || empty($name)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    $privateKey = get_private_key();
    if (!$privateKey) {
        echo json_encode(['success' => false, 'error' => 'Private key not set. Please set up your wallet first.']);
        exit;
    }
    
    // Check if backend is available
    if (!api_check_health()) {
        echo json_encode(['success' => false, 'error' => 'Backend API is not available. Please start the backend server.']);
        exit;
    }
    
    // Register product on blockchain via backend API
    $result = api_register_product(
        $privateKey,
        $name,
        $description,
        $price,
        'Initial Location'
    );
    
    if ($result['success']) {
        // Update local product status
        $current_user = get_logged_in_user();
        update_product($productId, $current_user['username'], [
            'status' => 'approved',
            'updated_at' => date('Y-m-d\TH:i:s'),
            'tx_hash' => $result['data']['transactionHash'] ?? ''
        ]);
        
        echo json_encode([
            'success' => true,
            'transactionHash' => $result['data']['transactionHash'] ?? '',
            'productId' => $result['data']['productId'] ?? ''
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Failed to register product on blockchain'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

?>
