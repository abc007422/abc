<?php
$pageTitle = "Home"; // Set the page title for the home page

// Start output buffering to capture content for the layout
ob_start();

// In a real application, this data would come from a database via a controller
$latest_news = [
    ['id' => 1, 'title' => 'School Reopening Dates Announced', 'date' => '2024-07-15', 'summary' => 'The school will reopen for the new term on August 1st, 2024. All students are requested to attend.'],
    ['id' => 2, 'title' => 'Annual Sports Meet Highlights', 'date' => '2024-07-10', 'summary' => 'Our annual sports meet was a grand success with Blue House emerging as champions.'],
    ['id' => 3, 'title' => 'Inter-School Debate Competition Winners', 'date' => '2024-07-05', 'summary' => 'Our debate team won the Zonal Inter-School Debate Competition. Congratulations!'],
];

$upcoming_events_home = [
    ['id' => 1, 'title' => 'Parent-Teacher Meeting', 'date' => '2024-08-25', 'time' => '10:00 AM', 'location' => 'School Main Hall'],
    ['id' => 2, 'title' => 'Science Exhibition', 'date' => '2024-09-10', 'location' => 'Science Labs'],
];

?>
<div class="container mt-4">
    <div class="p-5 mb-4 bg-light rounded-3 shadow-sm">
        <div class="container-fluid py-5">
            <h1 class="display-5 fw-bold">Welcome to <?php echo defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'Sandalanka Central College'; ?></h1>
            <p class="col-md-8 fs-4">This is the official website of Sandalanka Central College. Stay updated with the latest news, events, and information about our school. Our college is committed to providing quality education and fostering a supportive learning environment for all students.</p>
            <a href="#" class="btn btn-primary btn-lg">Learn More About Us</a>
        </div>
    </div>

    <div class="row align-items-md-stretch">
        <div class="col-md-6 mb-4">
            <div class="h-100 p-5 text-bg-dark rounded-3">
                <h2>Latest News</h2>
                <?php if (!empty($latest_news)): ?>
                    <?php foreach ($latest_news as $index => $news_item): ?>
                        <div class="mb-3 <?php if($index < count($latest_news) -1) echo 'border-bottom pb-2'; ?>">
                            <h5><a href="#" class="text-white text-decoration-none"><?php echo htmlspecialchars($news_item['title']); ?></a></h5>
                            <p class="small text-muted"><?php echo htmlspecialchars(date('F j, Y', strtotime($news_item['date']))); ?></p>
                            <p><?php echo htmlspecialchars($news_item['summary']); ?></p>
                        </div>
                    <?php endforeach; ?>
                     <a href="#" class="btn btn-outline-light btn-sm">View All News</a>
                <?php else: ?>
                    <p>No news articles available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="h-100 p-5 bg-light border rounded-3">
                <h2>Upcoming Events</h2>
                 <?php if (!empty($upcoming_events_home)): ?>
                    <ul class="list-unstyled">
                        <?php foreach ($upcoming_events_home as $event_item): ?>
                            <li class="mb-2">
                                <strong><?php echo htmlspecialchars($event_item['title']); ?></strong><br>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars(date('F j, Y', strtotime($event_item['date']))); ?>
                                    <?php if(!empty($event_item['time'])) echo ' at ' . htmlspecialchars($event_item['time']); ?>
                                    <?php if(!empty($event_item['location'])) echo ' - ' . htmlspecialchars($event_item['location']); ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?php echo APP_URL; ?>/index.php?action=public_load_events" class="btn btn-outline-secondary btn-sm">View All Events</a>
                <?php else: ?>
                    <p>No upcoming events scheduled at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title">Quick Links</h4>
                    <div class="list-group">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if ($_SESSION['role'] === 'student'): ?>
                                <a href="<?php echo APP_URL; ?>/index.php?action=student_dashboard" class="list-group-item list-group-item-action">My Dashboard</a>
                                <a href="<?php echo APP_URL; ?>/index.php?action=student_load_exam_papers" class="list-group-item list-group-item-action">Exam Papers</a>
                                <a href="<?php echo APP_URL; ?>/index.php?action=student_load_timetables" class="list-group-item list-group-item-action">Time Tables</a>
                            <?php elseif ($_SESSION['role'] === 'admin'): ?>
                                <a href="<?php echo APP_URL; ?>/index.php?action=admin_dashboard" class="list-group-item list-group-item-action">Admin Dashboard</a>
                            <?php elseif ($_SESSION['role'] === 'owner'): ?>
                                <a href="<?php echo APP_URL; ?>/index.php?action=owner_dashboard_view" class="list-group-item list-group-item-action">Owner Panel</a>
                                <a href="<?php echo APP_URL; ?>/index.php?action=admin_dashboard" class="list-group-item list-group-item-action">Admin Dashboard Access</a>
                            <?php endif; ?>
                            <a href="<?php echo APP_URL; ?>/index.php?action=logout" class="list-group-item list-group-item-action list-group-item-danger">Logout</a>
                        <?php else: ?>
                            <a href="<?php echo APP_URL; ?>/index.php?action=login_student" class="list-group-item list-group-item-action">Student Login</a>
                            <a href="<?php echo APP_URL; ?>/index.php?action=login_admin" class="list-group-item list-group-item-action">Staff Login</a>
                            <a href="<?php echo APP_URL; ?>/index.php?action=public_load_events" class="list-group-item list-group-item-action">View School Events</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Get the buffered content
$viewContent = ob_get_clean();

// Include the main layout
require_once __DIR__ . '/../layouts/main_layout.php';
?>
