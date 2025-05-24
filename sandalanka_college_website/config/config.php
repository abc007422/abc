<?php
// Database Configuration

// Database Host (e.g., 'localhost' or IP address)
define('DB_HOST', 'localhost');

// Database Name
define('DB_NAME', 'sandalanka_college_db'); // Choose an appropriate name

// Database Username
define('DB_USER', 'root'); // Replace with your database username

// Database Password
define('DB_PASS', ''); // Replace with your database password

// Application Configuration
define('APP_NAME', 'Sandalanka Central College');
define('APP_URL', 'http://localhost/sandalanka_college_website/public'); // Adjust if your public folder is served differently

// Error Reporting (set to 0 in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Timezone
date_default_timezone_set('Asia/Colombo');

// Other global settings can be defined here
// For example, paths to certain directories if needed outside of direct includes
define('BASE_PATH', __DIR__ . '/..'); // Root project directory
define('PUBLIC_PATH', BASE_PATH . '/public');
define('SRC_PATH', BASE_PATH . '/src');
define('VIEWS_PATH', SRC_PATH . '/views');

// Session configuration (if using sessions)
// session_start(); // Uncomment if you plan to use sessions immediately

// Access Keys
define('ADMIN_ACCESS_KEY', 'SandalankaAdmin@2024'); // Change this in a production environment!

// Site Settings
define('SITE_NAME', 'Sandalanka Central College');
define('MAX_OWNERS', 4);

?>
