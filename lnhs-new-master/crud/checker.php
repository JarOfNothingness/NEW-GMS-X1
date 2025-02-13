<?php
session_start();
include("../LoginRegisterAuthentication/connection.php");

if (isset($_GET['checkDuplicate']) && isset($_GET['last_name']) && isset($_GET['first_name'])) {
    $last_name = mysqli_real_escape_string($connection, $_GET['last_name']);
    $first_name = mysqli_real_escape_string($connection, $_GET['first_name']);
    $user_id = $_SESSION['userid'];

    $query = "SELECT * FROM students WHERE LOWER(learners_name) LIKE LOWER(?) AND user_id = ?";
    $stmt = mysqli_prepare($connection, $query);
    
    // Construct the name pattern to match
    $name_pattern = "$last_name, $first_name%";
    
    mysqli_stmt_bind_param($stmt, "si", $name_pattern, $user_id);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>