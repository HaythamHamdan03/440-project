<?php
/**
 * ============================================
 * ICS 440 - Supply Chain Transparency Tracking
 * Configuration and Authentication Functions
 * ============================================
 * 
 * This file handles:
 * - PHP session management
 * - User authentication from users.txt
 * - Role-based access control
 * - Product storage helpers (products.txt)
 */

// Start PHP session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Load users from users.txt file
 * Format: username|password|role (one per line)
 * 
 * @return array Array of user arrays with keys: username, password, role
 */
function load_users() {
    $users = [];
    $file = 'users.txt';
    
    // Check if file exists
    if (!file_exists($file)) {
        // Create default users file if it doesn't exist
        $default_users = [
            'admin|admin123|admin',
            'producer1|prod123|producer',
            'supplier1|supp123|supplier',
            'consumer1|cons123|consumer'
        ];
        file_put_contents($file, implode("\n", $default_users));
    }
    
    // Read file line by line
    if (($handle = fopen($file, 'r')) !== false) {
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            // Skip empty lines and comments (lines starting with #)
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            
            // Split by pipe character
            $parts = explode('|', $line);
            if (count($parts) >= 3) {
                $users[] = [
                    'username' => trim($parts[0]),
                    'password' => trim($parts[1]),
                    'role'     => trim($parts[2])
                ];
            }
        }
        fclose($handle);
    }
    
    return $users;
}

/**
 * Authenticate a user by username and password
 * 
 * @param string $username The username to check
 * @param string $password The password to check
 * @return array|null User array if authenticated, null otherwise
 */
function authenticate($username, $password) {
    $users = load_users();
    
    foreach ($users as $user) {
        // Simple password comparison (in production, use password_hash/password_verify)
        if ($user['username'] === $username && $user['password'] === $password) {
            return $user;
        }
    }
    
    return null;
}

/**
 * Check if user is logged in, redirect to login if not
 */
function require_login() {
    if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
        // User is not logged in, redirect to login page
        header('Location: login.php');
        exit();
    }
}

/**
 * Check if current user has one of the required roles
 * 
 * @param array $allowed_roles Array of role strings (e.g., ['admin', 'producer'])
 */
function require_role($allowed_roles) {
    // First ensure user is logged in
    require_login();
    
    // Get current user's role
    $user_role = $_SESSION['user']['role'] ?? null;
    
    // Check if role is in allowed list
    if (!in_array($user_role, $allowed_roles)) {
        // User doesn't have required role, redirect to dashboard
        header('Location: dashboard.php');
        exit();
    }
}

/**
 * Get current logged-in user info
 * 
 * @return array|null User array or null if not logged in
 */
function get_logged_in_user() {
    return $_SESSION['user'] ?? null;
}

/**
 * Get wallet address from session
 * 
 * @return string|null Wallet address or null
 */
function get_wallet_address() {
    return $_SESSION['wallet_address'] ?? null;
}

/**
 * Store private key in session (encrypted)
 * Note: In production, use proper encryption
 * 
 * @param string $privateKey Private key
 * @return bool
 */
function store_private_key($privateKey) {
    if (empty($privateKey) || !preg_match('/^0x[a-fA-F0-9]{64}$/', $privateKey)) {
        return false;
    }
    
    // In production, encrypt this
    $_SESSION['private_key'] = $privateKey;
    return true;
}

/**
 * Get private key from session
 * 
 * @return string|null Private key or null
 */
function get_private_key() {
    return $_SESSION['private_key'] ?? null;
}

/**
 * Clear private key from session
 */
function clear_private_key() {
    unset($_SESSION['private_key']);
}

/**
 * Check if user has private key stored
 * 
 * @return bool
 */
function has_private_key() {
    return !empty($_SESSION['private_key']);
}

/* ============================================================
 * PRODUCT HELPERS (JSON-based storage)
 * ============================================================
 *
 * products.json structure:
 * {
 *   "products": [
 *     {
 *       "id": "unique-id",
 *       "productId": "1001",
 *       "name": "Product Name",
 *       "description": "Description",
 *       "batchId": "BATCH-1001",
 *       "creator": "producer1",
 *       "price": "0.01",
 *       "quantity": "10",
 *       "status": "draft|saved|approved|shipped|purchased|delivered",
 *       "owner": "current owner username",
 *       "txHash": "0x...",
 *       "blockchainProductId": "bytes32...",
 *       "createdAt": "2024-01-01T00:00:00",
 *       "updatedAt": "2024-01-01T00:00:00"
 *     }
 *   ],
 *   "lastUpdated": "2024-01-01T00:00:00"
 * }
 */

define('PRODUCTS_FILE', __DIR__ . '/products.json');

/**
 * Load products from products.json file
 * 
 * @return array Array of product arrays
 */
function load_products() {
    if (!file_exists(PRODUCTS_FILE)) {
        // Create default empty file
        $data = ['products' => [], 'lastUpdated' => date('c')];
        file_put_contents(PRODUCTS_FILE, json_encode($data, JSON_PRETTY_PRINT));
        return [];
    }
    
    $content = file_get_contents(PRODUCTS_FILE);
    $data = json_decode($content, true);
    
    if (!$data || !isset($data['products'])) {
        return [];
    }
    
    return $data['products'];
}

/**
 * Save all products to products.json file
 * 
 * @param array $products Array of product arrays
 * @return bool True on success
 */
function save_products($products) {
    $data = [
        'products' => array_values($products), // Re-index array
        'lastUpdated' => date('c')
    ];
    
    return file_put_contents(PRODUCTS_FILE, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX) !== false;
}

/**
 * Add a product to products.json
 * 
 * @param string $productId Product ID
 * @param string $name Product name
 * @param string $batchId Batch ID
 * @param string $creator Creator username
 * @param string $price Price
 * @param string $quantity Quantity
 * @param string $status Status (draft/saved/approved/shipped/purchased)
 * @param string|null $description Product description
 * @return bool True on success, false on failure
 */
function add_product($productId, $name, $batchId, $creator, $price = '', $quantity = '', $status = 'draft', $description = null) {
    $products = load_products();
    
    // Check if product with same ID and creator already exists
    foreach ($products as $p) {
        if ($p['productId'] === $productId && $p['creator'] === $creator) {
            return false; // Product already exists
        }
    }
    
    $now = date('c');
    $product = [
        'id' => uniqid('prod_', true),
        'productId' => $productId,
        'name' => $name,
        'description' => $description ?? $name,
        'batchId' => $batchId,
        'creator' => $creator,
        'price' => $price,
        'quantity' => $quantity,
        'status' => $status,
        'owner' => '',
        'txHash' => '',
        'blockchainProductId' => '',
        'createdAt' => $now,
        'updatedAt' => $now
    ];
    
    $products[] = $product;
    return save_products($products);
}

/**
 * Update a product identified by (productId + creator)
 *
 * @param string $productId
 * @param string $creator
 * @param array $newFields Associative array of fields to update
 * @return bool
 */
function update_product($productId, $creator, array $newFields) {
    $products = load_products();
    $updated = false;
    
    // Find the product and update it
    for ($i = count($products) - 1; $i >= 0; $i--) {
        if ($products[$i]['productId'] === $productId && $products[$i]['creator'] === $creator) {
            foreach ($newFields as $key => $value) {
                $products[$i][$key] = $value;
            }
            $products[$i]['updatedAt'] = date('c');
            $updated = true;
            break;
        }
    }
    
    if (!$updated) {
        return false;
    }
    
    return save_products($products);
}

/**
 * Delete a product by (productId + creator)
 *
 * @param string $productId
 * @param string $creator
 * @return bool
 */
function delete_product($productId, $creator) {
    $products = load_products();
    $newProducts = [];
    $deleted = false;
    
    foreach ($products as $p) {
        if ($p['productId'] === $productId && $p['creator'] === $creator) {
            $deleted = true;
            continue; // Skip this product
        }
        $newProducts[] = $p;
    }
    
    if (!$deleted) {
        return false;
    }
    
    return save_products($newProducts);
}

/**
 * Get a product by productId
 *
 * @param string $productId
 * @return array|null Product array or null if not found
 */
function get_product($productId) {
    $products = load_products();
    
    foreach ($products as $p) {
        if ($p['productId'] === $productId) {
            return $p;
        }
    }
    
    return null;
}

/**
 * Get a product by blockchain product ID (bytes32)
 *
 * @param string $blockchainProductId
 * @return array|null Product array or null if not found
 */
function get_product_by_blockchain_id($blockchainProductId) {
    $products = load_products();
    
    foreach ($products as $p) {
        if (isset($p['blockchainProductId']) && $p['blockchainProductId'] === $blockchainProductId) {
            return $p;
        }
    }
    
    return null;
}

/**
 * Update product by any unique identifier
 *
 * @param string $productId
 * @param array $newFields
 * @return bool
 */
function update_product_by_id($productId, array $newFields) {
    $products = load_products();
    $updated = false;
    
    for ($i = 0; $i < count($products); $i++) {
        if ($products[$i]['productId'] === $productId) {
            foreach ($newFields as $key => $value) {
                $products[$i][$key] = $value;
            }
            $products[$i]['updatedAt'] = date('c');
            $updated = true;
            break;
        }
    }
    
    if (!$updated) {
        return false;
    }
    
    return save_products($products);
}

/**
 * Purchase a product (transfer ownership to supplier)
 * 
 * @param string $productId Product ID to purchase
 * @param string $supplier Supplier username
 * @param int $quantity Quantity to purchase
 * @param string $txHash Transaction hash from blockchain
 * @return bool True on success, false on failure
 */
function purchase_product($productId, $supplier, $quantity, $txHash = '') {
    $products = load_products();
    $lastMatchIndex = -1;
    
    // Find the last matching product that's not already owned
    for ($i = count($products) - 1; $i >= 0; $i--) {
        if ($products[$i]['productId'] === $productId && empty($products[$i]['owner'])) {
            $lastMatchIndex = $i;
            break;
        }
    }
    
    if ($lastMatchIndex < 0) {
        return false;
    }
    
    $product = &$products[$lastMatchIndex];
    $available_qty = intval($product['quantity'] ?? 0);
    
    // Check if enough quantity is available
    if ($quantity > $available_qty) {
        return false;
    }
    
    // If purchasing full quantity
    if ($quantity == $available_qty) {
        $product['owner'] = $supplier;
        $product['status'] = 'shipped';
        $product['updatedAt'] = date('c');
        if ($txHash) {
            $product['txHash'] = $txHash;
        }
    } else {
        // Partial purchase: reduce original and create new entry
        $remaining_qty = $available_qty - $quantity;
        $product['quantity'] = strval($remaining_qty);
        
        // Create new product entry for purchased quantity
        $purchased_product = $product;
        $purchased_product['id'] = uniqid('prod_', true);
        $purchased_product['quantity'] = strval($quantity);
        $purchased_product['owner'] = $supplier;
        $purchased_product['status'] = 'shipped';
        $purchased_product['updatedAt'] = date('c');
        if ($txHash) {
            $purchased_product['txHash'] = $txHash;
        }
        $products[] = $purchased_product;
    }
    
    return save_products($products);
}

/**
 * Purchase a product for consumer (transfer ownership from supplier to consumer)
 * 
 * @param string $productId Product ID to purchase
 * @param string $consumer Consumer username
 * @param int $quantity Quantity to purchase
 * @param string $txHash Transaction hash from blockchain
 * @return bool True on success, false on failure
 */
function purchase_product_consumer($productId, $consumer, $quantity, $txHash = '') {
    $products = load_products();
    $lastMatchIndex = -1;
    
    // Find products owned by suppliers (available for consumer purchase)
    for ($i = count($products) - 1; $i >= 0; $i--) {
        if ($products[$i]['productId'] === $productId && 
            !empty($products[$i]['owner']) && 
            $products[$i]['status'] === 'shipped') {
            $lastMatchIndex = $i;
            break;
        }
    }
    
    if ($lastMatchIndex < 0) {
        return false;
    }
    
    $product = &$products[$lastMatchIndex];
    $available_qty = intval($product['quantity'] ?? 0);
    
    if ($quantity > $available_qty) {
        return false;
    }
    
    // If purchasing full quantity
    if ($quantity == $available_qty) {
        $product['owner'] = $consumer;
        $product['status'] = 'purchased';
        $product['updatedAt'] = date('c');
        if ($txHash) {
            $product['txHash'] = $txHash;
        }
    } else {
        // Partial purchase
        $remaining_qty = $available_qty - $quantity;
        $product['quantity'] = strval($remaining_qty);
        
        $purchased_product = $product;
        $purchased_product['id'] = uniqid('prod_', true);
        $purchased_product['quantity'] = strval($quantity);
        $purchased_product['owner'] = $consumer;
        $purchased_product['status'] = 'purchased';
        $purchased_product['updatedAt'] = date('c');
        if ($txHash) {
            $purchased_product['txHash'] = $txHash;
        }
        $products[] = $purchased_product;
    }
    
    return save_products($products);
}

/**
 * Get user count by role
 * 
 * @return array Associative array with role as key and count as value
 */
function get_user_counts_by_role() {
    $users = load_users();
    $counts = [
        'admin'    => 0,
        'producer' => 0,
        'supplier' => 0,
        'consumer' => 0
    ];
    
    foreach ($users as $user) {
        $role = $user['role'];
        if (isset($counts[$role])) {
            $counts[$role]++;
        }
    }
    
    return $counts;
}

?>
