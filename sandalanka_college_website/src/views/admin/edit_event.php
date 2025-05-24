<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure config is available for APP_URL
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    define('APP_URL', '/'); // Fallback
    error_log("Config file not found for edit_event.php (admin)");
}

// Access Control
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    $_SESSION['error_message'] = "Unauthorized access.";
    header("Location: " . APP_URL . "/index.php?action=login_admin");
    exit;
}

$pageTitle = "Edit Event";
ob_start();

// Fetch event data from session, set by admin_controller.php's handleEditEventForm
$event = isset($_SESSION['edit_event_data']) ? $_SESSION['edit_event_data'] : null;
if (isset($_SESSION['edit_event_data'])) unset($_SESSION['edit_event_data']);

$error_message_display = isset($_SESSION['error_message_event_edit']) ? $_SESSION['error_message_event_edit'] : null;
if (isset($_SESSION['error_message_event_edit'])) unset($_SESSION['error_message_event_edit']);

if (!$event || !isset($event['id'])) {
    $_SESSION['error_message_event_mgmt'] = "No event selected for editing or invalid event ID.";
    header("Location: " . APP_URL . "/index.php?action=admin_manage_events_load");
    exit;
}
?>
<div class="container mt-4">
    <h2 class="mb-4">Edit Event: <strong><?php echo htmlspecialchars($event['title']); ?></strong></h2>

    <?php if ($error_message_display): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message_display); ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form action="<?php echo APP_URL; ?>/index.php?action=admin_update_event_submit" method="POST">
                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                <div class="mb-3">
                    <label for="title" class="form-label">Event Title:</label>
                    <input type="text" class="form-control" id="title" name="title" required value="<?php echo htmlspecialchars($event['title']); ?>">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description:</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="date" class="form-label">Date:</label>
                        <input type="date" class="form-control" id="date" name="date" required value="<?php echo htmlspecialchars($event['date']); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="time" class="form-label">Time (Optional):</label>
                        <input type="time" class="form-control" id="time" name="time" value="<?php echo htmlspecialchars($event['time'] ?? ''); ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="location" class="form-label">Location (Optional):</label>
                    <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($event['location'] ?? ''); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Update Event</button>
                <a href="<?php echo APP_URL; ?>/index.php?action=admin_manage_events_load" class="btn btn-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php
$viewContent = ob_get_clean();
require_once __DIR__ . '/../layouts/main_layout.php'; 
?>
