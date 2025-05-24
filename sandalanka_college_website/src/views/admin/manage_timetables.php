<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure config is available for APP_URL
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    define('APP_URL', '/'); // Fallback
    error_log("Config file not found for manage_timetables.php (admin)");
}

// Access Control: Check if user is logged in and is an admin or owner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    $_SESSION['error_message'] = "You must be logged in as an admin or owner to view this page.";
    header("Location: " . APP_URL . "/index.php?action=login_admin");
    exit;
}

$pageTitle = "Manage Timetables";
ob_start();

// Fetch timetables from session if passed by controller
$timetables_list = isset($_SESSION['timetables_list']) ? $_SESSION['timetables_list'] : [];
if (isset($_SESSION['timetables_list'])) unset($_SESSION['timetables_list']);

$error_message = isset($_SESSION['error_message_timetable_mgmt']) ? $_SESSION['error_message_timetable_mgmt'] : null;
if (isset($_SESSION['error_message_timetable_mgmt'])) unset($_SESSION['error_message_timetable_mgmt']);

$success_message = isset($_SESSION['success_message_timetable_mgmt']) ? $_SESSION['success_message_timetable_mgmt'] : null;
if (isset($_SESSION['success_message_timetable_mgmt'])) unset($_SESSION['success_message_timetable_mgmt']);

$grades = [];
for ($i = 6; $i <= 13; $i++) {
    $grades[] = "Grade " . $i;
}
$classes = ["A", "B", "C", "D", "E", "F", "Combined Maths", "Bio Science", "Commerce", "Arts"]; // Added more class options

?>

<h2>Manage Timetables</h2>

<?php if ($error_message): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
<?php endif; ?>
<?php if ($success_message): ?>
    <p style="color: green;"><?php echo htmlspecialchars($success_message); ?></p>
<?php endif; ?>

<p><a href="<?php echo APP_URL; ?>/index.php?action=admin_dashboard">Back to Admin Dashboard</a></p>

<hr>
<h3>Upload New Timetable</h3>
<form action="<?php echo APP_URL; ?>/index.php?action=admin_upload_timetable_submit" method="POST" enctype="multipart/form-data" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd;">
    <div>
        <label for="grade">Grade:</label>
        <select id="grade" name="grade" required>
            <?php foreach ($grades as $grade_val): ?>
                <option value="<?php echo htmlspecialchars($grade_val); ?>"><?php echo htmlspecialchars($grade_val); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div style="margin-top: 10px;">
        <label for="class_name">Class Name:</label>
        <select id="class_name" name="class_name" required>
            <?php foreach ($classes as $class_val): ?>
                <option value="<?php echo htmlspecialchars($class_val); ?>"><?php echo htmlspecialchars($class_val); ?></option>
            <?php endforeach; ?>
             <option value="Other">Other (Specify Below)</option>
        </select>
        <input type="text" name="class_name_other" id="class_name_other" placeholder="Specify if 'Other'" style="display:none; margin-left:10px;">
    </div>
     <div style="margin-top: 10px;">
        <label for="timetable_file">Select File (PDF, JPG, PNG):</label>
        <input type="file" id="timetable_file" name="timetable_file" required>
    </div>
    <div style="margin-top: 10px;">
        <label for="description">Description (Optional):</label>
        <textarea id="description" name="description" rows="3" style="width: 98%;" placeholder="e.g., Term 1 Timetable"></textarea>
    </div>
    <button type="submit" style="margin-top: 15px;">Upload Timetable</button>
</form>

<hr>
<h3>Existing Timetables</h3>
<a href="<?php echo APP_URL; ?>/index.php?action=admin_manage_timetables_load">Refresh Timetables List</a>
<?php if (!empty($timetables_list)): ?>
    <table border="1" cellpadding="5" style="width:100%; border-collapse: collapse; margin-top:10px;">
        <thead>
            <tr>
                <th>Grade</th>
                <th>Class</th>
                <th>Filename</th>
                <th>Description</th>
                <th>Uploaded By</th>
                <th>Uploaded At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($timetables_list as $timetable): ?>
                <tr>
                    <td><?php echo htmlspecialchars($timetable['grade']); ?></td>
                    <td><?php echo htmlspecialchars($timetable['class_name']); ?></td>
                    <td><a href="<?php echo APP_URL . '/' . htmlspecialchars($timetable['file_path']); ?>" target="_blank"><?php echo htmlspecialchars($timetable['original_filename']); ?></a></td>
                    <td><?php echo htmlspecialchars($timetable['description'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($timetable['uploader_username'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($timetable['uploaded_at']))); ?></td>
                    <td>
                        <a href="<?php echo APP_URL; ?>/index.php?action=admin_delete_timetable_submit&timetable_id=<?php echo $timetable['id']; ?>" onclick="return confirm('Are you sure you want to delete this timetable?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No timetables uploaded yet. Use the form above to add one.</p>
<?php endif; ?>

<script>
    document.getElementById('class_name').addEventListener('change', function() {
        var otherInput = document.getElementById('class_name_other');
        if (this.value === 'Other') {
            otherInput.style.display = 'inline-block';
            otherInput.required = true;
        } else {
            otherInput.style.display = 'none';
            otherInput.required = false;
            otherInput.value = '';
        }
    });
</script>

<?php
$viewContent = ob_get_clean();
require_once __DIR__ . '/../layouts/main_layout.php'; 
?>
