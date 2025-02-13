<?php
include("../LoginRegisterAuthentication/connection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $field = $_POST['field'];
    $value = $_POST['value'];

    // Update the specific field in the database
    $query = "UPDATE student_grades SET $field = ? WHERE id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param('si', $value, $id);
    if ($stmt->execute()) {
        echo "Success";
    } else {
        echo "Error updating grade";
    }
}

?>
