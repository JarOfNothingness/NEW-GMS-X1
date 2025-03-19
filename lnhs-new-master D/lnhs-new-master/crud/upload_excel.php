<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include necessary libraries
// require '../vendor/autoload.php';  // Ensure the path is correct
require '../vendor/autoload.php';  // Ensure the path is correct
use PhpOffice\PhpSpreadsheet\IOFactory;  // For loading Excel files

include("../LoginRegisterAuthentication/connection.php");

if (!isset($_SESSION['username']) || !isset($_SESSION['userid'])) {
    header("Location: ../Home/login.php");
    exit();
}

$userid = $_SESSION['userid'];

// Handle the Excel file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excelFile'])) {
    // Check for upload errors
    if ($_FILES['excelFile']['error'] != UPLOAD_ERR_OK) {
        die('File upload error: ' . $_FILES['excelFile']['error']);
    }

    // Load the uploaded Excel file
    $file = $_FILES['excelFile']['tmp_name'];
    $schoolyear = $_POST['schoolyear'];
    $grade = $_POST['grade'];            // Get grade
    $section = $_POST['section'];        // Get section
    $grade_section = $grade." ".$section;
    $subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];
    
    try {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();
        
        // Loop through the rows and insert data into the database
        foreach ($data as $row) {
            $learners_name = ucfirst($row[0]); // Learner's name
            $gender = ucfirst($row[1]);        // Gender

            // Generate a custom ID that starts with 2025 and ends with 3 random digits
            $random_digits = rand(100, 999);
            $custom_id = '2025' . $random_digits;

            // Check if the generated ID already exists in the database
            $check_id_query = "SELECT id FROM students WHERE id = ?";
            $check_stmt = mysqli_prepare($connection, $check_id_query);
            mysqli_stmt_bind_param($check_stmt, 's', $custom_id);
            mysqli_stmt_execute($check_stmt);
            $result = mysqli_stmt_get_result($check_stmt);

            // If the ID already exists, generate a new one
            while (mysqli_num_rows($result) > 0) {
                $random_digits = rand(100, 999);
                $custom_id = '2025' . $random_digits;
                mysqli_stmt_execute($check_stmt);  // Re-execute the check query with the new ID
                $result = mysqli_stmt_get_result($check_stmt);
            }

            // Insert data into the students table with the custom ID
            $insert_query = "INSERT INTO students (id, learners_name, `grade & section`, gender, school_year, user_id) 
                             VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_students = mysqli_prepare($connection, $insert_query);
            mysqli_stmt_bind_param($stmt_students, 'sssssi', $custom_id, $learners_name, $grade_section, $gender, $schoolyear, $userid);
            mysqli_stmt_execute($stmt_students);

            $student_id = mysqli_insert_id($connection);

               
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
            mysqli_stmt_close($stmt_students);
        

            // Commit transaction
            mysqli_commit($connection);
    }

        // Set success message in session
        $_SESSION['upload_success'] = 'The student data has been added successfully and enrolled in all subjects!';
        // Redirect to the view master list page
        header('Location: view_masterlist.php');
        exit();

    } catch (Exception $e) {
        // If there is an error loading or processing the file, show an error message
        echo "
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Upload Failed',
                text: 'There was an error processing the file. Please try again.',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}
?>
