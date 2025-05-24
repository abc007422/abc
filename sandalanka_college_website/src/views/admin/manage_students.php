<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure config is available for APP_URL
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    define('APP_URL', '/'); // Fallback
    error_log("Config file not found for manage_students.php");
}

// Access Control: Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "You must be logged in as an admin to view this page.";
    header("Location: " . APP_URL . "/index.php?action=login_admin");
    exit;
}

$pageTitle = "Manage Student Details";
ob_start();

// Fetch students from session if passed by controller
$students = isset($_SESSION['students_list']) ? $_SESSION['students_list'] : [];
if (isset($_SESSION['students_list'])) unset($_SESSION['students_list']);

$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;
if (isset($_SESSION['error_message'])) unset($_SESSION['error_message']);

$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
if (isset($_SESSION['success_message'])) unset($_SESSION['success_message']);

?>

<h2>Manage Student Details</h2>

<?php if ($error_message): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
<?php endif; ?>
<?php if ($success_message): ?>
    <p style="color: green;"><?php echo htmlspecialchars($success_message); ?></p>
<?php endif; ?>

<p>This page allows administrators to view, add, or edit personal details for students.</p>
<p><a href="<?php echo APP_URL; ?>/index.php?action=admin_dashboard" class="btn btn-secondary btn-sm mb-3">Back to Admin Dashboard</a></p>


<?php if (!empty($students)): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Username (Index Number)</th>
                <th>Email</th>
                <th>Full Name (from details)</th>
                <th>Class (from details)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($student['username']); ?></td>
                    <td><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($student['full_name'] ?? 'Not Set'); ?></td>
                    <td><?php echo htmlspecialchars($student['class'] ?? 'Not Set'); ?></td>
                    <td>
                        <a href="<?php echo APP_URL; ?>/index.php?action=admin_edit_student_details_form&user_id=<?php echo $student['user_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        <a href="<?php echo APP_URL; ?>/index.php?action=admin_view_student_details&user_id=<?php echo $student['user_id']; ?>" class="btn btn-sm btn-outline-info">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        No students found or an error occurred fetching the list. Please ensure students are registered.
    </div>
<?php endif; ?>

<p class="mt-3">
    <a href="<?php echo APP_URL; ?>/index.php?action=admin_manage_students_load" class="btn btn-info">Refresh Student List</a>
</p>


<?php
$viewContent = ob_get_clean();
require_once __DIR__ . '/../layouts/main_layout.php'; 
?>
