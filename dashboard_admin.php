<?php
/**
 * ============================================
 * ICS 440 - Supply Chain Transparency Tracking
 * Admin Dashboard (User Management Panel)
 * ============================================
 */

require_once 'config.php';
require_once 'api_client.php';
require_login();
require_role(['admin']);

$page_title = 'Admin Dashboard';
$logged_in_user = get_logged_in_user();

// Load data
$users = load_users();
$products = load_products();
$user_counts = get_user_counts_by_role();

$add_user_error = '';
$add_user_success = '';

// Handle new user submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username'] ?? '');
    $new_password = trim($_POST['password'] ?? '');
    $new_role     = trim($_POST['role'] ?? '');

    $valid_roles = ['admin', 'producer', 'supplier', 'consumer'];

    if ($new_username === '' || $new_password === '' || $new_role === '') {
        $add_user_error = 'All fields are required.';
    } elseif (!in_array($new_role, $valid_roles, true)) {
        $add_user_error = 'Invalid role selected.';
    } else {
        // check if username already exists
        $exists = false;
        foreach ($users as $u) {
            if (strcasecmp($u['username'], $new_username) === 0) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $add_user_error = 'Username already exists. Please choose another.';
        } else {
            // append to users.txt
            $line = $new_username . '|' . $new_password . '|' . $new_role . "\n";
            $file = 'users.txt';

            if (file_put_contents($file, $line, FILE_APPEND | LOCK_EX) !== false) {
                $add_user_success = 'User "' . htmlspecialchars($new_username) . '" added successfully.';
                // reload users and counts after adding
                $users = load_users();
                $user_counts = get_user_counts_by_role();
            } else {
                $add_user_error = 'Failed to save the new user. Please try again.';
            }
        }
    }
}
?>
<?php include 'partials/header.php'; ?>

<div class="dashboard-layout">
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <ul class="sidebar-nav">
            <li><a href="dashboard.php">Dashboard</a></li>

            <?php if ($logged_in_user['role'] === 'admin'): ?>
                <li><a href="dashboard_admin.php" class="active">Admin Panel</a></li>
            <?php endif; ?>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <h1 class="page-title">Admin Panel</h1>
        <p class="page-subtitle">Manage system users</p>

        <!-- Add New User -->
        <div class="card" style="margin-bottom: 24px;">
            <h2 class="card-title">Add New User</h2>

            <?php if ($add_user_error): ?>
                <div class="alert alert-error" style="margin-bottom: 15px; color: #ff6b6b;">
                    <?php echo htmlspecialchars($add_user_error); ?>
                </div>
            <?php endif; ?>

            <?php if ($add_user_success): ?>
                <div class="alert alert-success" style="margin-bottom: 15px; color: #4ade80;">
                    <?php echo $add_user_success; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="form-grid">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        placeholder="e.g., operator1"
                    />
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="text"
                        id="password"
                        name="password"
                        required
                        placeholder="Set a password"
                    />
                </div>

                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="">Select role</option>
                        <option value="admin">Admin</option>
                        <option value="producer">Producer</option>
                        <option value="supplier">Supplier</option>
                        <option value="consumer">Consumer</option>
                    </select>
                </div>

                <div class="form-group" style="margin-top: 10px;">
                    <button type="submit" class="btn btn-primary">
                        Create User
                    </button>
                </div>
            </form>
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
        </div>
    </main>
</div>

<?php include 'partials/footer.php'; ?>
