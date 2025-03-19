<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include("classrecordheader.php");

include("../LoginRegisterAuthentication/connection.php");

// Function to add a new assessment
function addAssessment($connection) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addAssessment'])) {
        $subject_id = $_POST['subject_id'];
        $gradeandsection=$_POST['grade_section'];
        $assessment_type_id = $_POST['assessment_type_id'];
        $max_score = $_POST['max_score'];
        $quarter = $_POST['quarter'];
        $userid = $_SESSION['userid'];

        $stmt = $connection->prepare("INSERT INTO assessments (user_id,subject_id, grade_section, assessment_type_id, max_score, quarter) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisiis", $userid,$subject_id,$gradeandsection, $assessment_type_id, $max_score, $quarter);

        if ($stmt->execute()) {
            $assessment_id = $stmt->insert_id;
            
            $studentSql = "SELECT s.id 
                        FROM students s
                        JOIN student_subjects ss ON s.id = ss.student_id
                        WHERE ss.description = (SELECT name FROM subjects WHERE id = ?)";
            $studentStmt = $connection->prepare($studentSql);
            $studentStmt->bind_param("i", $subject_id);
            $studentStmt->execute();
            $studentResult = $studentStmt->get_result();

            while ($student = $studentResult->fetch_assoc()) {
                $insertGradeSql = "INSERT INTO student_quiz (subject_id, student_id, user_id, assessment_id, raw_score, weighted_score) 
                                VALUES (?, ?, ?, ?, 0, NULL)";
                $insertGradeStmt = $connection->prepare($insertGradeSql);
                $insertGradeStmt->bind_param("iiii", 
                    $subject_id,
                    $student['id'], 
                    $userid, // Assuming you have the user_id in the session
                    $assessment_id
                );
                $insertGradeStmt->execute();
            }

            ob_clean(); // Clears any previous output
            header('Location: view_update_score.php');
            exit; // Ensure script execution stops after redirection
            // return "Assessment added successfully and grades initialized for all students!";

            
        } else {
            return "Error adding assessment: " . $stmt->error;
        }
    }
    return null;
}

// Function to update student grades
function updateGrades($connection) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updateGrades'])) {
        $userid = $_SESSION['userid'];
        foreach ($_POST['grades'] as $student_quiz_id => $grade) {
            $stmt = $connection->prepare("UPDATE student_quiz SET user_id = ?,raw_score = ?, weighted_score = ? WHERE id = ?");
            $weighted_score = calculateWeightedScore($grade, $_POST['max_score'][$student_quiz_id], $_POST['assessment_type'][$student_quiz_id]);
            $stmt->bind_param("iddi",$userid,$grade, $weighted_score, $student_quiz_id);
            $stmt->execute();
        }
        return "Grades updated successfully!";
    }
    return null;
}

function calculateWeightedScore($raw_score, $max_score, $assessment_type) {
    // Implement your weighted score calculation logic here
    // This is a placeholder implementation
    $weight = 0.2; // Example weight, adjust as needed
    return ($raw_score / $max_score) * $weight * 100;
}

// Handle form submissions
$message = addAssessment($connection);
if (!$message) {
    $message = updateGrades($connection);
}
$userid = $_SESSION['userid'];
$gradeSectionSql = "SELECT DISTINCT `grade & section` as gradesection FROM students WHERE user_id='".$userid."' ORDER BY `grade & section`";
$gradeSectionResult = $connection->query($gradeSectionSql);

// Fetch subjects for dropdown (we'll modify this later with JavaScript)
$subjectsSql = "SELECT description FROM student_subjects ORDER BY description";
$subjectsResult = $connection->query($subjectsSql);

// Fetch assessment types for dropdown
$assessmentTypesSql = "SELECT DISTINCT id, name FROM assessment_types ORDER BY name";
$assessmentTypesResult = $connection->query($assessmentTypesSql);

// Store unique assessment types
$uniqueAssessmentTypes = [];
while ($assessmentType = $assessmentTypesResult->fetch_assoc()) {
    if (!isset($uniqueAssessmentTypes[$assessmentType['name']])) {
        $uniqueAssessmentTypes[$assessmentType['name']] = $assessmentType['id'];
    }
}


// Fetch existing assessments
$userid = $_SESSION['userid'];
$assessmentsSql = "SELECT a.id, a.grade_section, s.name AS subject_name, at.name AS assessment_type, a.max_score, a.quarter 
                   FROM assessments a 
                   JOIN subjects s ON a.subject_id = s.id 
                   JOIN assessment_types at ON a.assessment_type_id = at.id 
                   WHERE a.user_id=".$userid."
                   ORDER BY a.id DESC";
$assessmentsResult = $connection->query($assessmentsSql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Assessment and Grades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 50px;
        }
        h2 {
            color: #0d6efd;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .form-label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
        #gradesTable {
            margin-top: 30px;
        }
        #notificationArea {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
        .summary-table {
            font-size: 0.9em;
        }
        .summary-table th, .summary-table td {
            padding: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4"><i class="fas fa-graduation-cap me-2"></i>Add Assessment and Grades</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- <div class="row"> -->
            <div id="step1" class="step">
                <h3 class="mb-3">Add New Assessment</h3>
                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="grade_section" class="form-label">Grade & Section</label>
                        <select class="form-select" id="grade_section" name="grade_section" onchange="fetchSubjects(this.value)">
                            <option value="">Select Grade & Section</option>
                            <?php while($gradeSection = $gradeSectionResult->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($gradeSection['gradesection']); ?>">
                                    <?php echo htmlspecialchars($gradeSection['gradesection']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <div class="invalid-feedback">Please select a grade & section.</div>
                    </div>
                    <div class="mb-3">
                        <label for="subject_id" class="form-label">Subject</label>
                        <select class="form-select" id="summary_subject" name="subject_id" required>
                            <option value="">Select Subject</option>
                            <?php 
                            $subjectsQuery = "SELECT DISTINCT description,subject_id FROM student_subjects WHERE student_id IN (SELECT id FROM students WHERE user_id = ?) ORDER BY description";
                            $stmt = mysqli_prepare($connection, $subjectsQuery);
                            mysqli_stmt_bind_param($stmt, 'i', $userid);
                            mysqli_stmt_execute($stmt);
                            $subjectsResult = mysqli_stmt_get_result($stmt);
                            
                            // Store the current subject_id for comparison
                            $selected_subject = isset($_POST['subject_id']) ? $_POST['subject_id'] : (isset($_GET['subject_id']) ? $_GET['subject_id'] : '');
                            
                            while ($subject = mysqli_fetch_assoc($subjectsResult)):
                                $subject_description = $subject['description'];
                            ?>
                                <option value="<?php echo $subject['subject_id'] ; ?>"
                                    <?php echo ($subject['subject_id'] == $selected_subject) ? 'selected' : ''; ?>>
                                    <?php echo $subject_description; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <div class="invalid-feedback">Please select a subject.</div>
                    </div>
                    <div class="mb-3">
                        <label for="assessment_type_id" class="form-label">Assessment Type</label>
                        <select class="form-select" id="assessment_type_id" name="assessment_type_id" required>
                            <option value="">Select an assessment type</option>
                            <?php foreach ($uniqueAssessmentTypes as $name => $id): ?>
                                <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select an assessment type.</div>
                    </div>
                    <div class="mb-3" id="max_score_div" style="display: none;">
                        <label for="max_score" class="form-label">Max Score</label>
                        <input type="number" class="form-control" id="max_score" name="max_score" required>
                        <div class="invalid-feedback">Please enter a valid max score.</div>
                    </div>

                    <div class="mb-3">
                        <label for="quarter" class="form-label">Quarter</label>
                        <select class="form-select" id="quarter" name="quarter" required>
                            <option value="">Select a quarter</option>
                            <option value="1st">1st</option>
                            <option value="2nd">2nd</option>
                            <option value="3rd">3rd</option>
                            <option value="4th">4th</option>
                        </select>
                        <div class="invalid-feedback">Please select a quarter.</div>
                    </div>
                    <button type="submit" class="btn btn-primary" name="addAssessment" id="addAssessmentButton">
                        <i class="fas fa-plus-circle me-2"></i>Add Assessment
                    </button>
                    <a href="view_subject_scores.php" class="btn btn-secondary ms-2">
                        <i class="fas fa-eye me-2"></i>View Subject Scores
                    </a>
                </form>
            </div>
            
            <!-- <div id="step2" class="step" >
                <h3 class="mb-3">Update Grades</h3>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="assessment_id" class="form-label">Select Assessment</label>
                        <select class="form-select" id="assessment_id" name="assessment_id" onchange="loadStudentGrades(this.value)">
                            <option value="">Select an assessment</option>
                            <?php while($assessment = $assessmentsResult->fetch_assoc()): ?>
                                <option value="<?php echo $assessment['id']; ?>">
                                    <?php echo htmlspecialchars($assessment['grade_section'] . " - " .$assessment['subject_name'] . " - " . $assessment['assessment_type'] . 
                                    " (Max: " . $assessment['max_score'] . ", Quarter: " . $assessment['quarter'] . ")"); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div id="gradesTable"></div>
                </form>
            </div> -->
        <!-- </div> -->
        <!-- <div class="row mt-5">
            <div class="col-12">
                <h3>Assessment Summary</h3>
                <form id="generateSummaryForm" class="mb-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="summary_grade_section" class="form-label">Grade & Section</label>
                            <select class="form-select" id="summary_grade_section" name="grade_section" required>
                                <option value="">Select Grade & Section</option>
                                <?php
                                $gradeSectionResult->data_seek(0); // Reset the result pointer
                                while($gradeSection = $gradeSectionResult->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo htmlspecialchars($gradeSection['gradesection']); ?>">
                                        <?php echo htmlspecialchars($gradeSection['gradesection']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="summary_subject" class="form-label">Subject</label>
                            <select class="form-select" id="summary_subject2" name="subject_id" required>
                                <option value="">Select Subject</option>
                                <?php 
                                $subjectsQuery = "SELECT DISTINCT description,subject_id FROM student_subjects WHERE student_id IN (SELECT id FROM students WHERE user_id = ?) ORDER BY description";
                                $stmt = mysqli_prepare($connection, $subjectsQuery);
                                mysqli_stmt_bind_param($stmt, 'i', $userid);
                                mysqli_stmt_execute($stmt);
                                $subjectsResult = mysqli_stmt_get_result($stmt);
                                
                                // Store the current subject_id for comparison
                                $selected_subject = isset($_POST['subject_id']) ? $_POST['subject_id'] : (isset($_GET['subject_id']) ? $_GET['subject_id'] : '');
                                
                                while ($subject = mysqli_fetch_assoc($subjectsResult)):
                                    $subject_description = $subject['description'];
                                ?>
                                    <option value="<?php echo $subject['subject_id'] ; ?>"
                                        <?php echo ($subject['subject_id'] == $selected_subject) ? 'selected' : ''; ?>>
                                        <?php echo $subject_description; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="summary_quarter" class="form-label">Quarter</label>
                            <select class="form-select" id="summary_quarter" name="quarter" required>
                                <option value="">Select a quarter</option>
                                <option value="1st">1st</option>
                                <option value="2nd">2nd</option>
                                <option value="3rd">3rd</option>
                                <option value="4th">4th</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-chart-bar me-2"></i>Generate Summary
                            </button>
                        </div>
                    </div>
                </form>
                <div id="summaryTable" class="table-responsive"></div>
            </div>
        </div> -->

    </div>
    <div id="notificationArea"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function loadStudentGrades(assessmentId) {
        if (assessmentId) {
            $.ajax({
                url: 'get_student_grades.php',
                type: 'GET',
                data: { assessment_id: assessmentId },
                success: function(response) {
                    $('#gradesTable').html(response);
                }
            });
        } else {
            $('#gradesTable').html('');
        }
    }

    // Form validation
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
    })()
    $(document).ready(function() {
        $('#generateSummaryForm').on('submit', function(e) {
            e.preventDefault();
            var subjectId = $('#summary_subject2').val();
            var quarter = $('#summary_quarter').val();
            var gradeSection = $('#summary_grade_section').val();
            $.ajax({
                url: 'generate_summary.php',
                type: 'POST',
                data: { 
                    subject_id: subjectId, 
                    quarter: quarter,
                    grade_section: gradeSection
                },
                success: function(response) {
                    $('#summaryTable').html(response);
                },
                error: function() {
                    showNotification('An error occurred while generating the summary.', 'danger');
                }
            });
        });
    });
    $(document).ready(function() {
    $('#assessment_type_id').change(function() {
        var assessmentTypeId = $(this).val();
        if (assessmentTypeId) {
            $('#max_score_div').show();  // Show the max score input field
            fetchMaxScore(assessmentTypeId);  // Fetch the max score for the selected assessment type
        } else {
            $('#max_score_div').hide();  // Hide the max score input field if no assessment type is selected
        }
    });

    // Function to fetch max score based on selected assessment type (optional)
    function fetchMaxScore(assessmentTypeId) {
        $.ajax({
            url: 'get_max_score.php',  // A new PHP file to fetch the max score from the database
            type: 'GET',
            data: { assessment_type_id: assessmentTypeId },
            success: function(response) {
                $('#max_score').val(response);  // Set the max score to the fetched value
            }
        });
    }
    // $(document).ready(function() {
    //         $("#addAssessmentButton").click(function() {
    //             let subjectValue = $("#summary_subject").val(); // Get selected value
    //             let maxScore = $("#max_score").val();
    //             let assessmentId = $("#assessment_type_id").val();
    //             let quarterSel = $("#quarter").val();
    //             if( subjectValue  === "" || maxScore === "" || assessmentId === "" || quarterSel === ""){

    //             }else{
    //                 $("#step1").hide();
    //                 $("#step2").show();
    //             }
              
    //         });

         
    //     });    
});

function fetchSubjects(gradeSection) {
        var subjectDropdown = document.getElementById('summary_subject');
        subjectDropdown.innerHTML = '<option value="">Loading...</option>';

        if (gradeSection !== '') {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '../crud/fetch_subjects_new.php?grade_section=' + encodeURIComponent(gradeSection), true);
            
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    subjectDropdown.innerHTML = xhr.responseText;
                }
            };
            
            xhr.send();
        } else {
            subjectDropdown.innerHTML = '<option value="">All Subjects</option>';
        }
    }


    </script>
</body>
</html>