<?php
// Database connection logic

// Include database configuration
require_once __DIR__ . '/../../config/config.php'; // Adjusted path to be relative to this file

$conn = null;

function getPDOConnection() {
    global $conn;

    if ($conn === null) {
        try {
            // Create a new PDO instance
            $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);

            // Set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Optional: Set character set to utf8mb4 for better Unicode support
            $conn->exec("SET NAMES 'utf8mb4'");

            // echo "Connected successfully to database: " . DB_NAME; // For debugging
        } catch(PDOException $e) {
            // Log the error to a file or error tracking system
            error_log("Connection failed: " . $e->getMessage());

            // Display a generic error message to the user
            // In a real application, you might want to redirect to an error page
            die("Database connection error. Please try again later. Details: " . $e->getMessage());
        }
    }
    return $conn;
}

// Function to create tables from schema.sql if they don't exist
function initializeDatabase() {
    $pdo = getPDOConnection();
    try {
        // Check if users table exists
        $result = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($result->rowCount() == 0) {
            // Users table doesn't exist, try to create schema
            $sql = file_get_contents(__DIR__ . '/../../config/schema.sql');
            if ($sql === false) {
                error_log("Failed to read schema.sql file.");
                die("Error initializing database: Could not read schema file.");
            }
            $pdo->exec($sql);
            // echo "Database tables created successfully from schema.sql."; // For debugging
        }
    } catch (PDOException $e) {
        error_log("Database initialization failed: " . $e->getMessage());
        die("Error initializing database: " . $e->getMessage());
    }
}

// Call this function once, perhaps in a part of your application's bootstrap process
// For example, you might call it in your main index.php or a specific setup script.
// For now, we can call it here to ensure tables are checked/created when db.php is included.
// However, it's generally better to have a more explicit initialization step.
// initializeDatabase(); // We will call this explicitly where needed, e.g., in index.php or a setup script

// Function to close the database connection (optional, as PDO closes automatically)
function closeConnection() {
    global $conn;
    $conn = null;
}

?>
