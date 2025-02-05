<?php
session_start();
include("../LoginRegisterAuthentication/connection.php");

if (!isset($_SESSION['username'])) {
    exit(json_encode([]));
}

$userid = $_SESSION['userid'];
$grade_section = isset($_POST['grade_section']) ? $_POST['grade_section'] : null;
$subject_id = isset($_POST['subject_id']) ? $_POST['subject_id'] : null;

// Get available quarters
$query = "SELECT DISTINCT a.quarter 
          FROM assessments a 
          WHERE a.user_id = ?";
$params = array($userid);
$types = "i";

if ($grade_section) {
    $query .= " AND a.grade_section = ?";
    $params[] = $grade_section;
    $types .= "s";
}

if ($subject_id) {
    $query .= " AND a.subject_id = ?";
    $params[] = $subject_id;
    $types .= "i";
}

$query .= " ORDER BY FIELD(a.quarter, '1st', '2nd', '3rd', '4th')";

$stmt = $connection->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$quarters = array();
while ($row = $result->fetch_assoc()) {
    $quarters[] = $row['quarter'];
}

echo json_encode($quarters);
?>