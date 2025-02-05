<?php
session_start();
include('../LoginRegisterAuthentication/connection.php');

header('Content-Type: application/json');

if (!isset($_GET['student_id']) || !isset($_GET['subject_id'])) {
    die(json_encode(['error' => 'Missing required parameters']));
}

$student_id = (int)$_GET['student_id'];
$subject_id = (int)$_GET['subject_id'];

// Fetch assessment summary data
$query = "SELECT 
    AVG(quarterly_grade) as final_grade
FROM 
    assessment_summary 
WHERE 
    student_id = ? 
    AND subject_id = ?
    AND quarterly_grade IS NOT NULL";

$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "ii", $student_id, $subject_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$grade_data = mysqli_fetch_assoc($result);

echo json_encode([
    'success' => true,
    'final_grade' => $grade_data['final_grade'] ? number_format($grade_data['final_grade'], 2) : null
]);
?>