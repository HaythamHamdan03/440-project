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
$current_user = get_current_user();
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
        <h1 class="page-title">Consumer Dashboard</h1>
        <p class="page-subtitle">Verify product authenticity and view complete history</p>

        <!-- Verify Product Authenticity -->
        <div class="card">
            <h2 class="card-title">Verify Product Authenticity</h2>
            <form id="verifyForm" onsubmit="event.preventDefault(); verifyProduct();">
                <div class="form-group">
                    <label for="verifyProductId">Product ID</label>
                    <input type="number" id="verifyProductId" name="verifyProductId" required placeholder="e.g., 1001">
                </div>
                <button type="submit" class="btn btn-primary">Check on Blockchain</button>
            </form>
            
            <div id="verify-result" style="margin-top: 20px;"></div>
            <div id="status-message" class="message" style="display: none;"></div>
        </div>

        <!-- Product History -->
        <div class="card">
            <h2 class="card-title">Product History</h2>
            <form id="historyForm" onsubmit="event.preventDefault(); loadHistoryForProduct(document.getElementById('historyProductId').value, 'historyTableBody');">
                <div class="form-group">
                    <label for="historyProductId">Product ID</label>
                    <input type="number" id="historyProductId" name="historyProductId" required placeholder="e.g., 1001">
                </div>
                <button type="submit" class="btn btn-primary">Get Product History</button>
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
                            <td colspan="4" class="text-center text-muted">Enter a Product ID and click "Get Product History" to view traceability information</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- QR Code Placeholder (Future Work) -->
        <div class="card">
            <h2 class="card-title">Future Work: QR Code Scan</h2>
            <p class="text-muted">QR scanner can be integrated here for easy product verification (not required now).</p>
            <button class="btn btn-disabled" disabled>QR Scanner (Coming Soon)</button>
            <p style="margin-top: 15px; font-size: 0.9em; color: var(--text-secondary);">
                <strong>Note:</strong> This is a placeholder for optional bonus feature. QR code scanning would allow 
                consumers to quickly scan a product's QR code to automatically retrieve its blockchain history.
            </p>
        </div>
    </main>
</div>

<script>
/**
 * Verify product authenticity
 */
async function verifyProduct() {
    const productId = document.getElementById('verifyProductId').value;
    const resultDiv = document.getElementById('verify-result');
    
    if (!productId || isNaN(productId)) {
        showMessage('error', 'Please enter a valid Product ID');
        return;
    }
    
    // First, try to get history (if product exists, it has history)
    try {
        showMessage('info', 'Checking product on blockchain...');
        
        // Load history to verify product exists
        await loadHistoryForProduct(productId, 'historyTableBody');
        
        // Check if verifyProduct function is available
        const verifyResult = await verifyProductOnChain(productId);
        
        if (verifyResult === true) {
            resultDiv.innerHTML = '<div class="message message-success">✓ Product verified and authentic on blockchain!</div>';
        } else if (verifyResult === false) {
            resultDiv.innerHTML = '<div class="message message-warning">⚠ Product found but verification failed</div>';
        } else {
            // verifyProduct function not available, but history exists
            resultDiv.innerHTML = '<div class="message message-success">✓ Product found on blockchain (history available above)</div>';
        }
    } catch (error) {
        resultDiv.innerHTML = '<div class="message message-error">✗ Product not found or error occurred</div>';
    }
}
</script>

<?php include 'partials/footer.php'; ?>

