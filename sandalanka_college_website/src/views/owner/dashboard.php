<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure config is available for APP_URL and SITE_NAME
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    define('APP_URL', '/'); 
    define('SITE_NAME', 'Sandalanka Central College'); // Fallback
    error_log("Config file not found for owner_dashboard.php");
}

// Access Control: Check if user is logged in and is an owner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    $_SESSION['error_message'] = "You must be logged in as an owner to view this page.";
    header("Location: " . APP_URL . "/index.php?action=login_admin"); // Or a general login page
    exit;
}

$pageTitle = "Owner Dashboard - " . SITE_NAME;
ob_start();

$success_message_display = isset($_SESSION['success_message_owner']) ? $_SESSION['success_message_owner'] : null;
if (isset($_SESSION['success_message_owner'])) unset($_SESSION['success_message_owner']);

$error_message_display = isset($_SESSION['error_message_owner']) ? $_SESSION['error_message_owner'] : null;
if (isset($_SESSION['error_message_owner'])) unset($_SESSION['error_message_owner']);

// Placeholder for fetching stats - e.g., from owner_controller
$total_users = $_SESSION['owner_stats']['total_users'] ?? 'N/A';
$total_students = $_SESSION['owner_stats']['total_students'] ?? 'N/A';
$total_admins = $_SESSION['owner_stats']['total_admins'] ?? 'N/A';
$total_owners = $_SESSION['owner_stats']['total_owners'] ?? 'N/A';
if (isset($_SESSION['owner_stats'])) unset($_SESSION['owner_stats']); // Clear after use
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Owner Dashboard</h2>
        <a href="<?php echo APP_URL; ?>/index.php?action=owner_dashboard_load_stats" class="btn btn-sm btn-outline-info">Refresh Stats</a>
    </div>
    <p class="lead">Welcome, Owner <strong><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?></strong>!</p>
    <p>This is the central control panel for site-wide settings and user management.</p>

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

    <div class="row mt-4">
        <div class="col-md-4 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text fs-4"><?php echo $total_users; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Students</h5>
                    <p class="card-text fs-4"><?php echo $total_students; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
             <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Administrators</h5>
                    <p class="card-text fs-4"><?php echo $total_admins; ?></p>
                </div>
            </div>
        </div>
         <div class="col-md-4 mb-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Owners</h5>
                    <p class="card-text fs-4"><?php echo $total_owners; ?> <small class="fs-6">(Max: <?php echo defined('MAX_OWNERS') ? MAX_OWNERS : 'N/A'; ?>)</small></p>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h3>Key Management Areas:</h3>
    <div class="list-group mt-3">
        <a href="<?php echo APP_URL; ?>/index.php?action=owner_manage_users_load" class="list-group-item list-group-item-action">
            Manage All Users (View, Change Roles)
        </a>
        <a href="<?php echo APP_URL; ?>/index.php?action=owner_site_settings_load" class="list-group-item list-group-item-action">
            Site Settings (Site Name, etc.)
        </a>
        <a href="<?php echo APP_URL; ?>/index.php?action=admin_dashboard" class="list-group-item list-group-item-action list-group-item-info">
            Access Admin Dashboard Features
        </a>
    </div>
    
    <p class="mt-4"><a href="<?php echo APP_URL; ?>/index.php?action=logout" class="btn btn-outline-danger">Logout</a></p>
</div>

<?php
$viewContent = ob_get_clean();
require_once __DIR__ . '/../layouts/main_layout.php'; 
?>
