<?php
/**
 * ============================================
 * ICS 440 - Supply Chain Transparency Tracking
 * Admin Dashboard
 * ============================================
 */

require_once 'config.php';
require_login();
require_role(['admin']);

$page_title = 'Admin Dashboard';
$current_user = get_current_user();

// Load data
$users = load_users();
$products = load_products();
$user_counts = get_user_counts_by_role();
?>
<?php include 'partials/header.php'; ?>

<div class="dashboard-layout">
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <ul class="sidebar-nav">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="dashboard_admin.php" class="active">Admin Panel</a></li>
            <li><a href="dashboard_producer.php">Producer Panel</a></li>
            <li><a href="dashboard_supplier.php">Supplier Panel</a></li>
            <li><a href="dashboard_consumer.php">Consumer Panel</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <h1 class="page-title">Admin Dashboard</h1>
        <p class="page-subtitle">System overview and management</p>

        <!-- System Overview -->
        <div class="card">
            <h2 class="card-title">System Overview</h2>
            <div class="card-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo count($users); ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $user_counts['admin']; ?></div>
                    <div class="stat-label">Admins</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $user_counts['producer']; ?></div>
                    <div class="stat-label">Producers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $user_counts['supplier']; ?></div>
                    <div class="stat-label">Suppliers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $user_counts['consumer']; ?></div>
                    <div class="stat-label">Consumers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo count($products); ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
            </div>
        </div>

        <!-- User List -->
        <div class="card">
            <h2 class="card-title">User List</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted">No users found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td>
                                        <span class="user-role" style="font-size: 0.9em;">
                                            <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-muted" style="margin-top: 15px; font-size: 0.9em;">
                <strong>Note:</strong> User management (add/edit/delete) is not implemented in this version.
                Users are stored in users.txt file.
            </p>
        </div>

        <!-- Product List -->
        <div class="card">
            <h2 class="card-title">Product List</h2>
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
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No products registered yet</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['productId']); ?></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['batchId']); ?></td>
                                    <td><?php echo htmlspecialchars($product['creator']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-muted" style="margin-top: 15px; font-size: 0.9em;">
                <strong>Note:</strong> Products are stored in products.txt file. This is a simple off-chain log.
                The actual product data is stored on the blockchain via smart contract.
            </p>
        </div>
    </main>
</div>

<?php include 'partials/footer.php'; ?>

