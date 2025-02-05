<?php
include("../LoginRegisterAuthentication/connection.php");

if (isset($_GET['assessment_id'])) {
    $assessment_id = $_GET['assessment_id'];
    
    $sql = "SELECT sq.id, s.learners_name, sq.raw_score, a.max_score, at.name AS assessment_type 
            FROM student_quiz sq 
            JOIN students s ON sq.student_id = s.id 
            JOIN assessments a ON sq.assessment_id = a.id 
            JOIN assessment_types at ON a.assessment_type_id = at.id 
            WHERE sq.assessment_id = ?
            AND s.`grade & section` = a.grade_section  
            "
            ;
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $assessment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<div class='table-responsive'>";
        echo "<table class='table table-striped table-hover'>";
        echo "<thead class='table-light'><tr><th>Student Name</th><th>Raw Score</th><th>Max Score</th></tr></thead>";
        echo "<tbody>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['learners_name']) . "</td>";
            echo "<td><input type='number' class='form-control' name='grades[" . $row['id'] . "]' value='" . $row['raw_score'] . "' min='0' max='" . $row['max_score'] . "'></td>";
            echo "<td>" . $row['max_score'] . "</td>";
            echo "<input type='hidden' name='max_score[" . $row['id'] . "]' value='" . $row['max_score'] . "'>";
            echo "<input type='hidden' name='assessment_type[" . $row['id'] . "]' value='" . $row['assessment_type'] . "'>";
            echo "</tr>";
        }
        echo "</tbody></table>";
        echo "</div>";
        echo "<button type='submit' class='btn btn-primary mt-3' name='updateGrades'><i class='fas fa-save me-2'></i>Update Grades</button>";
    } else {
        echo "<div class='alert alert-info'>No students found for this assessment.</div>";
    }
}
?>