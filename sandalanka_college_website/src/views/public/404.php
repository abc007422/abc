<?php
$pageTitle = "Page Not Found"; // Set the page title

// Start output buffering to capture content for the layout
ob_start();
?>

<h2>404 - Page Not Found</h2>
<p>Sorry, the page you are looking for does not exist or has been moved.</p>
<p>You can <a href="<?php echo APP_URL; ?>/">return to the homepage</a> or try searching for what you need.</p>

<?php
// Get the buffered content
$viewContent = ob_get_clean();

// Include the main layout
require_once __DIR__ . '/../layouts/main_layout.php';
?>
