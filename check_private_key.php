<?php
/**
 * Check if private key is available in session
 */

session_start();
require_once 'config.php';
require_login();

header('Content-Type: application/json');

echo json_encode([
    'hasPrivateKey' => has_private_key()
]);

?>
