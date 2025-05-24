<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure config is available for APP_URL
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    define('APP_URL', '/'); // Fallback
    error_log("Config file not found for manage_files.php (admin)");
}

// Access Control: Check if user is logged in and is an admin or owner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    $_SESSION['error_message'] = "You must be logged in as an admin or owner to view this page.";
    header("Location: " . APP_URL . "/index.php?action=login_admin"); // Or a general login page
    exit;
}

$pageTitle = "Manage Files & Folders";
ob_start();

// Fetch folders from session if passed by controller
$folders = isset($_SESSION['folders_list']) ? $_SESSION['folders_list'] : [];
if (isset($_SESSION['folders_list'])) unset($_SESSION['folders_list']); // Clear after use

// Fetch files from session if passed by controller (for later use)
$files = isset($_SESSION['files_list']) ? $_SESSION['files_list'] : [];
if (isset($_SESSION['files_list'])) unset($_SESSION['files_list']); // Clear after use

$error_message = isset($_SESSION['error_message_file_mgmt']) ? $_SESSION['error_message_file_mgmt'] : null;
if (isset($_SESSION['error_message_file_mgmt'])) unset($_SESSION['error_message_file_mgmt']);

$success_message = isset($_SESSION['success_message_file_mgmt']) ? $_SESSION['success_message_file_mgmt'] : null;
if (isset($_SESSION['success_message_file_mgmt'])) unset($_SESSION['success_message_file_mgmt']);

?>
<div class="container mt-4">
    <h2 class="mb-4">Manage Files & Folders (Exam Papers)</h2>

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

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Create New Folder</div>
                <div class="card-body">
                    <form action="<?php echo APP_URL; ?>/index.php?action=admin_create_folder_submit" method="POST">
                        <div class="mb-3">
                            <label for="folder_name" class="form-label">Folder Name:</label>
                            <input type="text" class="form-control" id="folder_name" name="folder_name" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Create Folder</button>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">Existing Folders</div>
                <div class="card-body">
                    <a href="<?php echo APP_URL; ?>/index.php?action=admin_manage_files_load" class="btn btn-sm btn-outline-info mb-2">Refresh Folders List</a>
                    <?php if (!empty($folders)): ?>
                        <ul class="list-group">
                            <?php foreach ($folders as $folder): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    📁 <?php echo htmlspecialchars($folder['name']); ?>
                                    <small class="text-muted"><?php echo htmlspecialchars(date('Y-m-d', strtotime($folder['created_at']))); ?></small>
                                    <!-- Add delete/edit links later if needed -->
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No folders found. Create one using the form above.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Upload New File (Exam Paper)</div>
                <div class="card-body">
                    <form action="<?php echo APP_URL; ?>/index.php?action=admin_upload_file_submit" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="file_upload" class="form-label">Select File:</label>
                            <input type="file" class="form-control" id="file_upload" name="file_upload" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">Subject:</label>
                                <input type="text" class="form-control" id="subject" name="subject" placeholder="Optional (e.g., Mathematics)">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="year" class="form-label">Year:</label>
                                <input type="number" class="form-control" id="year" name="year" placeholder="Optional (e.g., 2023)" min="1900" max="<?php echo date('Y')+1; ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="folder_id" class="form-label">Select Folder (Optional):</label>
                            <select class="form-select" id="folder_id" name="folder_id">
                                <option value="">None (Root/Uncategorized)</option>
                                <?php if (!empty($folders)): ?>
                                    <?php foreach ($folders as $folder): ?>
                                        <option value="<?php echo $folder['id']; ?>"><?php echo htmlspecialchars($folder['name']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description (Optional):</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brief description of the file content..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Upload File</button>
                    </form>
                </div>
            </div>
            
            <div class="mt-4">
                <h4>Uploaded Files</h4>
                 <a href="<?php echo APP_URL; ?>/index.php?action=admin_manage_files_load" class="btn btn-sm btn-outline-info mb-2">Refresh Files List</a>
                <?php if (!empty($files)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-primary">
                            <tr>
                                <th>Filename</th>
                                <th>Subject</th>
                                <th>Year</th>
                                <th>Folder</th>
                                <th>Uploader</th>
                                <th>Uploaded</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $file): ?>
                                <tr>
                                    <td><a href="<?php echo APP_URL . '/' . htmlspecialchars($file['file_path']); ?>" target="_blank"><?php echo htmlspecialchars($file['original_filename']); ?></a></td>
                                    <td><?php echo htmlspecialchars($file['subject'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($file['year'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($file['folder_name'] ?? 'Root'); ?></td>
                                    <td><?php echo htmlspecialchars($file['uploader_username'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($file['uploaded_at']))); ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-secondary disabled">Edit</a> 
                                        <a href="#" class="btn btn-sm btn-outline-danger disabled">Delete</a> <!-- Placeholder for future actions -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="alert alert-info" role="alert">No files uploaded yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php
$viewContent = ob_get_clean();
require_once __DIR__ . '/../layouts/main_layout.php'; 
?>
