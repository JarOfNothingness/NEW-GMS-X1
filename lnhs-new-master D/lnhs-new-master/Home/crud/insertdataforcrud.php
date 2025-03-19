<?php
include('../LoginRegisterAuthentication/connection.php');
session_start();

// Capture user ID from session
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture and sanitize inputs
    $learners_name = mysqli_real_escape_string($connection, $_POST['learners_name']);
    $grade = mysqli_real_escape_string($connection, $_POST['grade']);
    $school_level = mysqli_real_escape_string($connection, $_POST['school_level']);
    $region = mysqli_real_escape_string($connection, $_POST['region']);
    $division = mysqli_real_escape_string($connection, $_POST['division']);
    $school_year = mysqli_real_escape_string($connection, $_POST['school_year']);
    $section = mysqli_real_escape_string($connection, $_POST['section']);
    $gender = mysqli_real_escape_string($connection, $_POST['gender']);
    $subject = mysqli_real_escape_string($connection, $_POST['subject']);

    // Extract the first 2 digits of the school year
    $year_prefix = substr($school_year, 0, 2);

    // Generate a unique 4-digit random number
    $unique_suffix = sprintf("%04d", mt_rand(1, 9999));

    // Combine them to form the school_id
    $school_id = $year_prefix . $unique_suffix;

    // Insert data into the database
    $query = "INSERT INTO students (learners_name, grade, school_level, region, division, school_year, section, gender, subject, school_id, user_id)
              VALUES ('$learners_name', '$grade', '$school_level', '$region', '$division', '$school_year', '$section', '$gender', '$subject', '$school_id', '$user_id')";

    if (mysqli_query($connection, $query)) {
        header("");
    } else {
        die("Error: " . mysqli_error($connection));
    }
}
?>