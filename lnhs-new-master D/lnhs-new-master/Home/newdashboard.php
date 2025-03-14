<?php 
session_start();

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']); // Remove the success message from session
}

include("../LoginRegisterAuthentication/connection.php");
include("../crud/header.php");

// Query for total students based on logged-in user
$userid = $_SESSION['userid'];
$totalStudentsQuery = "SELECT COUNT(*) as total_students FROM students WHERE user_id = ?";
$stmt = mysqli_prepare($connection, $totalStudentsQuery);
mysqli_stmt_bind_param($stmt, 'i', $userid);
mysqli_stmt_execute($stmt);
$totalStudentsResult = mysqli_stmt_get_result($stmt);
$totalStudents = mysqli_fetch_assoc($totalStudentsResult)['total_students'];

// Query for total subjects
$totalSubjectsQuery = "SELECT COUNT(*) as total_subjects FROM subjects";
$totalSubjectsResult = mysqli_query($connection, $totalSubjectsQuery);
$totalSubjects = mysqli_fetch_assoc($totalSubjectsResult)['total_subjects'];

// Query for attendance overview
$todayDay = date('d');
$attendanceColumn = "day_" . str_pad($todayDay, 2, '0', STR_PAD_LEFT);
$attendanceQuery = "SELECT COUNT(*) as total_attendance FROM attendance WHERE $attendanceColumn = 'P'";
$attendanceResult = mysqli_query($connection, $attendanceQuery);
$totalAttendance = mysqli_fetch_assoc($attendanceResult)['total_attendance'];

// Attendance overview is calculated as the percentage of students who attended today
$attendanceOverview = ($totalStudents > 0) ? ($totalAttendance / $totalStudents) * 100 : 0;

// Query for total passing students
$passingStudentsQuery = "
    SELECT COUNT(DISTINCT student_id) as total_passing
    FROM assessment_summary
    WHERE user_id = ".$userid." 
    AND quarterly_grade >= 75
";
$passingStudentsResult = mysqli_query($connection, $passingStudentsQuery);
$totalPassingStudents = mysqli_fetch_assoc($passingStudentsResult)['total_passing'];

// Query for total failing students
$failingStudentsQuery = "
    SELECT COUNT(DISTINCT student_id) as total_failing
    FROM assessment_summary
    WHERE user_id = ".$userid." 
    AND quarterly_grade < 75
";
$failingStudentsResult = mysqli_query($connection, $failingStudentsQuery);
$totalFailingStudents = mysqli_fetch_assoc($failingStudentsResult)['total_failing'];

// Close the database connection
mysqli_close($connection);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            background-color: #4a69bd;
            padding: 20px;
            color: #fff;
            font-size: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 1200px;
        }

        .header-text {
            text-align: center;
            margin: 0;
        }

        .main-content {
            padding: 30px;
            flex-grow: 1;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .dashboard-cards {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background-color: #fff;
            flex-basis: calc(25% - 15px);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            box-sizing: border-box;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .card h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #4a69bd;
        }

        .card p {
            font-size: 36px;
            margin: 0;
            color: #2c3e50;
            font-weight: bold;
        }

        .card i {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .passing-icon {
            color: #28a745; /* Green for passing students */
        }

        .failing-icon {
            color: #dc3545; /* Red for failing students */
        }

        .clock {
            font-size: 48px;
            margin: 30px 0;
            text-align: center;
            color: #2c3e50;
            font-weight: bold;
        }

        .attendance-card {
            width: 100%;
            margin-top: 20px;
        }

        .progress {
            height: 25px;
            background-color: #e9ecef;
            border-radius: 12.5px;
            overflow: hidden;
            margin-top: 15px;
        }

        .progress-bar {
            height: 100%;
            line-height: 25px;
            color: white;
            text-align: center;
            background-color: #4a69bd;
            transition: width 0.5s ease-in-out;
        }

        @media (max-width: 1200px) {
            .card {
                flex-basis: calc(50% - 10px);
            }
        }

        @media (max-width: 768px) {
            .card {
                flex-basis: 100%;
            }
        }
    </style>
    </style>
</head>
<body>
    <header>
        <h1 class="header-text">Dashboard</h1>
    </header>

    <div class="main-content">
        <div class="dashboard-cards">
            <div class="card">
                <i class="fas fa-user-graduate" onclick="window.location.href='../crud/Crud.php'" style="cursor:pointer"></i>
                <h3>Handled Students</h3>
                <p><?php echo $totalStudents; ?></p>
            </div>
            <div class="card">
                <i class="fas fa-book"></i>
                <h3>Available Subjects</h3>
                <p><?php echo $totalSubjects; ?></p>
            </div>
            <div class="card">
                <i class="fas fa-check-circle passing-icon" onclick="window.location.href='statistics.php'" style="cursor:pointer"></i>
                <h3>Passing Students</h3>
                <p><?php echo $totalPassingStudents; ?></p>
            </div>
            <div class="card">
                <i class="fas fa-times-circle failing-icon" onclick="window.location.href='statistics.php'" style="cursor:pointer"></i>
                <h3>Failing Students</h3>
                <p><?php echo $totalFailingStudents; ?></p>
            </div>
     
        </div>

        <div class="clock" id="clock"></div>
    </div>

    <script>
        function updateClock() {
            var now = new Date();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var seconds = now.getSeconds();
            var ampm = hours >= 12 ? 'PM' : 'AM';

            hours = hours % 12;
            hours = hours ? hours : 12;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;

            var timeStr = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
            document.getElementById('clock').innerText = timeStr;
        }

        setInterval(updateClock, 1000);
        updateClock();

        function updateAttendanceBar(percentage) {
            var bar = document.getElementById('attendanceBar');
            bar.style.width = percentage + '%';
            bar.innerText = percentage.toFixed(2) + '%';
        }

        var attendanceRate = <?php echo $attendanceOverview; ?>;
        updateAttendanceBar(attendanceRate);
    </script>
</body>
</html>