<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include("../LoginRegisterAuthentication/connection.php");
include("functions.php");

// Initialize filter variables
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : '';
$subject_id = isset($_GET['subject']) ? mysqli_real_escape_string($connection, $_GET['subject']) : '';
$section = isset($_GET['section']) ? mysqli_real_escape_string($connection, $_GET['section']) : '';
$school_year = isset($_GET['school_year']) ? mysqli_real_escape_string($connection, $_GET['school_year']) : '';
$quarter = isset($_GET['quarter']) ? mysqli_real_escape_string($connection, $_GET['quarter']) : '';
$gender = isset($_GET['gender']) ? mysqli_real_escape_string($connection, $_GET['gender']) : '';
$grade_level = isset($_GET['grade']) ? mysqli_real_escape_string($connection, $_GET['grade']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';

// Build the query with filters
$query = "SELECT sg.*, s.learners_name, sub.name as subject_name 
          FROM student_grades sg
          JOIN students s ON sg.student_id = s.id
          JOIN subjects sub ON sg.subject_id = sub.id
          WHERE 1=1";

if ($search) {
    $query .= " AND (s.learners_name LIKE '%$search%' OR sub.name LIKE '%$search%')";
}
if ($student_id) {
    $query .= " AND sg.student_id = $student_id";
}
if ($subject_id) {
    $query .= " AND sub.name = '$subject_id'";
}
if ($section) {
    $query .= " AND s.section = '$section'";
}
if ($school_year) {
    $query .= " AND s.school_year = '$school_year'";
}
if ($quarter) {
    $query .= " AND sg.quarter = '$quarter'";
}
if ($gender) {
    $query .= " AND s.gender = '$gender'";
}
if ($grade_level) {
    $query .= " AND s.grade = '$grade_level'";
}

$query .= " ORDER BY s.learners_name ASC, sub.name ASC, sg.quarter ASC";
$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}

// Fetch data for dropdowns
$students_query = "SELECT id, learners_name FROM students";
$students_result = mysqli_query($connection, $students_query);

$subjects_query = "SELECT DISTINCT name FROM subjects";
$subjects_result = mysqli_query($connection, $subjects_query);

$sections_query = "SELECT DISTINCT section FROM students";
$sections_result = mysqli_query($connection, $sections_query);

$school_years_query = "SELECT DISTINCT school_year FROM students";
$school_years_result = mysqli_query($connection, $school_years_query);

$quarters_query = "SELECT DISTINCT quarter FROM enrollments";
$quarters_result = mysqli_query($connection, $quarters_query);

if (!$students_result || !$subjects_result || !$sections_result || !$school_years_result || !$quarters_result) {
    die("Query failed: " . mysqli_error($connection));
}

// Initialize arrays to hold aggregated data
$students_subjects = [];

// Function to determine remarks
function get_remarks($final_grade) {
    return $final_grade >= 75 ? 'Passed' : 'Failed';
}

while ($row = mysqli_fetch_assoc($result)) {
    $student_name = $row['learners_name'];
    $subject_name = $row['subject_name'];
    $quarter = $row['quarter'];
    
    // Initialize data structure if not exists
    if (!isset($students_subjects[$student_name])) {
        $students_subjects[$student_name] = [];
    }
    if (!isset($students_subjects[$student_name][$subject_name])) {
        $students_subjects[$student_name][$subject_name] = [
            'grades' => [],
            'totals' => ['written_exam' => 0, 'performance_task' => 0, 'quarterly_exam' => 0, 'final_grade' => 0],
            'count' => 0
        ];
    }

    // Aggregate grades
    $students_subjects[$student_name][$subject_name]['grades'][$quarter] = $row;
    $students_subjects[$student_name][$subject_name]['totals']['written_exam'] += $row['written_exam'] ?? 0;
    $students_subjects[$student_name][$subject_name]['totals']['performance_task'] += $row['performance_task'] ?? 0;
    $students_subjects[$student_name][$subject_name]['totals']['quarterly_exam'] += $row['quarterly_exam'] ?? 0;
    $students_subjects[$student_name][$subject_name]['totals']['final_grade'] += $row['final_grade'] ?? 0;
    $students_subjects[$student_name][$subject_name]['count']++;
}

// Function to calculate averages
function calculate_averages($totals, $count) {
    return array_map(function($total) use ($count) {
        return $count > 0 ? $total / $count : 0;
    }, $totals);
}

// Handle download request
if (isset($_GET['download'])) {
    if ($student_id) {
        // Filter data for the specific student
        $filtered_data = array_filter($students_subjects, function($student) use ($student_id) {
            foreach ($student as $subject) {
                foreach ($subject['grades'] as $grade) {
                    if ($grade['student_id'] == $student_id) {
                        return true;
                    }
                }
            }
            return false;
        });
        generate_csv_report($filtered_data);
    } else {
        // Generate report for all students
        generate_csv_report($students_subjects);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Record</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 1200px;
        }
        .btn-group, .form-group {
            margin-right: 0.5rem;
        }
        .card {
            margin-bottom: 1rem;
        }
        .card-header {
            cursor: pointer;
        }
        .remarks {
            font-weight: bold;
            color: green;
        }
        .remarks.failed {
            color: red;
        }
        .total-row th, .average-row th {
            background-color: #f8f9fa;
        }
        .table td, .table th {
            text-align: center;
        }
        .table th {
            vertical-align: middle;
        }
        .student-row {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Buttons and Filters Container -->
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-3">
                <div class="btn-group">
                    <a href="FinalGrade.php" class="btn btn-success">All</a>
                    <a href="updategraderecord.php" class="btn btn-success">Update Grade</a>
                    <a href="ClassRecord.php" class="btn btn-danger">Back</a>
                    <a href="add_grade.php" class="btn btn-primary">Add New Record</a>
                </div>
            </div>
            <!-- Report Download Button -->
            <div class="mb-3">
                <a href="report.php?download=all" class="btn btn-primary">Download All Reports</a>
                <?php if ($student_id): ?>
                    <a href="report.php?download=single&student_id=<?php echo htmlspecialchars($student_id); ?>" class="btn btn-secondary">Download Single Student Report</a>
                <?php endif; ?>
            </div>
            <!-- Filter Form -->
            <form method="GET" action="" class="form-inline">
                <div class="form-group mx-2">
                    <label for="student_id" class="sr-only">Student:</label>
                    <select name="student_id" id="student_id" class="form-control" onchange="this.form.submit()">
                        <option value="">Select Student</option>
                        <?php while ($student = mysqli_fetch_assoc($students_result)) { ?>
                            <option value="<?php echo htmlspecialchars($student['id']); ?>" <?php if ($student_id == $student['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($student['learners_name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <!-- (Other filter fields remain unchanged) -->
                <div class="form-group mx-2">
                    <label for="gender" class="sr-only">Gender:</label>
                    <select class="form-control" id="gender" name="gender" onchange="this.form.submit()">
                        <option value="">Select Gender</option>
                        <option value="Male" <?php if ($gender == 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if ($gender == 'Female') echo 'selected'; ?>>Female</option>
                    </select>
                </div>
                <!-- (Add other filter options as needed) -->
            </form>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header">
                <h5>Student Grades</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Subject</th>
                            <th>Quarter</th>
                            <th>Written Exam</th>
                            <th>Performance Task</th>
                            <th>Quarterly Exam</th>
                            <th>Final Grade</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students_subjects as $student_name => $subjects) { ?>
                            <?php foreach ($subjects as $subject_name => $data) { ?>
                                <?php foreach ($data['grades'] as $quarter => $grade) { ?>
                                    <tr>
                                        <td class="student-row"><?php echo htmlspecialchars($student_name); ?></td>
                                        <td><?php echo htmlspecialchars($subject_name); ?></td>
                                        <td><?php echo htmlspecialchars($quarter); ?></td>
                                        <td><?php echo htmlspecialchars($grade['written_exam'] ?? 0); ?></td>
                                        <td><?php echo htmlspecialchars($grade['performance_task'] ?? 0); ?></td>
                                        <td><?php echo htmlspecialchars($grade['quarterly_exam'] ?? 0); ?></td>
                                        <td><?php echo htmlspecialchars($grade['final_grade'] ?? 0); ?></td>
                                        <td class="<?php echo get_remarks($grade['final_grade']) == 'Passed' ? 'remarks' : 'remarks failed'; ?>">
                                            <?php echo get_remarks($grade['final_grade']); ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                    <!-- Totals and Averages -->
                    <tfoot>
                        <!-- Total and Average rows can be added here -->
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
