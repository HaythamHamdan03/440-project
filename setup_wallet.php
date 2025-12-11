<?php
/**
 * ============================================
 * ICS 440 - Supply Chain Transparency Tracking
 * Wallet Setup Page
 * ============================================
 * 
 * This page allows users to set up their wallet private key
 * for blockchain operations
 */

require_once 'config.php';
require_login();

$page_title = 'Wallet Setup';
$current_user = get_logged_in_user();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'set_private_key') {
        $privateKey = trim($_POST['private_key'] ?? '');
        
        if (empty($privateKey)) {
            $error = 'Private key is required';
        } elseif (!preg_match('/^0x[a-fA-F0-9]{64}$/', $privateKey)) {
            $error = 'Invalid private key format. Must be 0x followed by 64 hex characters.';
        } else {
            // Verify the private key address matches MetaMask address if connected
            $walletAddress = get_wallet_address();
            if ($walletAddress) {
                $result = api_get_wallet_address($privateKey);
                if ($result['success'] && strtolower($result['data']['address']) === strtolower($walletAddress)) {
                    store_private_key($privateKey);
                    $success = 'Private key stored successfully!';
                } else {
                    $error = 'Private key address does not match your connected MetaMask address.';
                }
            } else {
                // Store anyway if no MetaMask connected
                store_private_key($privateKey);
                $success = 'Private key stored successfully!';
            }
        }
    } elseif ($action === 'generate_key') {
        // Generate a new private key (for demo purposes)
        // In production, use a proper cryptographic library
        $bytes = random_bytes(32);
        $privateKey = '0x' . bin2hex($bytes);
        store_private_key($privateKey);
        
        // Get address from backend
        $result = api_get_wallet_address($privateKey);
        if ($result['success']) {
            $_SESSION['wallet_address'] = $result['data']['address'];
            $success = 'New private key generated! Address: ' . $result['data']['address'];
        } else {
            $error = 'Failed to generate key: ' . ($result['error'] ?? 'Unknown error');
        }
    }
    
}

$hasPrivateKey = has_private_key();
$walletAddress = get_wallet_address();
?>
<?php include 'partials/header.php'; ?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <ul class="sidebar-nav">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="setup_wallet.php" class="active">Wallet Setup</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1 class="page-title">Wallet Setup</h1>
        <p class="page-subtitle">Configure your wallet for blockchain operations</p>

        <?php if ($error): ?>
            <div class="message message-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="message message-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card">
            <h2 class="card-title">MetaMask Connection</h2>
            <p class="text-muted">Connect your MetaMask wallet to verify your address.</p>
            <div style="margin: 15px 0;">
                <div class="wallet-status" id="wallet-status">Not connected</div>
                <button onclick="connectWallet()" class="btn btn-primary" style="margin-top: 10px;">Connect Wallet</button>
            </div>
            <?php if ($walletAddress): ?>
                <p class="text-muted">Connected Address: <code><?php echo htmlspecialchars($walletAddress); ?></code></p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2 class="card-title">Private Key Configuration</h2>
            <p class="text-muted">
                For blockchain operations, you need to provide a private key. 
                This key will be used to sign transactions on your behalf.
            </p>
            <p class="text-muted" style="color: var(--warning);">
                <strong>Warning:</strong> Never share your private key with anyone. 
                Store it securely.
            </p>

            <?php if ($hasPrivateKey): ?>
                <div class="message message-success">
                    ✓ Private key is configured
                </div>
                <form method="post" style="margin-top: 20px;">
                    <input type="hidden" name="action" value="clear_key">
                    <button type="submit" class="btn btn-secondary">Clear Private Key</button>
                </form>
            <?php else: ?>
                <form method="post" style="margin-top: 20px;">
                    <input type="hidden" name="action" value="set_private_key">
                    <div class="form-group">
                        <label for="private_key">Private Key</label>
                        <input 
                            type="text" 
                            id="private_key" 
                            name="private_key" 
                            placeholder="0x..."
                            required
                            pattern="^0x[a-fA-F0-9]{64}$"
                        />
                        <small class="text-muted">Enter your private key (0x followed by 64 hex characters)</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Private Key</button>
                </form>

                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                    <p class="text-muted">Don't have a private key?</p>
                    <form method="post">
                        <input type="hidden" name="action" value="generate_key">
                        <button type="submit" class="btn btn-secondary">Generate New Key</button>
                    </form>
                    <p class="text-muted" style="font-size: 0.85em; margin-top: 10px;">
                        This will generate a new private key. Make sure to save it securely!
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($hasPrivateKey && $walletAddress): ?>
    <div class="card">
        <h2 class="card-title">Verification Status</h2>
        <?php
        $privateKey = get_private_key();
        $result = api_get_wallet_address($privateKey);

        if ($result['success']):
            $keyAddress = $result['data']['address'];
            $matches = strtolower($keyAddress) === strtolower($walletAddress);
        ?>
            <p>Private Key Address: <code><?php echo htmlspecialchars($keyAddress); ?></code></p>
            <p>MetaMask Address: <code><?php echo htmlspecialchars($walletAddress); ?></code></p>
            <?php if ($matches): ?>
                <div class="message message-success">✓ Addresses match!</div>
            <?php else: ?>
                <div class="message message-warning">
                    ⚠ Addresses do not match. Please use the private key for your MetaMask address.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="message message-error">
                Error verifying address: <?php echo htmlspecialchars($result['error'] ?? 'Unknown error'); ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

    </main>
</div>

<?php include 'partials/footer.php'; ?>
