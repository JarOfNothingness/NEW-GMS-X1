<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include("../LoginRegisterAuthentication/connection.php");

// Initialize filter variables
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : '';

// Fetch all students for the dropdown
$students_query = "SELECT id, learners_name FROM students";
$students_result = mysqli_query($connection, $students_query);

// If the student_id is set, fetch grades for that student
$grades = [];
if ($student_id) {
    $query = "SELECT sg.*, s.learners_name, sub.name AS subject_name 
              FROM student_grades sg
              JOIN students s ON sg.student_id = s.id
              JOIN subjects sub ON sg.subject_id = sub.id
              WHERE sg.student_id = $student_id
              ORDER BY sg.quarter";

    $result = mysqli_query($connection, $query);
    if (!$result) {
        die("Query failed: " . mysqli_error($connection));
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $grades[] = $row;
    }
}

// Function to determine remarks
function get_remarks($final_grade) {
    return $final_grade >= 75 ? 'Passed' : 'Failed';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Grade Card</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 800px;
            margin: 40px auto;
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
            color: #2c3e50;
        }
        .form-inline {
            justify-content: center;
        }
        table {
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        td {
            font-size: 0.9rem;
        }
        .remarks.passed {
            color: green;
            font-weight: bold;
        }
        .remarks.failed {
            color: red;
            font-weight: bold;
        }
        .alert {
            margin-top: 20px;
        }
        .btn {
            width: 100px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Student Grade Card</h2>
        
        <!-- Filter Form -->
        <form method="GET" action="" class="form-inline mb-3">
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
        </form>

        <?php if ($student_id && !empty($grades)) { ?>
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
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
                    <?php foreach ($grades as $grade): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($grade['subject_name']); ?></td>
                            <td><?php echo htmlspecialchars($grade['quarter']); ?></td>
                            <td><?php echo htmlspecialchars($grade['written_exam']) ?: 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($grade['performance_task']) ?: 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($grade['quarterly_exam']) ?: 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($grade['final_grade']) ?: 'N/A'; ?></td>
                            <td class="<?php echo get_remarks($grade['final_grade']) === 'Passed' ? 'remarks passed' : 'remarks failed'; ?>">
                                <?php echo htmlspecialchars(get_remarks($grade['final_grade'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php } elseif ($student_id) { ?>
            <div class="alert alert-warning">No grades found for this student.</div>
        <?php } ?>

        <div class="text-center mt-4">
            <a href="ClassRecord.php" class="btn btn-danger">Back</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
