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
 * PRODUCT HELPERS
 * ============================================================
 *
 * products.txt line format (new format):
 *   productId|name|batchId|creator|price|quantity|status|updated_at
 *
 * Old lines with only 4 fields:
 *   productId|name|batchId|creator
 * are still supported and will get default values for new fields.
 */

/**
 * Load products from products.txt file
 * 
 * @return array Array of product arrays
 */
function load_products() {
    $products = [];
    $file = 'products.txt';
    
    if (!file_exists($file)) {
        return $products;
    }
    
    if (($handle = fopen($file, 'r')) !== false) {
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            
            $parts = explode('|', $line);
            if (count($parts) >= 4) {
                $product = [
                    'productId'  => trim($parts[0]),
                    'name'       => trim($parts[1]),
                    'batchId'    => trim($parts[2]),
                    'creator'    => trim($parts[3]),
                    'price'      => isset($parts[4]) ? trim($parts[4]) : '',
                    'quantity'   => isset($parts[5]) ? trim($parts[5]) : '',
                    // draft / saved / approved (for now we use draft/saved)
                    'status'     => isset($parts[6]) ? trim($parts[6]) : 'draft',
                    'updated_at' => isset($parts[7]) ? trim($parts[7]) : '',
                    // Extended fields for supplier tracking
                    'owner'      => isset($parts[8]) ? trim($parts[8]) : '',
                    'tx_hash'    => isset($parts[9]) ? trim($parts[9]) : '',
                ];
                $products[] = $product;
            }
        }
        fclose($handle);
    }
    
    return $products;
}

/**
 * Add a product to products.txt
 * 
 * @param string      $productId Product ID
 * @param string      $name      Product name
 * @param string      $batchId   Batch ID
 * @param string      $creator   Creator username
 * @param string|null $price
 * @param string|null $quantity
 * @param string      $status    draft/saved/approved
 * @param string|null $updated_at
 * @return bool True on success, false on failure
 */
function add_product($productId, $name, $batchId, $creator, $price = '', $quantity = '', $status = 'draft', $updated_at = null) {
    $file = 'products.txt';

    // Check if product with same ID and creator already exists
    $products = load_products();
    foreach ($products as $p) {
        if ($p['productId'] === $productId && $p['creator'] === $creator) {
            // Product already exists, don't add duplicate
            return false;
        }
    }

    if ($updated_at === null) {
        $updated_at = date('Y-m-d\TH:i:s');
    }

    $fields = [
        $productId,
        $name,
        $batchId,
        $creator,
        $price,
        $quantity,
        $status,
        $updated_at
    ];

    $line = implode('|', $fields) . "\n";
    
    // Append to file
    return file_put_contents($file, $line, FILE_APPEND | LOCK_EX) !== false;
}

/**
 * Update a product identified by (productId + creator)
 *
 * @param string $productId
 * @param string $creator
 * @param array  $newFields associative keys: name, price, quantity, status, updated_at, batchId
 * @return bool
 */
function update_product($productId, $creator, array $newFields) {
    $file = 'products.txt';
    $products = load_products();
    $updated = false;
    $lastMatchIndex = -1;

    // Find the last matching product (most recent one in case of duplicates)
    for ($i = count($products) - 1; $i >= 0; $i--) {
        if ($products[$i]['productId'] === $productId && $products[$i]['creator'] === $creator) {
            $lastMatchIndex = $i;
            break;
        }
    }

    // Update only the last match (most recent product)
    if ($lastMatchIndex >= 0) {
        foreach ($newFields as $k => $v) {
            if (array_key_exists($k, $products[$lastMatchIndex])) {
                $products[$lastMatchIndex][$k] = $v;
            }
        }
        $updated = true;
    }

    if (!$updated) {
        return false;
    }

    // Rewrite file with updated products
    $lines = [];
    foreach ($products as $p) {
        $lines[] = implode('|', [
            $p['productId'],
            $p['name'],
            $p['batchId'],
            $p['creator'],
            $p['price'],
            $p['quantity'],
            $p['status'],
            $p['updated_at'],
            isset($p['owner']) ? $p['owner'] : '',
            isset($p['tx_hash']) ? $p['tx_hash'] : '',
        ]);
    }

    return file_put_contents($file, implode("\n", $lines) . "\n", LOCK_EX) !== false;
}

/**
 * Delete a product by (productId + creator)
 *
 * @param string $productId
 * @param string $creator
 * @return bool
 */
function delete_product($productId, $creator) {
    $file = 'products.txt';
    $products = load_products();

    $newProducts = [];
    $deleted = false;

    foreach ($products as $p) {
        if ($p['productId'] === $productId && $p['creator'] === $creator) {
            $deleted = true;
            continue; // skip this product
        }
        $newProducts[] = $p;
    }

    if (!$deleted) {
        return false;
    }

    $lines = [];
    foreach ($newProducts as $p) {
        $lines[] = implode('|', [
            $p['productId'],
            $p['name'],
            $p['batchId'],
            $p['creator'],
            $p['price'],
            $p['quantity'],
            $p['status'],
            $p['updated_at'],
            isset($p['owner']) ? $p['owner'] : '',
            isset($p['tx_hash']) ? $p['tx_hash'] : '',
        ]);
    }

    $content = $lines ? implode("\n", $lines) . "\n" : '';
    return file_put_contents($file, $content, LOCK_EX) !== false;
}

/**
 * Purchase a product (transfer ownership to supplier)
 * 
 * @param string $productId Product ID to purchase
 * @param string $supplier Supplier username
 * @param int    $quantity Quantity to purchase
 * @return bool True on success, false on failure
 */
function purchase_product($productId, $supplier, $quantity) {
    $products = load_products();
    $updated = false;
    $lastMatchIndex = -1;

    // Find the last matching product (most recent one)
    for ($i = count($products) - 1; $i >= 0; $i--) {
        if ($products[$i]['productId'] === $productId) {
            $lastMatchIndex = $i;
            break;
        }
    }

    if ($lastMatchIndex < 0) {
        return false;
    }

    $product = &$products[$lastMatchIndex];
    $available_qty = intval($product['quantity'] ?? 0);
    
    // Check if enough quantity is available and product is not already owned
    if ($quantity > $available_qty || (!empty($product['owner']) && $product['owner'] !== $supplier)) {
        return false;
    }

    // If purchasing full quantity
    if ($quantity == $available_qty) {
        // Update product: transfer ownership, set status
        $product['owner'] = $supplier;
        $product['quantity'] = strval($quantity);
        $product['status'] = 'shipped';
        $product['updated_at'] = date('Y-m-d\TH:i:s');
        $product['tx_hash'] = '0x' . bin2hex(random_bytes(16)); // Mock transaction hash
    } else {
        // Partial purchase: create new entry for purchased quantity, reduce original
        $remaining_qty = $available_qty - $quantity;
        $product['quantity'] = strval($remaining_qty);
        
        // Create new product entry for purchased quantity
        $purchased_product = $product;
        $purchased_product['quantity'] = strval($quantity);
        $purchased_product['owner'] = $supplier;
        $purchased_product['status'] = 'shipped';
        $purchased_product['updated_at'] = date('Y-m-d\TH:i:s');
        $purchased_product['tx_hash'] = '0x' . bin2hex(random_bytes(16));
        $products[] = $purchased_product;
    }

    $updated = true;

    // Rewrite file with updated products
    $file = 'products.txt';
    $lines = [];
    foreach ($products as $p) {
        $lines[] = implode('|', [
            $p['productId'],
            $p['name'],
            $p['batchId'],
            $p['creator'],
            $p['price'],
            $p['quantity'],
            $p['status'],
            $p['updated_at'],
            isset($p['owner']) ? $p['owner'] : '',
            isset($p['tx_hash']) ? $p['tx_hash'] : '',
        ]);
    }

    return file_put_contents($file, implode("\n", $lines) . "\n", LOCK_EX) !== false;
}

/**
 * Purchase a product for consumer (transfer ownership from supplier to consumer)
 * 
 * @param string $productId Product ID to purchase
 * @param string $consumer Consumer username
 * @param int    $quantity Quantity to purchase
 * @return bool True on success, false on failure
 */
function purchase_product_consumer($productId, $consumer, $quantity) {
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
    
    // Check if enough quantity is available
    if ($quantity > $available_qty) {
        return false;
    }

    // If purchasing full quantity
    if ($quantity == $available_qty) {
        // Update product: transfer ownership to consumer, set status
        $product['owner'] = $consumer;
        $product['quantity'] = strval($quantity);
        $product['status'] = 'purchased';
        $product['updated_at'] = date('Y-m-d\TH:i:s');
        // Keep existing tx_hash or create new one
        if (empty($product['tx_hash'])) {
            $product['tx_hash'] = '0x' . bin2hex(random_bytes(16));
        }
    } else {
        // Partial purchase: create new entry for purchased quantity, reduce original
        $remaining_qty = $available_qty - $quantity;
        $product['quantity'] = strval($remaining_qty);
        
        // Create new product entry for purchased quantity
        $purchased_product = $product;
        $purchased_product['quantity'] = strval($quantity);
        $purchased_product['owner'] = $consumer;
        $purchased_product['status'] = 'purchased';
        $purchased_product['updated_at'] = date('Y-m-d\TH:i:s');
        if (empty($purchased_product['tx_hash'])) {
            $purchased_product['tx_hash'] = '0x' . bin2hex(random_bytes(16));
        }
        $products[] = $purchased_product;
    }

    // Rewrite file with updated products
    $file = 'products.txt';
    $lines = [];
    foreach ($products as $p) {
        $lines[] = implode('|', [
            $p['productId'],
            $p['name'],
            $p['batchId'],
            $p['creator'],
            $p['price'],
            $p['quantity'],
            $p['status'],
            $p['updated_at'],
            isset($p['owner']) ? $p['owner'] : '',
            isset($p['tx_hash']) ? $p['tx_hash'] : '',
        ]);
    }

    return file_put_contents($file, implode("\n", $lines) . "\n", LOCK_EX) !== false;
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
