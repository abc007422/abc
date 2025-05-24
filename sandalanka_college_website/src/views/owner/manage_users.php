<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure config is available
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    define('APP_URL', '/'); 
    define('MAX_OWNERS', 4); // Fallback
    error_log("Config file not found for manage_users.php (owner)");
}

// Access Control: Check if user is logged in and is an owner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    $_SESSION['error_message'] = "You must be logged in as an owner to view this page.";
    header("Location: " . APP_URL . "/index.php?action=login_admin");
    exit;
}

$pageTitle = "Manage All Users";
ob_start();

$all_users = isset($_SESSION['all_users_list']) ? $_SESSION['all_users_list'] : [];
if (isset($_SESSION['all_users_list'])) unset($_SESSION['all_users_list']);

$current_owners_count = isset($_SESSION['current_owners_count']) ? $_SESSION['current_owners_count'] : 0;
if (isset($_SESSION['current_owners_count'])) unset($_SESSION['current_owners_count']);


$error_message_display = isset($_SESSION['error_message_user_mgmt']) ? $_SESSION['error_message_user_mgmt'] : null;
if (isset($_SESSION['error_message_user_mgmt'])) unset($_SESSION['error_message_user_mgmt']);

$success_message_display = isset($_SESSION['success_message_user_mgmt']) ? $_SESSION['success_message_user_mgmt'] : null;
if (isset($_SESSION['success_message_user_mgmt'])) unset($_SESSION['success_message_user_mgmt']);
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Manage All Users</h2>
        <a href="<?php echo APP_URL; ?>/index.php?action=owner_manage_users_load" class="btn btn-sm btn-info">Refresh User List</a>
    </div>
    <p><a href="<?php echo APP_URL; ?>/index.php?action=owner_dashboard_view" class="btn btn-secondary btn-sm mb-3">Back to Owner Dashboard</a></p>

    <?php if ($success_message_display): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($success_message_display); ?>
        </div>
    <?php endif; ?>
    <?php if ($error_message_display): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message_display); ?>
        </div>
    <?php endif; ?>

    <div class="alert alert-info">
        Current number of Owners: <strong><?php echo $current_owners_count; ?> / <?php echo MAX_OWNERS; ?></strong>
    </div>

    <?php if (!empty($all_users)): ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Index Number</th>
                    <th>Registered At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_users as $user): ?>
                    <tr class="<?php if ($user['id'] == $_SESSION['user_id']) echo 'table-info'; ?>">
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                        <td><?php echo htmlspecialchars($user['index_number'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($user['created_at']))); ?></td>
                        <td>
                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                <em class="text-muted">(This is you)</em>
                            <?php else: ?>
                                <form action="<?php echo APP_URL; ?>/index.php?action=owner_change_user_role" method="POST" class="d-inline-flex align-items-center">
                                    <input type="hidden" name="user_id_to_change" value="<?php echo $user['id']; ?>">
                                    <select name="new_role" class="form-select form-select-sm me-2" style="width: auto;">
                                        <option value="student" <?php echo ($user['role'] === 'student') ? 'selected' : ''; ?>>Student</option>
                                        <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                        <option value="owner" 
                                            <?php echo ($user['role'] === 'owner') ? 'selected' : ''; ?>
                                            <?php if ($user['role'] !== 'owner' && $current_owners_count >= MAX_OWNERS) echo 'disabled'; ?>
                                            <?php if ($user['role'] === 'owner' && $current_owners_count <= 1) echo 'disabled title="Cannot demote the last owner"';?>
                                        >Owner</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary"
                                            <?php 
                                            if ($user['role'] !== 'owner' && $new_role_is_owner = true && $current_owners_count >= MAX_OWNERS) { // Simplified logic for button disabling
                                                // This logic is complex for the button directly, better handled by select option disabling
                                            }
                                            if ($user['role'] === 'owner' && $current_owners_count <= 1 && $user['id'] != $_SESSION['user_id'] /* Trying to demote last owner who is not you */) {
                                                // This should be handled by the select option being disabled
                                            }
                                            ?>
                                    >Set Role</button>
                                </form>
                                <?php if ($user['role'] === 'student' && $user['id']): ?>
                                    <a href="<?php echo APP_URL; ?>/index.php?action=admin_view_student_details&user_id=<?php echo $user['id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary ms-1">Details</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">No users found.</div>
    <?php endif; ?>
</div>

<?php
$viewContent = ob_get_clean();
require_once __DIR__ . '/../layouts/main_layout.php'; 
?>
