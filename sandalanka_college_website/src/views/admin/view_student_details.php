<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure config is available for APP_URL
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    define('APP_URL', '/'); // Fallback
    error_log("Config file not found for view_student_details.php (admin)");
}

// Access Control: Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "You must be logged in as an admin to view this page.";
    header("Location: " . APP_URL . "/index.php?action=login_admin");
    exit;
}

$pageTitle = "View Student Details (Admin)";
ob_start();

// Fetch data from session, set by admin_controller.php's handleViewStudentDetailsForAdmin
$user_info = isset($_SESSION['admin_view_student_user_info']) ? $_SESSION['admin_view_student_user_info'] : null;
$student_details = isset($_SESSION['admin_view_student_details']) ? $_SESSION['admin_view_student_details'] : null;

// Clear session data after fetching to prevent stale data display
unset($_SESSION['admin_view_student_user_info'], $_SESSION['admin_view_student_details']);

$error_message_display = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;
if (isset($_SESSION['error_message'])) unset($_SESSION['error_message']);

?>

<div class="container mt-4">
    <h2 class="mb-4">View Student Details (Admin)</h2>

    <?php if ($error_message_display): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message_display); ?>
        </div>
    <?php endif; ?>

    <?php if ($user_info): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Account Information (User ID: <?php echo htmlspecialchars($user_info['id']); ?>)
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Username:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars($user_info['username']); ?></dd>

                    <dt class="col-sm-3">Email:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars($user_info['email'] ?? 'N/A'); ?></dd>

                    <dt class="col-sm-3">Index Number:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars($user_info['index_number'] ?? 'N/A'); ?></dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-info text-white">
                Personal Details
            </div>
            <div class="card-body">
                <?php if ($student_details && !empty(array_filter($student_details))): ?>
                    <dl class="row">
                        <dt class="col-sm-3">Full Name:</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($student_details['full_name'] ?? 'N/A'); ?></dd>

                        <dt class="col-sm-3">Class:</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($student_details['class'] ?? 'N/A'); ?></dd>

                        <dt class="col-sm-3">Date of Birth:</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($student_details['date_of_birth'] ?? 'N/A'); ?></dd>

                        <dt class="col-sm-3">Address:</dt>
                        <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars($student_details['address'] ?? 'N/A')); ?></dd>

                        <dt class="col-sm-3">Parent Contact:</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($student_details['parent_contact'] ?? 'N/A'); ?></dd>

                        <dt class="col-sm-3">Exam Performance:</dt>
                        <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars($student_details['exam_performance'] ?? 'N/A')); ?></dd>

                        <dt class="col-sm-3">Other Information:</dt>
                        <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars($student_details['other_information'] ?? 'N/A')); ?></dd>

                        <dt class="col-sm-3 text-muted">Details Last Updated:</dt>
                        <dd class="col-sm-9 text-muted"><?php echo htmlspecialchars($student_details['updated_at'] ?? 'N/A'); ?></dd>
                    </dl>
                <?php else: ?>
                    <div class="alert alert-secondary" role="alert">
                        No personal details have been recorded for this student yet.
                    </div>
                <?php endif; ?>
                <a href="<?php echo APP_URL; ?>/index.php?action=admin_edit_student_details_form&user_id=<?php echo $user_info['id']; ?>" class="btn btn-primary mt-3">
                    Add/Edit Details for this Student
                </a>
            </div>
        </div>
    <?php else: ?>
        <?php if (!$error_message_display) : ?>
            <div class="alert alert-warning" role="alert">
                Student information could not be loaded. Please select a student from the list.
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <p class="mt-4">
        <a href="<?php echo APP_URL; ?>/index.php?action=admin_manage_students_load" class="btn btn-secondary">Back to Manage Students List</a>
    </p>
</div>
</p>

<?php
$viewContent = ob_get_clean();
require_once __DIR__ . '/../layouts/main_layout.php'; 
?>
