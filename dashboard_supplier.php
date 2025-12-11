<?php
/**
 * ============================================
 * ICS 440 - Supply Chain Transparency Tracking
 * Supplier Dashboard
 * ============================================
 */

require_once 'config.php';
require_login();
require_role(['supplier', 'admin']);

$page_title = 'Supplier Dashboard';
$current_user = get_logged_in_user();

// Load products for display
$products = load_products();
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
        <h1 class="page-title">Supplier Dashboard</h1>
        <p class="page-subtitle">Transfer products and update status on the blockchain</p>

        <!-- Transfer Product Form -->
        <div class="card">
            <h2 class="card-title">Transfer Product</h2>
            <form id="transferProductForm" onsubmit="event.preventDefault(); transferProductFromForm(this);">
                <div class="form-group">
                    <label for="productId">Product ID (Number)</label>
                    <input type="number" id="productId" name="productId" required placeholder="e.g., 1001">
                </div>
                
                <div class="form-group">
                    <label for="receiverAddress">Receiver Address (Ethereum)</label>
                    <input type="text" id="receiverAddress" name="receiverAddress" required placeholder="0x..." class="monospace">
                </div>
                
                <div class="form-group">
                    <label for="newStatus">New Status</label>
                    <input type="text" id="newStatus" name="newStatus" required placeholder="e.g., In Transit, Arrived at Warehouse, Delivered to Store">
                </div>
                
                <button type="submit" class="btn btn-primary">Transfer on Blockchain</button>
            </form>
            
            <div id="status-message" class="message" style="display: none;"></div>
        </div>

        <!-- Quick Product Lookup -->
        <div class="card">
            <h2 class="card-title">Quick Product Lookup</h2>
            <form id="lookupForm" onsubmit="event.preventDefault(); loadHistoryForProduct(document.getElementById('lookupProductId').value, 'historyTableBody');">
                <div class="form-group">
                    <label for="lookupProductId">Product ID</label>
                    <input type="number" id="lookupProductId" name="lookupProductId" required placeholder="e.g., 1001">
                </div>
                <button type="submit" class="btn btn-primary">View History</button>
            </form>
            
            <div class="table-container" style="margin-top: 20px;">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Owner Address</th>
                            <th>Status</th>
                            <th>Date/Time</th>
                        </tr>
                    </thead>
                    <tbody id="historyTableBody">
                        <tr>
                            <td colspan="4" class="text-center text-muted">Enter a Product ID and click "View History" to see product history</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sample Products List -->
        <div class="card">
            <h2 class="card-title">Available Products</h2>
            <?php if (empty($products)): ?>
                <p class="text-muted">No products available.</p>
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
                            <?php foreach ($products as $product): ?>
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

