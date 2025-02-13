<?php
session_start();
include_once("../LoginRegisterAuthentication/connection.php");

// Check if user is logged in and has appropriate permissions
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$teacher_id = isset($_GET['teacher_id']) ? intval($_GET['teacher_id']) : 0;

if (!$teacher_id) {
    die("Invalid teacher ID");
}

// Fetch teacher details
$teacherQuery = "SELECT name FROM user WHERE userid = ? AND role = 'Teacher'";
$teacherStmt = $connection->prepare($teacherQuery);
$teacherStmt->bind_param("i", $teacher_id);
$teacherStmt->execute();
$teacherResult = $teacherStmt->get_result();
$teacherData = $teacherResult->fetch_assoc();

if (!$teacherData) {
    die("Teacher not found");
}

// Fetch unique grade & sections assigned to this teacher
$gradeSectionQuery = "
    SELECT DISTINCT s.`grade & section`
    FROM students s
    WHERE s.user_id = ?
    ORDER BY s.`grade & section`";

$gradeSectionStmt = $connection->prepare($gradeSectionQuery);
$gradeSectionStmt->bind_param("i", $teacher_id);
$gradeSectionStmt->execute();
$gradeSectionResult = $gradeSectionStmt->get_result();

// Fetch unique subjects assigned to this teacher
$subjectsQuery = "
    SELECT DISTINCT subj.id, subj.name
    FROM assessment_summary asm
    JOIN subjects subj ON asm.subject_id = subj.id
    JOIN students s ON asm.student_id = s.id
    WHERE s.user_id = ?
    ORDER BY subj.name";

$subjectsStmt = $connection->prepare($subjectsQuery);
$subjectsStmt->bind_param("i", $teacher_id);
$subjectsStmt->execute();
$subjectsResult = $subjectsStmt->get_result();

// Fetch unique quarters from assessment_summary
$quartersQuery = "
    SELECT DISTINCT asm.quarter
    FROM assessment_summary asm
    JOIN students s ON asm.student_id = s.id
    WHERE s.user_id = ?
    ORDER BY FIELD(asm.quarter, '1st', '2nd', '3rd', '4th')";

$quartersStmt = $connection->prepare($quartersQuery);
$quartersStmt->bind_param("i", $teacher_id);
$quartersStmt->execute();
$quartersResult = $quartersStmt->get_result();

// Check if teacher has any records
$hasRecords = $gradeSectionResult->num_rows > 0 && 
              $subjectsResult->num_rows > 0 && 
              $quartersResult->num_rows > 0;

// Debug information
// var_dump($gradeSectionResult->num_rows);
// var_dump($subjectsResult->num_rows);
// var_dump($quartersResult->num_rows);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Record for <?php echo htmlspecialchars($teacherData['name']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Previous styles remain the same */
        .no-records-message {
            text-align: center;
            padding: 2rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin: 2rem 0;
        }
        
        .no-records-message i {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .form-label {
            font-weight: 500;
        }

        .section-header {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .btn-generate {
            padding: 0.5rem 1.5rem;
        }

        .btn-back {
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="section-header">Class Record for <?php echo htmlspecialchars($teacherData['name']); ?></h2>

        <?php if (!$hasRecords): ?>
            <div class="no-records-message">
                <i class="fas fa-exclamation-circle"></i>
                <h4>No Records Available</h4>
                <p>This teacher currently has no class records in the system.</p>
            </div>
        <?php else: ?>
            <h3 class="mt-4 mb-3">Assessment Summary</h3>
            <form id="recordForm" class="mb-4">
                <input type="hidden" name="teacher_id" value="<?php echo $teacher_id; ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="grade_section" class="form-label">Grade & Section:</label>
                        <select name="grade_section" id="grade_section" class="form-select" required>
                            <option value="">Select Grade & Section</option>
                            <?php while ($gradeSection = $gradeSectionResult->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($gradeSection['grade & section']); ?>">
                                    <?php echo htmlspecialchars($gradeSection['grade & section']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="subject" class="form-label">Subject:</label>
                        <select name="subject_id" id="subject" class="form-select" required>
                            <option value="">Select Subject</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="quarter" class="form-label">Quarter:</label>
                        <select name="quarter" id="quarter" class="form-select" required>
                            <option value="">Select Quarter</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-generate">
                            <i class="fas fa-chart-bar me-2"></i>Generate Summary
                        </button>
                    </div>
                </div>
            </form>

            <div id="summaryResult"></div>
        <?php endif; ?>

        <a href="manage_user.php" class="btn btn-primary btn-back">
            <i class="fas fa-arrow-left me-2"></i>Back to User Management
        </a>
    </div>

    <script>
    $(document).ready(function() {
        // Load initial data
        updateSubjects();

        $('#recordForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Generating...');
            
            $.ajax({
                url: 'admin_generate_summary.php', // Changed URL to new file
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#summaryResult').html(response);
                },
                error: function() {
                    $('#summaryResult').html('<div class="alert alert-danger">Error generating summary.</div>');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html('<i class="fas fa-chart-bar me-2"></i>Generate Summary');
                }
            });
        });

        $('#grade_section').on('change', function() {
            updateSubjects();
        });

        $('#subject').on('change', function() {
            updateQuarters();
        });

        function updateSubjects() {
            const gradeSection = $('#grade_section').val();
            const teacherId = $('input[name="teacher_id"]').val();
            
            $.ajax({
                url: 'get_teacher_subjects.php',
                method: 'POST',
                data: {
                    grade_section: gradeSection,
                    teacher_id: teacherId
                },
                success: function(response) {
                    $('#subject').html(response);
                    updateQuarters();
                }
            });
        }

        function updateQuarters() {
            const gradeSection = $('#grade_section').val();
            const subjectId = $('#subject').val();
            const teacherId = $('input[name="teacher_id"]').val();
            
            $.ajax({
                url: 'get_teacher_quarters.php',
                method: 'POST',
                data: {
                    grade_section: gradeSection,
                    subject_id: subjectId,
                    teacher_id: teacherId
                },
                success: function(response) {
                    $('#quarter').html(response);
                }
            });
        }
    });
</script>
</body>
</html>