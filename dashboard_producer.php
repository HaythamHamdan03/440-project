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
$current_user = get_logged_in_user();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_product') {
        // Create new draft product
        $productId   = trim($_POST['productId'] ?? '');
        $productName = trim($_POST['productName'] ?? '');
        $price       = trim($_POST['price'] ?? '');
        $quantity    = trim($_POST['quantity'] ?? '');

        if ($productId !== '' && $productName !== '') {
            // Auto batch id for now
            $batchId = 'BATCH-' . $productId;
            add_product(
                $productId,
                $productName,
                $batchId,
                $current_user['username'],
                $price,
                $quantity,
                'draft'
            );
        }

    } elseif ($action === 'row_action') {
        $rowAction   = $_POST['row_action'] ?? '';
        $productId   = trim($_POST['productId'] ?? '');
        $productName = trim($_POST['productName'] ?? '');
        $price       = trim($_POST['price'] ?? '');
        $quantity    = trim($_POST['quantity'] ?? '');

        if ($productId !== '') {
            if ($rowAction === 'save') {
                update_product($productId, $current_user['username'], [
                    'name'       => $productName,
                    'price'      => $price,
                    'quantity'   => $quantity,
                    'status'     => 'saved',
                    'updated_at' => date('Y-m-d\TH:i:s'),
                ]);
            } elseif ($rowAction === 'edit') {
                // Make row editable again
                update_product($productId, $current_user['username'], [
                    'status'     => 'draft',
                    'updated_at' => date('Y-m-d\TH:i:s'),
                ]);
            } elseif ($rowAction === 'delete') {
                delete_product($productId, $current_user['username']);
            }
        }
    }
}

// Load products belonging to this producer
$products    = load_products();
$my_products = array_values(array_filter($products, function ($p) use ($current_user) {
    return isset($p['creator']) && $p['creator'] === $current_user['username'];
}));

$total_my_products = count($my_products);              // treat all as "approved" count for now
$last_product      = !empty($my_products) ? end($my_products) : null;
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
        <p class="page-subtitle">Register and manage your products before sending them to the blockchain</p>

        <!-- Summary Cards -->
        <div class="card-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_my_products; ?></div>
                <div class="stat-label">My Approved Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    <?php echo $last_product ? htmlspecialchars($last_product['productId']) : 'N/A'; ?>
                </div>
                <div class="stat-label">Last Product ID</div>
            </div>
        </div>

        <!-- Register Product Form -->
        <div class="card">
            <h2 class="card-title">Register New Product</h2>
            <p class="text-muted" style="margin-bottom: 16px; font-size: 0.9em;">
                This form only prepares a product entry. Actual blockchain registration will be
                done later via MetaMask when you click <strong>Approve</strong> in the table below.
            </p>
            <form method="post">
                <input type="hidden" name="action" value="create_product" />

                <div class="form-group">
                    <label for="productId">Product ID (Number)</label>
                    <input type="number" id="productId" name="productId" required placeholder="e.g., 1001">
                </div>
                
                <div class="form-group">
                    <label for="productName">Product Name</label>
                    <input type="text" id="productName" name="productName" required placeholder="e.g., Organic Coffee Beans">
                </div>

                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" required placeholder="e.g., 3.50">
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" step="1" required placeholder="e.g., 20">
                </div>
                
                <button type="submit" class="btn btn-primary">Register on Blockchain (Prepare)</button>
            </form>
        </div>

        <!-- My New Products Table -->
        <div class="card">
            <h2 class="card-title">My New Products</h2>
            <?php if (empty($my_products)): ?>
                <p class="text-muted">You have not registered any products yet.</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Status</th>
                                <th>Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // newest first
                            $rows = array_reverse($my_products);
                            foreach ($rows as $product):
                                $status = $product['status'] ?? 'draft';
                                $isDraft = ($status === 'draft');
                            ?>
                                <tr>
                                    <!-- Real product ID -->
                                    <td><?php echo htmlspecialchars($product['productId']); ?></td>

                                    <!-- Name -->
                                    <td>
                                        <form method="post" class="product-row-form">
                                            <input type="hidden" name="action" value="row_action">
                                            <input type="hidden" name="productId" value="<?php echo htmlspecialchars($product['productId']); ?>">
                                            <input
                                                type="text"
                                                name="productName"
                                                value="<?php echo htmlspecialchars($product['name']); ?>"
                                                <?php echo $isDraft ? '' : 'readonly'; ?>
                                                style="width: 100%; min-width: 150px;"
                                            />
                                    </td>

                                    <!-- Price -->
                                    <td>
                                            <input
                                                type="number"
                                                step="0.01"
                                                name="price"
                                                value="<?php echo htmlspecialchars($product['price']); ?>"
                                                <?php echo $isDraft ? '' : 'readonly'; ?>
                                                style="width: 100px;"
                                            />
                                    </td>

                                    <!-- Quantity -->
                                    <td>
                                            <input
                                                type="number"
                                                step="1"
                                                name="quantity"
                                                value="<?php echo htmlspecialchars($product['quantity']); ?>"
                                                <?php echo $isDraft ? '' : 'readonly'; ?>
                                                style="width: 70px;"
                                            />
                                    </td>

                                    <!-- Status -->
                                    <td>
                                            <span class="badge badge-pending">
                                                <?php echo htmlspecialchars($status); ?>
                                            </span>
                                    </td>

                                    <!-- Updated -->
                                    <td>
                                            <span class="text-muted" style="font-size: 0.85em;">
                                                <?php echo $product['updated_at'] ? htmlspecialchars($product['updated_at']) : 'N/A'; ?>
                                            </span>
                                    </td>

                                    <!-- Actions -->
                                    <td>
                                            <div style="display:flex; flex-direction:column; gap:4px;">
                                                <?php if ($isDraft): ?>
                                                    <!-- While draft: Save + Delete only -->
                                                    <button
                                                        type="submit"
                                                        name="row_action"
                                                        value="save"
                                                        class="btn btn-primary"
                                                        style="padding: 4px 12px;"
                                                    >
                                                        Save
                                                    </button>
                                                    <button
                                                        type="submit"
                                                        name="row_action"
                                                        value="delete"
                                                        class="btn btn-secondary"
                                                        style="padding: 4px 12px;"
                                                    >
                                                        Delete
                                                    </button>
                                                <?php else: ?>
                                                    <!-- After Save: Approve + Edit + Delete -->
                                                    <button
                                                        type="button"
                                                        class="btn btn-primary"
                                                        style="padding: 4px 12px;"
                                                        onclick="approveProductPlaceholder('<?php echo htmlspecialchars($product['productId']); ?>');"
                                                    >
                                                        Approve
                                                    </button>
                                                    <button
                                                        type="submit"
                                                        name="row_action"
                                                        value="edit"
                                                        class="btn btn-primary"
                                                        style="padding: 4px 12px;"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        type="submit"
                                                        name="row_action"
                                                        value="delete"
                                                        class="btn btn-secondary"
                                                        style="padding: 4px 12px;"
                                                    >
                                                        Delete
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="text-muted" style="margin-top: 12px; font-size: 0.85em;">
                    While a product is in <strong>draft</strong> status, its fields are editable and you can <em>Save</em> or
                    <em>Delete</em> it. After you <strong>Save</strong>, the row is locked for editing and you can
                    <strong>Approve</strong> (MetaMask later), <strong>Edit</strong>, or <strong>Delete</strong> it.
                </p>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include 'partials/footer.php'; ?>

<script>
    // Placeholder function for later integration with MetaMask / contract.js
    function approveProductPlaceholder(productId) {
        alert("Approve product " + productId + " – later this will call MetaMask and the smart contract.");
    }
</script>
