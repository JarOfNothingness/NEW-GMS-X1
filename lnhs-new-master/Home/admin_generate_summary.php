<?php
session_start();
include_once("../LoginRegisterAuthentication/connection.php");

if (!isset($_POST['teacher_id']) || !isset($_POST['grade_section']) || !isset($_POST['subject_id']) || !isset($_POST['quarter'])) {
    die("Missing required parameters");
}

$teacherId = intval($_POST['teacher_id']);
$gradeSection = $_POST['grade_section'];
$subjectId = intval($_POST['subject_id']);
$quarter = $_POST['quarter'];

// Get the subject name
$subjectQuery = "SELECT name FROM subjects WHERE id = ?";
$subjectStmt = $connection->prepare($subjectQuery);
$subjectStmt->bind_param("i", $subjectId);
$subjectStmt->execute();
$subjectResult = $subjectStmt->get_result();
$subjectName = $subjectResult->fetch_assoc()['name'];

// Updated query to include proper status check and last approval action
$query = "
    SELECT DISTINCT
        s.learners_name,
        asm.written_works_total,
        asm.written_works_ps,
        asm.written_works_ws,
        asm.performance_tasks_total,
        asm.performance_tasks_ps,
        asm.performance_tasks_ws,
        asm.quarterly_assessment_score,
        asm.quarterly_assessment_ps,
        asm.quarterly_assessment_ws,
        asm.initial_grade,
        asm.quarterly_grade,
        asm.approval_status,
        asm.updated_at,
        FIRST_VALUE(gal.action) OVER (
            PARTITION BY s.id, asm.subject_id, asm.quarter 
            ORDER BY gal.approval_date DESC
        ) as last_action,
        FIRST_VALUE(gal.approval_date) OVER (
            PARTITION BY s.id, asm.subject_id, asm.quarter 
            ORDER BY gal.approval_date DESC
        ) as last_approval_date,
        FIRST_VALUE(u.name) OVER (
            PARTITION BY s.id, asm.subject_id, asm.quarter 
            ORDER BY gal.approval_date DESC
        ) as approved_by
    FROM assessment_summary asm
    JOIN students s ON asm.student_id = s.id
    LEFT JOIN grade_approval_log gal ON asm.student_id = gal.student_id 
        AND asm.subject_id = gal.subject_id 
        AND asm.quarter = gal.quarter
    LEFT JOIN user u ON gal.admin_id = u.userid
    WHERE s.user_id = ?
    AND s.`grade & section` = ?
    AND asm.subject_id = ?
    AND asm.quarter = ?
    ORDER BY s.learners_name";


$stmt = $connection->prepare($query);
$stmt->bind_param("isis", $teacherId, $gradeSection, $subjectId, $quarter);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo '<div class="alert alert-info mt-3">No records found for the selected criteria.</div>';
    exit;
}
?>

<div class="table-responsive mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Class Record Summary</h4>
        <div class="summary-info text-end">
            <p class="mb-1"><strong>Subject:</strong> <?php echo htmlspecialchars($subjectName); ?></p>
            <p class="mb-1"><strong>Grade & Section:</strong> <?php echo htmlspecialchars($gradeSection); ?></p>
            <p class="mb-0"><strong>Quarter:</strong> <?php echo htmlspecialchars($quarter); ?></p>
        </div>
    </div>

    <table class="table table-striped table-bordered">
        <thead class="table-light">
            <tr>
                <th rowspan="2" class="align-middle">Student Name</th>
                <th colspan="3" class="text-center">Written Works</th>
                <th colspan="3" class="text-center">Performance Tasks</th>
                <th colspan="3" class="text-center">Quarterly Assessment</th>
                <th rowspan="2" class="align-middle text-center">Initial<br>Grade</th>
                <th rowspan="2" class="align-middle text-center">Quarterly<br>Grade</th>
         
                <th rowspan="2" class="align-middle text-center">Last Action</th>
            </tr>
            <tr>
                <th class="text-center">Total</th>
                <th class="text-center">PS</th>
                <th class="text-center">WS</th>
                <th class="text-center">Total</th>
                <th class="text-center">PS</th>
                <th class="text-center">WS</th>
                <th class="text-center">Score</th>
                <th class="text-center">PS</th>
                <th class="text-center">WS</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['learners_name']); ?></td>
                    
                    <!-- Written Works -->
                    <td class="text-center"><?php echo number_format($row['written_works_total'] ?: 0, 2); ?></td>
                    <td class="text-center"><?php echo number_format($row['written_works_ps'] ?: 0, 2); ?></td>
                    <td class="text-center"><?php echo number_format($row['written_works_ws'] ?: 0, 2); ?></td>
                    
                    <!-- Performance Tasks -->
                    <td class="text-center"><?php echo number_format($row['performance_tasks_total'] ?: 0, 2); ?></td>
                    <td class="text-center"><?php echo number_format($row['performance_tasks_ps'] ?: 0, 2); ?></td>
                    <td class="text-center"><?php echo number_format($row['performance_tasks_ws'] ?: 0, 2); ?></td>
                    
                    <!-- Quarterly Assessment -->
                    <td class="text-center"><?php echo number_format($row['quarterly_assessment_score'] ?: 0, 2); ?></td>
                    <td class="text-center"><?php echo number_format($row['quarterly_assessment_ps'] ?: 0, 2); ?></td>
                    <td class="text-center"><?php echo number_format($row['quarterly_assessment_ws'] ?: 0, 2); ?></td>
                    
                    <!-- Grades -->
                    <td class="text-center"><?php echo number_format($row['initial_grade'] ?: 0, 2); ?></td>
                    <td class="text-center fw-bold"><?php echo number_format($row['quarterly_grade'] ?: 0, 2); ?></td>
                    
              

                    <!-- Last Action -->
                    <td class="text-center">
                        <?php if ($row['last_action']): ?>
                            <small>
                                <?php echo ucfirst($row['last_action']); ?>d by 
                                <?php echo htmlspecialchars($row['approved_by']); ?><br>
                                <span class="text-muted">
                                    <?php echo date('M d, Y h:i A', strtotime($row['last_approval_date'])); ?>
                                </span>
                            </small>
                        <?php else: ?>
                            <span class="text-muted">No action yet</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<style>
    .badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }
    .text-muted {
        font-size: 0.75rem;
    }
    .table th, .table td {
        vertical-align: middle;
    }
</style>