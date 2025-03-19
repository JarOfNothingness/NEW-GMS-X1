<?php
session_start();
include("connection.php");

header('Content-Type: application/json');

// Prevent caching
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

if (isset($_SESSION['userid'])) {
    $userid = $_SESSION['userid'];
    
    // Check user status
    $query = "SELECT status FROM user WHERE userid = ?";
    $stmt = $connection->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        $stmt->bind_result($status);
        $stmt->fetch();
        $stmt->close();

        // Return the status as JSON
        if (!empty($status)) {
            echo json_encode(array('status' => $status));
        } else {
            echo json_encode(array('status' => 'error'));
        }
    } else {
        echo json_encode(array('status' => 'error'));
    }
} else {
    echo json_encode(array('status' => 'error'));
}
?>
