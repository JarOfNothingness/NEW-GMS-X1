<?php
include("../LoginRegisterAuthentication/connection.php");

$school_year = $_POST['school_year'];
$grade_section = $_POST['grade_section'];
$subject_id = $_POST['subject_id'];
$quarter = $_POST['quarter'];

// Ensure the subject_id is set, this will allow us to fetch students just by subject
if ($subject_id) {
    // Prepare the query to fetch students and their corresponding grades if available
    $query = "
        SELECT s.learners_name, sg.written_exam, sg.performance_task, sg.quarterly_exam, sg.final_grade, sg.remarks 
        FROM students s
        LEFT JOIN student_grades sg ON s.id = sg.student_id 
            AND sg.subject_id = ? 
            AND sg.quarter = ?
        WHERE s.school_year = ? 
        AND s.`grade & section` = ?";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("iiss", $subject_id, $quarter, $school_year, $grade_section);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $written_exam = $row['written_exam'] ?? 'N/A';
            $performance_task = $row['performance_task'] ?? 'N/A';
            $quarterly_exam = $row['quarterly_exam'] ?? 'N/A';
            $final_grade = $row['final_grade'] ?? 'N/A';
            $remarks = $row['remarks'] ?? 'N/A';

            echo "<tr>
                    <td>{$row['learners_name']}</td>
                    <td>{$written_exam}</td>
                    <td>{$performance_task}</td>
                    <td>{$quarterly_exam}</td>
                    <td>{$final_grade}</td>
                    <td>{$remarks}</td>
                    <td><button class='btn btn-sm btn-primary'>Edit</button></td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='7'>No records found</td></tr>";
    }
} else {
    echo "<tr><td colspan='7'>Please select a subject.</td></tr>";
}
?>
