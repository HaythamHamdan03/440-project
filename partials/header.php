<?php
/**
 * ============================================
 * ICS 440 - Supply Chain Transparency Tracking
 * Header Partial (Shared Navigation)
 * ============================================
 */

// Get current user info
$current_user = get_current_user();
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
            <button onclick="connectWallet()" class="btn btn-secondary" style="padding: 8px 15px; font-size: 0.9em;">Connect Wallet</button>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>
    </nav>

