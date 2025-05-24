<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    define('APP_URL', '/');
    define('SITE_NAME', 'Sandalanka Central College');
}

$pageTitle = "Contact Us";
ob_start();
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h1 class="card-title mb-0"><?php echo htmlspecialchars($pageTitle); ?> - <?php echo htmlspecialchars(SITE_NAME); ?></h1>
                </div>
                <div class="card-body">
                    <p class="lead">We'd love to hear from you! Please find our contact details below or use the contact form to send us a message.</p>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h3>Our Address</h3>
                            <address>
                                <strong><?php echo htmlspecialchars(SITE_NAME); ?></strong><br>
                                [Street Address Placeholder, e.g., Main Street],<br>
                                Sandalanka, [Postal Code Placeholder, e.g., 60170],<br>
                                Sri Lanka.<br>
                                <abbr title="Phone">P:</abbr> (+94) XX-XXXXXXX (Placeholder)<br>
                                <abbr title="Email">E:</abbr> <a href="mailto:info@sandalankacc.lk">info@sandalankacc.lk</a> (Placeholder)
                            </address>

                            <h3 class="mt-4">Office Hours (Placeholder)</h3>
                            <p>
                                Monday - Friday: 8:00 AM - 4:00 PM<br>
                                Saturday: 8:00 AM - 12:00 PM (Office Only)<br>
                                Sunday & Public Holidays: Closed
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h3>Location Map (Placeholder)</h3>
                            <!-- In a real site, embed an iframe from Google Maps or similar -->
                            <div class="bg-light border rounded d-flex align-items-center justify-content-center" style="height: 300px;">
                                <p class="text-muted">Google Map Placeholder Image/Embed Here</p>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h3>Send Us a Message (Placeholder Form)</h3>
                    <form action="#" method="POST">
                        <div class="mb-3">
                            <label for="contact_name" class="form-label">Your Name:</label>
                            <input type="text" class="form-control" id="contact_name" name="contact_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="contact_email" class="form-label">Your Email:</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" required>
                        </div>
                        <div class="mb-3">
                            <label for="contact_subject" class="form-label">Subject:</label>
                            <input type="text" class="form-control" id="contact_subject" name="contact_subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="contact_message" class="form-label">Message:</label>
                            <textarea class="form-control" id="contact_message" name="contact_message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" disabled>Submit Message (Disabled)</button>
                        <small class="d-block mt-2 text-muted"><em>Note: This contact form is a placeholder and not functional in this version.</em></small>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$viewContent = ob_get_clean();
require_once __DIR__ . '/../layouts/main_layout.php';
?>
