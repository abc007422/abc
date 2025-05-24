<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../../config/config.php';

// Function to ensure only admins or owners can access controller actions
function ensureAdmin() { // Renaming to ensureStaff or ensurePrivileged might be clearer, but keeping as ensureAdmin for now
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
        $_SESSION['error_message'] = "You must be logged in as an administrator or owner to perform this action.";
        header("Location: " . APP_URL . "/index.php?action=login_admin"); // Redirect to staff login
        exit;
    }
}

function handleListStudents() {
    ensureAdmin();
    $pdo = getPDOConnection();
    try {
        // Fetch all users with role 'student' and their details if available
        // Using LEFT JOIN to include students even if they don't have an entry in student_details yet
        $stmt = $pdo->query(
            "SELECT u.id as user_id, u.username, u.email, u.index_number, 
                    sd.full_name, sd.class 
             FROM users u
             LEFT JOIN student_details sd ON u.id = sd.user_id
             WHERE u.role = 'student'
             ORDER BY u.id ASC"
        );
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $_SESSION['students_list'] = $students;
    } catch (PDOException $e) {
        error_log("Database error fetching student list: " . $e->getMessage());
        $_SESSION['error_message'] = "An error occurred while fetching the student list.";
        $_SESSION['students_list'] = []; // Send empty array on error
    }
    header("Location: " . APP_URL . "/index.php?action=admin_manage_students_view");
    exit;
}

function handleViewStudentDetailsForAdmin() {
    ensureAdmin();
    if (!isset($_GET['user_id'])) {
        $_SESSION['error_message'] = "User ID not provided.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_students_load");
        exit;
    }
    $user_id = $_GET['user_id'];
    $pdo = getPDOConnection();
    try {
        $stmt_user = $pdo->prepare("SELECT id, username, email, index_number FROM users WHERE id = :user_id AND role = 'student'");
        $stmt_user->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_user->execute();
        $user_info = $stmt_user->fetch(PDO::FETCH_ASSOC);

        if(!$user_info) {
            $_SESSION['error_message'] = "Student not found.";
            header("Location: " . APP_URL . "/index.php?action=admin_manage_students_load");
            exit;
        }

        $stmt_details = $pdo->prepare("SELECT * FROM student_details WHERE user_id = :user_id");
        $stmt_details->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_details->execute();
        $student_details = $stmt_details->fetch(PDO::FETCH_ASSOC);

        // Pass data to a view page specifically for admin viewing details
        $_SESSION['admin_view_student_user_info'] = $user_info;
        $_SESSION['admin_view_student_details'] = $student_details ? $student_details : []; // Ensure it's an array

        // We need a new view for this: src/views/admin/view_student_details.php
        header("Location: " . APP_URL . "/index.php?action=admin_view_student_details_page");
        exit;

    } catch (PDOException $e) {
        error_log("Database error fetching student details for admin: " . $e->getMessage());
        $_SESSION['error_message'] = "An error occurred fetching details for user ID: " . htmlspecialchars($user_id);
        header("Location: " . APP_URL . "/index.php?action=admin_manage_students_load");
        exit;
    }
}


// Routing within this controller based on 'admin_action' or similar
if (isset($_GET['action'])) { // Re-using 'action' as index.php forwards here for specific admin actions
    $admin_action = $_GET['action'];
    switch ($admin_action) {
        case 'admin_manage_students_load': // Action to load student list
            handleListStudents();
            break;
        case 'admin_view_student_details': // Action for admin to view specific student's details
            handleViewStudentDetailsForAdmin();
            break;
        case 'admin_edit_student_details_form': // Action to show the form for editing/adding details
            handleEditStudentDetailsForm();
            break;
        case 'admin_save_student_details': // Action to save the submitted student details
            handleSaveStudentDetails();
            break;
        case 'admin_manage_files_load': // Action to load folders and files for manage_files.php
            handleLoadFilesAndFolders();
            break;
        case 'admin_create_folder_submit': // Action to create a new folder
            handleCreateFolder();
            break;
        case 'admin_upload_file_submit': // Action to upload a new file
            handleUploadFile();
            break;
        // Event Management
        case 'admin_manage_events_load': // Action to load events for manage_events.php
            handleListEvents();
            break;
        case 'admin_create_event_submit': // Action to create a new event
            handleCreateEvent();
            break;
        case 'admin_edit_event_form': // Action to show the form for editing an event
            handleEditEventForm();
            break;
        case 'admin_update_event_submit': // Action to update an event
            handleUpdateEvent();
            break;
        case 'admin_delete_event_submit': // Action to delete an event
            handleDeleteEvent();
            break;
        // Timetable Management
        case 'admin_manage_timetables_load':
            handleListTimetablesAdmin();
            break;
        case 'admin_upload_timetable_submit':
            handleUploadTimetable();
            break;
        case 'admin_delete_timetable_submit':
            handleDeleteTimetable();
            break;
    }
}

function handleListTimetablesAdmin() {
    ensureAdmin();
    $pdo = getPDOConnection();
    try {
        $stmt = $pdo->query(
            "SELECT tt.*, u.username as uploader_username 
             FROM timetables tt
             JOIN users u ON tt.uploaded_by = u.id
             ORDER BY tt.grade ASC, tt.class_name ASC, tt.uploaded_at DESC"
        );
        $_SESSION['timetables_list'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error loading timetables list for admin: " . $e->getMessage());
        $_SESSION['error_message_timetable_mgmt'] = "An error occurred while loading the timetables list.";
        $_SESSION['timetables_list'] = [];
    }
    header("Location: " . APP_URL . "/index.php?action=admin_manage_timetables_view");
    exit;
}

function handleUploadTimetable() {
    ensureAdmin();

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        $_SESSION['error_message_timetable_mgmt'] = "Invalid request method.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_timetables_load");
        exit;
    }

    // Validate inputs
    $grade = trim($_POST['grade'] ?? '');
    $class_name_select = trim($_POST['class_name'] ?? '');
    $class_name_other = trim($_POST['class_name_other'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $uploaded_by = $_SESSION['user_id'];

    $class_name = ($class_name_select === 'Other') ? $class_name_other : $class_name_select;

    if (empty($grade) || empty($class_name)) {
        $_SESSION['error_message_timetable_mgmt'] = "Grade and Class Name are required.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_timetables_load");
        exit;
    }
    if (!isset($_FILES['timetable_file']) || $_FILES['timetable_file']['error'] == UPLOAD_ERR_NO_FILE) {
        $_SESSION['error_message_timetable_mgmt'] = "No file selected for upload.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_timetables_load");
        exit;
    }
    if ($_FILES['timetable_file']['error'] !== UPLOAD_ERR_OK) {
        // Use a more user-friendly error message function if available
        $_SESSION['error_message_timetable_mgmt'] = "File upload error code: " . $_FILES['timetable_file']['error'];
        header("Location: " . APP_URL . "/index.php?action=admin_manage_timetables_load");
        exit;
    }

    $target_dir_relative = "public/uploads/timetables/";
    $target_dir_absolute = BASE_PATH . "/" . $target_dir_relative;

    if (!is_dir($target_dir_absolute) || !is_writable($target_dir_absolute)) {
        $_SESSION['error_message_timetable_mgmt'] = "Upload directory error. Please contact administrator.";
        error_log("Timetable upload directory error: Path " . $target_dir_absolute . " not writable or does not exist.");
        header("Location: " . APP_URL . "/index.php?action=admin_manage_timetables_load");
        exit;
    }

    $original_filename = basename($_FILES["timetable_file"]["name"]);
    $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
    $stored_filename = "timetable_" . str_replace(" ", "_", strtolower($grade)) . "_" . str_replace(" ", "_", strtolower($class_name)) . "_" . time() . "." . $file_extension;
    
    // $target_dir_relative was "public/uploads/timetables/"
    // Store path relative to webroot's uploads dir.
    $db_path_segment_timetables = "uploads/timetables/";

    $target_file_absolute = $target_dir_absolute . $stored_filename; // Full server path for move_uploaded_file
    $target_file_relative_for_db = $db_path_segment_timetables . $stored_filename; // Path to store in DB

    $allowed_types = ['pdf', 'png', 'jpg', 'jpeg', 'gif'];
    if (!in_array($file_extension, $allowed_types)) {
        $_SESSION['error_message_timetable_mgmt'] = "Invalid file type. Only " . implode(', ', $allowed_types) . " allowed.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_timetables_load");
        exit;
    }
    
    $max_file_size = 5 * 1024 * 1024; // 5 MB
    if ($_FILES["timetable_file"]["size"] > $max_file_size) {
        $_SESSION['error_message_timetable_mgmt'] = "File is too large. Max size is 5MB.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_timetables_load");
        exit;
    }

    if (move_uploaded_file($_FILES["timetable_file"]["tmp_name"], $target_file_absolute)) {
        $pdo = getPDOConnection();
        try {
            $sql = "INSERT INTO timetables (grade, class_name, original_filename, stored_filename, file_path, file_type, description, uploaded_by) 
                    VALUES (:grade, :class_name, :original_filename, :stored_filename, :file_path, :file_type, :description, :uploaded_by)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':grade', $grade);
            $stmt->bindParam(':class_name', $class_name);
            $stmt->bindParam(':original_filename', $original_filename);
            $stmt->bindParam(':stored_filename', $stored_filename);
            $stmt->bindParam(':file_path', $target_file_relative_for_db);
            $stmt->bindParam(':file_type', $file_extension);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':uploaded_by', $uploaded_by, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $_SESSION['success_message_timetable_mgmt'] = "Timetable for <strong>" . htmlspecialchars($grade) . " - " . htmlspecialchars($class_name) . "</strong> uploaded successfully.";
            } else {
                $_SESSION['error_message_timetable_mgmt'] = "Failed to save timetable information to database.";
                unlink($target_file_absolute); // Delete uploaded file if DB entry fails
            }
        } catch (PDOException $e) {
            unlink($target_file_absolute); // Delete uploaded file if DB entry fails
            error_log("Database error uploading timetable: " . $e->getMessage());
            $_SESSION['error_message_timetable_mgmt'] = "DB error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message_timetable_mgmt'] = "Failed to move uploaded file.";
    }
    header("Location: " . APP_URL . "/index.php?action=admin_manage_timetables_load");
    exit;
}

function handleDeleteTimetable() {
    ensureAdmin();
    if (!isset($_GET['timetable_id'])) {
        $_SESSION['error_message_timetable_mgmt'] = "Timetable ID not provided for deletion.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_timetables_load");
        exit;
    }
    $timetable_id = filter_input(INPUT_GET, 'timetable_id', FILTER_VALIDATE_INT);
    if (!$timetable_id) {
        $_SESSION['error_message_timetable_mgmt'] = "Invalid Timetable ID.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_timetables_load");
        exit;
    }

    $pdo = getPDOConnection();
    try {
        // First, get the file path to delete the actual file
        $stmt_file = $pdo->prepare("SELECT file_path FROM timetables WHERE id = :id");
        $stmt_file->bindParam(':id', $timetable_id, PDO::PARAM_INT);
        $stmt_file->execute();
        $file_path_relative = $stmt_file->fetchColumn();

        if ($file_path_relative) {
            $file_path_absolute = BASE_PATH . '/' . $file_path_relative;
            
            // Delete DB record
            $stmt_delete = $pdo->prepare("DELETE FROM timetables WHERE id = :id");
            $stmt_delete->bindParam(':id', $timetable_id, PDO::PARAM_INT);
            
            if ($stmt_delete->execute()) {
                // If DB deletion is successful, delete the file
                if (file_exists($file_path_absolute)) {
                    if (unlink($file_path_absolute)) {
                        $_SESSION['success_message_timetable_mgmt'] = "Timetable (ID: " . htmlspecialchars($timetable_id) . ") and its file deleted successfully.";
                    } else {
                        $_SESSION['error_message_timetable_mgmt'] = "Timetable record deleted from DB, but failed to delete the actual file. Path: " . htmlspecialchars($file_path_absolute);
                        error_log("Failed to delete timetable file: " . $file_path_absolute);
                    }
                } else {
                     $_SESSION['success_message_timetable_mgmt'] = "Timetable (ID: " . htmlspecialchars($timetable_id) . ") deleted from DB. File not found on server: " . htmlspecialchars($file_path_absolute);
                }
            } else {
                $_SESSION['error_message_timetable_mgmt'] = "Failed to delete timetable record (ID: " . htmlspecialchars($timetable_id) . ") from database.";
            }
        } else {
            $_SESSION['error_message_timetable_mgmt'] = "Timetable record (ID: " . htmlspecialchars($timetable_id) . ") not found.";
        }
    } catch (PDOException $e) {
        error_log("Database error deleting timetable ID {$timetable_id}: " . $e->getMessage());
        $_SESSION['error_message_timetable_mgmt'] = "An internal error occurred: " . $e->getMessage();
    }
    header("Location: " . APP_URL . "/index.php?action=admin_manage_timetables_load");
    exit;
}

function handleEditEventForm() {
    ensureAdmin();
    if (!isset($_GET['event_id'])) {
        $_SESSION['error_message_event_mgmt'] = "Event ID not provided for editing.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_events_load");
        exit;
    }
    $event_id = $_GET['event_id'];
    $pdo = getPDOConnection();
    try {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = :event_id");
        $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            $_SESSION['error_message_event_mgmt'] = "Event not found for editing (ID: " . htmlspecialchars($event_id) . ").";
            header("Location: " . APP_URL . "/index.php?action=admin_manage_events_load");
            exit;
        }
        $_SESSION['edit_event_data'] = $event;
        // Redirect to the actual view page for editing the event
        header("Location: " . APP_URL . "/index.php?action=admin_edit_event_page_view"); 
        exit;
    } catch (PDOException $e) {
        error_log("Database error preparing edit form for event ID {$event_id}: " . $e->getMessage());
        $_SESSION['error_message_event_mgmt'] = "An error occurred preparing data for editing event ID: " . htmlspecialchars($event_id);
        header("Location: " . APP_URL . "/index.php?action=admin_manage_events_load");
        exit;
    }
}

function handleUpdateEvent() {
    ensureAdmin();
    if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['event_id'])) {
        $_SESSION['error_message_event_mgmt'] = "Invalid request or event ID missing.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_events_load");
        exit;
    }

    $event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = !empty($_POST['time']) ? trim($_POST['time']) : null;
    $location = !empty($_POST['location']) ? trim($_POST['location']) : null;

    if (!$event_id || empty($title) || empty($description) || empty($date)) {
        $_SESSION['error_message_event_edit'] = "Event ID, Title, Description, and Date are required.";
        // Redirect back to edit form with the ID
        header("Location: " . APP_URL . "/index.php?action=admin_edit_event_form&event_id=" . ($event_id ?: '0'));
        exit;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $_SESSION['error_message_event_edit'] = "Invalid date format. Please use YYYY-MM-DD.";
        header("Location: " . APP_URL . "/index.php?action=admin_edit_event_form&event_id=" . $event_id);
        exit;
    }
    if ($time !== null && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time)) {
         $_SESSION['error_message_event_edit'] = "Invalid time format. Please use HH:MM or HH:MM:SS.";
        header("Location: " . APP_URL . "/index.php?action=admin_edit_event_form&event_id=" . $event_id);
        exit;
    }

    $pdo = getPDOConnection();
    try {
        $sql = "UPDATE events SET title = :title, description = :description, date = :date, 
                       time = :time, location = :location, updated_at = CURRENT_TIMESTAMP 
                WHERE id = :event_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':time', $time, $time === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindParam(':location', $location, $location === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['success_message_event_mgmt'] = "Event '<strong>" . htmlspecialchars($title) . "</strong>' updated successfully.";
        } else {
            $_SESSION['error_message_event_mgmt'] = "Failed to update event. Please try again.";
        }
    } catch (PDOException $e) {
        error_log("Database error updating event ID {$event_id}: " . $e->getMessage());
        $_SESSION['error_message_event_mgmt'] = "An internal error occurred while updating the event: " . $e->getMessage();
    }
    header("Location: " . APP_URL . "/index.php?action=admin_manage_events_load");
    exit;
}

function handleDeleteEvent() {
    ensureAdmin();
    if (!isset($_GET['event_id'])) {
        $_SESSION['error_message_event_mgmt'] = "Event ID not provided for deletion.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_events_load");
        exit;
    }
    $event_id = $_GET['event_id'];

    $pdo = getPDOConnection();
    try {
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = :event_id");
        $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $_SESSION['success_message_event_mgmt'] = "Event (ID: " . htmlspecialchars($event_id) . ") deleted successfully.";
        } else {
            $_SESSION['error_message_event_mgmt'] = "Failed to delete event (ID: " . htmlspecialchars($event_id) . ").";
        }
    } catch (PDOException $e) {
        error_log("Database error deleting event ID {$event_id}: " . $e->getMessage());
        $_SESSION['error_message_event_mgmt'] = "An internal error occurred while deleting the event: " . $e->getMessage();
    }
    header("Location: " . APP_URL . "/index.php?action=admin_manage_events_load");
    exit;
}

function handleListEvents() {
    ensureAdmin(); // Or ensureAdminOrOwner
    $pdo = getPDOConnection();
    try {
        $stmt = $pdo->query(
            "SELECT e.*, u.username as creator_username 
             FROM events e
             JOIN users u ON e.created_by = u.id
             ORDER BY e.date DESC, e.time DESC"
        );
        $_SESSION['events_list'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error loading events list: " . $e->getMessage());
        $_SESSION['error_message_event_mgmt'] = "An error occurred while loading the events list.";
        $_SESSION['events_list'] = [];
    }
    header("Location: " . APP_URL . "/index.php?action=admin_manage_events_view"); // The view page
    exit;
}

function handleCreateEvent() {
    ensureAdmin(); // Or ensureAdminOrOwner

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        $_SESSION['error_message_event_mgmt'] = "Invalid request method for creating event.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_events_load");
        exit;
    }

    // Validate inputs
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = !empty($_POST['time']) ? trim($_POST['time']) : null;
    $location = !empty($_POST['location']) ? trim($_POST['location']) : null;
    $created_by = $_SESSION['user_id'];

    if (empty($title) || empty($description) || empty($date)) {
        $_SESSION['error_message_event_mgmt'] = "Title, Description, and Date are required fields.";
        // Consider preserving form data in session to repopulate
        header("Location: " . APP_URL . "/index.php?action=admin_manage_events_load");
        exit;
    }

    // Validate date format (basic check)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $_SESSION['error_message_event_mgmt'] = "Invalid date format. Please use YYYY-MM-DD.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_events_load");
        exit;
    }
    // Validate time format if provided
    if ($time !== null && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time)) {
         $_SESSION['error_message_event_mgmt'] = "Invalid time format. Please use HH:MM or HH:MM:SS.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_events_load");
        exit;
    }


    $pdo = getPDOConnection();
    try {
        $sql = "INSERT INTO events (title, description, date, time, location, created_by) 
                VALUES (:title, :description, :date, :time, :location, :created_by)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':time', $time, $time === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindParam(':location', $location, $location === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindParam(':created_by', $created_by, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['success_message_event_mgmt'] = "Event '<strong>" . htmlspecialchars($title) . "</strong>' created successfully.";
        } else {
            $_SESSION['error_message_event_mgmt'] = "Failed to create event. Please try again.";
        }
    } catch (PDOException $e) {
        error_log("Database error creating event: " . $e->getMessage());
        $_SESSION['error_message_event_mgmt'] = "An internal error occurred while creating the event: " . $e->getMessage();
    }
    header("Location: " . APP_URL . "/index.php?action=admin_manage_events_load"); // Refresh the list
    exit;
}

function handleUploadFile() {
    ensureAdmin(); // Or ensureAdminOrOwner

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        $_SESSION['error_message_file_mgmt'] = "Invalid request method for file upload.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_files_load");
        exit;
    }

    if (!isset($_FILES['file_upload']) || $_FILES['file_upload']['error'] == UPLOAD_ERR_NO_FILE) {
        $_SESSION['error_message_file_mgmt'] = "No file selected for upload.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_files_load");
        exit;
    }

    // File upload error handling
    if ($_FILES['file_upload']['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE   => "File exceeds upload_max_filesize directive in php.ini.",
            UPLOAD_ERR_FORM_SIZE  => "File exceeds MAX_FILE_SIZE directive specified in the HTML form.",
            UPLOAD_ERR_PARTIAL    => "File was only partially uploaded.",
            UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder for uploads.",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
            UPLOAD_ERR_EXTENSION  => "A PHP extension stopped the file upload.",
        ];
        $error_code = $_FILES['file_upload']['error'];
        $_SESSION['error_message_file_mgmt'] = isset($upload_errors[$error_code]) ? $upload_errors[$error_code] : "Unknown upload error.";
        error_log("File upload error: " . $_SESSION['error_message_file_mgmt'] . " (Code: $error_code)");
        header("Location: " . APP_URL . "/index.php?action=admin_manage_files_load");
        exit;
    }

    // Configuration for uploads
    $target_dir_relative = "public/uploads/papers/"; // Relative to APP_URL or document root
    $target_dir_absolute = BASE_PATH . "/" . $target_dir_relative; // BASE_PATH defined in config.php

    if (!is_dir($target_dir_absolute) || !is_writable($target_dir_absolute)) {
        $_SESSION['error_message_file_mgmt'] = "Upload directory is not writable or does not exist. Please check server configuration. Path: " . $target_dir_absolute;
        error_log("Upload directory error: " . $_SESSION['error_message_file_mgmt']);
        header("Location: " . APP_URL . "/index.php?action=admin_manage_files_load");
        exit;
    }
    
    $original_filename = basename($_FILES["file_upload"]["name"]);
    $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
    $stored_filename = uniqid('file_', true) . '.' . $file_extension; // Generate unique filename
    
    // $target_dir_relative was "public/uploads/papers/"
    // We want to store path relative to the webroot's uploads dir, not including "public/" in the DB path.
    // APP_URL should point to the public directory.
    // So, if APP_URL is "http://localhost/sandalanka_college_website/public",
    // and files are in "sandalanka_college_website/public/uploads/papers/",
    // the path stored in DB should be "uploads/papers/filename.pdf".
    $db_path_segment = "uploads/papers/";
    
    $target_file_absolute = $target_dir_absolute . $stored_filename; // Full server path for move_uploaded_file
    $target_file_relative_for_db = $db_path_segment . $stored_filename; // Path to store in DB

    // Validate file type (example: allow PDF, DOCX, PNG, JPG)
    $allowed_types = ['pdf', 'docx', 'doc', 'png', 'jpg', 'jpeg', 'txt', 'xls', 'xlsx', 'ppt', 'pptx'];
    if (!in_array($file_extension, $allowed_types)) {
        $_SESSION['error_message_file_mgmt'] = "Sorry, only " . implode(', ', $allowed_types) . " files are allowed. You uploaded a '." . $file_extension . "' file.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_files_load");
        exit;
    }

    // Validate file size (example: max 10MB)
    $max_file_size = 10 * 1024 * 1024; // 10 MB in bytes
    if ($_FILES["file_upload"]["size"] > $max_file_size) {
        $_SESSION['error_message_file_mgmt'] = "Sorry, your file is too large. Maximum size is " . ($max_file_size / 1024 / 1024) . " MB.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_files_load");
        exit;
    }

    // Attempt to move the uploaded file
    if (move_uploaded_file($_FILES["file_upload"]["tmp_name"], $target_file_absolute)) {
        // File is uploaded, now save info to database
        $folder_id = isset($_POST['folder_id']) && !empty($_POST['folder_id']) ? (int)$_POST['folder_id'] : null;
        $user_id = $_SESSION['user_id'];
        $subject = isset($_POST['subject']) && !empty($_POST['subject']) ? trim($_POST['subject']) : null;
        $year = isset($_POST['year']) && !empty($_POST['year']) ? (int)$_POST['year'] : null;
        $description = isset($_POST['description']) && !empty($_POST['description']) ? trim($_POST['description']) : null;
        $file_type = $_FILES["file_upload"]["type"]; // MIME type from browser
        $file_size = $_FILES["file_upload"]["size"];

        $pdo = getPDOConnection();
        try {
            $sql = "INSERT INTO files (folder_id, user_id, subject, year, original_filename, stored_filename, file_path, file_type, file_size, description) 
                    VALUES (:folder_id, :user_id, :subject, :year, :original_filename, :stored_filename, :file_path, :file_type, :file_size, :description)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':folder_id', $folder_id, $folder_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':year', $year, $year === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindParam(':original_filename', $original_filename);
            $stmt->bindParam(':stored_filename', $stored_filename);
            $stmt->bindParam(':file_path', $target_file_relative_for_db);
            $stmt->bindParam(':file_type', $file_type);
            $stmt->bindParam(':file_size', $file_size, PDO::PARAM_INT);
            $stmt->bindParam(':description', $description);

            if ($stmt->execute()) {
                $_SESSION['success_message_file_mgmt'] = "The file <strong>" . htmlspecialchars($original_filename) . "</strong> has been uploaded successfully.";
            } else {
                $_SESSION['error_message_file_mgmt'] = "File uploaded to server, but failed to save file information to database.";
                // Optionally, delete the uploaded file if DB entry fails: unlink($target_file_absolute);
                error_log("File upload DB save failed for: " . $original_filename);
            }
        } catch (PDOException $e) {
            // Optionally, delete the uploaded file if DB entry fails: unlink($target_file_absolute);
            error_log("Database error uploading file: " . $e->getMessage());
            $_SESSION['error_message_file_mgmt'] = "An internal error occurred while saving file information to the database. " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message_file_mgmt'] = "Sorry, there was an error moving your uploaded file. Check permissions on target directory.";
        error_log("File move error for: " . $original_filename . " to " . $target_file_absolute);
    }

    header("Location: " . APP_URL . "/index.php?action=admin_manage_files_load"); // Refresh list
    exit;
}

function handleLoadFilesAndFolders() {
    ensureAdmin(); // Or ensureAdminOrOwner if owners can also manage
    $pdo = getPDOConnection();
    try {
        // Fetch folders
        $stmt_folders = $pdo->query("SELECT id, name, parent_id, created_at FROM folders ORDER BY name ASC");
        $_SESSION['folders_list'] = $stmt_folders->fetchAll(PDO::FETCH_ASSOC);

        // Fetch files (can be refined with folder_id later)
        $stmt_files = $pdo->query(
            "SELECT f.id, f.original_filename, f.subject, f.year, f.uploaded_at, fo.name as folder_name, u.username as uploader_username
             FROM files f
             LEFT JOIN folders fo ON f.folder_id = fo.id
             LEFT JOIN users u ON f.user_id = u.id
             ORDER BY f.uploaded_at DESC"
        );
        $_SESSION['files_list'] = $stmt_files->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Database error loading files/folders: " . $e->getMessage());
        $_SESSION['error_message_file_mgmt'] = "An error occurred while loading files and folders.";
        $_SESSION['folders_list'] = [];
        $_SESSION['files_list'] = [];
    }
    header("Location: " . APP_URL . "/index.php?action=admin_manage_files_view"); // The view page
    exit;
}

function handleCreateFolder() {
    ensureAdmin(); // Or ensureAdminOrOwner
    if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['folder_name'])) {
        $_SESSION['error_message_file_mgmt'] = "Invalid request or folder name not provided.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_files_load");
        exit;
    }

    $folder_name = trim($_POST['folder_name']);
    // $parent_id = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $created_by = $_SESSION['user_id'];

    if (empty($folder_name)) {
        $_SESSION['error_message_file_mgmt'] = "Folder name cannot be empty.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_files_load");
        exit;
    }

    // Optional: Check for duplicate folder name (within the same parent_id if implementing subfolders)
    // For now, checking top-level duplicates if parent_id is not implemented for creation yet
    $pdo = getPDOConnection();
    try {
        $stmt_check = $pdo->prepare("SELECT id FROM folders WHERE name = :name AND parent_id IS NULL"); // Adjust if using parent_id
        $stmt_check->bindParam(':name', $folder_name);
        $stmt_check->execute();
        if ($stmt_check->fetch()) {
            $_SESSION['error_message_file_mgmt'] = "A folder with this name already exists at the top level.";
            header("Location: " . APP_URL . "/index.php?action=admin_manage_files_load");
            exit;
        }

        $stmt_insert = $pdo->prepare("INSERT INTO folders (name, created_by) VALUES (:name, :created_by)"); // Add parent_id if used
        $stmt_insert->bindParam(':name', $folder_name);
        $stmt_insert->bindParam(':created_by', $created_by, PDO::PARAM_INT);
        // $stmt_insert->bindParam(':parent_id', $parent_id, $parent_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

        if ($stmt_insert->execute()) {
            $_SESSION['success_message_file_mgmt'] = "Folder '<strong>" . htmlspecialchars($folder_name) . "</strong>' created successfully.";
        } else {
            $_SESSION['error_message_file_mgmt'] = "Failed to create folder. Please try again.";
        }
    } catch (PDOException $e) {
        error_log("Database error creating folder: " . $e->getMessage());
        $_SESSION['error_message_file_mgmt'] = "An internal error occurred while creating the folder.";
    }
    header("Location: " . APP_URL . "/index.php?action=admin_manage_files_load"); // Refresh the list
    exit;
}

function handleEditStudentDetailsForm() {
    ensureAdmin();
    if (!isset($_GET['user_id'])) {
        $_SESSION['error_message'] = "User ID not provided for editing.";
        header("Location: " . APP_URL . "/index.php?action=admin_manage_students_load");
        exit;
    }
    $user_id = $_GET['user_id'];
    $pdo = getPDOConnection();

    try {
        // Fetch user info (username, index_number, email)
        $stmt_user = $pdo->prepare("SELECT id, username, email, index_number FROM users WHERE id = :user_id AND role = 'student'");
        $stmt_user->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_user->execute();
        $user_info = $stmt_user->fetch(PDO::FETCH_ASSOC);

        if (!$user_info) {
            $_SESSION['error_message'] = "Student not found for editing.";
            header("Location: " . APP_URL . "/index.php?action=admin_manage_students_load");
            exit;
        }

        // Fetch existing student details if any
        $stmt_details = $pdo->prepare("SELECT * FROM student_details WHERE user_id = :user_id");
        $stmt_details->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_details->execute();
        $student_details = $stmt_details->fetch(PDO::FETCH_ASSOC);

        $_SESSION['edit_student_user_info'] = $user_info;
        $_SESSION['edit_student_details_data'] = $student_details ? $student_details : []; // Pass empty array if no details yet

        header("Location: " . APP_URL . "/index.php?action=admin_edit_student_details_page"); // The actual view page
        exit;

    } catch (PDOException $e) {
        error_log("Database error preparing edit form for student ID {$user_id}: " . $e->getMessage());
        $_SESSION['error_message'] = "An error occurred preparing data for editing student ID: " . htmlspecialchars($user_id);
        header("Location: " . APP_URL . "/index.php?action=admin_manage_students_load");
        exit;
    }
}

function handleSaveStudentDetails() {
    ensureAdmin();
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        $_SESSION['error_message_form'] = "Invalid request method.";
        // Attempt to redirect back to form if user_id is available, otherwise to list
        $redirect_url = isset($_POST['user_id']) 
            ? APP_URL . "/index.php?action=admin_edit_student_details_form&user_id=" . $_POST['user_id'] 
            : APP_URL . "/index.php?action=admin_manage_students_load";
        header("Location: " . $redirect_url);
        exit;
    }

    // Validate inputs
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    if (!$user_id) {
        $_SESSION['error_message'] = "Invalid or missing User ID."; // Error for general student list page
        header("Location: " . APP_URL . "/index.php?action=admin_manage_students_load");
        exit;
    }

    // Retrieve all POST data - sanitize as needed
    $full_name = trim($_POST['full_name'] ?? '');
    $class = trim($_POST['class'] ?? '');
    $date_of_birth = !empty($_POST['date_of_birth']) ? trim($_POST['date_of_birth']) : null;
    $address = trim($_POST['address'] ?? '');
    $parent_contact = trim($_POST['parent_contact'] ?? '');
    $exam_performance = trim($_POST['exam_performance'] ?? '');
    $other_information = trim($_POST['other_information'] ?? '');

    // Basic validation example (can be more extensive)
    if (empty($full_name)) {
        $_SESSION['error_message_form'] = "Full name is required.";
        // Need to repopulate user_info for the form when redirecting
        // This is tricky because user_info was set by handleEditStudentDetailsForm
        // A better approach might be to store user_info also in form or re-fetch.
        // For now, just redirecting to form. User might need to click "edit" again from list for full context.
        header("Location: " . APP_URL . "/index.php?action=admin_edit_student_details_form&user_id=" . $user_id);
        exit;
    }
    if ($date_of_birth !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_of_birth)) {
        $_SESSION['error_message_form'] = "Invalid date format for Date of Birth. Use YYYY-MM-DD.";
        header("Location: " . APP_URL . "/index.php?action=admin_edit_student_details_form&user_id=" . $user_id);
        exit;
    }


    $pdo = getPDOConnection();
    try {
        // Check if details already exist for this user_id to decide on INSERT vs UPDATE
        $stmt_check = $pdo->prepare("SELECT id FROM student_details WHERE user_id = :user_id");
        $stmt_check->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_check->execute();
        $existing_details_id = $stmt_check->fetchColumn();

        if ($existing_details_id) {
            // Update existing details
            $sql = "UPDATE student_details SET 
                        full_name = :full_name, class = :class, date_of_birth = :date_of_birth, 
                        address = :address, parent_contact = :parent_contact, 
                        exam_performance = :exam_performance, other_information = :other_information
                    WHERE user_id = :user_id";
        } else {
            // Insert new details
            $sql = "INSERT INTO student_details 
                        (user_id, full_name, class, date_of_birth, address, 
                         parent_contact, exam_performance, other_information) 
                    VALUES 
                        (:user_id, :full_name, :class, :date_of_birth, :address, 
                         :parent_contact, :exam_performance, :other_information)";
        }

        $stmt_save = $pdo->prepare($sql);
        $stmt_save->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_save->bindParam(':full_name', $full_name);
        $stmt_save->bindParam(':class', $class);
        $stmt_save->bindParam(':date_of_birth', $date_of_birth); // PDO handles null correctly
        $stmt_save->bindParam(':address', $address);
        $stmt_save->bindParam(':parent_contact', $parent_contact);
        $stmt_save->bindParam(':exam_performance', $exam_performance);
        $stmt_save->bindParam(':other_information', $other_information);

        if ($stmt_save->execute()) {
            $_SESSION['success_message'] = "Student details saved successfully for User ID: " . htmlspecialchars($user_id);
            // Redirect to the view details page for this student
            header("Location: " . APP_URL . "/index.php?action=admin_view_student_details&user_id=" . $user_id);
            exit;
        } else {
            $_SESSION['error_message_form'] = "Failed to save student details. Please try again.";
            header("Location: " . APP_URL . "/index.php?action=admin_edit_student_details_form&user_id=" . $user_id);
            exit;
        }

    } catch (PDOException $e) {
        error_log("Database error saving student details for User ID {$user_id}: " . $e->getMessage());
        $_SESSION['error_message_form'] = "An internal error occurred while saving details. Error: " . $e->getCode();
        // More specific error for unique constraint violation on user_id if it's an INSERT failing due to prior record
        if ($e->getCode() == '23000' && strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'user_id') !== false) {
             $_SESSION['error_message_form'] = "Error: Details for this user ID might already exist. Try editing instead of inserting if this was a new entry attempt that failed previously.";
        }
        header("Location: " . APP_URL . "/index.php?action=admin_edit_student_details_form&user_id=" . $user_id);
        exit;
    }
}
?>
