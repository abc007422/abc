<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../../config/config.php'; // For APP_URL, MAX_OWNERS

// Function to ensure only owners can access controller actions
function ensureOwner() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
        $_SESSION['error_message'] = "You must be logged in as an owner to perform this action.";
        // Redirect to a generic login or admin login if appropriate
        header("Location: " . APP_URL . "/index.php?action=login_admin"); 
        exit;
    }
}

function handleOwnerDashboardLoadStats() {
    ensureOwner();
    $pdo = getPDOConnection();
    $stats = [
        'total_users' => 0,
        'total_students' => 0,
        'total_admins' => 0,
        'total_owners' => 0,
    ];

    try {
        $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['total_students'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
        $stats['total_admins'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
        $stats['total_owners'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'owner'")->fetchColumn();
        
        $_SESSION['owner_stats'] = $stats;

    } catch (PDOException $e) {
        error_log("Database error loading owner dashboard stats: " . $e->getMessage());
        $_SESSION['error_message_owner'] = "An error occurred while loading site statistics.";
        $_SESSION['owner_stats'] = $stats; // Send default/zeroed stats
    }
    header("Location: " . APP_URL . "/index.php?action=owner_dashboard_view");
    exit;
}


// Routing within this controller
if (isset($_GET['action'])) {
    $owner_action = $_GET['action'];
    switch ($owner_action) {
        case 'owner_dashboard_load_stats':
            handleOwnerDashboardLoadStats();
            break;
        case 'owner_manage_users_load':
            handleListAllUsers();
            break;
        // case 'owner_site_settings_load':
        // case 'owner_update_site_name':
        // case 'owner_change_user_role':
    }
}

function handleListAllUsers() {
    ensureOwner();
    $pdo = getPDOConnection();
    try {
        // Fetch all users
        $stmt_users = $pdo->query(
            "SELECT id, username, email, role, index_number, created_at 
             FROM users 
             ORDER BY role ASC, id ASC"
        );
        $_SESSION['all_users_list'] = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

        // Fetch current number of owners for MAX_OWNERS check in the view
        $_SESSION['current_owners_count'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'owner'")->fetchColumn();

    } catch (PDOException $e) {
        error_log("Database error loading all users list: " . $e->getMessage());
        $_SESSION['error_message_user_mgmt'] = "An error occurred while loading the user list.";
        $_SESSION['all_users_list'] = [];
        $_SESSION['current_owners_count'] = 0;
    }
    header("Location: " . APP_URL . "/index.php?action=owner_manage_users_view"); // The view page
    exit;
}

function handleLoadSiteSettingsForm() {
    ensureOwner();
    // In this demo, SITE_NAME is from config.php. A real app might use a DB or JSON.
    // We pass it to the view via session for consistency if we were to change it dynamically (even if just for show in this version).
    $_SESSION['site_settings_site_name'] = defined('SITE_NAME') ? SITE_NAME : 'Default Site Name';
    header("Location: " . APP_URL . "/index.php?action=owner_site_settings_view");
    exit;
}

function handleUpdateSiteName() {
    ensureOwner();
    if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['site_name'])) {
        $_SESSION['error_message_settings'] = "Invalid request or site name not provided.";
        header("Location: " . APP_URL . "/index.php?action=owner_site_settings_load");
        exit;
    }

    $new_site_name = trim($_POST['site_name']);
    if (empty($new_site_name)) {
        $_SESSION['error_message_settings'] = "Site name cannot be empty.";
        header("Location: " . APP_URL . "/index.php?action=owner_site_settings_load");
        exit;
    }

    // --- IMPORTANT ---
    // In a real application, updating config.php programmatically is highly risky and generally bad practice.
    // You would typically update a database setting, a .env file, or a dedicated JSON/YAML config file.
    // For this project, we will simulate the change by updating a session variable and informing the user
    // that a manual config.php change would be needed for persistence.
    
    // Simulate update for display purposes:
    $_SESSION['site_settings_site_name'] = $new_site_name; // Show the new name on redirect
    $_SESSION['success_message_settings'] = "Site name updated to '<strong>" . htmlspecialchars($new_site_name) . "</strong>' for this session. For permanent change, config/config.php needs manual update (demonstration only).";
    
    // To make it appear in the layout immediately for this session, we could also update the SITE_NAME constant
    // if it's not already defined, or if we redefine constants (which is bad practice).
    // For simplicity, we'll just rely on the session value being picked up by the site_settings view and potentially layout if adapted.
    // The most straightforward way for the layout to reflect this without redefining constants would be for the layout
    // to check for $_SESSION['dynamic_site_name'] and use it if available, otherwise use the SITE_NAME constant.

    header("Location: " . APP_URL . "/index.php?action=owner_site_settings_load");
    exit;
}

function handleChangeUserRole() {
    ensureOwner();
    if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['user_id_to_change'], $_POST['new_role'])) {
        $_SESSION['error_message_user_mgmt'] = "Invalid request or missing data.";
        header("Location: " . APP_URL . "/index.php?action=owner_manage_users_load");
        exit;
    }

    $user_id_to_change = filter_input(INPUT_POST, 'user_id_to_change', FILTER_VALIDATE_INT);
    $new_role = trim($_POST['new_role']);
    $current_user_id = $_SESSION['user_id'];

    if (!$user_id_to_change || !in_array($new_role, ['student', 'admin', 'owner'])) {
        $_SESSION['error_message_user_mgmt'] = "Invalid user ID or role specified.";
        header("Location: " . APP_URL . "/index.php?action=owner_manage_users_load");
        exit;
    }

    // Prevent owner from changing their own role via this form
    if ($user_id_to_change == $current_user_id) {
        $_SESSION['error_message_user_mgmt'] = "You cannot change your own role using this form.";
        header("Location: " . APP_URL . "/index.php?action=owner_manage_users_load");
        exit;
    }

    $pdo = getPDOConnection();
    try {
        // Get current role of the user being changed
        $stmt_current_role = $pdo->prepare("SELECT role FROM users WHERE id = :user_id");
        $stmt_current_role->bindParam(':user_id', $user_id_to_change, PDO::PARAM_INT);
        $stmt_current_role->execute();
        $current_role_of_target_user = $stmt_current_role->fetchColumn();

        if (!$current_role_of_target_user) {
            $_SESSION['error_message_user_mgmt'] = "User not found.";
            header("Location: " . APP_URL . "/index.php?action=owner_manage_users_load");
            exit;
        }

        // Check MAX_OWNERS constraint if promoting to 'owner'
        if ($new_role === 'owner' && $current_role_of_target_user !== 'owner') {
            $stmt_owner_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'owner'");
            $current_owner_count = $stmt_owner_count->fetchColumn();
            if ($current_owner_count >= MAX_OWNERS) {
                $_SESSION['error_message_user_mgmt'] = "Cannot promote to Owner. The maximum number of owners (" . MAX_OWNERS . ") has been reached.";
                header("Location: " . APP_URL . "/index.php?action=owner_manage_users_load");
                exit;
            }
        }

        // Prevent demoting the last owner (this is a simplified check; more robust logic might be needed if multiple owners can demote each other)
        if ($current_role_of_target_user === 'owner' && $new_role !== 'owner') {
            $stmt_owner_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'owner'");
            $current_owner_count = $stmt_owner_count->fetchColumn();
            if ($current_owner_count <= 1) {
                 // This logic implies that if there's only one owner, they cannot be demoted by another owner.
                 // If it's the owner themselves trying to demote, that's blocked by the self-change check earlier.
                 // If this means "an owner cannot demote the *sole existing* owner", this is correct.
                $_SESSION['error_message_user_mgmt'] = "Cannot demote the last owner. The site must have at least one owner.";
                header("Location: " . APP_URL . "/index.php?action=owner_manage_users_load");
                exit;
            }
        }
        
        // Proceed with role change
        $stmt_update_role = $pdo->prepare("UPDATE users SET role = :new_role WHERE id = :user_id");
        $stmt_update_role->bindParam(':new_role', $new_role);
        $stmt_update_role->bindParam(':user_id', $user_id_to_change, PDO::PARAM_INT);

        if ($stmt_update_role->execute()) {
            $_SESSION['success_message_user_mgmt'] = "User role updated successfully.";
        } else {
            $_SESSION['error_message_user_mgmt'] = "Failed to update user role.";
        }

    } catch (PDOException $e) {
        error_log("Database error changing user role: " . $e->getMessage());
        $_SESSION['error_message_user_mgmt'] = "An internal error occurred while changing the user role.";
    }
    header("Location: " . APP_URL . "/index.php?action=owner_manage_users_load");
    exit;
}
?>
