<?php
// Ensure session is started at the very beginning where it's needed.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php'; // Path to your database connection script
require_once __DIR__ . '/../../config/config.php'; // For APP_URL, etc.

function handleStudentRegistration() {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!isset($_POST['index_number'], $_POST['password'], $_POST['confirm_password'])) {
            $_SESSION['error_message'] = "All fields are required.";
            header("Location: " . APP_URL . "/index.php?action=register_student");
            exit;
        }

        $index_number = trim($_POST['index_number']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($index_number)) {
            $_SESSION['error_message'] = "Index number is required.";
            header("Location: " . APP_URL . "/index.php?action=register_student");
            exit;
        }
        if (empty($password)) {
            $_SESSION['error_message'] = "Password is required.";
            header("Location: " . APP_URL . "/index.php?action=register_student");
            exit;
        }
        if ($password !== $confirm_password) {
            $_SESSION['error_message'] = "Passwords do not match.";
            header("Location: " . APP_URL . "/index.php?action=register_student");
            exit;
        }
        if (strlen($password) < 8) {
            $_SESSION['error_message'] = "Password must be at least 8 characters long.";
            header("Location: " . APP_URL . "/index.php?action=register_student");
            exit;
        }

        $pdo = getPDOConnection();
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR index_number = :index_number");
            $stmt->bindParam(':username', $index_number);
            $stmt->bindParam(':index_number', $index_number);
            $stmt->execute();

            if ($stmt->fetch()) {
                $_SESSION['error_message'] = "This index number is already registered.";
                header("Location: " . APP_URL . "/index.php?action=register_student");
                exit;
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $pdo->prepare(
                "INSERT INTO users (username, password, role, index_number) VALUES (:username, :password, 'student', :index_number)"
            );
            $insert_stmt->bindParam(':username', $index_number);
            $insert_stmt->bindParam(':password', $hashed_password);
            $insert_stmt->bindParam(':index_number', $index_number);
            
            if ($insert_stmt->execute()) {
                $_SESSION['success_message'] = "Registration successful! You can now login.";
                header("Location: " . APP_URL . "/index.php?action=login_student");
                exit;
            } else {
                $_SESSION['error_message'] = "Registration failed. Please try again.";
                error_log("Student registration failed for index: " . $index_number);
                header("Location: " . APP_URL . "/index.php?action=register_student");
                exit;
            }
        } catch (PDOException $e) {
            error_log("Database error during student registration: " . $e->getMessage());
            $_SESSION['error_message'] = "An internal error occurred. Please try again later.";
            header("Location: " . APP_URL . "/index.php?action=register_student");
            exit;
        }
    } else {
        header("Location: " . APP_URL . "/index.php?action=register_student");
        exit;
    }
}

function handleAdminRegistration() {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $_SESSION['form_data'] = $_POST;

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $access_key_input = trim($_POST['access_key']);

        if (!$email) {
            $_SESSION['error_message'] = "Invalid email format.";
            header("Location: " . APP_URL . "/index.php?action=register_admin");
            exit;
        }
        if (empty($username)) {
            $_SESSION['error_message'] = "Username is required.";
            header("Location: " . APP_URL . "/index.php?action=register_admin");
            exit;
        }
        if (strlen($password) < 8) {
            $_SESSION['error_message'] = "Password must be at least 8 characters long.";
            header("Location: " . APP_URL . "/index.php?action=register_admin");
            exit;
        }
        if ($password !== $confirm_password) {
            $_SESSION['error_message'] = "Passwords do not match.";
            header("Location: " . APP_URL . "/index.php?action=register_admin");
            exit;
        }
        if (empty($access_key_input)) {
            $_SESSION['error_message'] = "Access key is required.";
            header("Location: " . APP_URL . "/index.php?action=register_admin");
            exit;
        }
        if (!defined('ADMIN_ACCESS_KEY') || $access_key_input !== ADMIN_ACCESS_KEY) {
            $_SESSION['error_message'] = "Invalid access key.";
            header("Location: " . APP_URL . "/index.php?action=register_admin");
            exit;
        }

        $pdo = getPDOConnection();
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR username = :username");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            if ($stmt->fetch()) {
                $_SESSION['error_message'] = "Email or username already exists.";
                header("Location: " . APP_URL . "/index.php?action=register_admin");
                exit;
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $pdo->prepare(
                "INSERT INTO users (username, email, password, role, access_key) 
                 VALUES (:username, :email, :password, 'admin', :access_key)"
            );
            $insert_stmt->bindParam(':username', $username);
            $insert_stmt->bindParam(':email', $email);
            $insert_stmt->bindParam(':password', $hashed_password);
            $insert_stmt->bindParam(':access_key', $access_key_input); 

            if ($insert_stmt->execute()) {
                unset($_SESSION['form_data']); 
                $_SESSION['success_message'] = "Admin registration successful! You can now login.";
                header("Location: " . APP_URL . "/index.php?action=login_admin");
                exit;
            } else {
                $_SESSION['error_message'] = "Admin registration failed. Please try again.";
                error_log("Admin registration failed for email: " . $email);
                header("Location: " . APP_URL . "/index.php?action=register_admin");
                exit;
            }
        } catch (PDOException $e) {
            error_log("Database error during admin registration: " . $e->getMessage());
            $_SESSION['error_message'] = "An internal error occurred. Please try again later.";
            header("Location: " . APP_URL . "/index.php?action=register_admin");
            exit;
        }
    } else {
        header("Location: " . APP_URL . "/index.php?action=register_admin");
        exit;
    }
}

function handleAdminLogin() { // Used for both Admin and Owner login
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $_SESSION['form_data'] = $_POST; 

        $email_or_username = trim($_POST['email_or_username']);
        $password = $_POST['password'];
        $access_key_input = trim($_POST['access_key']);

        if (empty($email_or_username) || empty($password) || empty($access_key_input)) {
            $_SESSION['error_message'] = "All fields are required for staff login.";
            header("Location: " . APP_URL . "/index.php?action=login_admin");
            exit;
        }

        if (!defined('ADMIN_ACCESS_KEY') || $access_key_input !== ADMIN_ACCESS_KEY) {
            $_SESSION['error_message'] = "Invalid access key.";
            header("Location: " . APP_URL . "/index.php?action=login_admin");
            exit;
        }

        $pdo = getPDOConnection();
        try {
            $login_identifier_type = filter_var($email_or_username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            // Allow login for 'admin' or 'owner' roles
            $stmt = $pdo->prepare("SELECT id, username, email, password, role FROM users WHERE {$login_identifier_type} = :identifier AND (role = 'admin' OR role = 'owner')");
            $stmt->bindParam(':identifier', $email_or_username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true); 

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                unset($_SESSION['form_data']);

                if ($user['role'] === 'owner') {
                    header("Location: " . APP_URL . "/index.php?action=owner_dashboard_view");
                } else { // 'admin' role
                    header("Location: " . APP_URL . "/index.php?action=admin_dashboard");
                }
                exit;
            } else {
                $_SESSION['error_message'] = "Invalid credentials, not an admin/owner, or incorrect access key.";
                header("Location: " . APP_URL . "/index.php?action=login_admin");
                exit;
            }
        } catch (PDOException $e) {
            error_log("Database error during admin/owner login: " . $e->getMessage());
            $_SESSION['error_message'] = "An internal error occurred. Please try again later.";
            header("Location: " . APP_URL . "/index.php?action=login_admin");
            exit;
        }
    } else {
        header("Location: " . APP_URL . "/index.php?action=login_admin");
        exit;
    }
}

function handleStudentLogin() {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!isset($_POST['index_number'], $_POST['password'])) {
            $_SESSION['error_message'] = "Index number and password are required.";
            header("Location: " . APP_URL . "/index.php?action=login_student");
            exit;
        }

        $index_number = trim($_POST['index_number']);
        $password = $_POST['password'];

        if (empty($index_number)) {
            $_SESSION['error_message'] = "Index number is required.";
            $_SESSION['form_data']['index_number'] = $index_number;
            header("Location: " . APP_URL . "/index.php?action=login_student");
            exit;
        }
        if (empty($password)) {
            $_SESSION['error_message'] = "Password is required.";
            $_SESSION['form_data']['index_number'] = $index_number; 
            header("Location: " . APP_URL . "/index.php?action=login_student");
            exit;
        }

        $pdo = getPDOConnection();
        try {
            $stmt = $pdo->prepare("SELECT id, username, password, role, index_number FROM users WHERE index_number = :identifier AND role = 'student'");
            $stmt->bindParam(':identifier', $index_number);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username']; 
                $_SESSION['role'] = $user['role'];
                $_SESSION['index_number'] = $user['index_number'];
                
                if(isset($_SESSION['form_data'])) unset($_SESSION['form_data']);

                header("Location: " . APP_URL . "/index.php?action=student_dashboard");
                exit;
            } else {
                $_SESSION['error_message'] = "Invalid index number or password.";
                $_SESSION['form_data']['index_number'] = $index_number; 
                header("Location: " . APP_URL . "/index.php?action=login_student");
                exit;
            }
        } catch (PDOException $e) {
            error_log("Database error during student login: " . $e->getMessage());
            $_SESSION['error_message'] = "An internal error occurred. Please try again later.";
            header("Location: " . APP_URL . "/index.php?action=login_student");
            exit;
        }
    } else {
        header("Location: " . APP_URL . "/index.php?action=login_student");
        exit;
    }
}

// This controller now handles multiple actions based on the 'action' GET parameter.
// index.php will require this file, and this internal routing will take over.
if (isset($_GET['action'])) {
    $controller_action = $_GET['action'];

    // Ensure session is started for all controller actions (already at top)
    // if (session_status() == PHP_SESSION_NONE) {
    //     session_start();
    // }

    switch ($controller_action) {
        case 'register_student_submit':
            handleStudentRegistration();
            break;
        case 'login_student_submit':
            handleStudentLogin();
            break;
        case 'register_admin_submit':
            handleAdminRegistration();
            break;
        case 'login_admin_submit': // Handles both admin and owner login attempts
            handleAdminLogin();
            break;
        default:
            // If action is not recognized by this controller, it does nothing.
            // index.php handles general 404s for completely unknown actions.
            break;
    }
}
?>
