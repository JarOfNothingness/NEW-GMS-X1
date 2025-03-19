<?php
session_start();
// include("../LoginRegisterAuthentication/connection.php");
include("../LoginRegisterAuthentication/connection.php");
include("classrecordheader.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$userid = $_SESSION['userid'];

// Get filter values
$selected_grade_section = isset($_GET['grade_section']) ? $_GET['grade_section'] : '';
$selected_subject = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';
$selected_quarter = isset($_GET['quarter']) ? $_GET['quarter'] : '';

// Fetch all grade sections
$gradeSectionQuery = "SELECT DISTINCT `grade & section` as gradesection 
                     FROM students 
                     WHERE user_id = ? 
                     ORDER BY `grade & section`";
$stmt = $connection->prepare($gradeSectionQuery);
$stmt->bind_param("i", $userid);
$stmt->execute();
$gradeSectionResult = $stmt->get_result();

// Fetch all subjects
$subjectsQuery = "SELECT DISTINCT s.id, s.name 
                 FROM subjects s
                 JOIN student_subjects ss ON s.id = ss.subject_id
                 JOIN students st ON ss.student_id = st.id
                 WHERE st.user_id = ?
                 ORDER BY s.name";
$stmt = $connection->prepare($subjectsQuery);
$stmt->bind_param("i", $userid);
$stmt->execute();
$subjectsResult = $stmt->get_result();

// Fetch assessment scores if filters are set
$scores = [];
if ($selected_grade_section && $selected_subject && $selected_quarter) {
    $scoresQuery = "SELECT 
        s.learners_name,
        a.assessment_type_id,
        at.name as assessment_type,
        sq.raw_score,
        sq.weighted_score,
        a.max_score,
        a.quarter
    FROM students s
    JOIN student_quiz sq ON s.id = sq.student_id
    JOIN assessments a ON sq.assessment_id = a.id
    JOIN assessment_types at ON a.assessment_type_id = at.id
    WHERE s.`grade & section` = ?
    AND a.grade_section = ?
    AND a.subject_id = ?
    AND a.quarter = ?
    AND s.user_id = ?
    ORDER BY s.learners_name, at.name";
    
    $stmt = $connection->prepare($scoresQuery);
    $stmt->bind_param("ssisi", $selected_grade_section,$selected_grade_section, $selected_subject, $selected_quarter, $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $scores[] = $row;
    }
}

function getAvailableQuarters($connection, $userid, $grade_section = null, $subject_id = null) {
    $quarters = array();
    
    $query = "SELECT DISTINCT a.quarter 
              FROM assessments a
              WHERE a.user_id = ?";
    $params = array($userid);
    $types = "i";
    
    if ($grade_section) {
        $query .= " AND a.grade_section = ?";
        $params[] = $grade_section;
        $types .= "s";
    }
    
    if ($subject_id) {
        $query .= " AND a.subject_id = ?";
        $params[] = $subject_id;
        $types .= "i";
    }
    
    $query .= " ORDER BY FIELD(a.quarter, '1st', '2nd', '3rd', '4th')";
    
    $stmt = $connection->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $quarters[] = $row['quarter'];
    }
    
    return $quarters;
}

// Get available quarters based on filters
$available_quarters = getAvailableQuarters(
    $connection, 
    $selected_grade_section, 
    $selected_subject,
    $userid
);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Grade Scores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .filter-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .scores-table {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .table th {
            background-color: #f1f1f1;
        }
        .no-scores {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="back-button">
            <a href="add_grade.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Add Grades
            </a>
        </div>
        <h2 class="mb-4 text-center">
            <i class="fas fa-clipboard-list me-2"></i>
            Grade Scores
        </h2>

        <div class="filter-section">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="grade_section" class="form-label">Grade & Section</label>
                    <select name="grade_section" id="grade_section" class="form-select" onchange="fetchSubjects(this.value)" required>
                        <option value="">Select Grade & Section</option>
                        <?php while ($row = $gradeSectionResult->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['gradesection']); ?>"
                                    <?php echo ($selected_grade_section == $row['gradesection']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['gradesection']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="subject_id" class="form-label">Subject</label>
                    <select name="subject_id" id="subject_id" class="form-select" required>
                        <option value="">Select Subject</option>
                        <?php while ($row = $subjectsResult->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"
                                    <?php echo ($selected_subject == $row['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="quarter" class="form-label">Quarter</label>
                    <select name="quarter" id="quarter" class="form-select" required>
                        <option value="">Select Quarter</option>
                        <option value="1st" <?php echo ($selected_quarter == '1st') ? 'selected' : ''; ?>>1st</option>
                        <option value="2nd" <?php echo ($selected_quarter == '2nd') ? 'selected' : ''; ?>>2nd</option>
                        <option value="3rd" <?php echo ($selected_quarter == '3rd') ? 'selected' : ''; ?>>3rd</option>
                        <option value="4th" <?php echo ($selected_quarter == '4th') ? 'selected' : ''; ?>>4th</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>View Scores
                    </button>
                </div>
            </form>
        </div>

        <?php if ($selected_grade_section && $selected_subject && $selected_quarter): ?>
            <div class="scores-table">
                <?php if (count($scores) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Assessment Type</th>
                                    <th>Raw Score</th>
                                    <th>Max Score</th>
                                    <th>Weighted Score</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($scores as $score): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($score['learners_name']); ?></td>
                                        <td><?php echo htmlspecialchars($score['assessment_type']); ?></td>
                                        <td><?php echo number_format($score['raw_score'], 2); ?></td>
                                        <td><?php echo number_format($score['max_score'], 2); ?></td>
                                        <td><?php echo number_format($score['weighted_score'], 2); ?></td>
                                        <td>
                                            <?php 
                                            $percentage = ($score['raw_score'] / $score['max_score']) * 100;
                                            echo number_format($percentage, 2) . '%';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-scores">
                        <i class="fas fa-info-circle me-2"></i>
                        No scores found for the selected filters.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Function to update quarters dropdown
        function updateQuarters() {
            var gradeSection = $('#grade_section').val();
            var subjectId = $('#subject_id').val();
            
            if (gradeSection && subjectId) {
                $.ajax({
                    url: 'get_available_quarters.php',
                    type: 'POST',
                    data: {
                        grade_section: gradeSection,
                        subject_id: subjectId
                    },
                    success: function(response) {
                        var quarters = JSON.parse(response);
                        var quarterSelect = $('#quarter');
                        quarterSelect.empty();
                        quarterSelect.append('<option value="">Select Quarter</option>');
                        
                        quarters.forEach(function(quarter) {
                            quarterSelect.append(
                                $('<option></option>')
                                    .val(quarter)
                                    .text(quarter)
                            );
                        });
                    }
                });
            }
        }

        // Update quarters when grade section or subject changes
        $('#grade_section, #subject_id').change(function() {
            updateQuarters();
        });
    });

    function fetchSubjects(gradeSection) {
        var subjectDropdown = document.getElementById('subject_id');
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