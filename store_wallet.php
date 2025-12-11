<?php
/**
 * Store wallet address in session
 */

session_start();
require_once 'config.php';
require_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $address = $input['address'] ?? '';
    
    // Allow empty address for disconnect
    if (empty($address)) {
        unset($_SESSION['wallet_address']);
        echo json_encode(['success' => true, 'address' => '']);
        exit;
    }
    
    if (!preg_match('/^0x[a-fA-F0-9]{40}$/', $address)) {
        echo json_encode(['success' => false, 'error' => 'Invalid address']);
        exit;
    }
    
    $_SESSION['wallet_address'] = $address;
    
    echo json_encode(['success' => true, 'address' => $address]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

?>
