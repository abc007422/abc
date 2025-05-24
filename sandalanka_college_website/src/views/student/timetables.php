<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure config is available for APP_URL
if (file_exists(__DIR__ . '/../../../config/config.php')) {
    require_once __DIR__ . '/../../../config/config.php';
} else {
    define('APP_URL', '/'); // Fallback
    error_log("Config file not found for timetables.php (student)");
}

// Access Control: Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    $_SESSION['error_message'] = "You must be logged in as a student to view this page.";
    header("Location: " . APP_URL . "/index.php?action=login_student");
    exit;
}

$pageTitle = "Class Timetables";
ob_start();

// Fetch timetables from session if passed by controller
$timetables_by_grade_class = isset($_SESSION['timetables_by_grade_class']) ? $_SESSION['timetables_by_grade_class'] : [];
if (isset($_SESSION['timetables_by_grade_class'])) unset($_SESSION['timetables_by_grade_class']);

$error_message_display = isset($_SESSION['error_message_student_timetable']) ? $_SESSION['error_message_student_timetable'] : null;
if (isset($_SESSION['error_message_student_timetable'])) unset($_SESSION['error_message_student_timetable']);

$grades_available = array_keys($timetables_by_grade_class);
// Custom sort for grades to handle "Grade X" format correctly numerically
usort($grades_available, function($a, $b) {
    $num_a = (int) filter_var($a, FILTER_SANITIZE_NUMBER_INT);
    $num_b = (int) filter_var($b, FILTER_SANITIZE_NUMBER_INT);
    return $num_a <=> $num_b;
});

?>
<div class="container mt-4">
    <h2 class="mb-3">Class Timetables</h2>
    <p class="lead">Select your grade and class to view the timetable.</p>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="<?php echo APP_URL; ?>/index.php?action=student_dashboard" class="btn btn-sm btn-outline-secondary">Back to Student Dashboard</a>
        <a href="<?php echo APP_URL; ?>/index.php?action=student_load_timetables" class="btn btn-sm btn-info">Refresh Timetables List</a>
    </div>

    <?php if ($error_message_display): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message_display); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($timetables_by_grade_class)): ?>
        <div class="alert alert-info" role="alert">
            No timetables are currently available. Please check back later.
        </div>
    <?php else: ?>
        <div class="accordion" id="timetablesAccordion">
            <?php $grade_accordion_index = 0; ?>
            <?php foreach ($grades_available as $grade): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingGrade<?php echo str_replace(' ', '', $grade); ?>">
                        <button class="accordion-button <?php if($grade_accordion_index > 0) echo 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGrade<?php echo str_replace(' ', '', $grade); ?>" aria-expanded="<?php echo ($grade_accordion_index == 0) ? 'true' : 'false'; ?>" aria-controls="collapseGrade<?php echo str_replace(' ', '', $grade); ?>">
                            <?php echo htmlspecialchars($grade); ?>
                        </button>
                    </h2>
                    <div id="collapseGrade<?php echo str_replace(' ', '', $grade); ?>" class="accordion-collapse collapse <?php if($grade_accordion_index == 0) echo 'show'; ?>" aria-labelledby="headingGrade<?php echo str_replace(' ', '', $grade); ?>" data-bs-parent="#timetablesAccordion">
                        <div class="accordion-body">
                            <?php if (!empty($timetables_by_grade_class[$grade])): ?>
                                <?php 
                                    $classes_in_grade = array_keys($timetables_by_grade_class[$grade]);
                                    sort($classes_in_grade); // Sort class names alphabetically
                                ?>
                                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                                <?php foreach ($classes_in_grade as $class_name): ?>
                                    <div class="col">
                                        <div class="card h-100">
                                            <div class="card-header bg-light">
                                                <h5>Class: <?php echo htmlspecialchars($class_name); ?></h5>
                                            </div>
                                            <div class="card-body">
                                                <?php if (!empty($timetables_by_grade_class[$grade][$class_name])): ?>
                                                    <?php foreach ($timetables_by_grade_class[$grade][$class_name] as $timetable): ?>
                                                        <div class="timetable-entry mb-3 pb-2 border-bottom">
                                                            <p class="mb-1">
                                                                <strong><?php echo htmlspecialchars($timetable['original_filename']); ?></strong>
                                                                <?php if ($timetable['description']): ?>
                                                                    <br><small class="text-muted"><em><?php echo htmlspecialchars($timetable['description']); ?></em></small>
                                                                <?php endif; ?>
                                                            </p>
                                                            <a href="<?php echo APP_URL . '/' . htmlspecialchars($timetable['file_path']); ?>" target="_blank" class="btn btn-sm btn-success">
                                                                Download <?php echo htmlspecialchars(strtoupper($timetable['file_type'])); ?>
                                                            </a>
                                                            <?php
                                                            $file_ext_tt = strtolower($timetable['file_type']);
                                                            if (in_array($file_ext_tt, ['jpg', 'jpeg', 'png'])): // Removed 'gif' for timetables, less common
                                                            ?>
                                                                <a href="<?php echo APP_URL . '/' . htmlspecialchars($timetable['file_path']); ?>" data-bs-toggle="modal" data-bs-target="#timetableModal<?php echo $timetable['id']; ?>">
                                                                    <img src="<?php echo APP_URL . '/' . htmlspecialchars($timetable['file_path']); ?>" alt="Timetable Preview" class="img-thumbnail mt-2" style="max-height: 150px;">
                                                                </a>
                                                                <!-- Modal for image preview -->
                                                                <div class="modal fade" id="timetableModal<?php echo $timetable['id']; ?>" tabindex="-1" aria-labelledby="timetableModalLabel<?php echo $timetable['id']; ?>" aria-hidden="true">
                                                                  <div class="modal-dialog modal-lg">
                                                                    <div class="modal-content">
                                                                      <div class="modal-header">
                                                                        <h5 class="modal-title" id="timetableModalLabel<?php echo $timetable['id']; ?>"><?php echo htmlspecialchars($timetable['original_filename']); ?></h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                      </div>
                                                                      <div class="modal-body text-center">
                                                                        <img src="<?php echo APP_URL . '/' . htmlspecialchars($timetable['file_path']); ?>" class="img-fluid" alt="Timetable Preview">
                                                                      </div>
                                                                    </div>
                                                                  </div>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php $grade_accordion_index++; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
