<?php
/**
 * ============================================================================
 * ACCESS LOG PAGE
 * ============================================================================
 * Purpose: View system access logs and user activities
 * Author: Billit Pro
 * Date: 2026-01-23
 * ============================================================================
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

// Require Admin Role
require_role('admin');

$page_title = "Access Logs";
require_once '../includes/header.php';

// Pagination
$page = isset($_GET['page']) && validate_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

// Fetch Logs
$total_query = "SELECT COUNT(*) as total FROM access_logs";
$total_result = db_fetch_one($connection, $total_query);
$total_logs = $total_result['total'];
$total_pages = ceil($total_logs / $limit);

$query = "SELECT l.*, u.full_name, u.username 
          FROM access_logs l 
          LEFT JOIN users u ON l.user_id = u.user_id 
          ORDER BY l.created_at DESC 
          LIMIT $limit OFFSET $offset";
$logs = db_fetch_all($connection, $query);
?>

<div class="page-header">
    <div>
        <h2 class="page-title">
            <span class="page-icon">🛡️</span>
            Access Logs
        </h2>
        <p class="page-description">Monitor user activity and system access.</p>
    </div>
    <div class="page-actions">
        <a href="../users/users.php" class="btn btn-secondary">
            <span class="btn-icon">👥</span> User Manager
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($logs) > 0): ?>
                        <?php foreach ($logs as $log): 
                            $username = $log['username'] ?? 'System';
                            $full_name = $log['full_name'] ?? 'System User';
                        ?>
                            <tr>
                                <td style="white-space: nowrap;"><?php echo format_datetime($log['created_at']); ?></td>
                                <td>
                                    <?php if ($log['user_id']): ?>
                                        <strong><?php echo escape_html($full_name); ?></strong><br>
                                        <small class="text-muted">@<?php echo escape_html($username); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted"><?php echo escape_html($username); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-gray"><?php echo escape_html($log['action']); ?></span>
                                </td>
                                <td>
                                    <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo escape_html($log['details']); ?>
                                    </div>
                                </td>
                                <td>
                                    <code style="background: var(--gray-100); padding: 2px 5px; border-radius: 4px;"><?php echo $log['ip_address']; ?></code>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div style="display: flex; justify-content: center; margin-top: 20px; gap: 5px;">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo ($page - 1); ?>" class="btn btn-sm btn-secondary">Previous</a>
            <?php endif; ?>
            
            <span style="padding: 5px 10px; align-self: center;">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo ($page + 1); ?>" class="btn btn-sm btn-secondary">Next</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
