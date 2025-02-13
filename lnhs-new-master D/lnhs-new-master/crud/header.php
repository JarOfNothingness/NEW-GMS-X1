<?php
// Start PHP with no output or whitespace before it

// Example of setting headers if needed
// header("Content-Type: text/html; charset=UTF-8"); // Optional: Define content type

// Ensure no HTML or output before this
    $userid = $_SESSION['userid'];

    $query = "SELECT 
        s.id,
        s.learners_name, 
        s.`grade & section`, 
        s.gender, 
        s.school_year
        FROM 
        students s
        WHERE 
        s.user_id = ?";

    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'i', $userid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
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

         /* Target only the <th> elements with class "blue" inside this specific table */
        .table-responsive .table thead th.blue {
            background-color: blue; /* Color the background blue */
            color: white; /* Set the text color to white for better contrast */
        }
/* Add space between dropdowns */
.dropdown {
    margin-right: 10px; /* Adjust this value as needed */
}

/* Alternatively, if you want to use Bootstrap spacing utilities */
.d-flex .dropdown {
    margin-right: 2rem; /* Adjust this value as needed */
}

        /* Table styling */
.table1 {
    width: 100%;
    border-collapse: collapse;
    margin: 25px 0;
    font-size: 18px;
    text-align: left;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
}

.table1 thead {
    background-color: #343a40;
    color: #fff;
}

.table1 th,
.table1 td {
    padding: 12px 15px;
    border: 1px solid #ddd;
}

.table1 tbody tr {
    background-color: #f9f9f9;
}

.table1 tbody tr:nth-of-type(even) {
    background-color: #f1f1f1;
}

.table1 tbody tr:hover {
    background-color: #f5f5f5;
    cursor: pointer;
}

/* Button styling inside the table */
.table1 a.btn {
    padding: 6px 12px;
    color: #fff;
    background-color: #007bff;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-size: 14px;
    transition: background-color 0.3s ease;
}

.table1 a.btn:hover {
    background-color: #0056b3;
}

/* Specific styling for table headers */
.table1 th {
    background-color: #343a40;
    color: #ffffff;
    font-weight: bold;
    text-transform: uppercase;
}

/* Align the 'Actions' column for better visibility */
.table1 td.actions {
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
<a href="../Home/dashboard.php">
    <img src="../Home/Images/Logo.png.png" alt="Dashboard" style="width:60px; height:56px;">
    <span>LNHS GradingSystem</span>
</a>

 
<a href="../Home/newdashboard.php">
    <i class="fas fa-chart-line"></i>
    <span>Dashboard</span>
</a>
   
<!-- MASTERLIST -->
    <a href="#" class="toggle-submenu" data-target="#masterlistSubmenu">
        <i class="fas fa-user-graduate"></i>
        <span>Masterlist</span>
    </a>

        <!-- Submenu under Masterlist -->
    <ul class="submenu" id="masterlistSubmenu">
        <li><a href="../crud/Crud.php">Create Class</a></li>
        <?php if (mysqli_num_rows($result) != 0){
            //echo mysqli_num_rows($result);
            echo '<li><a href="../crud/view_masterlist.php">View Master List</a></li>';
        } ?>
      
    </ul>
  
    <!-- CLASS RECORD -->
    <a href="../Home/ClassRecord.php">
        <i class="fas fa-book"></i>
        <span>Class Records</span>
    </a>

    <a href="../Home/Attendance.php">
        <i class="fas fa-clipboard-list"></i>
        <span>Attendance</span>
    </a>

<!-- GradingSystem -->
<a href="#" class="toggle-submenu" data-target="#gradingSystemSubmenu">

        <i class="fas fa-calculator"></i>
        <span>Grade Components</span>
    </a>

    <ul class="submenu" id="gradingSystemSubmenu">
        <li><a href="../Home/GradingSystem.php">View Weight Components</a></li> 
        <li><a href="../Home/gradingscale.php">View Transmutation table</a></li>
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
<a href="../Home/view_attendance.php" class="toggle-submenu" data-target="#encodeForm137Submenu">
        <i class="fas fa-file-alt"></i>
        <span>Reports</span>
    </a>
    <ul class="submenu" id="encodeForm137Submenu">
    <li><a href="../Home/view_attendance.php">View Reports</a></li>
    <li><a href="../Home/encoder.php">Encode A Form</a></li>
    <li><a href="../Home/form137.php">View form137</a></li>
   </ul>





    <a href="../Home/manageaccount.php">
        <i class="fas fa-user-cog"></i>
        <span>Change Password</span>
    </a>

    <a href="../Home/logout.php">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
</div>
<div class="main-content">

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
