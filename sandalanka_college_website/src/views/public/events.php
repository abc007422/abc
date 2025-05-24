<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Session might be used for user preferences later, or if logged in, to show their name etc.
}

// Ensure config is available for APP_URL
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    define('APP_URL', '/'); // Fallback
    error_log("Config file not found for events.php (public)");
}

$pageTitle = "Upcoming Events";
ob_start();

// Fetch events from session if passed by controller
$upcoming_events = isset($_SESSION['public_upcoming_events']) ? $_SESSION['public_upcoming_events'] : [];
if (isset($_SESSION['public_upcoming_events'])) unset($_SESSION['public_upcoming_events']);

$past_events = isset($_SESSION['public_past_events']) ? $_SESSION['public_past_events'] : [];
if (isset($_SESSION['public_past_events'])) unset($_SESSION['public_past_events']);

$error_message_display = isset($_SESSION['error_message_public_events']) ? $_SESSION['error_message_public_events'] : null;
if (isset($_SESSION['error_message_public_events'])) unset($_SESSION['error_message_public_events']);

?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">School Events</h2>
        <a href="<?php echo APP_URL; ?>/index.php?action=public_load_events" class="btn btn-sm btn-outline-info">Refresh Events List</a>
    </div>
    <p class="lead">Stay updated with the latest happenings at <?php echo defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'Sandalanka Central College'; ?>.</p>

    <?php if ($error_message_display): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message_display); ?>
        </div>
    <?php endif; ?>

    <hr>
    <h3 class="mt-4 mb-3">Upcoming Events</h3>
    <?php if (!empty($upcoming_events)): ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($upcoming_events as $event): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-primary"><?php echo htmlspecialchars($event['title']); ?></h5>
                            <p class="card-text mb-1">
                                <small class="text-muted">
                                    <strong>Date:</strong> <?php echo htmlspecialchars(date('F j, Y', strtotime($event['date']))); ?>
                                    <?php if ($event['time']): ?>
                                        | <strong>Time:</strong> <?php echo htmlspecialchars(date('h:i A', strtotime($event['time']))); ?>
                                    <?php endif; ?>
                                </small>
                            </p>
                            <?php if ($event['location']): ?>
                                <p class="card-text mb-1"><small class="text-muted"><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></small></p>
                            <?php endif; ?>
                            <p class="card-text mt-2"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info" role="alert">
            No upcoming events scheduled at the moment. Please check back soon!
        </div>
    <?php endif; ?>

    <hr class="my-4">
    <h3 class="mt-4 mb-3">Past Events</h3>
    <?php if (!empty($past_events)): ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($past_events as $event): ?>
                 <div class="col">
                    <div class="card h-100 bg-light border-secondary">
                        <div class="card-body">
                            <h5 class="card-title text-muted"><?php echo htmlspecialchars($event['title']); ?></h5>
                            <p class="card-text mb-1">
                                <small class="text-muted">
                                    <strong>Date:</strong> <?php echo htmlspecialchars(date('F j, Y', strtotime($event['date']))); ?>
                                     <?php if ($event['time']): ?>
                                        | <strong>Time:</strong> <?php echo htmlspecialchars(date('h:i A', strtotime($event['time']))); ?>
                                    <?php endif; ?>
                                </small>
                            </p>
                             <?php if ($event['location']): ?>
                                <p class="card-text mb-1"><small class="text-muted"><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></small></p>
                            <?php endif; ?>
                            <p class="card-text mt-2 text-muted"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary" role="alert">
            No past events to display.
        </div>
    <?php endif; ?>
</div>

<?php
$viewContent = ob_get_clean();
require_once __DIR__ . '/../layouts/main_layout.php'; 
?>
