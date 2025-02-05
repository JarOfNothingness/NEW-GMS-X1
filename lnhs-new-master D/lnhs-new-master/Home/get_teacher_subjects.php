<?php
// get_teacher_subjects.php
session_start();
include_once("../LoginRegisterAuthentication/connection.php");

if (!isset($_POST['teacher_id'])) {
    die("Missing teacher ID");
}

$teacherId = intval($_POST['teacher_id']);
$gradeSection = isset($_POST['grade_section']) ? $_POST['grade_section'] : '';

// Modified query to match your database structure
$query = "
    SELECT DISTINCT subj.id, subj.name
    FROM assessment_summary asm
    JOIN subjects subj ON asm.subject_id = subj.id
    JOIN students s ON asm.student_id = s.id
    WHERE s.user_id = ?
    AND s.`grade & section` = ?
    ORDER BY subj.name";

$stmt = $connection->prepare($query);
$stmt->bind_param("is", $teacherId, $gradeSection);
$stmt->execute();
$result = $stmt->get_result();

echo '<option value="">Select Subject</option>';
while ($subject = $result->fetch_assoc()) {
    echo '<option value="' . $subject['id'] . '">' . htmlspecialchars($subject['name']) . '</option>';
}
?>