<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure config is available for APP_URL
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    define('APP_URL', '/'); // Fallback
    error_log("Config file not found for admin_dashboard.php");
}

// Access Control: Check if user is logged in and is an admin or owner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    $_SESSION['error_message'] = "You must be logged in as an administrator or owner to view this page.";
    header("Location: " . APP_URL . "/index.php?action=login_admin");
    exit;
}

$pageTitle = "Admin Dashboard";
ob_start();

$admin_username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin';
?>
<div class="container mt-4">
    <h2 class="mb-3">Admin Dashboard</h2>
    <p class="lead">Welcome, <strong><?php echo $admin_username; ?></strong>!</p>
    <p>This is the central control panel for managing the <?php echo defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'Sandalanka Central College'; ?> website.</p>

    <div class="row mt-4">
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Student Management</h5>
                    <p class="card-text">View, add, or edit student details and information.</p>
                    <a href="<?php echo APP_URL; ?>/index.php?action=admin_manage_students_load" class="btn btn-primary">Manage Students</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Exam Papers</h5>
                    <p class="card-text">Create folders and upload/manage past exam papers for student access.</p>
                    <a href="<?php echo APP_URL; ?>/index.php?action=admin_manage_files_load" class="btn btn-primary">Manage Exam Papers</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Class Timetables</h5>
                    <p class="card-text">Upload and manage class timetables for different grades.</p>
                    <a href="<?php echo APP_URL; ?>/index.php?action=admin_manage_timetables_load" class="btn btn-primary">Manage Timetables</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">School Events</h5>
                    <p class="card-text">Create, edit, and manage school events and announcements.</p>
                    <a href="<?php echo APP_URL; ?>/index.php?action=admin_manage_events_load" class="btn btn-primary">Manage Events</a>
                </div>
            </div>
        </div>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'owner'): ?>
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100 bg-light">
                <div class="card-body">
                    <h5 class="card-title text-success">Owner Panel</h5>
                    <p class="card-text">Access site-wide settings and user role management.</p>
                    <a href="<?php echo APP_URL; ?>/index.php?action=owner_dashboard_view" class="btn btn-success">Go to Owner Panel</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
         <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Site Settings (Placeholder)</h5>
                    <p class="card-text">Manage general site configurations (details TBD).</p>
                    <button class="btn btn-secondary" disabled>Site Settings (N/A)</button>
                </div>
            </div>
        </div>
    </div>

    <p class="mt-4"><a href="<?php echo APP_URL; ?>/index.php?action=logout" class="btn btn-outline-danger">Logout</a></p>
</div>
<?php
$viewContent = ob_get_clean();
require_once __DIR__ . '/../layouts/main_layout.php'; // Adjusted path
?>
