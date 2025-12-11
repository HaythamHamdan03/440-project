<?php
/**
 * ============================================
 * ICS 440 - Supply Chain Transparency Tracking
 * Supplier Dashboard
 * ============================================
 */

require_once 'config.php';
require_once 'api_client.php';
require_login();
require_role(['supplier', 'admin']);

$page_title = 'Supplier Dashboard';
$current_user = get_logged_in_user();
$username = $current_user['username'] ?? 'Guest';

// Handle POST actions
$status_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'purchase_product') {
        $productId = trim($_POST['productId'] ?? '');
        $quantity = intval($_POST['quantity'] ?? 0);
        $tx_hash = trim($_POST['tx_hash'] ?? '');
        
        if ($productId !== '' && $quantity > 0) {
            // Update local storage with transaction hash if provided
            if (purchase_product($productId, $username, $quantity)) {
                if ($tx_hash) {
                    // Update with transaction hash
                    $products = load_products();
                    foreach ($products as &$p) {
                        if ($p['productId'] === $productId && $p['owner'] === $username) {
                            $p['tx_hash'] = $tx_hash;
                            break;
                        }
                    }
                    // Save updated products
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
                    file_put_contents($file, implode("\n", $lines) . "\n", LOCK_EX);
                }
                $status_message = '<div class="message-success" style="display: block; margin: 15px 0;">Product Shipped.</div>';
            } else {
                $status_message = '<div class="message-error" style="display: block; margin: 15px 0;">Failed to purchase product. Please try again.</div>';
            }
        }
    }
}

// Load products
$all_products = load_products();

// Filter approved products (available for purchase - not already owned)
$available_products = array_filter($all_products, function($p) {
    $is_approved = isset($p['status']) && ($p['status'] === 'saved' || $p['status'] === 'approved');
    $not_owned = empty($p['owner']) || !isset($p['owner']);
    return $is_approved && $not_owned;
});

// Filter supplier's purchased products
$my_products = array_filter($all_products, function($p) use ($username) {
    return isset($p['owner']) && $p['owner'] === $username;
});

// Search functionality
$search_query = $_GET['search'] ?? '';
if ($search_query !== '') {
    $search_lower = strtolower($search_query);
    $available_products = array_filter($available_products, function($p) use ($search_lower) {
        return strpos(strtolower($p['name']), $search_lower) !== false ||
               strpos(strtolower($p['creator']), $search_lower) !== false ||
               strpos(strtolower($p['productId']), $search_lower) !== false;
    });
}
?>
<?php include 'partials/header.php'; ?>

<div class="dashboard-layout">
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <ul class="sidebar-nav">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="dashboard_supplier.php" class="active">Supplier Panel</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Welcome Section -->
        <div style="margin-bottom: 25px;">
            <h1 class="page-title" style="margin-bottom: 10px;">supplier</h1>
            <p class="page-subtitle" style="margin-bottom: 15px;">
                Welcome, <?php echo htmlspecialchars($username); ?>. View approved products, create shipments. On-chain actions record in Sepolia.
            </p>
            <?php echo $status_message; ?>
        </div>

        <!-- Search Bar -->
        <div class="card" style="margin-bottom: 25px;">
            <form method="get" style="display: flex; gap: 10px; align-items: flex-end;">
                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search by product or producer..." 
                        value="<?php echo htmlspecialchars($search_query); ?>"
                        style="width: 100%; padding: 12px; background: var(--bg-primary); border: 2px solid var(--border-color); border-radius: 6px; color: var(--text-primary); font-size: 1em;"
                    />
                </div>
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px 24px;">
                    Search
                </button>
            </form>
            <p class="text-muted" style="margin-top: 10px; font-size: 0.9em;">
                Search approved products and purchase your preferred quantity.
            </p>
        </div>

        <!-- Available Products Table -->
        <div class="card" style="margin-bottom: 25px;">
            <h2 class="card-title">Available Products</h2>
            <?php if (empty($available_products)): ?>
                <p class="text-muted">No approved products available.</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Producer</th>
                                <th>Price</th>
                                <th>Available</th>
                                <th>Purchase</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($available_products as $product): 
                                $available_qty = isset($product['quantity']) ? intval($product['quantity']) : 0;
                                $price = isset($product['price']) ? number_format(floatval($product['price']), 2) : '0.00';
                            ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($product['productId']); ?></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['creator']); ?></td>
                                    <td><?php echo $price; ?></td>
                                    <td><?php echo $available_qty; ?></td>
                                    <td>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <input 
                                                type="number" 
                                                id="qty_<?php echo htmlspecialchars($product['productId']); ?>"
                                                value="<?php echo $available_qty; ?>" 
                                                min="1" 
                                                max="<?php echo $available_qty; ?>"
                                                style="width: 80px; padding: 6px; background: var(--bg-primary); border: 2px solid var(--border-color); border-radius: 4px; color: var(--text-primary);"
                                            />
                                            <button 
                                                type="button"
                                                onclick="purchaseProductAsSupplier('<?php echo htmlspecialchars($product['productId']); ?>', '<?php echo htmlspecialchars($product['name']); ?>', '<?php echo $price; ?>', '<?php echo htmlspecialchars($product['tx_hash']); ?>')"
                                                class="btn btn-primary"
                                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 6px 16px; font-size: 0.9em;"
                                            >
                                                Approve
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Your Products Table -->
        <div class="card">
            <h2 class="card-title">Your Products</h2>
            <?php if (empty($my_products)): ?>
                <p class="text-muted">You have not purchased any products yet.</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Status</th>
                                <th>Updated</th>
                                <th>Transaction</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_products as $product): 
                                $price = isset($product['price']) ? number_format(floatval($product['price']), 2) : '0.00';
                                $qty = isset($product['quantity']) ? intval($product['quantity']) : 0;
                                $status = isset($product['status']) ? $product['status'] : 'pending';
                                $updated = isset($product['updated_at']) ? $product['updated_at'] : 'N/A';
                                $tx_hash = isset($product['tx_hash']) ? $product['tx_hash'] : 'Pending';
                            ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($product['productId']); ?></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo $price; ?></td>
                                    <td><?php echo $qty; ?></td>
                                    <td>
                                        <span class="badge badge-pending"><?php echo htmlspecialchars($status); ?></span>
                                    </td>
                                    <td style="font-size: 0.85em; color: var(--text-secondary);">
                                        <?php echo htmlspecialchars($updated); ?>
                                    </td>
                                    <td style="font-size: 0.85em; font-family: monospace; color: var(--text-secondary);">
                                        <?php echo htmlspecialchars($tx_hash); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
    /**
     * Purchase product as supplier using MetaMask
     */
    async function purchaseProductAsSupplier(productId, productName, price, blockchainProductId) {
        // Check if wallet is connected
        if (!isWalletConnected()) {
            alert('Please connect your MetaMask wallet first!');
            return;
        }
        
        // Initialize blockchain if not already done
        if (!contract) {
            const initialized = await initBlockchain();
            if (!initialized || !contract) {
                alert('Failed to initialize blockchain connection. Please make sure MetaMask is connected and CONTRACT_ADDRESS is set in js/blockchain.js');
                button.disabled = false;
                button.textContent = originalText;
                return;
            }
        }
        
        // Get quantity
        const quantityInput = document.getElementById('qty_' + productId);
        const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
        
        // Show loading
        const button = event.target;
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = 'Processing...';
        
        try {
            // Convert productId to bytes32 if we have blockchain productId
            let productIdBytes32 = blockchainProductId;
            if (!productIdBytes32 || productIdBytes32 === '') {
                // Generate bytes32 from productId
                productIdBytes32 = ethers.utils.keccak256(ethers.utils.toUtf8Bytes(productId.toString()));
            } else if (!productIdBytes32.startsWith('0x') || productIdBytes32.length !== 66) {
                // If it's not a valid bytes32, convert it
                productIdBytes32 = ethers.utils.keccak256(ethers.utils.toUtf8Bytes(productId.toString()));
            }
            
            // Purchase via blockchain
            const result = await purchaseProductOnBlockchain(productIdBytes32, price);
            
            if (result && result.success) {
                // Save to PHP
                await savePurchaseToPHP(productId, quantity, result.transactionHash);
                
                alert('Product purchased on blockchain!\n\nTransaction: ' + result.transactionHash + '\n\nView on Etherscan: https://sepolia.etherscan.io/tx/' + result.transactionHash);
                
                // Reload page
                window.location.reload();
            } else {
                alert('Error: Failed to purchase product');
                button.disabled = false;
                button.textContent = originalText;
            }
        } catch (error) {
            console.error('Error purchasing product:', error);
            let errorMsg = 'Error: ';
            if (error.code === 4001) {
                errorMsg += 'Transaction rejected by user';
            } else if (error.message) {
                errorMsg += error.message;
            } else {
                errorMsg += 'Failed to purchase product';
            }
            alert(errorMsg);
            button.disabled = false;
            button.textContent = originalText;
        }
    }
    
    /**
     * Save purchase to PHP
     */
    async function savePurchaseToPHP(productId, quantity, txHash) {
        try {
            const formData = new FormData();
            formData.append('action', 'purchase_product');
            formData.append('productId', productId);
            formData.append('quantity', quantity);
            formData.append('tx_hash', txHash);
            
            await fetch(window.location.href, {
                method: 'POST',
                body: formData
            });
        } catch (error) {
            console.error('Error saving purchase:', error);
        }
    }
</script>

<?php include 'partials/footer.php'; ?>
