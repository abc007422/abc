<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../../config/config.php';

function handleListPublicEvents() {
    $pdo = getPDOConnection();
    try {
        // Fetch upcoming events (from today onwards)
        $stmt_upcoming = $pdo->prepare(
            "SELECT id, title, description, date, time, location 
             FROM events 
             WHERE date >= CURDATE() 
             ORDER BY date ASC, time ASC"
        );
        $stmt_upcoming->execute();
        $_SESSION['public_upcoming_events'] = $stmt_upcoming->fetchAll(PDO::FETCH_ASSOC);

        // Fetch past events (before today, limit to a certain number e.g., 10 most recent)
        $stmt_past = $pdo->prepare(
            "SELECT id, title, description, date, time, location 
             FROM events 
             WHERE date < CURDATE() 
             ORDER BY date DESC, time DESC
             LIMIT 10" // Example limit
        );
        $stmt_past->execute();
        $_SESSION['public_past_events'] = $stmt_past->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Database error loading public events: " . $e->getMessage());
        $_SESSION['error_message_public_events'] = "An error occurred while fetching events.";
        $_SESSION['public_upcoming_events'] = [];
        $_SESSION['public_past_events'] = [];
    }
    header("Location: " . APP_URL . "/index.php?action=public_events_view"); // The view page
    exit;
}


// Routing within this controller
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    switch ($action) {
        case 'public_load_events':
            handleListPublicEvents();
            break;
        // Add other public actions here if needed
    }
}
?>
