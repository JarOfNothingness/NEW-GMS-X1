<?php
session_start();
include("../LoginRegisterAuthentication/connection.php");
include("../crud/header.php");

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['userid'];

// Fetch unique assigned students
$sql_students = "SELECT DISTINCT s.id AS student_id, s.learners_name
                  FROM students s
                  JOIN teacher_assignments ta ON s.id = ta.student_id
                  WHERE ta.teacher_id = ?
                  ORDER BY s.learners_name ASC";
$stmt_students = $connection->prepare($sql_students);
$stmt_students->bind_param("i", $teacher_id);
$stmt_students->execute();
$result_students = $stmt_students->get_result();

// Fetch unique assigned subjects
$sql_subjects = "SELECT DISTINCT sub.id, sub.name
                 FROM subjects sub
                 JOIN teacher_assignments ta ON sub.id = ta.subject_id
                 WHERE ta.teacher_id = ?
                 ORDER BY sub.name ASC";
$stmt_subjects = $connection->prepare($sql_subjects);
$stmt_subjects->bind_param("i", $teacher_id);
$stmt_subjects->execute();
$result_subjects = $stmt_subjects->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            margin-top: 30px;
        }

        .table thead {
            background-color: #007bff;
            color: white;
        }

        .navbar {
            background-color: #007bff;
        }

        .navbar a {
            color: white !important;
        }

        .dashboard-title {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2 class="dashboard-title">Teacher Dashboard</h2>

        <!-- Assigned Students Table -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">My Assigned Students</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_students->num_rows > 0) { 
                            $counter = 1;
                            while ($row = $result_students->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo htmlspecialchars($row['learners_name']); ?></td>
                                </tr>
                        <?php } } else { ?>
                            <tr>
                                <td colspan="2" class="text-center">No students assigned.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <p><strong>Total Students Assigned:</strong> <?php echo $result_students->num_rows; ?></p>
            </div>
        </div>

        <!-- Assigned Subjects Table -->
        <div class="card">
            <div class="card-header bg-primary text-white">My Assigned Subjects</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_subjects->num_rows > 0) { 
                            $counter = 1;
                            while ($row = $result_subjects->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                </tr>
                        <?php } } else { ?>
                            <tr>
                                <td colspan="2" class="text-center">No subjects assigned.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <p><strong>Total Subjects Assigned:</strong> <?php echo $result_subjects->num_rows; ?></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
