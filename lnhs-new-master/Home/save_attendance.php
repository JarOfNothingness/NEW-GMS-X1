<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("../LoginRegisterAuthentication/connection.php");

if (!isset($_SESSION['username']) || !isset($_SESSION['userid'])) {
    echo "<script>window.location.href = '../Home/login.php';</script>";
    exit();
}

$attendanceData = $_POST['attendance'] ?? [];
$section = $_POST['section'] ?? '';
$month = $_POST['month'] ?? '';
$subject_id = $_POST['subject_id'] ?? '';
$school_year = $_POST['school_year'] ?? '';
$user_id = $_SESSION['userid'];

if (empty($attendanceData) || !$section || !$month || !$subject_id || !$school_year) {
    die('Invalid input.');
}

// Fetch point values
$pointQuery = "SELECT points_present, points_absent, points_late, points_excused 
               FROM point_setter 
               WHERE userid = ? AND subject_id = ?";
$pointStmt = $connection->prepare($pointQuery);
$pointStmt->bind_param("ii", $user_id, $subject_id);
$pointStmt->execute();
$pointResult = $pointStmt->get_result();
$pointData = $pointResult->fetch_assoc();

$presentPoints = $pointData['points_present'] ?? 10;
$absentPoints = $pointData['points_absent'] ?? 0;
$latePoints = $pointData['points_late'] ?? 5;
$excusedPoints = $pointData['points_excused'] ?? 0;

try {
    $connection->begin_transaction();

    foreach ($attendanceData as $student_id => $attendance) {
        // Calculate totals
        $totalPresent = $totalAbsent = $totalLate = $totalExcused = $totalPoints = 0;

        foreach ($attendance as $status) {
            switch ($status) {
                case 'P': $totalPresent++; $totalPoints += $presentPoints; break;
                case 'A': $totalAbsent++; $totalPoints += $absentPoints; break;
                case 'L': $totalLate++; $totalPoints += $latePoints; break;
                case 'E': $totalExcused++; $totalPoints += $excusedPoints; break;
            }
        }

        // Check if attendance exists
        $checkQuery = "SELECT id FROM attendance 
                      WHERE user_id = ? AND student_id = ? AND month = ? AND subject_id = ?";
        $checkStmt = $connection->prepare($checkQuery);
        $checkStmt->bind_param("iisi", $user_id, $student_id, $month, $subject_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            // Build update query
            $updateQuery = "UPDATE attendance SET ";
            $params = [];
            $bindTypes = "";
            $dayColumns = [];

            // Process day columns
            foreach ($attendance as $day => $status) {
                $dayColumns[] = "`$day` = ?";
                $params[] = $status;
                $bindTypes .= "s";
            }

            // Add day columns to query
            $updateQuery .= implode(", ", $dayColumns);

            // Add other fields
            $updateQuery .= ", total_present = ?, total_absent = ?, total_late = ?, 
                             total_excused = ?, total_points = ?, school_year = ?, 
                             section = ? 
                             WHERE user_id = ? AND student_id = ? AND 
                             month = ? AND subject_id = ?";

            // Add remaining parameters in the correct order
            $params = array_merge($params, [
                $totalPresent,
                $totalAbsent,
                $totalLate,
                $totalExcused,
                $totalPoints,
                $school_year,
                $section,
                $user_id,
                $student_id,
                $month,
                $subject_id
            ]);

            // Complete bind types string
            $bindTypes .= "iiiiiissiis";

            $updateStmt = $connection->prepare($updateQuery);
            $updateStmt->bind_param($bindTypes, ...$params);
            $updateStmt->execute();

        } else {
            // Build insert query
            $columns = ['user_id', 'student_id', 'month', 'school_year', 'subject_id', 'section'];
            $values = [$user_id, $student_id, $month, $school_year, $subject_id, $section];
            $bindTypes = "iisiss";

            // Add attendance day columns
            foreach ($attendance as $day => $status) {
                $columns[] = "`$day`";
                $values[] = $status;
                $bindTypes .= "s";
            }

            // Add total columns
            $columns = array_merge($columns, [
                'total_present',
                'total_absent',
                'total_late',
                'total_excused',
                'total_points'
            ]);
            
            $values = array_merge($values, [
                $totalPresent,
                $totalAbsent,
                $totalLate,
                $totalExcused,
                $totalPoints
            ]);
            
            $bindTypes .= "iiiii";

            $placeholders = str_repeat("?,", count($columns));
            $placeholders = rtrim($placeholders, ",");

            $insertQuery = "INSERT INTO attendance (" . implode(", ", $columns) . ") 
                           VALUES (" . $placeholders . ")";

            $insertStmt = $connection->prepare($insertQuery);
            $insertStmt->bind_param($bindTypes, ...$values);
            $insertStmt->execute();
        }
    }

    $connection->commit();

    echo "<script>
        window.location.href = 'Attendance.php?saved=1" . 
        "&section=" . urlencode($section) . 
        "&subject_id=" . urlencode($subject_id) . 
        "&month=" . urlencode($month) . 
        "&school_year=" . urlencode($school_year) . "';
    </script>";

} catch (Exception $e) {
    $connection->rollback();
    echo "<script>
        alert('Error: " . addslashes($e->getMessage()) . "');
        window.location.href = 'Attendance.php';
    </script>";
}
?>