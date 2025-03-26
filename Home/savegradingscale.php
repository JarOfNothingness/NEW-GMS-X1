<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include("../LoginRegisterAuthentication/connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture form data
    $row_id = isset($_POST['row_id']) ? intval($_POST['row_id']) : 0;
    $subject = isset($_POST['subject']) ? $_POST['subject'] : null;
    $written = isset($_POST['written']) ? $_POST['written'] : null;
    $performance_task = isset($_POST['performance_task']) ? $_POST['performance_task'] : null;
    $quarterly_exams = isset($_POST['quarterly_exams']) ? $_POST['quarterly_exams'] : null;

    // Ensure all required data is provided
    if ($row_id > 0 && $subject && $written && $performance_task && $quarterly_exams) {
        // Prepare the update query using the row ID
        $query = "UPDATE grading_scale SET subject = ?, written = ?, performance_task = ?, quarterly_exams = ? WHERE id = ?";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo "Prepare failed: (" . $connection->errno . ") " . $connection->error;
        }

        // Bind the parameters and execute the query
        $stmt->bind_param("ssssi", $subject, $written, $performance_task, $quarterly_exams, $row_id);

        if ($stmt->execute()) {
            // Redirect after successful update
            header("Location: editscale.php?row=$row_id");
            exit();
        } else {
            // Output error if query execution fails
            echo "Error executing query: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: Invalid form data.";
    }
}

$connection->close();
?>
