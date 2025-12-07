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
                    'role' => trim($parts[2])
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
function get_current_user() {
    return $_SESSION['user'] ?? null;
}

/**
 * Load products from products.txt file
 * Format: productId|name|batchId|creatorUsername (one per line)
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
                $products[] = [
                    'productId' => trim($parts[0]),
                    'name' => trim($parts[1]),
                    'batchId' => trim($parts[2]),
                    'creator' => trim($parts[3])
                ];
            }
        }
        fclose($handle);
    }
    
    return $products;
}

/**
 * Add a product to products.txt
 * 
 * @param string $productId Product ID
 * @param string $name Product name
 * @param string $batchId Batch ID
 * @param string $creator Creator username
 * @return bool True on success, false on failure
 */
function add_product($productId, $name, $batchId, $creator) {
    $file = 'products.txt';
    $line = $productId . '|' . $name . '|' . $batchId . '|' . $creator . "\n";
    
    // Append to file
    return file_put_contents($file, $line, FILE_APPEND | LOCK_EX) !== false;
}

/**
 * Get user count by role
 * 
 * @return array Associative array with role as key and count as value
 */
function get_user_counts_by_role() {
    $users = load_users();
    $counts = [
        'admin' => 0,
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

