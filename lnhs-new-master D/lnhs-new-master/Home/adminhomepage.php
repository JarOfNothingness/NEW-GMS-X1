<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
if (isset($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']); // Remove the success message from session
}

// Include the database connection
include_once("../LoginRegisterAuthentication/connection.php");
// include_once("LoginRegisterAuthentication/connection.php");

// Check if the connection is successful
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query for total students
$totalStudentsQuery = "SELECT COUNT(*) as total_students FROM students";
$totalStudentsResult = mysqli_query($connection, $totalStudentsQuery);
$totalStudents = mysqli_fetch_assoc($totalStudentsResult)['total_students'];

// Query for total teachers
$totalTeachersQuery = "SELECT COUNT(*) as total_teachers FROM user WHERE role = 'Teacher' AND status = 'approved'";
$totalTeachersResult = mysqli_query($connection, $totalTeachersQuery);
$totalTeachers = mysqli_fetch_assoc($totalTeachersResult)['total_teachers'];

// Query for total subjects
$totalSubjectsQuery = "SELECT COUNT(*) as total_subjects FROM subjects";
$totalSubjectsResult = mysqli_query($connection, $totalSubjectsQuery);
$totalSubjects = mysqli_fetch_assoc($totalSubjectsResult)['total_subjects'];

// Query for pending approvals (users with status 'pending')
$pendingApprovalsQuery = "SELECT COUNT(*) as total_pending_approvals FROM user WHERE status = 'pending'";
$pendingApprovalsResult = mysqli_query($connection, $pendingApprovalsQuery);
$totalPendingApprovals = mysqli_fetch_assoc($pendingApprovalsResult)['total_pending_approvals'];


// Query for total admins
$totalAdminsQuery = "SELECT COUNT(*) as total_admins FROM user WHERE role = 'Admin' AND status = 'approved'";
$totalAdminsResult = mysqli_query($connection, $totalAdminsQuery);
$totalAdmins = mysqli_fetch_assoc($totalAdminsResult)['total_admins'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet">
    <style>
        /* Your existing styles */
        .success-box {
            display: <?php echo !empty($success_msg) ? 'block' : 'none'; ?>;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 30px;
            border: 2px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            max-width: 300px;
            text-align: center;
            z-index: 1000;
            opacity: 1;
            transition: opacity 0.5s ease-out;
        }

        .success-box p {
            color: #4caf50;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
        }

        header {
            background-color: #0047ab;
            color: #fff;
            padding: 20px;
            text-align: center;
            font-size: 28px;
        }

        .sidebar {
            width: 60px;
            background-color: #333;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            transition: width 0.3s;
            overflow-x: hidden;
            white-space: nowrap;
        }

        .sidebar:hover {
            width: 250px;
        }

        .sidebar a {
            color: #fff;
            padding: 15px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background-color: #575757;
        }

        .sidebar a span {
            display: none;
            margin-left: 10px;
        }

        .sidebar:hover a span {
            display: inline;
        }

        .main-content {
            margin-left: 60px;
            padding: 20px;
            transition: margin-left 0.3s, width 0.3s;
        }

        .sidebar:hover ~ .main-content {
            margin-left: 250px;
        }

        .dashboard-header {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .dashboard-cards {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }

        .clock {
            font-size: 36px;
            margin-bottom: 20px;
            text-align: center;
            color: #007bff;
        }

        .dashboard-cards .card {
            background-color: #fff;
            width: 30%;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s;
        }

        .dashboard-cards .card:hover {
            transform: scale(1.05);
        }

        .dashboard-cards .card h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #0047ab;
        }

        .dashboard-cards .card p {
            font-size: 36px;
            margin: 0;
            color: #333;
        }

        .card i {
            font-size: 50px;
            color: #0047ab;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Success message display -->
    <?php if (!empty($success_msg)): ?>
        <div class="success-box">
            <p><?php echo $success_msg; ?></p>
        </div>
    <?php endif; ?>

    <header>
        Admin Dashboard
    </header>

    <div class="sidebar">
        <a href="adminhomepage.php">
        <i class="fas fa-chart-line"></i>
        <span>Dashboard</span>

        </a>

        <a href="manage_user.php">
            <i class="fas fa-user-cog"></i>
            <span>Manage Teachers</span>
        </a>
       
        <a href="announcements.php">
            <i class="fas fa-bullhorn"></i>
            <span>Announcements</span>
        </a>
        <a href="adminpendingrequestapproval.php">
            <i class="fas fa-check"></i>
            <span>Registration Requests</span>
        </a>
        <a href="adminmanageaccount.php">
            <i class="fas fa-key"></i>
            <span>Change Password</span>
        </a>
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>

    <div class="main-content">
        <h2>Welcome Admin, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
        <div class="dashboard-cards">
            
            <div class="card animated fadeInUp" onclick="window.location.href='manage_user.php'" style="cursor:pointer">
                <i class="fas fa-chalkboard-teacher"></i>
                <h3>Total Teachers</h3>
                <p><?php echo $totalTeachers; ?></p>
            </div>
           
            <div class="card animated fadeInUp" onclick="window.location.href='adminpendingrequestapproval.php'" style="cursor:pointer">
                <i class="fas fa-tasks"></i>
                <h3>Pending Approvals</h3>
                <p><?php echo $totalPendingApprovals; ?></p>
            </div>
           
            <div class="card animated fadeInUp" onclick="window.location.href='admin_list.php'" style="cursor:pointer">
                <i class="fas fa-user-shield"></i>
                <h3>Total Admins</h3>
                <p><?php echo $totalAdmins; ?></p>
            </div>
        </div>

        <div class="clock" id="clock"></div>
    </div>

    <script>
        // Real-time clock function
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
        updateClock(); // Initialize clock immediately

        // Function to show alerts
        function showAlert(message) {
            alert(message);
        }

        // Example usage of the alert function:
        <?php if (!empty($_SESSION['alert_message'])): ?>
            showAlert("<?php echo addslashes($_SESSION['alert_message']); ?>");
            <?php unset($_SESSION['alert_message']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
