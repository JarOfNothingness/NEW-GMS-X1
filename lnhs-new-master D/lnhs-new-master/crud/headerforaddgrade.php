<?php
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
            margin-left: 10px;
            padding: 20px;
            width: calc(10% - 10px);
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


    
.submenu {
    list-style: none;
    padding-left: 0;
    display: none; /* Hidden by default */
    margin: 0;
}

.submenu li {
    padding: 10px;
    background-color: gray;
}

.submenu li a {
    text-decoration: none;
    color: white;
}

.submenu-active {
    display: block; /* Show submenu when active */
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
        <span>Home</span>
    </a>

    <a href="../Home/teacher_dashboard.php">
        <i class="fas fa-user-cog"></i>
        <span>View</span>
    </a>
 
<!-- MASTERLIST -->
<a href="#" class="toggle-submenu" data-target="#masterlistSubmenu">
        <i class="fas fa-user-graduate"></i>
        <span>Masterlist</span>
    </a>
    <ul class="submenu" id="masterlistSubmenu">
        <li><a href="../crud/Crud.php">View all Students</a></li> 
    </ul>








    <!-- CLASS RECORD -->
    <a href="#" class="toggle-submenu" data-target="#classRecordsSubmenu">
        <i class="fas fa-book"></i>
        <span>Class Records</span>
    </a>
    <ul class="submenu" id="classRecordsSubmenu">
        <li><a href="../Home/add_grade.php">Grade Student</a></li> 
        <li><a href="../Home/statistics.php">View Students Statistics</a></li>
        <li><a href="../Home/Grade_card.php">View Students Report Card</a></li>
        <li><a href="../Home/view_attendance.php">File Reports</a></li>
        <li><a href="../Home/updategraderecord.php">Update Record</a></li>
        <li><a href="../Home/search.php">View Records</a></li>
    </ul>



    <a href="../Home/Attendance.php">
        <i class="fas fa-clipboard-list"></i>
        <span>Attendance</span>
    </a>



<!-- GradingSystem -->
<a href="#" class="toggle-submenu" data-target="#gradingSystemSubmenu">

        <i class="fas fa-calculator"></i>
        <span>Grading System</span>
    </a>

    <ul class="submenu" id="gradingSystemSubmenu">
        <li><a href="../Home/GradingSystem.php">View Transmutation table</a></li> 
        <li><a href="../Home/gradingscale.php">View Chart</a></li>
    </ul>





  <!-- Main Final Grade Menu Link -->
<a href="../Home/FinalGrade.php">
    <i class="fas fa-graduation-cap"></i>
    <span>Final Grade</span>
</a>

<!-- Submenu for Final Grade -->
<ul class="submenu">
    <li><a href="../Home/FinalGrade.php">All Final Grades</a></li>
    <li><a href="../Home/grade_card.php">Student Grade Card</a></li> 
</ul>

<!-- Reports -->
<a href="../Home/view_attendance.phpp" class="toggle-submenu" data-target="#encodeForm137Submenu">
        <i class="fas fa-file-alt"></i>
        <span>Reports</span>
    </a>
    <ul class="submenu" id="encodeForm137Submenu">
    <li><a href="../Home/view_attendance.phpp">View Reports</a></li>
    <li><a href="../Home/encoder.php">Encode A Form</a></li>
    <li><a href="../Home/form137.php">View form137</a></li>
   </ul>


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

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
$(document).ready(function() {
    $('.toggle-submenu').click(function(event) {
        event.preventDefault(); // Prevent default anchor click behavior
        console.log('Submenu toggled'); // Debugging log
        var target = $(this).data('target');
        console.log('Target submenu: ', target); // Log the target

        $(target).toggleClass('submenu-active'); // Toggle the submenu visibility
        $(target).siblings('.submenu').removeClass('submenu-active'); // Close other submenus
    });
});


</script>

</body>
</html>