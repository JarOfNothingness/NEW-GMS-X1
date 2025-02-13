<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include("../LoginRegisterAuthentication/connection.php");

// Prepare and bind
$stmt = $connection->prepare("INSERT INTO student_quiz (student_id, user_id, student_subject_id, assessment_type_id, raw_score, max_score, status, quarter) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iiiiddss", $student_id, $user_id, $student_subject_id, $assessment_type_id, $raw_score, $max_score, $status, $quarter);

// Set parameters and execute
$student_id = $_POST['student_id'];
$user_id = $_POST['user_id'];
$student_subject_id = 1; // This should be dynamically set based on your system's logic
$assessment_type_id = $_POST['assessment_type_id'];
$raw_score = $_POST['raw_score'];
$max_score = $_POST['max_score'];
$status = $_POST['status'];
$quarter = $_POST['quarter'];

if ($stmt->execute()) {
    echo "New quiz added successfully";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$connection->close();
?>