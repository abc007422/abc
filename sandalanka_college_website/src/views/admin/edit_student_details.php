<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure config is available for APP_URL
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    define('APP_URL', '/'); // Fallback
    error_log("Config file not found for edit_student_details.php (admin)");
}

// Access Control: Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "You must be logged in as an admin to view this page.";
    header("Location: " . APP_URL . "/index.php?action=login_admin");
    exit;
}

$pageTitle = "Edit Student Details (Admin)";
ob_start();

// Fetch data from session, set by admin_controller.php's handleEditStudentDetailsForm
$user_info = isset($_SESSION['edit_student_user_info']) ? $_SESSION['edit_student_user_info'] : null;
$student_details = isset($_SESSION['edit_student_details_data']) ? $_SESSION['edit_student_details_data'] : []; // Default to empty array

// Clear session data after fetching to prevent stale data display on refresh
unset($_SESSION['edit_student_user_info'], $_SESSION['edit_student_details_data']);

$error_message_form_display = isset($_SESSION['error_message_form']) ? $_SESSION['error_message_form'] : null;
if (isset($_SESSION['error_message_form'])) unset($_SESSION['error_message_form']);

// Success message might be set by save operation before redirecting back here on error, or by other means
$success_message_form_display = isset($_SESSION['success_message_form']) ? $_SESSION['success_message_form'] : null;
if (isset($_SESSION['success_message_form'])) unset($_SESSION['success_message_form']);

if (!$user_info || !isset($user_info['id'])) {
    // This error should ideally be set by the controller if user_id is invalid from the start
    $_SESSION['error_message'] = "No student selected for editing or invalid student ID.";
    header("Location: " . APP_URL . "/index.php?action=admin_manage_students_load");
    exit;
}
$target_user_id = $user_info['id'];

?>
<div class="container mt-4">
    <h2 class="mb-4">Edit Student Details for: <strong><?php echo htmlspecialchars($user_info['username'] . ' (ID: ' . $target_user_id . ')'); ?></strong></h2>

    <?php if ($error_message_form_display): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message_form_display); ?>
        </div>
    <?php endif; ?>
    <?php if ($success_message_form_display): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($success_message_form_display); ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            Student Account Info
        </div>
        <div class="card-body">
            <p><strong>Index Number:</strong> <?php echo htmlspecialchars($user_info['index_number'] ?? 'N/A'); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user_info['email'] ?? 'N/A'); ?></p>
        </div>
    </div>

    <form action="<?php echo APP_URL; ?>/index.php?action=admin_save_student_details" method="POST" class="mt-4 card p-4">
        <input type="hidden" name="user_id" value="<?php echo $target_user_id; ?>">
        <h5 class="card-title mb-3">Update Personal Details</h5>

        <div class="mb-3">
            <label for="full_name" class="form-label">Full Name:</label>
            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($student_details['full_name'] ?? ''); ?>">
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="class" class="form-label">Class (e.g., Grade 10A):</label>
                <input type="text" class="form-control" id="class" name="class" value="<?php echo htmlspecialchars($student_details['class'] ?? ''); ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label for="date_of_birth" class="form-label">Date of Birth:</label>
                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($student_details['date_of_birth'] ?? ''); ?>">
            </div>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address:</label>
            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($student_details['address'] ?? ''); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="parent_contact" class="form-label">Parent Contact (Phone/Email):</label>
            <input type="text" class="form-control" id="parent_contact" name="parent_contact" value="<?php echo htmlspecialchars($student_details['parent_contact'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label for="exam_performance" class="form-label">Exam Performance (Notes):</label>
            <textarea class="form-control" id="exam_performance" name="exam_performance" rows="4"><?php echo htmlspecialchars($student_details['exam_performance'] ?? ''); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="other_information" class="form-label">Other Information:</label>
            <textarea class="form-control" id="other_information" name="other_information" rows="4"><?php echo htmlspecialchars($student_details['other_information'] ?? ''); ?></textarea>
        </div>

        <button type="submit" class="btn btn-success">Save Details</button>
        <a href="<?php echo APP_URL; ?>/index.php?action=admin_view_student_details&user_id=<?php echo $target_user_id; ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
    </form>

    <p class="mt-4">
        <a href="<?php echo APP_URL; ?>/index.php?action=admin_manage_students_load" class="btn btn-secondary">Back to Manage Students List</a>
    </p>
</div>

<?php
$viewContent = ob_get_clean();
require_once __DIR__ . '/../layouts/main_layout.php'; 
?>
