<?php
// Start PHP with no output or whitespace before it

// Example of setting headers if needed
// header("Content-Type: text/html; charset=UTF-8"); // Optional: Define content type

// Ensure no HTML or output before this
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Students Masterlist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Include Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" type="text/css" href="style.css">
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
        

        .submenu {
            display: none;
            flex-direction: column;
            position: relative; /* Relative to parent */
            left: 20px; /* Align submenus slightly to the right of the parent */
            background-color: darkblue;
            padding: 10px;
            border-radius: 4px;
        }

        .submenu a {
            padding: 5px 10px;
            white-space: nowrap;
        }

        .submenu.active {
            display: flex;
        }

        .container{
            margin-right: px;
        }

    </style>
</head>
<body>

<div class="sidebar">
<a href="">
    <img src="../Home/Images/Logo.png.png" alt="Dashboard" style="width:60px; height:56px;">
    <span>LNHS GradingSystem</span>
</a>

    <a href="../Home/dashboard.php">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </a>
    <a href="../crud/Crud.php">
        <i class="fas fa-user-graduate"></i>
        <span>Masterlist</span>
    </a>
    <a href="../Home/ClassRecord.php">
        <i class="fas fa-book"></i>
        <span>Class Records</span>
    </a>
    <a href="../Home/Attendance.php">
        <i class="fas fa-clipboard-list"></i>
        <span>Attendance</span>
    </a>
    <a href="../Home/GradingSystem.php">
        <i class="fas fa-calculator"></i>
        <span>Grading System</span>
    </a>
    <a href="../Home/FinalGrade.php">
        <i class="fas fa-graduation-cap"></i>
        <span>Final Grade</span>
    </a>
    <a href="../Home/view_attendance.php">
        <i class="fas fa-file-alt"></i>
        <span>Reports</span>
    </a>
    <a href="../Home/fileserver.php">
        <i class="fas fa-folder"></i>
        <span>File Server</span>
    </a>
    <a href="../Home/fetch_schedules.php">
        <i class="fas fa-calendar-alt"></i>
        <span>Schedule</span>
    </a>
    <a href="../Home/manageaccount.php">
        <i class="fas fa-user-cog"></i>
        <span>Manage Account</span>
    </a>
    <a href="../Home/logout.php">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
</div>
<div class="main-content"></div>