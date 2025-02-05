<?php
session_start();
include_once("../LoginRegisterAuthentication/connection.php");
include_once("functions.php");

// Check if user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Check if teacher ID is provided
if (!isset($_GET['id'])) {
    die("Teacher ID not provided");
}

$teacherId = mysqli_real_escape_string($connection, $_GET['id']);

// Fetch teacher details
$teacherQuery = "SELECT name FROM user WHERE userid = $teacherId AND role = 'Teacher'";
$teacherResult = mysqli_query($connection, $teacherQuery);
$teacherData = mysqli_fetch_assoc($teacherResult);

if (!$teacherData) {
    die("Teacher not found");
}

// Handle grade approval
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = mysqli_real_escape_string($connection, $_POST['grade_id']);
    $action = mysqli_real_escape_string($connection, $_POST['action']);
    $quarter = mysqli_real_escape_string($connection, $_POST['quarter']);
    $subjectId = mysqli_real_escape_string($connection, $_POST['subject_id']);
    
    if ($action === 'approve' || $action === 'reject') {
        // Start transaction
        mysqli_begin_transaction($connection);
        
        try {
            // Update the assessment_summary table
            $updateQuery = "
                UPDATE assessment_summary 
                SET approval_status = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE student_id = ? 
                AND subject_id = ? 
                AND quarter = ?
                AND approval_status = 'pending'";
            
            $stmt = mysqli_prepare($connection, $updateQuery);
            mysqli_stmt_bind_param($stmt, "siis", $action, $studentId, $subjectId, $quarter);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error updating grade status");
            }
            
            // Log the approval action
            $logQuery = "
                INSERT INTO grade_approval_log 
                (student_id, subject_id, quarter, action, admin_id, approval_date)
                VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
            
            $stmt = mysqli_prepare($connection, $logQuery);
            mysqli_stmt_bind_param($stmt, "iissi", $studentId, $subjectId, $quarter, $action, $_SESSION['userid']);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error logging approval action");
            }
            
            // If everything is successful, commit the transaction
            mysqli_commit($connection);
            $message = "Grade " . ucfirst($action) . "d successfully";
            
        } catch (Exception $e) {
            // If there's an error, rollback the changes
            mysqli_rollback($connection);
            $error = "Error processing grade approval: " . $e->getMessage();
        }
    }
}

// Fetch pending grade approvals for the teacher with real-time status
$gradesQuery = "
    SELECT 
        s.id AS student_id,
        s.learners_name,
        s.`grade & section`,
        subj.id AS subject_id,
        subj.name AS subject_name,
        asm.approval_status,
        MAX(CASE WHEN asm.quarter = '1st' THEN asm.quarterly_grade END) as quarter_1,
        MAX(CASE WHEN asm.quarter = '2nd' THEN asm.quarterly_grade END) as quarter_2,
        MAX(CASE WHEN asm.quarter = '3rd' THEN asm.quarterly_grade END) as quarter_3,
        MAX(CASE WHEN asm.quarter = '4th' THEN asm.quarterly_grade END) as quarter_4,
        MAX(CASE WHEN asm.quarter = '1st' THEN asm.approval_status END) as status_1,
        MAX(CASE WHEN asm.quarter = '2nd' THEN asm.approval_status END) as status_2,
        MAX(CASE WHEN asm.quarter = '3rd' THEN asm.approval_status END) as status_3,
        MAX(CASE WHEN asm.quarter = '4th' THEN asm.approval_status END) as status_4,
        MAX(CASE WHEN asm.quarter = '1st' THEN asm.updated_at END) as updated_1,
        MAX(CASE WHEN asm.quarter = '2nd' THEN asm.updated_at END) as updated_2,
        MAX(CASE WHEN asm.quarter = '3rd' THEN asm.updated_at END) as updated_3,
        MAX(CASE WHEN asm.quarter = '4th' THEN asm.updated_at END) as updated_4
    FROM students s
    JOIN assessment_summary asm ON s.id = asm.student_id
    JOIN subjects subj ON asm.subject_id = subj.id
    WHERE s.user_id = ?
    GROUP BY s.id, s.learners_name, s.`grade & section`, subj.id, subj.name
    ORDER BY subj.name, s.`grade & section`, s.learners_name
";

$stmt = mysqli_prepare($connection, $gradesQuery);
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$gradesResult = mysqli_stmt_get_result($stmt);

if (!$gradesResult) {
    die("Error fetching grades: " . mysqli_error($connection));
}

$grades = [];
while ($row = mysqli_fetch_assoc($gradesResult)) {
    $sum = 0;
    $count = 0;

    foreach (['quarter_1', 'quarter_2', 'quarter_3', 'quarter_4'] as $quarter) {
        if (is_numeric($row[$quarter])) {
            $sum += $row[$quarter];
            $count++;
        }
    }

    if ($count > 0) {
        $final_grade = round($sum / $count, 2);
        $remarks = ($final_grade >= 75) ? 'Passed' : 'Failed';
    } else {
        $final_grade = 'N/A';
        $remarks = 'N/A';
    }

    $grades[] = [
        'student_id' => $row['student_id'],
        'learners_name' => $row['learners_name'],
        'grade_section' => $row['grade & section'],
        'subject_id' => $row['subject_id'],
        'subject_name' => $row['subject_name'],
        'quarter_1' => $row['quarter_1'] ?? 'N/A',
        'quarter_2' => $row['quarter_2'] ?? 'N/A',
        'quarter_3' => $row['quarter_3'] ?? 'N/A',
        'quarter_4' => $row['quarter_4'] ?? 'N/A',
        'status_1' => $row['status_1'] ?? 'pending',
        'status_2' => $row['status_2'] ?? 'pending',
        'status_3' => $row['status_3'] ?? 'pending',
        'status_4' => $row['status_4'] ?? 'pending',
        'updated_1' => $row['updated_1'],
        'updated_2' => $row['updated_2'],
        'updated_3' => $row['updated_3'],
        'updated_4' => $row['updated_4'],
        'final_grade' => $final_grade,
        'remarks' => $remarks
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Grades</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .status-badge {
            padding: 0.25em 0.75em;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-pending {
            background-color: #ffeeba;
            color: #856404;
        }
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .approval-buttons {
            display: inline-flex;
            gap: 0.5rem;
        }
        .table th {
            vertical-align: middle;
            white-space: nowrap;
        }
        .action-timestamp {
            font-size: 0.75rem;
            color: #6c757d;
            display: block;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Approve Grades for <?php echo htmlspecialchars($teacherData['name']); ?></h1>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Grade & Section</th>
                        <th>Subject</th>
                        <th>1st Quarter</th>
                        <th>2nd Quarter</th>
                        <th>3rd Quarter</th>
                        <th>4th Quarter</th>
                        <th>Final Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grades as $grade): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($grade['learners_name']); ?></td>
                            <td><?php echo htmlspecialchars($grade['grade_section']); ?></td>
                            <td><?php echo htmlspecialchars($grade['subject_name']); ?></td>
                            
                            <?php foreach (range(1, 4) as $quarter): ?>
                                <td>
                                    <?php 
                                    $quarter_key = "quarter_" . $quarter;
                                    $status_key = "status_" . $quarter;
                                    $updated_key = "updated_" . $quarter;
                                    $quarter_text = $quarter . ($quarter == 1 ? "st" : ($quarter == 2 ? "nd" : ($quarter == 3 ? "rd" : "th")));
                                    ?>
                                    
                                    <?php if ($grade[$quarter_key] !== 'N/A'): ?>
                                        <?php echo htmlspecialchars($grade[$quarter_key]); ?>
                                        <span class="status-badge status-<?php echo $grade[$status_key]; ?>">
                                            <?php echo ucfirst($grade[$status_key]); ?>
                                        </span>
                                        
                                        <?php if ($grade[$updated_key]): ?>
                                            <span class="action-timestamp">
                                                Updated: <?php echo date('M d, Y H:i', strtotime($grade[$updated_key])); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($grade[$status_key] === 'pending'): ?>
                                            <div class="approval-buttons">
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="grade_id" value="<?php echo $grade['student_id']; ?>">
                                                    <input type="hidden" name="subject_id" value="<?php echo $grade['subject_id']; ?>">
                                                    <input type="hidden" name="quarter" value="<?php echo $quarter_text; ?>">
                                                    <button type="submit" name="action" value="approve" class="btn btn-success btn-sm" title="Approve">✓</button>
                                                    <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm" title="Reject">✗</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            
                            <td><?php echo htmlspecialchars($grade['final_grade']); ?></td>
                            <td><?php echo htmlspecialchars($grade['remarks']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($grades)): ?>
            <div class="alert alert-info">
                No pending grade approvals for this teacher.
            </div>
        <?php endif; ?>

        <a href="manage_user.php" class="btn btn-primary">Back to User Management</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>