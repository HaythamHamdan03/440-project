<?php
/**
 * ============================================
 * ICS 440 - Supply Chain Transparency Tracking
 * Login Page
 * ============================================
 */

require_once 'config.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit();
}

$error_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $user = authenticate($username, $password);
        
        if ($user) {
            // Login successful - set session
            $_SESSION['user'] = [
                'username' => $user['username'],
                'role' => $user['role']
            ];
            
            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header('Location: dashboard_admin.php');
                    break;
                case 'producer':
                    header('Location: dashboard_producer.php');
                    break;
                case 'supplier':
                    header('Location: dashboard_supplier.php');
                    break;
                case 'consumer':
                    header('Location: dashboard_consumer.php');
                    break;
                default:
                    header('Location: dashboard.php');
            }
            exit();
        } else {
            $error_message = 'Invalid username or password';
        }
    } else {
        $error_message = 'Please enter both username and password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ICS 440 Supply Chain Tracking</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1 class="login-title">Supply Chain Transparency</h1>
            <p class="login-subtitle">ICS 440 – Cryptography and Blockchain Applications | Term 251</p>
            
            <?php if ($error_message): ?>
                <div class="message message-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <p class="text-muted" style="font-size: 0.9em; text-align: center;">
                    <strong>Test Accounts:</strong><br>
                    admin / admin123<br>
                    producer1 / prod123<br>
                    supplier1 / supp123<br>
                    consumer1 / cons123
                </p>
            </div>
        </div>
    </div>
</body>
</html>

