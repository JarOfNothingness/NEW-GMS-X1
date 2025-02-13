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

// Function to get total summary grades
function getTotalSummaryGrades($connection) {
    $sql = "SELECT s.learners_name, sub.name AS subject_name, 
                   asm.quarter, asm.written_works_total, asm.written_works_ps, asm.written_works_ws,
                   asm.performance_tasks_total, asm.performance_tasks_ps, asm.performance_tasks_ws,
                   asm.quarterly_assessment_score, asm.quarterly_assessment_ps, asm.quarterly_assessment_ws,
                   asm.initial_grade, asm.quarterly_grade
            FROM students s
            JOIN assessment_summary asm ON s.id = asm.student_id
            JOIN subjects sub ON asm.subject_id = sub.id
            ORDER BY s.learners_name, sub.name, FIELD(asm.quarter, '1st', '2nd', '3rd', '4th')";
    
    $result = $connection->query($sql);
    
    if (!$result) {
        // Handle query error
        error_log("Database query failed: " . $connection->error);
        return null;
    }
    
    $summaryData = [];
    while ($row = $result->fetch_assoc()) {
        $summaryData[$row['learners_name']][$row['subject_name']][$row['quarter']] = [
            'written_works' => [
                'total' => $row['written_works_total'],
                'ps' => $row['written_works_ps'],
                'ws' => $row['written_works_ws']
            ],
            'performance_tasks' => [
                'total' => $row['performance_tasks_total'],
                'ps' => $row['performance_tasks_ps'],
                'ws' => $row['performance_tasks_ws']
            ],
            'quarterly_assessment' => [
                'score' => $row['quarterly_assessment_score'],
                'ps' => $row['quarterly_assessment_ps'],
                'ws' => $row['quarterly_assessment_ws']
            ],
            'initial_grade' => $row['initial_grade'],
            'quarterly_grade' => $row['quarterly_grade']
        ];
    }
    
    return $summaryData;
}

// Get the summary data
$summaryGrades = getTotalSummaryGrades($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total Summary Grades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container-fluid {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 20px;
        }
        h2 {
            color: #0d6efd;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .table th {
            background-color: #e9ecef;
        }
        .table-hover tbody tr:hover {
            background-color: #f1f3f5;
        }
        .btn-back {
            margin-bottom: 20px;
        }
        .quarter-header {
            background-color: #d1e7dd !important;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Total Summary Grades</h2>
            <a href="add_grade.php" class="btn btn-primary btn-back">
                <i class="fas fa-arrow-left me-2"></i>Back to Add Grades
            </a>
        </div>
        
        <table id="summaryTable" class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th rowspan="2">Student Name</th>
                    <th rowspan="2">Subject</th>
                    <th colspan="4" class="text-center quarter-header">1st Quarter</th>
                    <th colspan="4" class="text-center quarter-header">2nd Quarter</th>
                    <th colspan="4" class="text-center quarter-header">3rd Quarter</th>
                    <th colspan="4" class="text-center quarter-header">4th Quarter</th>
                    <th rowspan="2">Final Grade</th>
                </tr>
                <tr>
                    <th>WW</th>
                    <th>PT</th>
                    <th>QA</th>
                    <th>QG</th>
                    <th>WW</th>
                    <th>PT</th>
                    <th>QA</th>
                    <th>QG</th>
                    <th>WW</th>
                    <th>PT</th>
                    <th>QA</th>
                    <th>QG</th>
                    <th>WW</th>
                    <th>PT</th>
                    <th>QA</th>
                    <th>QG</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($summaryGrades as $studentName => $subjects): ?>
                    <?php foreach ($subjects as $subjectName => $quarters): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($studentName); ?></td>
                            <td><?php echo htmlspecialchars($subjectName); ?></td>
                            <?php 
                            $finalGrade = 0;
                            $quarterCount = 0;
                            foreach (['1st', '2nd', '3rd', '4th'] as $quarter): 
                                if (isset($quarters[$quarter])) {
                                    $ww = number_format($quarters[$quarter]['written_works']['ws'], 2);
                                    $pt = number_format($quarters[$quarter]['performance_tasks']['ws'], 2);
                                    $qa = number_format($quarters[$quarter]['quarterly_assessment']['ws'], 2);
                                    $qg = number_format($quarters[$quarter]['quarterly_grade'], 2);
                                    $finalGrade += $quarters[$quarter]['quarterly_grade'];
                                    $quarterCount++;
                                } else {
                                    $ww = $pt = $qa = $qg = 'N/A';
                                }
                            ?>
                                <td><?php echo $ww; ?></td>
                                <td><?php echo $pt; ?></td>
                                <td><?php echo $qa; ?></td>
                                <td class="font-weight-bold"><?php echo $qg; ?></td>
                            <?php endforeach; ?>
                            <td class="font-weight-bold"><?php echo $quarterCount > 0 ? number_format($finalGrade / $quarterCount, 2) : 'N/A'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap5.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#summaryTable').DataTable({
            "pageLength": 25,
            "ordering": true,
            "info": true,
            "searching": true,
            "scrollX": true,
            "language": {
                "search": "Filter records:",
                "paginate": {
                    "first": '<i class="fas fa-angle-double-left"></i>',
                    "previous": '<i class="fas fa-angle-left"></i>',
                    "next": '<i class="fas fa-angle-right"></i>',
                    "last": '<i class="fas fa-angle-double-right"></i>'
                }
            }
        });
    });
    </script>
</body>
</html>