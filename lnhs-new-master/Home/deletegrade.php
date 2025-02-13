<?php
session_start();
include("../LoginRegisterAuthentication/connection.php");

if (isset($_GET['id'])) {
    $grade_id = $_GET['id'];
    $delete_query = "DELETE FROM student_grades WHERE id = $grade_id";

    if (mysqli_query($connection, $delete_query)) {
        header("Location: addgrade.php?message=Grade deleted successfully");
    } else {
        echo "Error deleting record: " . mysqli_error($connection);
    }
} else {
    echo "No grade ID provided.";
}
?>
