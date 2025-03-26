<?php
// Start output buffering to avoid header issues
ob_start();

include('header.php');
include("../LoginRegisterAuthentication/connection.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Check if there are related records in the student_grades table
    $query_check_grades = "SELECT * FROM student_grades WHERE student_id = ?";
    $stmt_check_grades = mysqli_prepare($connection, $query_check_grades);
    mysqli_stmt_bind_param($stmt_check_grades, 'i', $id);
    mysqli_stmt_execute($stmt_check_grades);
    $result_check_grades = mysqli_stmt_get_result($stmt_check_grades);

    // Check if there are related records in the attendance table
    $query_check_attendance = "SELECT * FROM attendance WHERE student_id = ?";
    $stmt_check_attendance = mysqli_prepare($connection, $query_check_attendance);
    mysqli_stmt_bind_param($stmt_check_attendance, 'i', $id);
    mysqli_stmt_execute($stmt_check_attendance);
    $result_check_attendance = mysqli_stmt_get_result($stmt_check_attendance);

    if (mysqli_num_rows($result_check_grades) > 0 || mysqli_num_rows($result_check_attendance) > 0) {
        // Display an error message and do not delete the student record
        header('Location: Crud.php?delete_msg=Cannot delete student as there are related records in student_grades or attendance.');
        exit();
    } else {
        // No related records, proceed to delete the student record
        $query = "DELETE FROM students WHERE id = ?";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
            die("Query Failed: " . mysqli_error($connection));
        } else {
            header('Location: Crud.php?delete_msg=You have successfully deleted the student data!');
            exit();
        }
    }
}

// End output buffering and flush the buffer
ob_end_flush();
?>
