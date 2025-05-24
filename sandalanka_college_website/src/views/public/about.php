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

$pageTitle = "About Us";
ob_start();
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h1 class="card-title mb-0"><?php echo htmlspecialchars($pageTitle); ?> - <?php echo htmlspecialchars(SITE_NAME); ?></h1>
                </div>
                <div class="card-body">
                    <h2 class="mt-3">Our History</h2>
                    <p class="lead">
                        Sandalanka Central College, nestled in the heart of [Specify Region/Town if known, otherwise use generic term like "our community"], has a rich history of providing quality education and fostering academic excellence for decades. 
                        Established in [Year - placeholder, e.g., 19XX], our institution has grown from humble beginnings into a leading educational center in the region.
                    </p>
                    <p>
                        Over the years, Sandalanka Central College has been dedicated to nurturing young minds, equipping them with knowledge, skills, and values necessary to thrive in an ever-changing world. 
                        We are proud of our alumni who have gone on to achieve great success in various fields, contributing significantly to society.
                    </p>

                    <h2 class="mt-4">Our Mission</h2>
                    <p>
                        Our mission is to provide a holistic and inclusive learning environment that empowers students to reach their full potential. We aim to cultivate critical thinking, creativity, and a lifelong passion for learning, while instilling strong moral and ethical values.
                    </p>
                    <ul>
                        <li>To offer a comprehensive curriculum that meets diverse learning needs.</li>
                        <li>To foster a culture of respect, responsibility, and resilience.</li>
                        <li>To provide state-of-the-art facilities and resources that support effective teaching and learning.</li>
                        <li>To encourage active participation in co-curricular and extracurricular activities for overall development.</li>
                        <li>To build strong partnerships with parents, alumni, and the wider community.</li>
                    </ul>

                    <h2 class="mt-4">Our Vision</h2>
                    <p>
                        To be a center of educational excellence, recognized for producing well-rounded, confident, and socially responsible citizens who are prepared to make positive contributions to the global community.
                    </p>

                    <h2 class="mt-4">Principal's Message (Placeholder)</h2>
                    <p>
                        "Welcome to Sandalanka Central College! We are committed to providing a safe, supportive, and stimulating environment where every student can learn and grow. Our dedicated staff works tirelessly to ensure that our students receive the best possible education. We believe in the potential of each student and strive to help them achieve their dreams. Join us in our journey of academic and personal discovery." 
                        <br><em>- [Principal's Name Placeholder], Principal</em>
                    </p>
                </div>
                <div class="card-footer text-muted">
                    Learn more by exploring our website or contacting us directly.
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$viewContent = ob_get_clean();
require_once __DIR__ . '/../layouts/main_layout.php';
?>
