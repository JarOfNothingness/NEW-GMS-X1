<?php
// get_teacher_quarters.php
session_start();
include_once("../LoginRegisterAuthentication/connection.php");

if (!isset($_POST['teacher_id'])) {
    die("Missing teacher ID");
}

$teacherId = intval($_POST['teacher_id']);
$gradeSection = isset($_POST['grade_section']) ? $_POST['grade_section'] : '';
$subjectId = isset($_POST['subject_id']) ? intval($_POST['subject_id']) : 0;

// Modified query to match your database structure
$query = "
    SELECT DISTINCT asm.quarter
    FROM assessment_summary asm
    JOIN students s ON asm.student_id = s.id
    WHERE s.user_id = ?
    AND s.`grade & section` = ?
    AND asm.subject_id = ?
    ORDER BY FIELD(asm.quarter, '1st', '2nd', '3rd', '4th')";

$stmt = $connection->prepare($query);
$stmt->bind_param("isi", $teacherId, $gradeSection, $subjectId);
$stmt->execute();
$result = $stmt->get_result();

echo '<option value="">Select Quarter</option>';
while ($quarter = $result->fetch_assoc()) {
    echo '<option value="' . htmlspecialchars($quarter['quarter']) . '">' . 
         htmlspecialchars($quarter['quarter']) . ' Quarter</option>';
}
?>