<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include("../LoginRegisterAuthentication/connection.php");

// Initialize search variable
$search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';

// Build the query
$query = "SELECT s.id, s.learners_name, sub.name as subject_name, sg.quarter, sg.written_exam, sg.performance_task, sg.quarterly_exam, sg.final_grade 
          FROM student_grades sg
          JOIN students s ON sg.student_id = s.id
          JOIN subjects sub ON sg.subject_id = sub.id
          WHERE s.learners_name LIKE '%$search%' OR sub.name LIKE '%$search%'
          ORDER BY s.learners_name ASC, sub.name ASC, sg.quarter ASC";

$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Records</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .search-form {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">

        <h1 class="mt-5">Search Records</h1>
        <!-- Search Form -->
        <form class="search-form" method="GET" action="">
        <a href="search.php" class="btn btn-success">All</a>
                    
                    <a href="ClassRecord.php" class="btn btn-danger">Back</a>
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="search" placeholder="Search by student name or subject" value="<?php echo htmlspecialchars($search); ?>">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </div>
        </form>
        
        <!-- Results Table -->
        <table class="table table-bordered table-striped mt-3">
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
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['learners_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['quarter']); ?></td>
                            <td><?php echo htmlspecialchars($row['written_exam']) ?: 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($row['performance_task']) ?: 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($row['quarterly_exam']) ?: 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($row['final_grade']) ?: 'N/A'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
