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
        ]);
    }

    $content = $lines ? implode("\n", $lines) . "\n" : '';
    return file_put_contents($file, $content, LOCK_EX) !== false;
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
