<?php
session_start();
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
include("../LoginRegisterAuthentication/connection.php");

// Check if connection failed
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get school year from the request
$schoolYear = isset($_GET['school_year']) ? $_GET['school_year'] : '';
// Get school year from the request
$schoolSection = isset($_GET['section']) ? $_GET['section'] : '';
$userid = $_SESSION['userid'];

if (!empty($schoolYear)) {
    // Fetch sections based on selected school year
    $sectionsQuery = "SELECT DISTINCT `grade & section` FROM students WHERE user_id = ? AND school_year = ? ORDER BY `grade & section`";
    $stmt = mysqli_prepare($connection, $sectionsQuery);
    mysqli_stmt_bind_param($stmt, 'is', $userid, $schoolYear);
} else {
    // Fetch all sections if no school year is selected
    $sectionsQuery = "SELECT DISTINCT `grade & section` FROM students WHERE user_id = ? ORDER BY `grade & section`";
    $stmt = mysqli_prepare($connection, $sectionsQuery);
    mysqli_stmt_bind_param($stmt, 'i', $userid);
}

// Execute query
if (!mysqli_stmt_execute($stmt)) {
    die("Query error: " . mysqli_error($connection));
}

$sectionsResult = mysqli_stmt_get_result($stmt);

// Check if the query returned any data
if (!$sectionsResult) {
    die("Error fetching sections: " . mysqli_error($connection));
}

// Start building options
$options = '<option value="">All Grade & Section</option>';

while ($section = mysqli_fetch_assoc($sectionsResult)) {
    $options .= '<option value="' . htmlspecialchars($section['grade & section']) . '">' . htmlspecialchars($section['grade & section']) . '</option>';
}

// Return options as the response
echo $options;
?>