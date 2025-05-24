<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure config is available for APP_URL
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    define('APP_URL', '/'); // Fallback
    error_log("Config file not found for exam_papers.php (student)");
}

// Access Control: Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    $_SESSION['error_message'] = "You must be logged in as a student to view this page.";
    header("Location: " . APP_URL . "/index.php?action=login_student");
    exit;
}

$pageTitle = "Exam Papers";
ob_start();

// Fetch folders and files from session if passed by controller
$folders = isset($_SESSION['student_folders_list']) ? $_SESSION['student_folders_list'] : [];
if (isset($_SESSION['student_folders_list'])) unset($_SESSION['student_folders_list']);

$files_by_folder = isset($_SESSION['student_files_by_folder']) ? $_SESSION['student_files_by_folder'] : [];
if (isset($_SESSION['student_files_by_folder'])) unset($_SESSION['student_files_by_folder']);

$uncategorized_files = isset($_SESSION['student_uncategorized_files']) ? $_SESSION['student_uncategorized_files'] : [];
if (isset($_SESSION['student_uncategorized_files'])) unset($_SESSION['student_uncategorized_files']);


$error_message_display = isset($_SESSION['error_message_papers']) ? $_SESSION['error_message_papers'] : null;
if (isset($_SESSION['error_message_papers'])) unset($_SESSION['error_message_papers']);

$current_search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';
?>
<div class="container mt-4">
    <h2 class="mb-3">Download Exam Papers</h2>
    <p class="lead">Here you can find past exam papers and other learning materials.</p>
    <p><a href="<?php echo APP_URL; ?>/index.php?action=student_dashboard" class="btn btn-sm btn-outline-secondary mb-3">Back to Student Dashboard</a></p>

    <!-- Search Form -->
    <form action="<?php echo APP_URL; ?>/index.php" method="GET" class="mb-4 p-3 border rounded bg-light">
        <input type="hidden" name="action" value="student_load_exam_papers">
        <div class="row g-2 align-items-center">
            <div class="col-md">
                <label for="search_query" class="visually-hidden">Search Papers:</label>
                <input type="text" class="form-control" id="search_query" name="search_query" value="<?php echo htmlspecialchars($current_search_query); ?>" placeholder="Enter subject, year, filename...">
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
            <div class="col-md-auto">
                <a href="<?php echo APP_URL; ?>/index.php?action=student_load_exam_papers" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        </div>
    </form>

    <?php if ($error_message_display): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message_display); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($current_search_query)): ?>
        <h3 class="mb-3">Search Results for: "<?php echo htmlspecialchars($current_search_query); ?>"</h3>
    <?php endif; ?>

    <?php 
    $found_any_files = false;
    if (!empty($uncategorized_files)) $found_any_files = true;
    if (!$found_any_files) {
        foreach ($folders as $folder) {
            if (isset($files_by_folder[$folder['id']]) && !empty($files_by_folder[$folder['id']])) {
                $found_any_files = true;
                break;
            }
        }
    }
    ?>

    <?php if (!$found_any_files): ?>
        <?php if (!empty($current_search_query)): ?>
            <div class="alert alert-warning" role="alert">
                No files found matching your search criteria: "<strong><?php echo htmlspecialchars($current_search_query); ?></strong>".
            </div>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                No exam papers or folders are currently available. Please check back later.
            </div>
        <?php endif; ?>
    <?php else: ?>
        <?php if (!empty($folders)): ?>
            <h3 class="mt-4">Folders</h3>
            <div class="accordion" id="foldersAccordion">
                <?php $folder_index = 0; ?>
                <?php foreach ($folders as $folder): ?>
                    <?php if (isset($files_by_folder[$folder['id']]) && !empty($files_by_folder[$folder['id']])): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFolder<?php echo $folder['id']; ?>">
                                <button class="accordion-button <?php if($folder_index > 0 && empty($current_search_query)) echo 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFolder<?php echo $folder['id']; ?>" aria-expanded="<?php echo ($folder_index == 0 || !empty($current_search_query)) ? 'true' : 'false'; ?>" aria-controls="collapseFolder<?php echo $folder['id']; ?>">
                                    📁 <?php echo htmlspecialchars($folder['name']); ?> 
                                    <span class="badge bg-secondary ms-2"><?php echo count($files_by_folder[$folder['id']]); ?> file(s)</span>
                                </button>
                            </h2>
                            <div id="collapseFolder<?php echo $folder['id']; ?>" class="accordion-collapse collapse <?php if($folder_index == 0 || !empty($current_search_query)) echo 'show'; ?>" aria-labelledby="headingFolder<?php echo $folder['id']; ?>" data-bs-parent="#foldersAccordion">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr><th>Filename</th><th>Subject</th><th>Year</th><th>Description</th><th>Uploaded</th><th>Link</th></tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($files_by_folder[$folder['id']] as $file): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($file['original_filename']); ?></td>
                                                        <td><?php echo htmlspecialchars($file['subject'] ?? 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars($file['year'] ?? 'N/A'); ?></td>
                                                        <td><?php echo nl2br(htmlspecialchars($file['description'] ?? 'N/A')); ?></td>
                                                        <td><small><?php echo htmlspecialchars(date('Y-m-d', strtotime($file['uploaded_at']))); ?></small></td>
                                                        <td><a href="<?php echo APP_URL . '/' . htmlspecialchars($file['file_path']); ?>" target="_blank" class="btn btn-sm btn-success">Download</a></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $folder_index++; ?>
                    <?php elseif (!empty($current_search_query)): ?>
                        <!-- If searching and folder is empty, optionally hide or show "no results in this folder" -->
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($uncategorized_files)): ?>
            <h3 class="mt-4 <?php if(!empty($folders) && $found_any_files) echo 'pt-3 border-top'; ?>">Uncategorized Files</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered">
                    <thead class="table-light">
                        <tr><th>Filename</th><th>Subject</th><th>Year</th><th>Description</th><th>Uploaded</th><th>Link</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($uncategorized_files as $file): ?>
                             <tr>
                                <td><?php echo htmlspecialchars($file['original_filename']); ?></td>
                                <td><?php echo htmlspecialchars($file['subject'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($file['year'] ?? 'N/A'); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($file['description'] ?? 'N/A')); ?></td>
                                <td><small><?php echo htmlspecialchars(date('Y-m-d', strtotime($file['uploaded_at']))); ?></small></td>
                                <td><a href="<?php echo APP_URL . '/' . htmlspecialchars($file['file_path']); ?>" target="_blank" class="btn btn-sm btn-success">Download</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>


<?php
$viewContent = ob_get_clean();
require_once __DIR__ . '/../layouts/main_layout.php'; 
?>
