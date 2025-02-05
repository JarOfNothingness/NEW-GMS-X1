<?php
include("../LoginRegisterAuthentication/connection.php");

header('Content-Type: application/json');

$section = $_GET['section'] ?? '';
$subject = $_GET['subject'] ?? '';
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

if (!$section || !$subject || !$start || !$end) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$query = "SELECT a.student_id, s.learners_name, a.date, a.status 
          FROM attendance a 
          JOIN students s ON a.student_id = s.id 
          WHERE s.`grade & section` = ? 
          AND a.subject_id = ? 
          AND a.date BETWEEN ? AND ?";

$stmt = $connection->prepare($query);
$stmt->bind_param("ssss", $section, $subject, $start, $end);
$stmt->execute();
$result = $stmt->get_result();

$attendance_data = [];
while ($row = $result->fetch_assoc()) {
    $attendance_data[] = [
        'student_name' => $row['learners_name'],
        'date' => $row['date'],
        'status' => $row['status']
    ];
}

echo json_encode($attendance_data);