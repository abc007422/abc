<?php
// Main entry point for the website

// Start session handling at the very beginning
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../src/includes/db.php';
require_once '../config/config.php'; // Defines APP_URL, DB_NAME, SITE_NAME, MAX_OWNERS etc.

// Initialize the database (create tables if they don't exist)
initializeDatabase(); 

// Basic routing logic
$action = isset($_GET['action']) ? $_GET['action'] : '';
$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
$base_path = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);

// Known actions list
$known_actions = [
    // Student Auth & Features
    'register_student', 'register_student_submit', 'login_student', 'login_student_submit', 
    'student_dashboard', 'student_view_details_submit',
    'student_exam_papers_view', 'student_load_exam_papers',
    'student_timetables_view', 'student_load_timetables', 
    
    // Admin Auth & Features (also accessible by Owner)
    'register_admin', 'register_admin_submit', 'login_admin', 'login_admin_submit', 'admin_dashboard',
    'admin_manage_students_view', 'admin_manage_students_load', 
    'admin_view_student_details_page', 'admin_edit_student_details_page',
    'admin_view_student_details', 'admin_edit_student_details_form', 'admin_save_student_details',
    'admin_manage_files_view', 'admin_manage_files_load', 'admin_create_folder_submit', 'admin_upload_file_submit',
    'admin_manage_events_view', 'admin_edit_event_page_view',
    'admin_manage_events_load', 'admin_create_event_submit',
    'admin_edit_event_form', 'admin_update_event_submit', 'admin_delete_event_submit',
    'admin_manage_timetables_view', 'admin_manage_timetables_load', 
    'admin_upload_timetable_submit', 'admin_delete_timetable_submit',   

    // Owner Features
    'owner_dashboard_view', 'owner_dashboard_load_stats',
    'owner_manage_users_view', 'owner_manage_users_load', 'owner_change_user_role',
    'owner_site_settings_view', 'owner_site_settings_load', 'owner_update_site_name',

    // Common
    'logout', 
    
    // Public Pages
    'public_events_view', 'public_load_events',
    'public_about_us', // New
    'public_contact_us'  // New
];

$is_action_handled = false;

if (!empty($action)) {
    if (in_array($action, $known_actions)) {
        $is_action_handled = true;
        switch ($action) {
            // Student Auth & Features
            case 'register_student': require SRC_PATH . '/views/auth/register_student.php'; break;
            case 'login_student': require SRC_PATH . '/views/auth/login_student.php'; break;
            case 'student_dashboard': require SRC_PATH . '/views/student/dashboard.php'; break;
            case 'student_exam_papers_view': require SRC_PATH . '/views/student/exam_papers.php'; break;
            case 'student_timetables_view': require SRC_PATH . '/views/student/timetables.php'; break; 
            case 'register_student_submit': 
            case 'login_student_submit':
                require SRC_PATH . '/controllers/auth_controller.php'; break; 
            case 'student_view_details_submit': 
            case 'student_load_exam_papers': 
            case 'student_load_timetables': 
                require SRC_PATH . '/controllers/student_controller.php'; break; 

            // Admin Auth & Features (also accessible by Owner)
            case 'register_admin': require SRC_PATH . '/views/auth/register_admin.php'; break;
            case 'login_admin': require SRC_PATH . '/views/auth/login_admin.php'; break; 
            case 'admin_dashboard': require SRC_PATH . '/views/admin/dashboard.php'; break;
            case 'admin_manage_students_view': require SRC_PATH . '/views/admin/manage_students.php'; break;
            case 'admin_view_student_details_page': require SRC_PATH . '/views/admin/view_student_details.php'; break;
            case 'admin_edit_student_details_page': require SRC_PATH . '/views/admin/edit_student_details.php'; break;
            case 'admin_manage_files_view': require SRC_PATH . '/views/admin/manage_files.php'; break;
            case 'admin_manage_events_view': require SRC_PATH . '/views/admin/manage_events.php'; break;
            case 'admin_edit_event_page_view': require SRC_PATH . '/views/admin/edit_event.php'; break;
            case 'admin_manage_timetables_view': require SRC_PATH . '/views/admin/manage_timetables.php'; break; 
            case 'register_admin_submit':
            case 'login_admin_submit': 
                require SRC_PATH . '/controllers/auth_controller.php'; break; 
            case 'admin_manage_students_load':
            case 'admin_view_student_details':
            case 'admin_edit_student_details_form':
            case 'admin_save_student_details':
            case 'admin_manage_files_load':
            case 'admin_create_folder_submit':
            case 'admin_upload_file_submit':
            case 'admin_manage_events_load':
            case 'admin_create_event_submit':
            case 'admin_edit_event_form': 
            case 'admin_update_event_submit':
            case 'admin_delete_event_submit':
            case 'admin_manage_timetables_load': 
            case 'admin_upload_timetable_submit':  
            case 'admin_delete_timetable_submit':  
                require SRC_PATH . '/controllers/admin_controller.php'; break; 

            // Owner Features
            case 'owner_dashboard_view': require SRC_PATH . '/views/owner/dashboard.php'; break;
            case 'owner_manage_users_view': require SRC_PATH . '/views/owner/manage_users.php'; break;
            case 'owner_site_settings_view': require SRC_PATH . '/views/owner/site_settings.php'; break;
            case 'owner_dashboard_load_stats':
            case 'owner_manage_users_load':
            case 'owner_change_user_role':
            case 'owner_site_settings_load':
            case 'owner_update_site_name':
                require SRC_PATH . '/controllers/owner_controller.php'; break; 

            // Common
            case 'logout': require SRC_PATH . '/controllers/logout_controller.php'; break;
            
            // Public Pages
            case 'public_events_view': require SRC_PATH . '/views/public/events.php'; break; 
            case 'public_load_events': require SRC_PATH . '/controllers/public_controller.php'; break; 
            case 'public_about_us': require SRC_PATH . '/views/public/about.php'; break; // New
            case 'public_contact_us': require SRC_PATH . '/views/public/contact.php'; break; // New
            
            default: $is_action_handled = false; break; 
        }
    } else {
        // Action is not empty but not in known_actions
        $is_action_handled = false;
    }
}

// If no action was specified, or if the action was not handled (e.g., unknown action)
if (!$is_action_handled) {
    if (!empty($action)) { // An unknown action was specified
        http_response_code(404);
        require SRC_PATH . '/views/public/404.php';
    } else { // No action specified, default to URI routing
        $route = str_replace($base_path, '', $request_uri);
        $route = strtok($route, '?'); // Remove query string

        switch ($route) {
            case '/':
            case '':
                require SRC_PATH . '/views/public/home.php';
                break;
            case '/news.php': // Example
                require SRC_PATH . '/views/public/home.php'; break; // Placeholder
            case '/events': 
                 $_GET['action'] = 'public_load_events'; 
                 require SRC_PATH . '/controllers/public_controller.php';
                break;
            case '/about': // New URI route
                 require SRC_PATH . '/views/public/about.php';
                 break;
            case '/contact': // New URI route
                 require SRC_PATH . '/views/public/contact.php';
                 break;
            default:
                http_response_code(404);
                require SRC_PATH . '/views/public/404.php';
                break;
        }
    }
}
?>
