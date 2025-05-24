<?php
// Ensure config is available for APP_URL
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    define('APP_URL', '/'); 
    error_log("Config file not found for login_admin.php");
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Staff Login"; // Changed title to Staff Login
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card mt-5">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Staff Login (Admin/Owner)</h2>

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

                <form action="<?php echo APP_URL; ?>/index.php?action=login_admin_submit" method="POST">
                    <div class="mb-3">
                        <label for="email_or_username" class="form-label">Email or Username:</label>
                        <input type="text" class="form-control" id="email_or_username" name="email_or_username" required value="<?php echo isset($_SESSION['form_data']['email_or_username']) ? htmlspecialchars($_SESSION['form_data']['email_or_username']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="access_key" class="form-label">Access Key:</label>
                        <input type="password" class="form-control" id="access_key" name="access_key" required>
                        <div class="form-text">Required for admin/owner login.</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
                <p class="text-center mt-3">
                    Want to register a new admin? <a href="<?php echo APP_URL; ?>/index.php?action=register_admin">Register here</a>.
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
