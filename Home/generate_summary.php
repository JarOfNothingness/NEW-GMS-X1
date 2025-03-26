<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// include("../LoginRegisterAuthentication/connection.php");
include("../LoginRegisterAuthentication/connection.php");
$userid = $_SESSION['userid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject_id']) && isset($_POST['quarter'])) {
    $subject_id = $_POST['subject_id'];
    $grade_section = $_POST['grade_section'];
    $quarter = $_POST['quarter'];

    // Fetch assessment type percentages for the subject
    $percentagesSql = "SELECT name, percentage FROM assessment_types WHERE subject_id = ?";
    $percentagesStmt = $connection->prepare($percentagesSql);
    $percentagesStmt->bind_param("i", $subject_id);
    $percentagesStmt->execute();
    $percentagesResult = $percentagesStmt->get_result();
    $percentages = [];
    while ($row = $percentagesResult->fetch_assoc()) {
        $percentages[$row['name']] = $row['percentage'] / 100;
    }

    // Modified query to properly fetch subject name and grade section
    $subjectSql = "SELECT s.name AS subject_name, st.`grade & section` AS grade_section 
                   FROM subjects s
                   JOIN students st ON st.`grade & section` = ?
                   WHERE s.id = ?
                   LIMIT 1";
    $subjectStmt = $connection->prepare($subjectSql);
    $subjectStmt->bind_param("si", $grade_section, $subject_id);
    $subjectStmt->execute();
    $subjectResult = $subjectStmt->get_result();
    $subjectInfo = $subjectResult->fetch_assoc();
    $subjectName = $subjectInfo['subject_name'];
    $gradeSection = $subjectInfo['grade_section'];

    // Modified main query to properly filter by grade & section
    $sql = "SELECT s.id, s.learners_name, s.`grade & section`,
                   SUM(CASE WHEN at.name = 'WRITTEN WORKS' THEN sq.raw_score ELSE 0 END) as written_works_total,
                   SUM(CASE WHEN at.name = 'WRITTEN WORKS' THEN a.max_score ELSE 0 END) as written_works_max,
                   SUM(CASE WHEN at.name = 'PERFORMANCE TASKS' THEN sq.raw_score ELSE 0 END) as performance_tasks_total,
                   SUM(CASE WHEN at.name = 'PERFORMANCE TASKS' THEN a.max_score ELSE 0 END) as performance_tasks_max,
                   SUM(CASE WHEN at.name = 'QUARTERLY ASSESSMENT' THEN sq.raw_score ELSE 0 END) as quarterly_assessment_score,
                   SUM(CASE WHEN at.name = 'QUARTERLY ASSESSMENT' THEN a.max_score ELSE 0 END) as quarterly_assessment_max
            FROM students s
            LEFT JOIN student_quiz sq ON s.id = sq.student_id
            LEFT JOIN assessments a ON sq.assessment_id = a.id
            LEFT JOIN assessment_types at ON a.assessment_type_id = at.id
            WHERE a.subject_id = ? 
            AND sq.subject_id = ?
            AND a.quarter = ?
            AND s.user_id = ?
            AND s.`grade & section` = ?
            AND s.`grade & section` = a.grade_section
            GROUP BY s.id
            ORDER BY s.learners_name";

    $stmt = $connection->prepare($sql);
    $stmt->bind_param("iisis", $subject_id, $subject_id, $quarter, $userid, $grade_section);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch grading scale
    $gradingScaleSql = "SELECT * FROM grading_scale ORDER BY initial_grade_min DESC";
    $gradingScaleResult = $connection->query($gradingScaleSql);
    $gradingScale = [];
    while ($row = $gradingScaleResult->fetch_assoc()) {
        $gradingScale[] = $row;
    }

    // Start building the HTML table
    $html = "<h4>Summary for {$subjectName} - {$quarter} Quarter</h4>";
    $html .= "<table class='table table-sm table-bordered summary-table'>";
    $html .= "<thead class='table-light'>
                <tr>
                    <th>LEARNERS' NAMES</th>
                    <th colspan='3'>WRITTEN WORKS</th>
                    <th colspan='3'>PERFORMANCE TASKS</th>
                    <th colspan='3'>QUARTERLY ASSESSMENT</th>
                    <th>Initial Grade</th>
                    <th>Quarterly Grade</th>
                </tr>
                <tr>
                    <th></th>
                    <th>Total</th><th>PS</th><th>WS</th>
                    <th>Total</th><th>PS</th><th>WS</th>
                    <th>Score</th><th>PS</th><th>WS</th>
                    <th></th><th></th>
                </tr>
              </thead><tbody>";

    while ($row = $result->fetch_assoc()) {
        // Calculate PS and WS using high precision
        $written_works_ps = $row['written_works_max'] > 0 ? ($row['written_works_total'] / $row['written_works_max']) * 100 : 0;
        $written_works_ws = $written_works_ps * $percentages['WRITTEN WORKS'];
        
        $performance_tasks_ps = $row['performance_tasks_max'] > 0 ? ($row['performance_tasks_total'] / $row['performance_tasks_max']) * 100 : 0;
        $performance_tasks_ws = $performance_tasks_ps * $percentages['PERFORMANCE TASKS'];
        
        $quarterly_assessment_ps = $row['quarterly_assessment_max'] > 0 ? ($row['quarterly_assessment_score'] / $row['quarterly_assessment_max']) * 100 : 0;
        $quarterly_assessment_ws = $quarterly_assessment_ps * $percentages['QUARTERLY ASSESSMENT'];
        
        // Calculate initial grade with high precision
        $initial_grade = $written_works_ws + $performance_tasks_ws + $quarterly_assessment_ws;
        $initial_grade_display = sprintf("%.2f", floor($initial_grade * 100) / 100);
        
        // Format the initial grade to exactly two decimal places without rounding
        $initial_grade_formatted = number_format($initial_grade, 2, '.', '');
        
        // Find the transmuted grade based on the initial grade
        $quarterly_grade = 60; // Default to 60 if no matching range is found
        foreach ($gradingScale as $grade) {
            if ($initial_grade >= $grade['initial_grade_min'] && $initial_grade <= $grade['initial_grade_max']) {
                $quarterly_grade = $grade['transmuted_grade'];
                break;
            }
        }
        
        $html .= "<tr>
                    <td>{$row['learners_name']}</td>
                    <td>{$row['written_works_total']}</td>
                    <td>" . number_format($written_works_ps, 2) . "</td>
                    <td>" . number_format($written_works_ws, 2) . "</td>
                    <td>{$row['performance_tasks_total']}</td>
                    <td>" . number_format($performance_tasks_ps, 2) . "</td>
                    <td>" . number_format($performance_tasks_ws, 2) . "</td>
                    <td>{$row['quarterly_assessment_score']}</td>
                    <td>" . number_format($quarterly_assessment_ps, 2) . "</td>
                    <td>" . number_format($quarterly_assessment_ws, 2) . "</td>
                    <td>{$initial_grade_display}</td>
                    <td>" . number_format($quarterly_grade, 2) . "</td>
                  </tr>";

        // Insert or update the summary in the assessment_summary table
        $updateSql = "INSERT INTO assessment_summary 
                      (user_id, student_id, subject_id, quarter, written_works_total, written_works_ps, written_works_ws,
                       performance_tasks_total, performance_tasks_ps, performance_tasks_ws,
                       quarterly_assessment_score, quarterly_assessment_ps, quarterly_assessment_ws,
                       initial_grade, quarterly_grade)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE
                      written_works_total = VALUES(written_works_total),
                      written_works_ps = VALUES(written_works_ps),
                      written_works_ws = VALUES(written_works_ws),
                      performance_tasks_total = VALUES(performance_tasks_total),
                      performance_tasks_ps = VALUES(performance_tasks_ps),
                      performance_tasks_ws = VALUES(performance_tasks_ws),
                      quarterly_assessment_score = VALUES(quarterly_assessment_score),
                      quarterly_assessment_ps = VALUES(quarterly_assessment_ps),
                      quarterly_assessment_ws = VALUES(quarterly_assessment_ws),
                      initial_grade = VALUES(initial_grade),
                      quarterly_grade = VALUES(quarterly_grade)";
    
        $updateStmt = $connection->prepare($updateSql);
        $updateStmt->bind_param("iiisddddddddddd", 
            $userid,$row['id'], $subject_id, $quarter, 
            $row['written_works_total'], $written_works_ps, $written_works_ws,
            $row['performance_tasks_total'], $performance_tasks_ps, $performance_tasks_ws,
            $row['quarterly_assessment_score'], $quarterly_assessment_ps, $quarterly_assessment_ws,
            $initial_grade_rounded, $quarterly_grade
        );
        $updateStmt->execute();
    }

    $html .= "</tbody></table>";
    echo $html;
} else {
    echo "Invalid request";
}
?>