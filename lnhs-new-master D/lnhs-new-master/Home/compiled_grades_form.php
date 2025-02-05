<?php

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include("../LoginRegisterAuthentication/connection.php");

// Retrieve student_id from GET request, defaulting to 0 if not provided
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

// If student_id is missing or invalid, show an error or redirect
if ($student_id <= 0) {
    echo "<div class='alert alert-danger' role='alert'>Invalid student ID. Please provide a valid student ID in the URL.</div>";
    exit();
}

// Prepare the SQL query with a placeholder
$query = "
    SELECT sg.*, s.learners_name, sub.name AS subject_name
    FROM student_grades sg
    JOIN students s ON sg.student_id = s.id
    JOIN subjects sub ON sg.subject_id = sub.id
    WHERE sg.student_id = ?
    ORDER BY sg.quarter ASC
";

// Prepare the statement
$stmt = $connection->prepare($query);

if ($stmt === false) {
    die("Prepare failed: " . $connection->error);
}

// Bind the student_id parameter
$stmt->bind_param("i", $student_id);

// Execute the statement
$stmt->execute();

// Get the result
$result = $stmt->get_result();

if ($result === false) {
    die("Query failed: " . $stmt->error);
}

// Check if any rows were returned
if ($result->num_rows === 0) {
    echo "<div class='alert alert-warning' role='alert'>No grades found for student ID: $student_id.</div>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compiled Grades Form</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h2>All Quarters' Grades for Student</h2>

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
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['learners_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['quarter']); ?></td>
                    <td><?php echo htmlspecialchars($row['written_exam']); ?></td>
                    <td><?php echo htmlspecialchars($row['performance_task']); ?></td>
                    <td><?php echo htmlspecialchars($row['quarterly_exam']); ?></td>
                    <td><?php echo htmlspecialchars($row['final_grade']); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
