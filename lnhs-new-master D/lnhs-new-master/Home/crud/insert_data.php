<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../LoginRegisterAuthentication/connection.php');

// Check if the user is logged in and has a valid user ID
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

// Capture user ID from session
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture and sanitize inputs
    $learners_name = mysqli_real_escape_string($connection, $_POST['learners_name'] ?? '');
    $grade_section = mysqli_real_escape_string($connection, $_POST['grade_section'] ?? '');
    $school_year = mysqli_real_escape_string($connection, $_POST['school_year'] ?? '');
    $gender = mysqli_real_escape_string($connection, $_POST['gender'] ?? '');
    $subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];

    // Check if required fields are present
    if (empty($learners_name) || empty($grade_section) || empty($school_year) || empty($gender) || empty($subjects)) {
        echo "<script>
                alert('Error: All required fields must be filled out.');
                window.history.back();
              </script>";
        exit;
    }

    // Start transaction
    mysqli_begin_transaction($connection);

    try {
        $quarters = ['1st', '2nd', '3rd', '4th'];
        

            // Insert student data for each quarter
            $insert_query = "INSERT INTO students (learners_name, `grade & section`, school_year, gender, quarter, user_id, datetime_added) 
                             VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt_insert = mysqli_prepare($connection, $insert_query);
            mysqli_stmt_bind_param($stmt_insert, 'sssssi', $learners_name, $grade_section, $school_year, $gender, $quarter, $user_id);
            
            if (!mysqli_stmt_execute($stmt_insert)) {
                throw new Exception("Error adding student for $quarter quarter: " . mysqli_error($connection));
            }
            
            $student_id = mysqli_insert_id($connection);
            
            // Insert subject data for each selected subject

            
            $subject_insert_query = "INSERT INTO student_subjects (student_id, subject_id, description) VALUES (?, ?, ?)";
            $stmt_subject_insert = mysqli_prepare($connection, $subject_insert_query);
            
            foreach ($subjects as $subject) {

                $subjectsQuery = "SELECT name,id FROM subjects WHERE name = ?";
                $stmt = mysqli_prepare($connection, $subjectsQuery);
                mysqli_stmt_bind_param($stmt, 's', $subject);
                mysqli_stmt_execute($stmt);
                $subjectsResult = mysqli_stmt_get_result($stmt);
                $subjectsid=0;
                while ($subjectss = mysqli_fetch_assoc($subjectsResult)){
                    $subjectsid=$subjectss['id'];
                }
                
                mysqli_stmt_bind_param($stmt_subject_insert, 'iis', $student_id, $subjectsid, $subject);
                if (!mysqli_stmt_execute($stmt_subject_insert)) {
                    throw new Exception("Error adding subject for student: " . mysqli_error($connection));
                }
            }

            
            mysqli_stmt_close($stmt_subject_insert);
            mysqli_stmt_close($stmt_insert);
        

        // Commit transaction
        mysqli_commit($connection);

        // Success message
        echo "<script>
                alert('Student Added: Successfully added the student for all quarters with selected subjects!');
                window.location.href = 'Crud.php';
              </script>";
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($connection);
        echo "<script>
                alert('Error: " . $e->getMessage() . "');
                window.history.back();
              </script>";
    }
}
?>