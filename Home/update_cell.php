<?php
include("../LoginRegisterAuthentication/connection.php");

$data = json_decode(file_get_contents('php://input'), true); // Receive the JSON data

if (isset($data['id']) && isset($data['column']) && isset($data['value'])) {
    $id = intval($data['id']);
    $column = $data['column'];
    $value = $data['value'];

    // Use a prepared statement to update the correct column
    $query = "UPDATE grading_scale SET $column = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        exit();
    }

    $stmt->bind_param('si', $value, $id);

    if ($stmt->execute()) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Error: Invalid data";
}
$conn->close();
?>
