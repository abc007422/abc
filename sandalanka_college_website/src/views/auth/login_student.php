<?php
// Ensure config is available for APP_URL
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    // Fallback or error if config is not found
    define('APP_URL', '/'); // Default to root if not defined, adjust as necessary
    error_log("Config file not found for login_student.php");
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Student Login";
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card mt-5">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Student Login</h2>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo APP_URL; ?>/index.php?action=login_student_submit" method="POST">
                    <div class="mb-3">
                        <label for="index_number" class="form-label">Index Number:</label>
                        <input type="text" class="form-control" id="index_number" name="index_number" required value="<?php echo isset($_SESSION['form_data']['index_number']) ? htmlspecialchars($_SESSION['form_data']['index_number']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
                <p class="text-center mt-3">
                    Don't have an account? <a href="<?php echo APP_URL; ?>/index.php?action=register_student">Register here</a>.
                </p>
            </div>
        </div>
    </div>
</div>


<?php
// Clear form data after displaying
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}

$viewContent = ob_get_clean();
require_once __DIR__ . '/../layouts/main_layout.php'; // Adjusted path
?>
