<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure config is available for APP_URL
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    define('APP_URL', '/'); // Fallback
    error_log("Config file not found for student_dashboard.php");
}

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    $_SESSION['error_message'] = "You must be logged in as a student to view this page.";
    header("Location: " . APP_URL . "/index.php?action=login_student");
    exit;
}

$pageTitle = "Student Dashboard";
ob_start();

$student_details_data_from_session = isset($_SESSION['student_details_data']) ? $_SESSION['student_details_data'] : null;
$error_message_details_display = isset($_SESSION['student_details_error']) ? $_SESSION['student_details_error'] : null;

$show_details_form_flag = true; // Default to show form

if ($student_details_data_from_session !== null) {
    $show_details_form_flag = false; // Details fetched or attempted, hide form initially
    unset($_SESSION['student_details_data']); // Clear after use
}
if ($error_message_details_display !== null) {
    // If there was an error, we might want to show the form again.
    // $show_details_form_flag = true; // Uncomment if form should reappear on error
    unset($_SESSION['student_details_error']); // Clear after use
}

?>
<div class="container mt-4">
    <h2 class="mb-3">Welcome to Your Dashboard, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</h2>
    <p class="lead">This is your personal dashboard. From here you can access your information, timetables, and other student-specific resources.</p>
    
    <div class="card mb-4">
        <div class="card-header">Your Account</div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item"><strong>Index Number:</strong> <?php echo htmlspecialchars($_SESSION['index_number']); ?></li>
            <li class="list-group-item"><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?></li>
        </ul>
    </div>


    <div class="card mb-4">
        <div class="card-header">
            My Personal Details
        </div>
        <div class="card-body">
            <?php if ($error_message_details_display): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error_message_details_display); ?>
                </div>
            <?php endif; ?>

            <?php if ($show_details_form_flag && $student_details_data_from_session === null): ?>
                <p>To view your personal details, please confirm your Index Number below.</p>
                <form action="<?php echo APP_URL; ?>/index.php?action=student_view_details_submit" method="POST" class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label for="provided_index_number" class="visually-hidden">Confirm Index Number:</label>
                        <input type="text" class="form-control" id="provided_index_number" name="provided_index_number" placeholder="Confirm Index Number" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">View My Details</button>
                    </div>
                </form>
            <?php endif; ?>

            <?php if ($student_details_data_from_session !== null): ?>
                <?php if (empty(array_filter($student_details_data_from_session))): // Check if all detail fields are empty/null ?>
                    <div class="alert alert-info" role="alert">
                        No personal details have been recorded for you yet. Please contact administration if this is an error.
                    </div>
                <?php else: ?>
                    <dl class="row">
                        <dt class="col-sm-3">Full Name:</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($student_details_data_from_session['full_name'] ?? 'N/A'); ?></dd>
                        
                        <dt class="col-sm-3">Class:</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($student_details_data_from_session['class'] ?? 'N/A'); ?></dd>
                        
                        <dt class="col-sm-3">Date of Birth:</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($student_details_data_from_session['date_of_birth'] ?? 'N/A'); ?></dd>
                        
                        <dt class="col-sm-3">Address:</dt>
                        <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars($student_details_data_from_session['address'] ?? 'N/A')); ?></dd>
                        
                        <dt class="col-sm-3">Parent Contact:</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($student_details_data_from_session['parent_contact'] ?? 'N/A'); ?></dd>
                        
                        <dt class="col-sm-3">Exam Performance:</dt>
                        <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars($student_details_data_from_session['exam_performance'] ?? 'N/A')); ?></dd>
                        
                        <dt class="col-sm-3">Other Information:</dt>
                        <dd class="col-sm-9"><?php echo nl2br(htmlspecialchars($student_details_data_from_session['other_information'] ?? 'N/A')); ?></dd>
                    </dl>
                <?php endif; ?>
                <a href="<?php echo APP_URL; ?>/index.php?action=student_dashboard" class="btn btn-sm btn-outline-secondary mt-2">Hide/Re-enter Index</a>
            <?php elseif (!$show_details_form_flag && !$error_message_details_display): // This means an attempt was made, no error, but no data (explicitly empty) ?>
                 <div class="alert alert-warning" role="alert">
                    No personal details found. If you believe this is an error, please contact the administration.
                 </div>
                 <a href="<?php echo APP_URL; ?>/index.php?action=student_dashboard" class="btn btn-sm btn-outline-secondary mt-2">Re-enter Index Number</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Quick Links
        </div>
        <div class="list-group list-group-flush">
            <a href="<?php echo APP_URL; ?>/index.php?action=student_load_timetables" class="list-group-item list-group-item-action">View My Timetable</a>
            <a href="<?php echo APP_URL; ?>/index.php?action=student_load_exam_papers" class="list-group-item list-group-item-action">Download Past Papers</a>
            <a href="#" class="list-group-item list-group-item-action disabled">Check My Results (Coming Soon)</a>
            <a href="<?php echo APP_URL; ?>/index.php?action=logout" class="list-group-item list-group-item-action list-group-item-danger">Logout</a>
        </div>
    </div>
</div>

<?php
$viewContent = ob_get_clean();
require_once __DIR__ . '/../layouts/main_layout.php'; // Adjusted path
?>
