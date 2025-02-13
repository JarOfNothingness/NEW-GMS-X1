<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include necessary libraries
require '../../vendor/autoload.php';  // Ensure the path is correct
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
    try {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();
        // Loop through the rows and insert data into the database
        foreach ($data as $row) {
            $learners_name = ucfirst($row[0]); // Learner's name
            // $grade_section = $row[1]; // Grade & section
            // Normalize gender: Convert 'male' or 'female' to 'Male' or 'Female'
            $gender = ucfirst($row[1]); 

             // Default to 'Male' if empty
            // $school_year = $row[3]; // School year

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
            $stmt = mysqli_prepare($connection, $insert_query);
            mysqli_stmt_bind_param($stmt, 'sssssi', $custom_id, $learners_name, $grade_section, $gender, $schoolyear, $userid);
            mysqli_stmt_execute($stmt);
        }

        // Set success message in session
        $_SESSION['upload_success'] = 'The student data has been added successfully!';
        // Redirect to Crud.php
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
