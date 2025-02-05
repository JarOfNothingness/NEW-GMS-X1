<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include("../LoginRegisterAuthentication/connection.php");

$record_id = isset($_GET['id']) ? intval($_GET['id']) : '';

// Fetch the student and subject info
$query = "SELECT sg.*, s.learners_name, sub.name as subject_name 
          FROM student_grades sg
          JOIN students s ON sg.student_id = s.id
          JOIN subjects sub ON sg.subject_id = sub.id
          WHERE sg.id = $record_id";

$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Record not found.");
}

// Fetch the student's name and subject
$record = mysqli_fetch_assoc($result);
$student_id = $record['student_id'];
$subject_id = $record['subject_id'];

// Fetch all quarters' data for the student and subject
$query_all_quarters = "SELECT * FROM student_grades 
                       WHERE student_id = $student_id AND subject_id = $subject_id 
                       ORDER BY quarter";

$result_all_quarters = mysqli_query($connection, $query_all_quarters);

$grades = [];
$totals = ['written_exam' => 0, 'performance_task' => 0, 'quarterly_exam' => 0, 'final_grade' => 0];
$count = 0;

while ($row = mysqli_fetch_assoc($result_all_quarters)) {
    $quarter = $row['quarter'];
    $grades[$quarter] = $row;

    // Summing up scores
    $totals['written_exam'] += $row['written_exam'] ?? 0;
    $totals['performance_task'] += $row['performance_task'] ?? 0;
    $totals['quarterly_exam'] += $row['quarterly_exam'] ?? 0;
    $totals['final_grade'] += $row['final_grade'] ?? 0;
    $count++;
}

// Calculate averages based on actual data entries
$averages = array_map(function($total) use ($count) {
    return $count > 0 ? $total / $count : 0;
}, $totals);

// Determine remarks
$remark = '';
if ($averages['final_grade'] >= 90) {
    $remark = 'Excellent';
} elseif ($averages['final_grade'] >= 80) {
    $remark = 'Very Good';
} elseif ($averages['final_grade'] >= 70) {
    $remark = 'Good';
} elseif ($averages['final_grade'] >= 60) {
    $remark = 'Satisfactory';
} else {
    $remark = 'Needs Improvement';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Record</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table th, .table td {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Record Details</h2>
        <table class="table table-bordered">
            <tr>
                <th>Student</th>
                <td><?php echo htmlspecialchars($record['learners_name']); ?></td>
            </tr>
            <tr>
                <th>Subject</th>
                <td><?php echo htmlspecialchars($record['subject_name']); ?></td>
            </tr>
            <?php for ($quarter = 1; $quarter <= 4; $quarter++): ?>
                <?php if (isset($grades[$quarter])): ?>
                    <?php $data = $grades[$quarter]; ?>
                    <tr>
                        <th>Quarter <?php echo $quarter; ?> Written Exam</th>
                        <td><?php echo htmlspecialchars($data['written_exam']) ?: 'N/A'; ?></td>
                    </tr>
                    <tr>
                        <th>Quarter <?php echo $quarter; ?> Performance Task</th>
                        <td><?php echo htmlspecialchars($data['performance_task']) ?: 'N/A'; ?></td>
                    </tr>
                    <tr>
                        <th>Quarter <?php echo $quarter; ?> Quarterly Exam</th>
                        <td><?php echo htmlspecialchars($data['quarterly_exam']) ?: 'N/A'; ?></td>
                    </tr>
                    <tr>
                        <th>Quarter <?php echo $quarter; ?> Final Grade</th>
                        <td><?php echo htmlspecialchars($data['final_grade']) ?: 'N/A'; ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <th>Quarter <?php echo $quarter; ?> Written Exam</th>
                        <td>N/A</td>
                    </tr>
                    <tr>
                        <th>Quarter <?php echo $quarter; ?> Performance Task</th>
                        <td>N/A</td>
                    </tr>
                    <tr>
                        <th>Quarter <?php echo $quarter; ?> Quarterly Exam</th>
                        <td>N/A</td>
                    </tr>
                    <tr>
                        <th>Quarter <?php echo $quarter; ?> Final Grade</th>
                        <td>N/A</td>
                    </tr>
                <?php endif; ?>
            <?php endfor; ?>
            <tr>
                <th>Total Written Exam</th>
                <td><?php echo htmlspecialchars($totals['written_exam']); ?></td>
            </tr>
            <tr>
                <th>Total Performance Task</th>
                <td><?php echo htmlspecialchars($totals['performance_task']); ?></td>
            </tr>
            <tr>
                <th>Total Quarterly Exam</th>
                <td><?php echo htmlspecialchars($totals['quarterly_exam']); ?></td>
            </tr>
            <tr>
                <th>Total Final Grade</th>
                <td><?php echo htmlspecialchars($totals['final_grade']); ?></td>
            </tr>
            <tr>
                <th>Average Written Exam</th>
                <td><?php echo htmlspecialchars(number_format($averages['written_exam'], 2)); ?></td>
            </tr>
            <tr>
                <th>Average Performance Task</th>
                <td><?php echo htmlspecialchars(number_format($averages['performance_task'], 2)); ?></td>
            </tr>
            <tr>
                <th>Average Quarterly Exam</th>
                <td><?php echo htmlspecialchars(number_format($averages['quarterly_exam'], 2)); ?></td>
            </tr>
            <tr>
                <th>Average Final Grade</th>
                <td><?php echo htmlspecialchars(number_format($averages['final_grade'], 2)); ?></td>
            </tr>
            <tr>
                <th>Remark</th>
                <td><?php echo htmlspecialchars($remark); ?></td>
            </tr>
        </table>
        <a href="classrecord.php" class="btn btn-primary">Back to Class Record</a>
    </div>
</body>
</html>
