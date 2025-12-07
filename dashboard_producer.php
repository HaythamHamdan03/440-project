<?php
/**
 * ============================================
 * ICS 440 - Supply Chain Transparency Tracking
 * Producer Dashboard
 * ============================================
 */

require_once 'config.php';
require_login();
require_role(['producer', 'admin']);

$page_title = 'Producer Dashboard';
$current_user = get_current_user();

// Handle product registration (save to products.txt)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_product') {
    $productId = $_POST['productId'] ?? '';
    $productName = $_POST['productName'] ?? '';
    $batchId = $_POST['batchId'] ?? '';
    
    if (!empty($productId) && !empty($productName) && !empty($batchId)) {
        if (add_product($productId, $productName, $batchId, $current_user['username'])) {
            // Product saved successfully
        }
    }
}

// Load products
$products = load_products();
$total_products = count($products);
$last_product = !empty($products) ? end($products) : null;
?>
<?php include 'partials/header.php'; ?>

<div class="dashboard-layout">
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <ul class="sidebar-nav">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="dashboard_producer.php" class="active">Producer Panel</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <h1 class="page-title">Producer Dashboard</h1>
        <p class="page-subtitle">Register new products on the blockchain</p>

        <!-- Summary Cards -->
        <div class="card-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_products; ?></div>
                <div class="stat-label">Total Products Registered</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $last_product ? htmlspecialchars($last_product['productId']) : 'N/A'; ?></div>
                <div class="stat-label">Last Product ID</div>
            </div>
        </div>

        <!-- Register Product Form -->
        <div class="card">
            <h2 class="card-title">Register New Product</h2>
            <form id="registerProductForm" onsubmit="event.preventDefault(); registerProductFromForm(this);">
                <div class="form-group">
                    <label for="productId">Product ID (Number)</label>
                    <input type="number" id="productId" name="productId" required placeholder="e.g., 1001">
                </div>
                
                <div class="form-group">
                    <label for="productName">Product Name</label>
                    <input type="text" id="productName" name="productName" required placeholder="e.g., Organic Coffee Beans">
                </div>
                
                <div class="form-group">
                    <label for="productDescription">Description</label>
                    <textarea id="productDescription" name="productDescription" rows="3" placeholder="Product description..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="batchId">Batch ID</label>
                    <input type="text" id="batchId" name="batchId" required placeholder="e.g., BATCH-2024-001">
                </div>
                
                <button type="submit" class="btn btn-primary">Register on Blockchain</button>
            </form>
            
            <div id="status-message" class="message" style="display: none;"></div>
        </div>

        <!-- Latest Products Table -->
        <div class="card">
            <h2 class="card-title">Latest Registered Products</h2>
            <?php if (empty($products)): ?>
                <p class="text-muted">No products registered yet.</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Name</th>
                                <th>Batch ID</th>
                                <th>Creator</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Show last 10 products (most recent first)
                            $recent_products = array_slice(array_reverse($products), 0, 10);
                            foreach ($recent_products as $product): 
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['productId']); ?></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['batchId']); ?></td>
                                    <td><?php echo htmlspecialchars($product['creator']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include 'partials/footer.php'; ?>

