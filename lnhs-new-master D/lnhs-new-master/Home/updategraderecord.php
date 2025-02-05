<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

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
    </style>
</head>
<body>
    <div class="container">
        <!-- Buttons and Filters Container -->
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-3">
                <div class="btn-group">
                    <a href="updategraderecord.php" class="btn btn-success">All</a>
                    <a href="ClassRecord.php" class="btn btn-danger">Back</a>
                    <a href="add_grade.php" class="btn btn-success">Add Record</a>
                    
            
                  
                </div>
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

                <div class="form-group mx-2">
                    <label for="gender" class="sr-only">Gender:</label>
                    <select class="form-control" id="gender" name="gender" onchange="this.form.submit();">
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo ($gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>

                <div class="form-group mx-2">
                    <label for="grade" class="sr-only">Grade Level:</label>
                    <select class="form-control" id="grade" name="grade" onchange="this.form.submit();">
                        <option value="">Select Grade Level</option>
                        <?php for ($i = 7; $i <= 12; $i++): ?>
                            <option value="<?php echo $i . 'th'; ?>" <?php echo ($grade_level == $i . 'th') ? 'selected' : ''; ?>><?php echo $i . 'th'; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-group mx-2">
                    <label for="subject" class="sr-only">Subject:</label>
                    <select class="form-control" id="subject" name="subject" onchange="this.form.submit();">
                        <option value="">Select Subject</option>
                        <?php while ($subject = mysqli_fetch_assoc($subjects_result)) { ?>
                            <option value="<?php echo htmlspecialchars($subject['name']); ?>" <?php if ($subject_id == $subject['name']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($subject['name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group mx-2">
                    <label for="section" class="sr-only">Section:</label>
                    <select class="form-control" id="section" name="section" onchange="this.form.submit();">
                        <option value="">Select Section</option>
                        <?php while ($sec = mysqli_fetch_assoc($sections_result)) { ?>
                            <option value="<?php echo htmlspecialchars($sec['section']); ?>" <?php if ($section == $sec['section']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($sec['section']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group mx-2">
                    <label for="school_year" class="sr-only">School Year:</label>
                    <select class="form-control" id="school_year" name="school_year" onchange="this.form.submit();">
                        <option value="">All School Years</option>
                        <?php for ($year = 2020; $year <= 2024; $year++) { 
                            $nextYear = $year + 1;
                            $schoolYear = "{$year}-{$nextYear}";
                        ?>
                            <option value="<?php echo $schoolYear; ?>" <?php if ($school_year == $schoolYear) echo 'selected'; ?>><?php echo $schoolYear; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group mx-2">
                    <label for="quarter" class="sr-only">Quarter:</label>
                    <select class="form-control" id="quarter" name="quarter" onchange="this.form.submit();">
                        <option value="">All Quarters</option>
                        <?php while ($quarter = mysqli_fetch_assoc($quarters_result)) { ?>
                            <option value="<?php echo htmlspecialchars($quarter['quarter']); ?>" <?php if ($quarter['quarter'] == $quarter) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($quarter['quarter']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
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
                            <th>Remarks</th>
                            <th>Actions</th> <!-- New column header -->
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
                                        <td><?php echo htmlspecialchars(get_remarks($grade['final_grade'])); ?></td>
                                    </tr>
                                    <td>
                        <a href="update_grades.php?id=<?php echo $grade['id']; ?>" class="btn btn-warning btn-sm">Update</a>
                    </td>

                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <th>Total</th>
                                    <td><?php echo htmlspecialchars($data['totals']['written_exam']); ?></td>
                                    <td><?php echo htmlspecialchars($data['totals']['performance_task']); ?></td>
                                    <td><?php echo htmlspecialchars($data['totals']['quarterly_exam']); ?></td>
                                    <td><?php echo htmlspecialchars($data['totals']['final_grade']); ?></td>
                                    <td></td>
                                </tr>
                                <tr class="average-row">
                                    <th>Average</th>
                                    <td><?php echo htmlspecialchars(number_format($averages['written_exam'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($averages['performance_task'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($averages['quarterly_exam'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($averages['final_grade'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars(get_remarks($averages['final_grade'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
