<?php
session_start();
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
include("../LoginRegisterAuthentication/connection.php");
$userid = $_SESSION['userid'];

// Check connection
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the selected grade & section
$gradeSection = isset($_GET['grade_section']) ? $_GET['grade_section'] : '';

if (!empty($gradeSection)) {
    // Get student IDs that match the selected grade & section
    $studentsQuery = "SELECT id FROM students WHERE `grade & section` = ? AND user_id = ?";
    $stmt = mysqli_prepare($connection, $studentsQuery);
    mysqli_stmt_bind_param($stmt, 'si', $gradeSection, $userid);
    mysqli_stmt_execute($stmt);
    $studentsResult = mysqli_stmt_get_result($stmt);

    $studentIds = [];
    while ($row = mysqli_fetch_assoc($studentsResult)) {
        $studentIds[] = $row['id'];
    }

    if (!empty($studentIds)) {
        // Prepare placeholders for student IDs in the IN clause
        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
        
        // Prepare query to fetch subjects based on student IDs
        $subjectsQuery = "SELECT MIN(id) as id, description 
                  FROM student_subjects 
                  WHERE student_id IN ($placeholders) 
                  GROUP BY description 
                  ORDER BY description";
        $stmt = mysqli_prepare($connection, $subjectsQuery);
        
        // Dynamically bind student IDs
        $types = str_repeat('i', count($studentIds));
        mysqli_stmt_bind_param($stmt, $types, ...$studentIds);
        mysqli_stmt_execute($stmt);
        $subjectsResult = mysqli_stmt_get_result($stmt);

        // Generate options for subject dropdown
        while ($subject = mysqli_fetch_assoc($subjectsResult)) {
            $options .= '<option value="' . htmlspecialchars($subject['description']) . '">' . htmlspecialchars($subject['description']) . '</option>';
        }

        echo $options;
    } else {
        echo '<option value="">No subjects found</option>';
    }
} else {
    echo '<option value="">Please select a grade & section</option>';
}
?>