<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../../config/config.php';

function handleViewStudentDetails() {
    // Check if user is logged in and is a student
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
        $_SESSION['error_message'] = "You must be logged in as a student to perform this action.";
        header("Location: " . APP_URL . "/index.php?action=login_student");
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!isset($_POST['provided_index_number'])) {
            $_SESSION['student_details_error'] = "Please provide your index number.";
            header("Location: " . APP_URL . "/index.php?action=student_dashboard");
            exit;
        }

        $provided_index_number = trim($_POST['provided_index_number']);
        $user_id_from_session = $_SESSION['user_id'];
        $stored_index_number = $_SESSION['index_number']; 

        if (empty($provided_index_number)) {
            $_SESSION['student_details_error'] = "Index number cannot be empty.";
            header("Location: " . APP_URL . "/index.php?action=student_dashboard");
            exit;
        }

        if ($provided_index_number !== $stored_index_number) {
            $_SESSION['student_details_error'] = "The provided index number does not match our records for your account. Please try again.";
            header("Location: " . APP_URL . "/index.php?action=student_dashboard");
            exit;
        }

        $pdo = getPDOConnection();
        try {
            $stmt = $pdo->prepare("SELECT * FROM student_details WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id_from_session, PDO::PARAM_INT);
            $stmt->execute();
            $details = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($details) {
                $_SESSION['student_details_data'] = $details;
            } else {
                $_SESSION['student_details_data'] = []; 
            }
            header("Location: " . APP_URL . "/index.php?action=student_dashboard");
            exit;

        } catch (PDOException $e) {
            error_log("Database error fetching student details: " . $e->getMessage());
            $_SESSION['student_details_error'] = "An internal error occurred while fetching your details. Please try again later.";
            header("Location: " . APP_URL . "/index.php?action=student_dashboard");
            exit;
        }
    } else {
        header("Location: " . APP_URL . "/index.php?action=student_dashboard");
        exit;
    }
}

function handleLoadExamPapersForStudent() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
        $_SESSION['error_message'] = "You must be logged in as a student to view this page.";
        header("Location: " . APP_URL . "/index.php?action=login_student");
        exit;
    }

    $pdo = getPDOConnection();
    try {
        $search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : null;
        $search_param = !empty($search_query) ? "%{$search_query}%" : null;

        // Fetch all top-level folders. Folders themselves are not searched directly,
        // but their visibility in the view might depend on whether they contain search results.
        $stmt_folders = $pdo->query("SELECT id, name FROM folders WHERE parent_id IS NULL ORDER BY name ASC");
        $_SESSION['student_folders_list'] = $stmt_folders->fetchAll(PDO::FETCH_ASSOC);

        $files_by_folder = [];
        if (!empty($_SESSION['student_folders_list'])) {
            foreach ($_SESSION['student_folders_list'] as $folder) {
                $sql_files_in_folder = "SELECT id, original_filename, subject, year, description, file_path, uploaded_at 
                                       FROM files 
                                       WHERE folder_id = :folder_id";
                $params_files_in_folder = [':folder_id' => $folder['id']];

                if ($search_param) {
                    $sql_files_in_folder .= " AND (original_filename LIKE :search OR subject LIKE :search OR description LIKE :search OR CAST(year AS CHAR) LIKE :search_year_like)";
                    $params_files_in_folder[':search'] = $search_param;
                    $params_files_in_folder[':search_year_like'] = $search_param; // For "2023" in "Math 2023"
                }
                $sql_files_in_folder .= " ORDER BY year DESC, subject ASC, original_filename ASC";
                
                $stmt_files = $pdo->prepare($sql_files_in_folder);
                $stmt_files->execute($params_files_in_folder);
                $files_by_folder[$folder['id']] = $stmt_files->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        $_SESSION['student_files_by_folder'] = $files_by_folder;
        
        // Fetch uncategorized files (folder_id IS NULL), applying search filter if provided
        $sql_uncategorized_files = "SELECT id, original_filename, subject, year, description, file_path, uploaded_at 
                                   FROM files 
                                   WHERE folder_id IS NULL";
        $params_uncategorized = [];
        if ($search_param) {
            $sql_uncategorized_files .= " AND (original_filename LIKE :search OR subject LIKE :search OR description LIKE :search OR CAST(year AS CHAR) LIKE :search_year_like)";
            $params_uncategorized[':search'] = $search_param;
            $params_uncategorized[':search_year_like'] = $search_param;
        }
        $sql_uncategorized_files .= " ORDER BY year DESC, subject ASC, original_filename ASC";

        $stmt_uncategorized = $pdo->prepare($sql_uncategorized_files);
        $stmt_uncategorized->execute($params_uncategorized);
        $_SESSION['student_uncategorized_files'] = $stmt_uncategorized->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Database error loading exam papers for student: " . $e->getMessage());
        $_SESSION['error_message_papers'] = "An error occurred while fetching exam papers.";
        $_SESSION['student_folders_list'] = [];
        $_SESSION['student_files_by_folder'] = [];
        $_SESSION['student_uncategorized_files'] = [];
    }
    // Redirect to the view page, search query GET parameters will persist if the form used GET
    header("Location: " . APP_URL . "/index.php?action=student_exam_papers_view" . ($search_query ? "&search_query=" . urlencode($search_query) : ""));
    exit;
}

// Simple router for actions within this controller, called by index.php
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    switch ($action) {
        case 'student_view_details_submit':
            handleViewStudentDetails();
            break;
        case 'student_load_exam_papers': // Action to load data for the student exam papers view (handles search)
            handleLoadExamPapersForStudent();
            break;
        case 'student_load_timetables':
            handleListTimetablesStudent();
            break;
        // Add other student-specific actions here
    }
}

function handleListTimetablesStudent() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
        $_SESSION['error_message'] = "You must be logged in as a student to view this page.";
        header("Location: " . APP_URL . "/index.php?action=login_student");
        exit;
    }

    $pdo = getPDOConnection();
    $timetables_by_grade_class = [];
    try {
        $stmt = $pdo->query(
            "SELECT id, grade, class_name, original_filename, stored_filename, file_path, file_type, description, uploaded_at 
             FROM timetables 
             ORDER BY grade ASC, class_name ASC, uploaded_at DESC"
        );
        $all_timetables = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($all_timetables as $tt) {
            if (!isset($timetables_by_grade_class[$tt['grade']])) {
                $timetables_by_grade_class[$tt['grade']] = [];
            }
            if (!isset($timetables_by_grade_class[$tt['grade']][$tt['class_name']])) {
                $timetables_by_grade_class[$tt['grade']][$tt['class_name']] = [];
            }
            // Since a class might have multiple timetable files (e.g. different versions or terms)
            // we store them as a list. The view currently shows the latest one primarily.
            // If multiple are needed, the view logic would change. For now, this structure is fine.
            $timetables_by_grade_class[$tt['grade']][$tt['class_name']][] = $tt;
        }
        
        // Sort grades: Extract grade numbers, sort, then rebuild keys if necessary or ensure view sorts them.
        // For simplicity, if grades are stored like "Grade 6", "Grade 10", they will sort alphabetically okay.
        // If they are "6", "10", numeric sort is better. PHP's array key sort might be sufficient here.
        // ksort($timetables_by_grade_class); // Sorts by grade name
        // The view already sorts grade keys before display.

        $_SESSION['timetables_by_grade_class'] = $timetables_by_grade_class;

    } catch (PDOException $e) {
        error_log("Database error loading timetables for student: " . $e->getMessage());
        $_SESSION['error_message_student_timetable'] = "An error occurred while fetching timetables.";
        $_SESSION['timetables_by_grade_class'] = [];
    }
    header("Location: " . APP_URL . "/index.php?action=student_timetables_view");
    exit;
}
?>
