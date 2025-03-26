<?php
include("../LoginRegisterAuthentication/connection.php");

$school_year = isset($_GET['school_year']) ? $_GET['school_year'] : '';
$grade_section = isset($_GET['grade_section']) ? $_GET['grade_section'] : '';
$subject_id = isset($_GET['subject']) ? $_GET['subject'] : '';

$sql = "SELECT s.id, s.learners_name, s.`grade & section`, s.school_year,
               MAX(CASE WHEN a.quarter = '1st' THEN asm.quarterly_grade END) as quarter_1,
               MAX(CASE WHEN a.quarter = '2nd' THEN asm.quarterly_grade END) as quarter_2,
               MAX(CASE WHEN a.quarter = '3rd' THEN asm.quarterly_grade END) as quarter_3,
               MAX(CASE WHEN a.quarter = '4th' THEN asm.quarterly_grade END) as quarter_4
        FROM students s
        LEFT JOIN assessment_summary asm ON s.id = asm.student_id
        LEFT JOIN assessments a ON asm.subject_id = a.subject_id AND asm.quarter = a.quarter
        WHERE 1=1";

if (!empty($school_year)) {
    $sql .= " AND s.school_year = '$school_year'";
}
if (!empty($grade_section)) {
    $sql .= " AND s.`grade & section` = '$grade_section'";
}
if (!empty($subject_id)) {
    $sql .= " AND asm.subject_id = $subject_id";
}

$sql .= " GROUP BY s.id, s.learners_name, s.`grade & section`, s.school_year";

$result = mysqli_query($connection, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}

$grades = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Initialize variables for sum and count of valid grades
    $sum = 0;
    $count = 0;

    // Check each quarter and add to sum if it's a valid number
    foreach (['quarter_1', 'quarter_2', 'quarter_3', 'quarter_4'] as $quarter) {
        if (is_numeric($row[$quarter])) {
            $sum += $row[$quarter];
            $count++;
        }
    }

    // Calculate final grade only if there are valid grades
    if ($count > 0) {
        $final_grade = $sum / $count;
        $final_grade = round($final_grade, 2); // Round to two decimal places
        $remarks = ($final_grade >= 75) ? 'Passed' : 'Failed';
    } else {
        $final_grade = 'N/A';
        $remarks = 'N/A';
    }

    $grades[] = [
        'learners_name' => $row['learners_name'],
        'quarter_1' => $row['quarter_1'] ?? 'N/A',
        'quarter_2' => $row['quarter_2'] ?? 'N/A',
        'quarter_3' => $row['quarter_3'] ?? 'N/A',
        'quarter_4' => $row['quarter_4'] ?? 'N/A',
        'final_grade' => $final_grade,
        'remarks' => $remarks
    ];
}

header('Content-Type: application/json');
echo json_encode($grades);
?>