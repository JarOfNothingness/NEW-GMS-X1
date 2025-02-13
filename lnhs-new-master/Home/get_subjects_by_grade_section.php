<?php
include("../LoginRegisterAuthentication/connection.php");

if (isset($_GET['grade_section'])) {
    $gradeSection = $connection->real_escape_string($_GET['grade_section']);
    
    $sql = "SELECT DISTINCT s.id, s.name 
            FROM subjects s
            JOIN student_subjects ss ON s.id = ss.subject_id
            JOIN students st ON ss.student_id = st.id
            WHERE st.`grade & section` = ?
            ORDER BY s.name";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $gradeSection);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<option value=''>Select a subject</option>";
    while ($row = $result->fetch_assoc()) {
        echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
    }
} else {
    echo "<option value=''>Select a subject</option>";
}
?>