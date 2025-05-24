<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php 
        $site_title = defined('SITE_NAME') ? SITE_NAME : 'Sandalanka Central College';
        echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' . htmlspecialchars($site_title) : htmlspecialchars($site_title); 
    ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/style.css">
    <link rel="icon" href="<?php echo APP_URL; ?>/favicon.ico" type="image/x-icon"> <!-- Basic Favicon Link -->
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <a class="navbar-brand" href="<?php echo APP_URL; ?>/"><?php echo defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'Sandalanka CC'; ?></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavDropdown">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($_SERVER['REQUEST_URI'] == APP_URL.'/' || (isset($_GET['action']) && $_GET['action'] == '')) ? 'active' : ''; ?>" aria-current="page" href="<?php echo APP_URL; ?>/">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'event') !== false) ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/index.php?action=public_load_events">Events</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">News</a> <!-- Placeholder -->
                        </li>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if ($_SESSION['role'] === 'student'): ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="studentDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Student Menu
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="studentDropdown">
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/index.php?action=student_dashboard">My Dashboard</a></li>
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/index.php?action=student_load_exam_papers">Exam Papers</a></li>
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/index.php?action=student_load_timetables">Time Tables</a></li>
                                    </ul>
                                </li>
                            <?php elseif (in_array($_SESSION['role'], ['admin', 'owner'])): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'admin_dashboard') !== false) ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/index.php?action=admin_dashboard">Admin Dashboard</a>
                                </li>
                                <?php if ($_SESSION['role'] === 'owner'): ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo (isset($_GET['action']) && strpos($_GET['action'], 'owner_dashboard') !== false) ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/index.php?action=owner_dashboard_view">Owner Panel</a>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo (isset($_GET['action']) && $_GET['action'] == 'public_about_us') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/index.php?action=public_about_us">About Us</a>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link <?php echo (isset($_GET['action']) && $_GET['action'] == 'public_contact_us') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/index.php?action=public_contact_us">Contact Us</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo APP_URL; ?>/index.php?action=logout">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Login
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="loginDropdown">
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/index.php?action=login_student">Student Login</a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/index.php?action=login_admin">Staff Login</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container mt-4 mb-4 flex-grow-1"> <!-- Added flex-grow-1 for main content -->
        <?php
        if (isset($_SESSION['error_message']) && !empty($_SESSION['error_message']) && !isset($error_message_display) && !isset($error_message_form_display) && !isset($error_message_details_display) && !isset($error_message_event_mgmt) && !isset($error_message_file_mgmt) && !isset($error_message_papers) && !isset($error_message_student_timetable) && !isset($error_message_user_mgmt) && !isset($error_message_settings) && !isset($error_message_owner) ) {
            echo '<div class="alert alert-danger global-error-message" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }
        if (isset($_SESSION['success_message']) && !empty($_SESSION['success_message']) && !isset($success_message_display) && !isset($success_message_form_display) && !isset($success_message_event_mgmt) && !isset($success_message_file_mgmt) && !isset($success_message_timetable_mgmt) && !isset($success_message_user_mgmt) && !isset($success_message_settings) && !isset($success_message_owner) ) {
             echo '<div class="alert alert-success global-success-message" role="alert">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
            unset($_SESSION['success_message']);
        }
        
        if (isset($viewContent) && !empty($viewContent)) {
            echo $viewContent;
        } else {
            echo '<div class="alert alert-warning" role="alert">Page content is missing or could not be loaded.</div>';
        }
        ?>
    </main>

    <footer class="bg-light text-center text-lg-start mt-auto"> 
        <div class="container p-3">
            <p class="text-center mb-0">&copy; <?php echo date("Y"); ?> <?php echo defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'Sandalanka Central College'; ?>. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="<?php echo APP_URL; ?>/js/script.js"></script>
</body>
</html>
