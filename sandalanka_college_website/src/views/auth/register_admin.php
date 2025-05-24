<?php
// Ensure config is available for APP_URL and ADMIN_ACCESS_KEY
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    define('APP_URL', '/'); 
    define('ADMIN_ACCESS_KEY', 'DEFAULT_KEY'); // Fallback
    error_log("Config file not found for register_admin.php");
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Admin Registration";
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5"> <!-- Slightly wider for more fields -->
        <div class="card mt-5">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Admin Registration</h2>

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

                <form action="<?php echo APP_URL; ?>/index.php?action=register_admin_submit" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username:</label>
                        <input type="text" class="form-control" id="username" name="username" required value="<?php echo isset($_SESSION['form_data']['username']) ? htmlspecialchars($_SESSION['form_data']['username']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Repeat Password:</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="access_key" class="form-label">Access Key:</label>
                        <input type="password" class="form-control" id="access_key" name="access_key" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Register Admin</button>
                    </div>
                </form>
                <p class="text-center mt-3">
                    Already have an admin account? <a href="<?php echo APP_URL; ?>/index.php?action=login_admin">Login here</a>.
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
