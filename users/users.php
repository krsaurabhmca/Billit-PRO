<?php
/**
 * ============================================================================
 * USER MANAGEMENT PAGE
 * ============================================================================
 * Purpose: Manage users and assign roles (RBAC)
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

// Require Admin Role
require_role('admin');

$page_title = "User & Role Management";
require_once '../includes/header.php';

// Fetch all users
$query = "SELECT * FROM users ORDER BY created_at DESC";
$users = db_fetch_all($connection, $query);
?>

<div class="page-header">
    <div>
        <h2 class="page-title">
            <span class="page-icon">👥</span>
            User & Role Management
        </h2>
        <p class="page-description">Manage system users and assign access roles.</p>
    </div>
    <div class="page-actions">
        <a href="add_user.php" class="btn btn-primary">
            <span class="btn-icon">➕</span> Add New User
        </a>
    </div>
</div>

<?php echo display_messages(); ?>

<div class="dashboard-grid">
    <!-- User List -->
    <div class="card" style="grid-column: span 2;">
        <div class="card-header">
            <h3 class="card-title">System Users</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 32px; height: 32px; background: var(--gray-100); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                            👤
                                        </div>
                                        <div>
                                            <strong><?php echo escape_html($user['full_name']); ?></strong><br>
                                            <span class="text-sm text-muted">@<?php echo escape_html($user['username']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo escape_html($user['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user['role'] === 'admin' ? 'purple' : ($user['role'] === 'manager' ? 'blue' : 'gray'); ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn-action btn-edit" title="Edit User & Role">
                                            ✏️
                                        </a>
                                        <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                            <a href="delete_user.php?id=<?php echo $user['user_id']; ?>" 
                                               class="btn-action btn-delete" 
                                               onclick="return confirmDelete('Are you sure? This action cannot be undone.');"
                                               title="Delete User">
                                                🗑️
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Role Definitions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">🛡️ Role Permissions</h3>
        </div>
        <div class="card-body">
            <div class="role-item">
                <div class="role-header">
                    <span class="badge badge-purple">Admin</span>
                    <strong>Full Access</strong>
                </div>
                <ul class="role-perms">
                    <li>✅ Manage Users & Roles</li>
                    <li>✅ Manage Settings</li>
                    <li>✅ Full Inventory Access</li>
                    <li>✅ Manage Billing & Finance</li>
                    <li>✅ View All Reports</li>
                </ul>
            </div>
            
            <div class="role-item">
                <div class="role-header">
                    <span class="badge badge-blue">Manager</span>
                    <strong>Operational Access</strong>
                </div>
                <ul class="role-perms">
                    <li>❌ Manage Users & Roles</li>
                    <li>❌ Company Settings</li>
                    <li>✅ Full Inventory Access</li>
                    <li>✅ Manage Billing</li>
                    <li>✅ View Reports</li>
                </ul>
            </div>
            
            <div class="role-item">
                <div class="role-header">
                    <span class="badge badge-gray">Staff</span>
                    <strong>Restricted Access</strong>
                </div>
                <ul class="role-perms">
                    <li>❌ Administrative Access</li>
                    <li>❌ View Finance Reports</li>
                    <li>✅ View Inventory</li>
                    <li>✅ Create Invoices</li>
                    <li>✅ Stock In/Out</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.role-item {
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--gray-200);
}
.role-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}
.role-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.role-perms {
    list-style: none;
    padding: 0;
    margin: 0;
    font-size: 13px;
    color: var(--gray-600);
}
.role-perms li {
    margin-bottom: 5px;
}
</style>

<?php require_once '../includes/footer.php'; ?>
