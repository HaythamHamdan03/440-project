<?php
/**
 * ============================================
 * ICS 440 - Supply Chain Transparency Tracking
 * Backend API Client
 * ============================================
 * 
 * This file handles all communication with the Node.js backend API
 */

// Backend API base URL
define('API_BASE_URL', 'http://localhost:3000/api');

/**
 * Make HTTP request to backend API
 * 
 * @param string $method HTTP method (GET, POST, PUT, DELETE)
 * @param string $endpoint API endpoint (e.g., '/products')
 * @param array $data Request body data
 * @return array Response data or false on error
 */
function api_request($method, $endpoint, $data = null) {
    $url = API_BASE_URL . $endpoint;
    
    $ch = curl_init($url);
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data !== null && in_array($method, ['POST', 'PUT'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        error_log("API Request Error: " . $error);
        return ['success' => false, 'error' => 'Connection error: ' . $error];
    }
    
    $decoded = json_decode($response, true);
    
    if ($http_code >= 200 && $http_code < 300) {
        return $decoded ?: ['success' => true, 'data' => $response];
    } else {
        return [
            'success' => false,
            'error' => $decoded['error'] ?? 'API request failed',
            'http_code' => $http_code
        ];
    }
}

/**
 * Register a user on the blockchain
 * 
 * @param string $privateKey User's private key
 * @param int $role Role (1=Producer, 2=Supplier, 3=Consumer)
 * @param string $name User's name
 * @return array|false
 */
function api_register_user($privateKey, $role, $name) {
    return api_request('POST', '/users/register', [
        'privateKey' => $privateKey,
        'role' => $role,
        'name' => $name
    ]);
}

/**
 * Get user information by address
 * 
 * @param string $address Ethereum address
 * @return array|false
 */
function api_get_user($address) {
    return api_request('GET', '/users/' . urlencode($address));
}

/**
 * Get all products
 * 
 * @return array|false
 */
function api_get_all_products() {
    return api_request('GET', '/products');
}

/**
 * Get product details
 * 
 * @param string $productId Product ID (bytes32 hash)
 * @return array|false
 */
function api_get_product($productId) {
    return api_request('GET', '/products/' . urlencode($productId));
}

/**
 * Register a new product
 * 
 * @param string $privateKey Producer's private key
 * @param string $name Product name
 * @param string $description Product description
 * @param string $price Price in ETH
 * @param string $location Initial location
 * @return array|false
 */
function api_register_product($privateKey, $name, $description, $price, $location) {
    return api_request('POST', '/products', [
        'privateKey' => $privateKey,
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'location' => $location
    ]);
}

/**
 * Transfer a product
 * 
 * @param string $productId Product ID
 * @param string $privateKey Current owner's private key
 * @param string $toAddress Recipient address
 * @param string $location New location
 * @return array|false
 */
function api_transfer_product($productId, $privateKey, $toAddress, $location) {
    return api_request('PUT', '/products/' . urlencode($productId) . '/transfer', [
        'privateKey' => $privateKey,
        'toAddress' => $toAddress,
        'location' => $location
    ]);
}

/**
 * Purchase a product with payment
 * 
 * @param string $productId Product ID
 * @param string $privateKey Buyer's private key
 * @param string $location Delivery location
 * @param string $paymentAmount Payment amount in ETH
 * @return array|false
 */
function api_purchase_product($productId, $privateKey, $location, $paymentAmount) {
    return api_request('PUT', '/products/' . urlencode($productId) . '/purchase', [
        'privateKey' => $privateKey,
        'location' => $location,
        'paymentAmount' => $paymentAmount
    ]);
}

/**
 * Get product history
 * 
 * @param string $productId Product ID
 * @return array|false
 */
function api_get_product_history($productId) {
    return api_request('GET', '/products/' . urlencode($productId) . '/history');
}

/**
 * Verify product authenticity
 * 
 * @param string $productId Product ID
 * @param string $name Product name
 * @param string $description Product description
 * @param string $price Price in ETH
 * @param string $producer Producer address
 * @return array|false
 */
function api_verify_product($productId, $name, $description, $price, $producer) {
    return api_request('POST', '/products/' . urlencode($productId) . '/verify', [
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'producer' => $producer
    ]);
}

/**
 * Get wallet address from private key
 * 
 * @param string $privateKey Private key
 * @return array|false
 */
function api_get_wallet_address($privateKey) {
    return api_request('POST', '/wallet/address', [
        'privateKey' => $privateKey
    ]);
}

/**
 * Get user's products
 * 
 * @param string $address User's Ethereum address
 * @return array|false
 */
function api_get_user_products($address) {
    return api_request('GET', '/users/' . urlencode($address) . '/products');
}

/**
 * Get user's ETH balance
 * 
 * @param string $address User's Ethereum address
 * @return array|false
 */
function api_get_balance($address) {
    return api_request('GET', '/users/' . urlencode($address) . '/balance');
}

/**
 * Check if backend API is available
 * 
 * @return bool
 */
function api_check_health() {
    $ch = curl_init('http://localhost:3000/health');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $http_code === 200;
}

?>
