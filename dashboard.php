<?php
/**
 * ============================================
 * ICS 440 - Supply Chain Transparency Tracking
 * Main Dashboard (Landing Page)
 * ============================================
 */

require_once 'config.php';
require_login();

$page_title = 'Dashboard';
$current_user = get_current_user();
?>
<?php include 'partials/header.php'; ?>

<div class="dashboard-layout">
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <ul class="sidebar-nav">
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <?php if ($current_user['role'] === 'producer'): ?>
                <li><a href="dashboard_producer.php">Producer Panel</a></li>
            <?php endif; ?>
            <?php if ($current_user['role'] === 'supplier'): ?>
                <li><a href="dashboard_supplier.php">Supplier Panel</a></li>
            <?php endif; ?>
            <?php if ($current_user['role'] === 'consumer'): ?>
                <li><a href="dashboard_consumer.php">Consumer Panel</a></li>
            <?php endif; ?>
            <?php if ($current_user['role'] === 'admin'): ?>
                <li><a href="dashboard_admin.php">Admin Panel</a></li>
            <?php endif; ?>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <h1 class="page-title">Welcome, <?php echo htmlspecialchars($current_user['username']); ?>!</h1>
        <p class="page-subtitle">Select a dashboard to get started</p>

        <div class="card-grid">
            <!-- Producer Dashboard Card -->
            <?php if ($current_user['role'] === 'producer' || $current_user['role'] === 'admin'): ?>
            <div class="card stat-card">
                <h3 style="color: var(--accent-primary); margin-bottom: 15px;">Producer Dashboard</h3>
                <p class="text-muted" style="margin-bottom: 20px;">Register new products on the blockchain</p>
                <a href="dashboard_producer.php" class="btn btn-primary">Go to Producer Panel</a>
            </div>
            <?php endif; ?>

            <!-- Supplier Dashboard Card -->
            <?php if ($current_user['role'] === 'supplier' || $current_user['role'] === 'admin'): ?>
            <div class="card stat-card">
                <h3 style="color: var(--accent-primary); margin-bottom: 15px;">Supplier Dashboard</h3>
                <p class="text-muted" style="margin-bottom: 20px;">Transfer products and update status</p>
                <a href="dashboard_supplier.php" class="btn btn-primary">Go to Supplier Panel</a>
            </div>
            <?php endif; ?>

            <!-- Consumer Dashboard Card -->
            <?php if ($current_user['role'] === 'consumer' || $current_user['role'] === 'admin'): ?>
            <div class="card stat-card">
                <h3 style="color: var(--accent-primary); margin-bottom: 15px;">Consumer Dashboard</h3>
                <p class="text-muted" style="margin-bottom: 20px;">Verify products and view history</p>
                <a href="dashboard_consumer.php" class="btn btn-primary">Go to Consumer Panel</a>
            </div>
            <?php endif; ?>

            <!-- Admin Dashboard Card -->
            <?php if ($current_user['role'] === 'admin'): ?>
            <div class="card stat-card">
                <h3 style="color: var(--accent-primary); margin-bottom: 15px;">Admin Dashboard</h3>
                <p class="text-muted" style="margin-bottom: 20px;">System overview and management</p>
                <a href="dashboard_admin.php" class="btn btn-primary">Go to Admin Panel</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quick Stats -->
        <div class="card">
            <h2 class="card-title">Quick Statistics</h2>
            <div class="card-grid">
                <?php
                $products = load_products();
                $user_counts = get_user_counts_by_role();
                ?>
                <div class="stat-card">
                    <div class="stat-value"><?php echo count($products); ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo array_sum($user_counts); ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include 'partials/footer.php'; ?>

