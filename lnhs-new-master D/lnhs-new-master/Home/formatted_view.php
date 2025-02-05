<?php
session_start(); // Start the session at the very top

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include('../crud/header.php'); 
include("../LoginRegisterAuthentication/connection.php"); 

// Get the student ID from the query string
$student_id = isset($_GET['id']) ? intval($_GET['id']) : '';
// SQL query to fetch student details and grades
$query = "SELECT s.*, sg.*
          FROM students s 
          JOIN student_grades sg ON s.id = sg.student_id
          WHERE s.id = '$student_id'";


$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}

// Fetch the student details
$student = mysqli_fetch_assoc($result);

// Initialize variables for grades
$first_quarter = isset($student['first_quarter']) ? $student['first_quarter'] : 0;
$second_quarter = isset($student['second_quarter']) ? $student['second_quarter'] : 0;
$third_quarter = isset($student['third_quarter']) ? $student['third_quarter'] : 0;
$fourth_quarter = isset($student['fourth_quarter']) ? $student['fourth_quarter'] : 0;

// Calculate total and average
$total = $first_quarter + $second_quarter + $third_quarter + $fourth_quarter;
$average = $total > 0 ? $total / 4 : 0;

// Fetch final grades and metrics
$grades_query = "SELECT sg.*, s.learners_name FROM student_grades sg
                 JOIN students s ON sg.student_id = s.id
                 WHERE sg.student_id = '$student_id'";
$grades_result = mysqli_query($connection, $grades_query);

if (!$grades_result) {
    die("Query failed: " . mysqli_error($connection));
}

$grades = [];
while ($row = mysqli_fetch_assoc($grades_result)) {
    $grades[] = $row;
}

function calculateMetrics($grades) {
    $metrics = [
        'total_score' => 0,
        'no_of_cases' => 0,
        'highest_possible_score' => 0,
        'highest_score' => 0,
        'lowest_score' => null,
        'average_mean' => 0,
        'mps' => 0,
        'students_75_pl' => 0,
        'percentage_75_pl' => 0
    ];
    $total_scores = [];

    foreach ($grades as $grade) {
        $written_exam = isset($grade['written_exam']) ? $grade['written_exam'] : 0;
        $performance_task = isset($grade['performance_task']) ? $grade['performance_task'] : 0;
        $quarterly_exam = isset($grade['quarterly_exam']) ? $grade['quarterly_exam'] : 0;
        $final_grade = isset($grade['final_grade']) ? $grade['final_grade'] : 0;
        $highest_possible_score = isset($grade['highest_possible_score']) ? $grade['highest_possible_score'] : 0;

        $total_score = ($written_exam * 0.40) + ($performance_task * 0.40) + ($quarterly_exam * 0.20);
        $metrics['total_score'] += $total_score;
        $metrics['no_of_cases']++;
        $metrics['highest_possible_score'] = max($metrics['highest_possible_score'], $highest_possible_score);
        $metrics['highest_score'] = max($metrics['highest_score'], $final_grade);

        if ($metrics['lowest_score'] === null || $total_score < $metrics['lowest_score']) {
            $metrics['lowest_score'] = $total_score;
        }

        $total_scores[] = $total_score;
    }

    if ($metrics['no_of_cases'] > 0) {
        $metrics['average_mean'] = $metrics['total_score'] / $metrics['no_of_cases'];
        $metrics['mps'] = $metrics['average_mean'];
        $metrics['students_75_pl'] = count(array_filter($total_scores, fn($score) => $score >= 75));
        $metrics['percentage_75_pl'] = ($metrics['students_75_pl'] / $metrics['no_of_cases']) * 100;
    }

    return $metrics;
}

$metrics = calculateMetrics($grades);
?>

<style>
      body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 80px; /* Initial width showing only icons */
            background-color: darkblue;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            transition: width 0.3s;
            overflow-x: hidden;
            white-space: nowrap;
        }

        .sidebar:hover {
            width: 250px; /* Expand on hover */
        }

        .sidebar a {
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background-color: #575757;
        }

        .sidebar a span {
            display: none; /* Hide text initially */
            margin-left: 10px;
        }

        .sidebar:hover a span {
            display: inline; /* Show text on hover */
        }

        .main-content {
            margin-left: 100px;
            padding: 20px;
            width: calc(100% - 100px);
            transition: margin-left 0.3s, width 0.3s;
        }

        .sidebar:hover ~ .main-content {
            margin-left: 300px;
            width: calc(100% - 250px);
        }

        .dashboard-header {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
            font-size: 32px;
        }

        .form-links {
            position: fixed;
            top: 20px;
            right: 20px;
        }

        .form-links a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
        }

    .form137-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .form137-container img {
        height: 80px;
    }

    .center h2 {
        text-align: center;
        font-family: Arial, sans-serif;
        font-size: 24px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        font-family: Arial, sans-serif;
    }

    table, td {
        border: 1px solid black;
    }

    td {
        padding: 8px;
        text-align: center;
        vertical-align: middle;
        font-size: 14px;
    }

    .school-info {
        background-color: #f2f2f2;
        font-weight: bold;
    }

    .Container-info {
        background-color: #d9d9d9;
        font-weight: bold;
        text-transform: uppercase;
    }

    thead {
        background-color: #f8f8f8;
    }

    a {
        display: inline-block;
        margin-top: 20px;
        padding: 10px 20px;
        background-color: #blue;
        color: white;
        text-decoration: none;
        border-radius: 5px;
    }

    a:hover {
        background-color: #0056b3;
    }

</style>

<div class="form137-container">
    <div class="left">
        <img src="./images/Logo.png.png" alt="Logo">
    </div>
    <div class="center">
        <h2>Input Data Sheet for E-Class Record</h2>
    </div>
    <div class="right">
        <img src="./images/depedlogo.png.png" alt="DepEd Logo">
    </div>
</div>

<table>
    <thead>
        <tr class="school-info">
            <td colspan="15">REGION: VII</td>
            <td colspan="10">DIVISION CEBU: CEBU PROVINCE</td>
            <td colspan="15">SCHOOL NAME: LANAO NATIONAL HIGH SCHOOL</td>
            <td colspan="8">SCHOOL ID</td>
            <td colspan="6">SCHOOL YEAR: 2023-2024</td>
        </tr>
        <tr class="school-info">
            <td colspan="17">GRADE & SECTION: GRADE 9-SAPPHIRE</td>
            <td colspan="18" class="section-header">TEACHER: PAMILA ANN BULAS</td>
            <td colspan="19" class="section-header">SUBJECT: ESP</td>
        </tr>
        <tr class="Container-info">
            <td colspan="27">No.</td>
            <td colspan="27">LEARNER'S NAME</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="1">1</td>
            <td colspan="1">Male</td>
            <td><?php echo htmlspecialchars($student['learners_name']); ?></td>
        </tr>
    </tbody>
</table>

<a href="../crud/Crud.php">Back</a>
</body>
</html>
