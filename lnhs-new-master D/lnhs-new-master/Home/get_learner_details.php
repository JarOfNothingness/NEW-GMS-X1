<?php
include('../LoginRegisterAuthentication/connection.php');

if (isset($_GET['id'])) {
    $learnerId = $_GET['id'];
    $query = "SELECT learners_name, school_year FROM students WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'i', $learnerId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $learner = mysqli_fetch_assoc($result);
        echo json_encode($learner);
    } else {
        echo json_encode([]);
    }
}
?>
