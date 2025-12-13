<?php
/**
 * ============================================
 * ICS 440 - Supply Chain Transparency Tracking
 * Header Partial (Shared Navigation)
 * ============================================
 */

// Get current user info
$current_user = get_logged_in_user();
$username = $current_user['username'] ?? 'Guest';
$role = $current_user['role'] ?? 'guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?> - ICS 440 Supply Chain</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ethers/5.7.2/ethers.umd.min.js"></script>
    <script src="js/wallet.js"></script>
    <script src="js/blockchain.js"></script>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="top-nav">
        <div class="nav-left">
            <div class="nav-title">Supply Chain Transparency Tracking via Blockchain</div>
            <div class="nav-subtitle">ICS 440 – Term 251</div>
        </div>
        <div class="nav-right">
            <div class="user-info">
                <div class="user-role"><?php echo htmlspecialchars(ucfirst($role)); ?></div>
                <div style="color: var(--text-primary);"><?php echo htmlspecialchars($username); ?></div>
            </div>
            <div class="wallet-status" id="wallet-status">Not connected</div>
            <button id="connect-wallet-btn" onclick="connectWallet()" class="btn btn-secondary" style="padding: 8px 15px; font-size: 0.9em;">Connect Wallet</button>
            <button id="switch-wallet-btn" onclick="connectWallet(true)" class="btn btn-secondary" style="padding: 8px 15px; font-size: 0.9em; display: none; background: var(--primary);">Switch Account</button>
            <button id="disconnect-wallet-btn" onclick="disconnectWallet()" class="btn btn-secondary" style="padding: 8px 15px; font-size: 0.9em; display: none; background: var(--error);">Disconnect</button>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>
    </nav>

