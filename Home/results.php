<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

ob_start();
include("classrecordheader.php");
include("../LoginRegisterAuthentication/connection.php");

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

// Fetch students, subjects, sections, school years, and quarters for dropdowns
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
        .form-inline .form-group {
            margin-right: 1rem;
        }
        .form-inline .form-control {
            width: 180px;
        }
        .btn-primary, .btn-success, .btn-info {
            margin-right: 0.5rem;
        }
        .card {
            margin-bottom: 1rem;
        }
        .card-header {
            cursor: pointer;
        }
    </style>
</head>
<body>
  
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <div>
                <a href="ClassRecord.php" class="btn btn-success">All</a>
                <a href="add_grade.php" class="btn btn-success">Add Record</a>
                <a href="compiled_grades_form.php" class="btn btn-primary">View All Quarters' Grades</a>
                <a href="results.php" class="btn btn-info">View Results</a>
            </div>
            
            <!-- Filter Form -->
            <form method="GET" action="" class="form-inline">
                <div class="form-search">
                    <label for="search" class="sr-only">Search:</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Search by name or subject" value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : ''); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                Class Records
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Subject</th>
                            <th>Quarter</th>
                            <th>Written Exam</th>
                            <th>Performance Task</th>
                            <th>Quarterly Exam</th>
                            <th>Final Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students_subjects as $student_name => $subjects): ?>
                            <?php foreach ($subjects as $subject_name => $data): ?>
                                <?php $averages = calculate_averages($data['totals'], $data['count']); ?>
                                <tr>
                                    <td rowspan="<?php echo $data['count'] + 2; ?>"><?php echo htmlspecialchars($student_name); ?></td>
                                    <td rowspan="<?php echo $data['count'] + 2; ?>"><?php echo htmlspecialchars($subject_name); ?></td>
                                </tr>
                                <?php foreach ($data['grades'] as $quarter => $grade): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($quarter); ?></td>
                                        <td><?php echo htmlspecialchars($grade['written_exam']) ?: 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($grade['performance_task']) ?: 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($grade['quarterly_exam']) ?: 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($grade['final_grade']) ?: 'N/A'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <th>Total</th>
                                    <td><?php echo htmlspecialchars($data['totals']['written_exam']); ?></td>
                                    <td><?php echo htmlspecialchars($data['totals']['performance_task']); ?></td>
                                    <td><?php echo htmlspecialchars($data['totals']['quarterly_exam']); ?></td>
                                    <td><?php echo htmlspecialchars($data['totals']['final_grade']); ?></td>
                                </tr>
                                <tr class="average-row">
                                    <th>Average</th>
                                    <td><?php echo htmlspecialchars(number_format($averages['written_exam'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($averages['performance_task'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($averages['quarterly_exam'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($averages['final_grade'], 2)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>