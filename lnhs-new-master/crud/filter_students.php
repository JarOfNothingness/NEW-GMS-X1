<?php
// Include database connection
include('../LoginRegisterAuthentication/connection.php');

// Get filter parameters from POST request
$learners_name = isset($_POST['learners_name']) ? $_POST['learners_name'] : '';
$school_level = isset($_POST['school_level']) ? $_POST['school_level'] : '';
$school_year = isset($_POST['school_year']) ? $_POST['school_year'] : '';
$grade = isset($_POST['grade']) ? $_POST['grade'] : '';
$section = isset($_POST['section']) ? $_POST['section'] : '';
$gender = isset($_POST['gender']) ? $_POST['gender'] : '';
$subject = isset($_POST['subject']) ? $_POST['subject'] : '';

// Build the query with dynamic conditions
$query = "SELECT * FROM students WHERE 1=1";
$params = array();

if ($learners_name) {
    $query .= " AND learners_name LIKE ?";
    $params[] = "%$learners_name%";
}
if ($school_level) {
    $query .= " AND school_level = ?";
    $params[] = $school_level;
}
if ($school_year) {
    $query .= " AND school_year = ?";
    $params[] = $school_year;
}
if ($grade) {
    $query .= " AND grade = ?";
    $params[] = $grade;
}
if ($section) {
    $query .= " AND section = ?";
    $params[] = $section;
}
if ($gender) {
    $query .= " AND gender = ?";
    $params[] = $gender;
}
if ($subject) {
    $query .= " AND subject = ?";
    $params[] = $subject;
}

// Prepare and execute statement
$stmt = mysqli_prepare($connection, $query);
if ($params) {
    $types = str_repeat('s', count($params));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Output the results as JSON
$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}
echo json_encode($data);

// Close the database connection
mysqli_close($connection);
?>
