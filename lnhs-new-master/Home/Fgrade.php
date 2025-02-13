<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include("../LoginRegisterAuthentication/connection.php");

// Initialize filter variables
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : '';
$subject_id = isset($_GET['subject']) ? mysqli_real_escape_string($connection, $_GET['subject']) : '';
$school_year = isset($_GET['school_year']) ? mysqli_real_escape_string($connection, $_GET['school_year']) : '';

// Fetch data for all quarters for the specific student and subject
$query = "SELECT * FROM student_grades 
          WHERE student_id = $student_id AND subject_id = (SELECT id FROM subjects WHERE name = '$subject_id') AND school_year = '$school_year'";

$result = mysqli_query($connection, $query);
if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}

// Define the weights for each subject
$weights = [
    'English' => ['written' => 0.30, 'performance' => 0.50, 'quarterly' => 0.20],
    'Math' => ['written' => 0.40, 'performance' => 0.40, 'quarterly' => 0.20],
    // Add other subjects here
];

// Calculate final grade and insert/update into finalgrades table
if (mysqli_num_rows($result) > 0) {
    $grades = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $grades[] = $row;
    }

    // Assuming $grades contains data for all quarters
    if (isset($weights[$subject_id])) {
        $weight = $weights[$subject_id];
        
        $total_written = 0;
        $total_performance = 0;
        $total_quarterly = 0;
        $count = count($grades);

        foreach ($grades as $grade) {
            $total_written += $grade['written_exam'];
            $total_performance += $grade['performance_task'];
            $total_quarterly += $grade['quarterly_exam'];
        }

        // Calculate average scores
        $avg_written = $total_written / $count;
        $avg_performance = $total_performance / $count;
        $avg_quarterly = $total_quarterly / $count;

        // Calculate final grade based on weights
        $final_grade = ($avg_written * $weight['written']) +
                        ($avg_performance * $weight['performance']) +
                        ($avg_quarterly * $weight['quarterly']);

        // Insert/Update final grade in finalgrades table
        $check_query = "SELECT * FROM finalgrades WHERE student_id = $student_id AND subject_id = (SELECT id FROM subjects WHERE name = '$subject_id') AND school_year = '$school_year'";
        $check_result = mysqli_query($connection, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            // Update existing record
            $update_query = "UPDATE finalgrades SET final_grade = $final_grade WHERE student_id = $student_id AND subject_id = (SELECT id FROM subjects WHERE name = '$subject_id') AND school_year = '$school_year'";
            mysqli_query($connection, $update_query);
        } else {
            // Insert new record
            $insert_query = "INSERT INTO finalgrades (student_id, subject_id, school_year, final_grade) VALUES ($student_id, (SELECT id FROM subjects WHERE name = '$subject_id'), '$school_year', $final_grade)";
            mysqli_query($connection, $insert_query);
        }
    }
}
?>
