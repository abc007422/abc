<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure config is available for APP_URL
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    define('APP_URL', '/'); // Fallback
    error_log("Config file not found for manage_events.php (admin)");
}

// Access Control: Check if user is logged in and is an admin or owner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    $_SESSION['error_message'] = "You must be logged in as an admin or owner to view this page.";
    header("Location: " . APP_URL . "/index.php?action=login_admin");
    exit;
}

$pageTitle = "Manage Events";
ob_start();

// Fetch events from session if passed by controller
$events = isset($_SESSION['events_list']) ? $_SESSION['events_list'] : [];
if (isset($_SESSION['events_list'])) unset($_SESSION['events_list']);

$error_message = isset($_SESSION['error_message_event_mgmt']) ? $_SESSION['error_message_event_mgmt'] : null;
if (isset($_SESSION['error_message_event_mgmt'])) unset($_SESSION['error_message_event_mgmt']);

$success_message = isset($_SESSION['success_message_event_mgmt']) ? $_SESSION['success_message_event_mgmt'] : null;
if (isset($_SESSION['success_message_event_mgmt'])) unset($_SESSION['success_message_event_mgmt']);

?>
<div class="container mt-4">
    <h2 class="mb-4">Manage School Events</h2>

    <?php if ($error_message): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $success_message; // Already htmlspecialchars in controller or contains HTML ?>
        </div>
    <?php endif; ?>

    <p><a href="<?php echo APP_URL; ?>/index.php?action=admin_dashboard" class="btn btn-secondary btn-sm mb-3">Back to Admin Dashboard</a></p>

    <div class="card mb-4">
        <div class="card-header">Add New Event</div>
        <div class="card-body">
            <form action="<?php echo APP_URL; ?>/index.php?action=admin_create_event_submit" method="POST">
                <div class="mb-3">
                    <label for="title" class="form-label">Event Title:</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description:</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="date" class="form-label">Date:</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="time" class="form-label">Time (Optional):</label>
                        <input type="time" class="form-control" id="time" name="time">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="location" class="form-label">Location (Optional):</label>
                    <input type="text" class="form-control" id="location" name="location">
                </div>
                <button type="submit" class="btn btn-primary">Create Event</button>
            </form>
        </div>
    </div>

    <hr>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Existing Events</h3>
        <a href="<?php echo APP_URL; ?>/index.php?action=admin_manage_events_load" class="btn btn-info btn-sm">Refresh Events List</a>
    </div>
    <?php if (!empty($events)): ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered">
            <thead class="table-primary">
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($event['title']); ?></td>
                        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($event['date']))); ?></td>
                        <td><?php echo htmlspecialchars($event['time'] ? date('h:i A', strtotime($event['time'])) : 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($event['location'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($event['creator_username'] ?? 'N/A'); ?></td>
                        <td>
                            <a href="<?php echo APP_URL; ?>/index.php?action=admin_edit_event_form&event_id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a> 
                            <a href="<?php echo APP_URL; ?>/index.php?action=admin_delete_event_submit&event_id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="alert alert-info" role="alert">
            No events found. Add one using the form above.
        </div>
    <?php endif; ?>
</div>

<?php
$viewContent = ob_get_clean();
require_once __DIR__ . '/../layouts/main_layout.php'; 
?>
