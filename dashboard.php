<?php
/**
 * ============================================
 * ICS 440 - Supply Chain Transparency Tracking
 * Main Dashboard (Landing Page)
 * ============================================
 */

require_once 'config.php';
require_once 'api_client.php';
require_login();

$page_title = 'Dashboard';
$logged_in_user = get_logged_in_user();

// Shared stats (used by admin for overview + quick stats)
$products = load_products();
$user_counts = get_user_counts_by_role();
?>
<?php include 'partials/header.php'; ?>

<div class="dashboard-layout">
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <ul class="sidebar-nav">
            <li><a href="dashboard.php" class="active">Dashboard</a></li>

            <?php if ($logged_in_user && $logged_in_user['role'] === 'producer'): ?>
                <li><a href="dashboard_producer.php">Producer Panel</a></li>
            <?php endif; ?>

            <?php if ($logged_in_user && $logged_in_user['role'] === 'supplier'): ?>
                <li><a href="dashboard_supplier.php">Supplier Panel</a></li>
            <?php endif; ?>

            <?php if ($logged_in_user && $logged_in_user['role'] === 'consumer'): ?>
                <li><a href="dashboard_consumer.php">Consumer Panel</a></li>
            <?php endif; ?>

            <?php if ($logged_in_user && $logged_in_user['role'] === 'admin'): ?>
                <li><a href="dashboard_admin.php">Admin Panel</a></li>
            <?php endif; ?>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <h1 class="page-title">
            Welcome, <?php echo htmlspecialchars($logged_in_user['username']); ?>!
        </h1>
        <p class="page-subtitle">Select a dashboard to get started</p>

        <?php if ($logged_in_user['role'] === 'admin'): ?>
            <!-- System Overview (admin only) -->
            <div class="card" style="margin-bottom: 24px;">
                <h2 class="card-title">System Overview</h2>
                <div class="card-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo array_sum($user_counts); ?></div>
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
        <?php endif; ?>


        <!-- Quick Stats -->
        <div class="card">
            <h2 class="card-title">Quick Statistics</h2>
            <div class="card-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo count($products); ?></div>
                    <div class="stat-label">Total Products</div>
                </div>

            </div>
        </div>
    </main>
</div>

<?php include 'partials/footer.php'; ?>
