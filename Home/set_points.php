<?php
session_start();
include("../LoginRegisterAuthentication/connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_SESSION['userid'] ?? 0;
    $subjectId = $_POST['subjectId'] ?? 0;
    $presentPoints = $_POST['presentPoints'] ?? 10;
    $absentPoints = $_POST['absentPoints'] ?? 0;
    $latePoints = $_POST['latePoints'] ?? 5;
    $excusedPoints = $_POST['excusedPoints'] ?? 0;

    // Validate inputs
    if ($userId && $subjectId) {
        // Check if entry exists in point_setter table
        $checkQuery = "SELECT id FROM point_setter WHERE userid = ? AND subject_id = ?";
        $checkStmt = $connection->prepare($checkQuery);
        $checkStmt->bind_param("ii", $userId, $subjectId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            // Entry exists, perform UPDATE
            $query = "UPDATE point_setter 
                      SET points_present = ?, points_absent = ?, points_late = ?, points_excused = ?
                      WHERE userid = ? AND subject_id = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param("iiiiii", $presentPoints, $absentPoints, $latePoints, $excusedPoints, $userId, $subjectId);
        } else {
            // Entry doesn't exist, perform INSERT
            $query = "INSERT INTO point_setter (userid, subject_id, points_present, points_absent, points_late, points_excused) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($query);
            $stmt->bind_param("iiiiii", $userId, $subjectId, $presentPoints, $absentPoints, $latePoints, $excusedPoints);
        }

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Points saved successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error saving points: " . $connection->error]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Missing user or subject ID"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}