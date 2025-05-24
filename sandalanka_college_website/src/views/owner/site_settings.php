<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure config is available
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    define('APP_URL', '/'); 
    define('SITE_NAME', 'Sandalanka Central College'); // Fallback
    error_log("Config file not found for site_settings.php (owner)");
}

// Access Control
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    $_SESSION['error_message'] = "Unauthorized access.";
    header("Location: " . APP_URL . "/index.php?action=login_admin");
    exit;
}

$pageTitle = "Site Settings";
ob_start();

$current_site_name = isset($_SESSION['site_settings_site_name']) ? $_SESSION['site_settings_site_name'] : (defined('SITE_NAME') ? SITE_NAME : 'Sandalanka Central College');
// Clear after use if it was specifically set for display by controller
if (isset($_SESSION['site_settings_site_name'])) unset($_SESSION['site_settings_site_name']);


$error_message_display = isset($_SESSION['error_message_settings']) ? $_SESSION['error_message_settings'] : null;
if (isset($_SESSION['error_message_settings'])) unset($_SESSION['error_message_settings']);

$success_message_display = isset($_SESSION['success_message_settings']) ? $_SESSION['success_message_settings'] : null;
if (isset($_SESSION['success_message_settings'])) unset($_SESSION['success_message_settings']);
?>
<div class="container mt-4">
    <h2 class="mb-4">Site Settings</h2>
    <p><a href="<?php echo APP_URL; ?>/index.php?action=owner_dashboard_view" class="btn btn-secondary btn-sm mb-3">Back to Owner Dashboard</a></p>

    <?php if ($success_message_display): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $success_message_display; // Contains HTML, so don't escape ?>
        </div>
    <?php endif; ?>
    <?php if ($error_message_display): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message_display); ?>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">Update Site Name</div>
        <div class="card-body">
            <form action="<?php echo APP_URL; ?>/index.php?action=owner_update_site_name" method="POST">
                <div class="mb-3">
                    <label for="site_name" class="form-label">Site Name:</label>
                    <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($current_site_name); ?>" required>
                    <div class="form-text">Note: Changing this setting requires manual update in <code>config/config.php</code> for persistence across sessions in this demonstration version. This form only simulates the update for display purposes during your current session.</div>
                </div>
                <button type="submit" class="btn btn-primary">Update Site Name (Demo)</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Other Configuration (Display Only)</div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item"><strong>Domain Name:</strong> <em class="text-muted">(Managed externally, e.g., sandalankacc.lk)</em></li>
            <li class="list-group-item"><strong>Hosting Provider:</strong> <em class="text-muted">(Managed externally, e.g., YourHostingProvider.com)</em></li>
            <li class="list-group-item"><strong>PHP Version:</strong> <?php echo phpversion(); ?></li>
            <li class="list-group-item"><strong>Max Owners Allowed:</strong> <?php echo defined('MAX_OWNERS') ? MAX_OWNERS : 'N/A'; ?></li>
            <li class="list-group-item"><strong>Admin Access Key (Partial):</strong> <?php echo defined('ADMIN_ACCESS_KEY') ? substr(ADMIN_ACCESS_KEY, 0, 5) . str_repeat('*', strlen(ADMIN_ACCESS_KEY) - 5) : 'N/A'; ?></li>
        </ul>
    </div>
</div>

<?php
$viewContent = ob_get_clean();
require_once __DIR__ . '/../layouts/main_layout.php'; 
?>
