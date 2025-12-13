<?php
/**
 * ============================================
 * ICS 440 - Supply Chain Transparency Tracking
 * Consumer Dashboard
 * ============================================
 */

require_once 'config.php';
require_login();
require_role(['consumer', 'admin']);

$page_title = 'Consumer Dashboard';
$current_user = get_logged_in_user();
$username = $current_user['username'] ?? 'Guest';

// Handle POST actions
$status_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'purchase_product') {
        $productId = trim($_POST['productId'] ?? '');
        $quantity = intval($_POST['quantity'] ?? 0);
        $txHash = trim($_POST['tx_hash'] ?? '');
        
        if ($productId !== '' && $quantity > 0) {
            // Update local storage with transaction hash
            if (purchase_product_consumer($productId, $username, $quantity, $txHash)) {
                $status_message = '<div class="message-success" style="display: block; margin: 15px 0;">Product Purchased Successfully!</div>';
            } else {
                $status_message = '<div class="message-error" style="display: block; margin: 15px 0;">Failed to purchase product. Please try again.</div>';
            }
        }
    }
}

// Load products
$all_products = load_products();

// Filter available products (owned by suppliers, status = 'shipped', not owned by current consumer)
$available_products = array_filter($all_products, function($p) use ($username) {
    $is_shipped = isset($p['status']) && $p['status'] === 'shipped';
    $has_owner = !empty($p['owner']);
    $not_owned_by_me = empty($p['owner']) || $p['owner'] !== $username;
    return $is_shipped && $has_owner && $not_owned_by_me;
});

// Filter consumer's purchased products
$my_products = array_filter($all_products, function($p) use ($username) {
    return isset($p['owner']) && $p['owner'] === $username && isset($p['status']) && $p['status'] === 'purchased';
});

// Search functionality for "Available Products"
$search_available = $_GET['search_available'] ?? '';
if ($search_available !== '') {
    $search_lower = strtolower($search_available);
    $available_products = array_filter($available_products, function($p) use ($search_lower) {
        return strpos(strtolower($p['name']), $search_lower) !== false ||
               strpos(strtolower($p['creator']), $search_lower) !== false ||
               strpos(strtolower($p['productId']), $search_lower) !== false;
    });
}

// Search functionality for "Your Products"
$search_query = $_GET['search'] ?? '';
if ($search_query !== '') {
    $search_lower = strtolower($search_query);
    $my_products = array_filter($my_products, function($p) use ($search_lower) {
        return strpos(strtolower($p['productId']), $search_lower) !== false ||
               (isset($p['txHash']) && strpos(strtolower($p['txHash']), $search_lower) !== false) ||
               strpos(strtolower($p['name']), $search_lower) !== false;
    });
}

// Generate transaction links (for display)
function get_transaction_links($product) {
    $txHash = isset($product['txHash']) ? trim($product['txHash']) : '';
    if (empty($txHash) || $txHash === 'Pending') {
        return ['<span style="color: #666;">Pending</span>'];
    }
    
    $links = [];
    $link_style = 'color: #667eea; text-decoration: underline; cursor: pointer; display: block; margin-bottom: 4px;';
    $links[] = '<a href="#" onclick="viewTransaction(\'' . htmlspecialchars($txHash) . '\'); return false;" style="' . $link_style . '">View Tx</a>';
    
    return $links;
}
?>
<?php include 'partials/header.php'; ?>

<div class="dashboard-layout">
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <ul class="sidebar-nav">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="dashboard_consumer.php" class="active">Consumer Panel</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Welcome Section -->
        <div style="margin-bottom: 25px;">
            <h1 class="page-title" style="margin-bottom: 10px;">consumer</h1>
            <p class="page-subtitle" style="margin-bottom: 15px;">
                Welcome, <?php echo htmlspecialchars($username); ?>. View your purchased products and verify authenticity. On-chain actions record in Sepolia.
            </p>
            <?php echo $status_message; ?>
        </div>

        <!-- Available Products Section -->
        <div class="card" style="margin-bottom: 25px;">
            <h2 class="card-title">Available Products</h2>
            
            <!-- Search Bar for Available Products -->
            <form method="get" style="display: flex; gap: 10px; align-items: flex-end; margin-bottom: 15px;">
                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                    <input 
                        type="text" 
                        name="search_available" 
                        placeholder="Search by product or producer..." 
                        value="<?php echo htmlspecialchars($search_available); ?>"
                        style="width: 100%; padding: 12px; background: var(--bg-primary); border: 2px solid var(--border-color); border-radius: 6px; color: var(--text-primary); font-size: 1em;"
                    />
                </div>
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px 24px;">
                    Search
                </button>
            </form>
            <p class="text-muted" style="margin-bottom: 15px; font-size: 0.9em;">
                Search available products from suppliers and purchase your preferred quantity.
            </p>
            
            <?php if (empty($available_products)): ?>
                <p class="text-muted">No products available for purchase.</p>
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
                                                onclick="purchaseProductAsConsumer('<?php echo htmlspecialchars($product['productId']); ?>', '<?php echo htmlspecialchars($product['name']); ?>', '<?php echo $price; ?>', '<?php echo htmlspecialchars($product['blockchainProductId'] ?? $product['txHash'] ?? ''); ?>')"
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

        <!-- Your Products Section -->
        <div class="card" style="margin-bottom: 25px;">
            <h2 class="card-title">Your Products</h2>
            
            <!-- Search Bar -->
            <form method="get" style="display: flex; gap: 10px; align-items: flex-end; margin-bottom: 15px;">
                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Search by productID or Transaction..." 
                        value="<?php echo htmlspecialchars($search_query); ?>"
                        style="width: 100%; padding: 12px; background: var(--bg-primary); border: 2px solid var(--border-color); border-radius: 6px; color: var(--text-primary); font-size: 1em;"
                    />
                </div>
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px 24px;">
                    Search
                </button>
            </form>
            <p class="text-muted" style="margin-bottom: 15px; font-size: 0.9em;">
                Search your purchased products by product ID or transaction hash.
            </p>
            
            <?php if (!empty($status_message)): ?>
                <?php echo $status_message; ?>
            <?php endif; ?>
            
            <!-- Your Products Table -->
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
                                $updated = isset($product['updatedAt']) ? $product['updatedAt'] : 'N/A';
                                $transaction_links = get_transaction_links($product);
                            ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($product['productId']); ?></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo $price; ?></td>
                                    <td><?php echo $qty; ?></td>
                                    <td>
                                        <span class="badge badge-success">purchased</span>
                                    </td>
                                    <td style="font-size: 0.85em; color: var(--text-secondary);">
                                        <?php echo htmlspecialchars($updated); ?>
                                    </td>
                                    <td style="font-size: 0.85em;">
                                        <div style="display: flex; flex-direction: column;">
                                            <?php foreach ($transaction_links as $link): ?>
                                                <?php echo $link; ?>
                                            <?php endforeach; ?>
                                        </div>
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
     * Purchase product as consumer using MetaMask
     */
    async function purchaseProductAsConsumer(productId, productName, price, blockchainProductId) {
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
    
    /**
     * View transaction details
     */
    function viewTransaction(txHash) {
        window.open('https://sepolia.etherscan.io/tx/' + txHash, '_blank');
    }
</script>

<?php include 'partials/footer.php'; ?>
