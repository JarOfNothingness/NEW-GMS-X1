<?php
include("../LoginRegisterAuthentication/connection.php");

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $query = "SELECT * FROM students WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    echo json_encode($data);
}
?>
