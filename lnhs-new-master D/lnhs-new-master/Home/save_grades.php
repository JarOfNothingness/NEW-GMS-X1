<?php
session_start();
include("../LoginRegisterAuthentication/connection.php");

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_id = $_POST['selected_subject_id'];
    $quarter = $_POST['selected_quarter'];

    foreach ($_POST['student_id'] as $student_id => $value) {
        // Sanitize input and ensure numeric values
        $written_works = isset($_POST['written_works'][$student_id]) && is_numeric($_POST['written_works'][$student_id]) ? (float)$_POST['written_works'][$student_id] : 0;
        $performance_tasks = isset($_POST['performance_tasks'][$student_id]) && is_numeric($_POST['performance_tasks'][$student_id]) ? (float)$_POST['performance_tasks'][$student_id] : 0;
        $quarterly_assessment = isset($_POST['quarterly_assessment'][$student_id]) && is_numeric($_POST['quarterly_assessment'][$student_id]) ? (float)$_POST['quarterly_assessment'][$student_id] : 0;

        // Calculate initial grade
        $initial_grade = ($written_works * 0.4) + ($performance_tasks * 0.4) + ($quarterly_assessment * 0.2);

        // Insert grades into the database for each student
        $insert_query = "INSERT INTO grades (student_id, subject, grade, quarter)
                         VALUES (?, ?, ?, ?)"; // Use the selected quarter dynamically

        $stmt = mysqli_prepare($connection, $insert_query);
        mysqli_stmt_bind_param($stmt, 'isds', $student_id, $subject_id, $initial_grade, $quarter);

        if (mysqli_stmt_execute($stmt)) {
            echo "Grades saved successfully for Student ID: " . $student_id . "<br>";
        } else {
            echo "Error saving grades for Student ID: " . $student_id . "<br>";
        }
    }
}
?>
