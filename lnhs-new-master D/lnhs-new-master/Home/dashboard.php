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

include("LoginRegisterAuthentication/connection.php");
include("crud/header.php");
// include("../LoginRegisterAuthentication/connection.php");
// include("../crud/header.php");

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

$sql_announcements = "SELECT title, content, created_at, expiration_date,
                     CASE 
                         WHEN expiration_date IS NOT NULL THEN 
                             TIMESTAMPDIFF(SECOND, NOW(), expiration_date)
                         ELSE NULL 
                     END as seconds_remaining
                     FROM announcements 
                     WHERE expiration_date IS NULL OR expiration_date > NOW()
                     ORDER BY created_at DESC";

$statement_announcements = mysqli_stmt_init($connection);
$announcements = [];

if (mysqli_stmt_prepare($statement_announcements, $sql_announcements)) {
    mysqli_stmt_execute($statement_announcements);
    $result_announcements = mysqli_stmt_get_result($statement_announcements);
    while ($row = mysqli_fetch_assoc($result_announcements)) {
        $announcements[] = $row;
    }
}


// Close the database connection
mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            background: linear-gradient(to bottom, #e9ecef, #f8f9fa); /* Soft gradient background */
        }

        .dashboard {
            background-color: #fff;
            width: 100%;
            max-width: 2000px; /* Adjusted for responsive look */
            padding: 0px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: ;
        }

        header {
            background-color: #3f51b5; /* Dark blue header */
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            color: #fff;
            font-size: 24px;
            position: relative;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .quote-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color:;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .quote-container p {
            font-size: 22px;
            font-style: italic;
            color: #333;
            margin: 0;
            max-width: 65%;
        }

        .quote-container img {
            max-width: 200px; /* Resize the image */
            height: auto;
            border-radius: 50%; /* Circular image */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .announcement-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .announcement-section h3 {
            font-size: 22px;
            color: #3f51b5; /* Matching header color */
            border-bottom: 2px solid #3f51b5;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .announcement {
            margin-bottom: 20px;
        }

        .announcement-title {
            font-size: 20px;
            font-weight: bold;
            color: #3f51b5;
        }

        .announcement-content {
            font-size: 16px;
            color: #6c757d;
            margin: 5px 0;
        }

        .announcement-date {
            font-size: 14px;
            color: #adb5bd;
        }

        .announcements-container {
            margin-top: 20px;
            padding: 15px;
            background-color: #f7f9fc;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .expiration-badge {
        font-size: 0.8em;
        padding: 0.3em 0.6em;
        border-radius: 10px;
        margin-left: 10px;
        display: inline-block;
    }
    
    .expires-soon {
        background-color: #ffd700;
        color: #000;
    }
    
    .expires-later {
        background-color: #90EE90;
        color: #000;
    }
    
    .announcement {
        background-color: #fff;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .announcement-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    </style>
</head>
<body>

    <div class="dashboard">
        <header>
            <h1 class="header-text">Welcome, Teacher <?php echo htmlspecialchars(preg_replace('/[0-9]/', '', $_SESSION['username'])); ?></h1>
        </header>

        <div class="quote-container">
            <p>"Teaching is the one profession that creates all other professions." Have a fantastic day ahead!</p>
            <img src="Images/femaleteacher.jpg" alt="Teacher Illustration">
        </div>

        <!-- Announcements Section -->
        <div class="announcements-container">
    <h3>Announcements</h3>
    <?php if (empty($announcements)): ?>
        <p>No announcements at the moment.</p>
    <?php else: ?>
        <?php foreach ($announcements as $announcement): ?>
            <div class="announcement">
                <div class="announcement-title">
                    <?php echo htmlspecialchars($announcement['title']); ?>
                    <?php if ($announcement['expiration_date']): ?>
                     
                   
                        </span>
                    <?php endif; ?>
                </div>
                <div class="announcement-content">
                    <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                </div>
                <div class="announcement-date">
                    <small>Posted on: <?php echo date('F j, Y', strtotime($announcement['created_at'])); ?></small>
                    <?php if ($announcement['expiration_date']): ?>
                        <br>
                        <small>Expires on: <?php echo date('F j, Y', strtotime($announcement['expiration_date'])); ?></small>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


</body>
</html>
