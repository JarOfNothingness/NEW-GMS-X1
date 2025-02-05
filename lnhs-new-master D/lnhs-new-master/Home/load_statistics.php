<?php
session_start();
include("../LoginRegisterAuthentication/connection.php");

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Function to log debug information
function debug_log($message) {
    error_log($message);
}

// Get filter values
$school_year = isset($_GET['school_year']) ? $_GET['school_year'] : '';
$grade_section = isset($_GET['grade_section']) ? $_GET['grade_section'] : '';
$subject_id = isset($_GET['subject']) ? $_GET['subject'] : '';
$quarter = isset($_GET['quarter']) ? $_GET['quarter'] : '';

// Build the WHERE clause for filtering
$where_clause = "WHERE 1=1";
if ($school_year) $where_clause .= " AND s.school_year = '$school_year'";
if ($grade_section) $where_clause .= " AND s.`grade & section` = '$grade_section'";
if ($subject_id) $where_clause .= " AND asm.subject_id = '$subject_id'";
if ($quarter) $where_clause .= " AND asm.quarter = '$quarter'";

// Subquery to get the latest assessment summary for each student and subject
$latest_assessment_summary = "
    SELECT asm1.*
    FROM assessment_summary asm1
    INNER JOIN (
        SELECT student_id, subject_id, MAX(id) as max_id
        FROM assessment_summary
        GROUP BY student_id, subject_id
    ) asm2 ON asm1.id = asm2.max_id
";

// Query to get statistics
$stats_query = "
    SELECT 
        s.id,
        s.learners_name,
        s.gender,
        asm.quarterly_grade,
        asm.quarter
    FROM 
        students s
    JOIN 
        ($latest_assessment_summary) asm ON s.id = asm.student_id
    $where_clause
    ORDER BY asm.quarterly_grade DESC, s.learners_name ASC
";

debug_log("Stats Query: " . $stats_query);

$stats_result = mysqli_query($connection, $stats_query);
if (!$stats_result) {
    die("Query failed: " . mysqli_error($connection));
}

$male_passed = $female_passed = $male_failed = $female_failed = 0;
$passed_students = $failed_students = [];
$processed_students = [];

while ($row = mysqli_fetch_assoc($stats_result)) {
    debug_log("Processing student: " . json_encode($row));
    
    // Skip if we've already processed this student
    if (in_array($row['id'], $processed_students)) {
        continue;
    }
    
    $processed_students[] = $row['id'];
    
    if ($row['quarterly_grade'] >= 75) {
        if ($row['gender'] == 'Male') {
            $male_passed++;
        } else {
            $female_passed++;
        }
        if (count($passed_students) < 10) {
            $passed_students[] = [
                'name' => $row['learners_name'],
                'gender' => $row['gender'],
                'final_grade' => number_format($row['quarterly_grade'], 2)
            ];
        }
    } else {
        if ($row['gender'] == 'Male') {
            $male_failed++;
        } else {
            $female_failed++;
        }
        if (count($failed_students) < 10) {
            $failed_students[] = [
                'name' => $row['learners_name'],
                'gender' => $row['gender'],
                'final_grade' => number_format($row['quarterly_grade'], 2)
            ];
        }
    }
}

// Calculate totals
$total_passed = $male_passed + $female_passed;
$total_failed = $male_failed + $female_failed;

// Prepare the response
$response = [
    'male_passed' => $male_passed,
    'female_passed' => $female_passed,
    'male_failed' => $male_failed,
    'female_failed' => $female_failed,
    'total_passed' => $total_passed,
    'total_failed' => $total_failed,
    'passed_students' => $passed_students,
    'failed_students' => $failed_students
];

debug_log("Final Response: " . json_encode($response));

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);