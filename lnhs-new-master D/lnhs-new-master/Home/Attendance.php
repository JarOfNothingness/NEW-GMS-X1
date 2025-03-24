<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Connect to the database
include("../LoginRegisterAuthentication/connection.php");
// include("LoginRegisterAuthentication/connection.php");
// include("crud/header.php");
include("../crud/header.php");
$userid = $_SESSION['userid'];
// Fetch school years
$school_years_query = "SELECT DISTINCT school_year FROM students WHERE user_id=".$userid." ORDER BY school_year DESC";
$school_years_result = mysqli_query($connection, $school_years_query);

// Fetch sections
$sections_query = "SELECT DISTINCT `grade & section` FROM students WHERE user_id=".$userid."";
$sections_result = mysqli_query($connection, $sections_query);

// Fetch subjects
$subjects_query = "SELECT id, name FROM subjects";
$subjects_result = mysqli_query($connection, $subjects_query);

// Get selected values (if any) - Now checking both POST and GET
$school_year = $_POST['school_year'] ?? $_GET['school_year'] ?? '';
$section = $_POST['section'] ?? $_GET['section'] ?? 'GRADE & SECTION';
$subject_id = $_POST['subject_id'] ?? $_GET['subject_id'] ?? 'SUBJECT';
$month = $_POST['month'] ?? $_GET['month'] ?? date('Y-m');
$saved = $_GET['saved'] ?? 0;

// If we have all the necessary parameters, treat it as a form submission
if ($school_year && $section && $subject_id && $month) {
    $form_submitted = true;
} else {
    $form_submitted = false;
}

// Auto-populate the form if redirected from save_attendance.php
if ($saved == 1) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-select the values in the form
            var subjectDropdown = document.getElementById('subject_id');
            var sectionDropdown = document.getElementById('section');
            sectionDropdown.innerHTML = '<option value=\"" . htmlspecialchars($section) . "\">" . htmlspecialchars($section) . "</option>';
            subjectDropdown.innerHTML = '<option value=\"" . htmlspecialchars($subject_id) . "\">" . htmlspecialchars($subject_id) . "</option>';
            document.querySelector('select[name=\"school_year\"]').value = '" . htmlspecialchars($school_year) . "';
            document.querySelector('select[name=\"section\"]').value = '" . htmlspecialchars($section) . "';
            document.querySelector('select[name=\"subject_id\"]').value = '" . htmlspecialchars($subject_id) . "';
            document.querySelector('input[name=\"month\"]').value = '" . htmlspecialchars($month) . "';
            
            // Automatically submit the form
             document.querySelector('form').submit();
        });
    </script>";
}else{
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            var subjectDropdown = document.getElementById('subject_id');
            var sectionDropdown = document.getElementById('section');
            sectionDropdown.innerHTML = '<option value=\"" . htmlspecialchars($section) . "\">" . htmlspecialchars($section) . "</option>';
            subjectDropdown.innerHTML = '<option value=\"" . htmlspecialchars($subject_id) . "\">" . htmlspecialchars($subject_id) . "</option>';
        
        });
    </script>";
}

$subject_names = array();
while ($row = mysqli_fetch_assoc($subjects_result)) {
    $subject_names[$row['id']] = $row['name'];
}

$current_subject_name = $subject_names[$subject_id] ?? 'Select Subject';

// Fetch point values
$point_setter_query = "SELECT points_present, points_absent, points_late, points_excused 
FROM point_setter 
WHERE userid = ? AND subject_id = ?";
$point_setter_stmt = $connection->prepare($point_setter_query);
$point_setter_stmt->bind_param("ii", $_SESSION['userid'], $subject_id);
$point_setter_stmt->execute();
$point_setter_result = $point_setter_stmt->get_result();

$points_present = 10; // Default values
$points_absent = 0;
$points_late = 5;
$points_excused = 0; // Default value for excused

if ($point_setter_row = $point_setter_result->fetch_assoc()) {
    $points_present = $point_setter_row['points_present'];
    $points_absent = $point_setter_row['points_absent'];
    $points_late = $point_setter_row['points_late'];
    $points_excused = $point_setter_row['points_excused']; // Fetch excused points
}
$userid = $_SESSION['userid'];
function insertPointSetter($connection, $userid, $subjectId, $presentPoints, $absentPoints, $latePoints, $excusedPoints) {
    $query = "INSERT INTO point_setter (userid, subject_id, points_present, points_absent, points_late, points_excused) 
              VALUES (?, ?, ?, ?, ?, ?) 
              ON DUPLICATE KEY UPDATE 
              points_present = VALUES(points_present), 
              points_absent = VALUES(points_absent), 
              points_late = VALUES(points_late),
              points_excused = VALUES(points_excused)";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("iiiiii", $userid, $subjectId, $presentPoints, $absentPoints, $latePoints, $excusedPoints);
    
    return $stmt->execute();
}

$form_submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Sheet</title>
    <style>
        .main-wrapper{
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding-left: 10px;
        padding-right: 10px;
        }
        header {
                background-color: #4a69bd;
                padding: 20px;
                color: #fff;
                font-size: 24px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                width: 100%;
                border-radius: 15px;
        }

        .header-text {
                text-align: center;
                margin: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f0f2f5;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .report-title {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
            font-weight: bold;
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #34495e;
        }
        select, input[type="month"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
        }
        button[type="submit"] {
            background-color: #3f51b5;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button[type="submit"]:hover {
            background-color: #2980b9;
        }
        .table-container {
            position: relative;
            max-height: calc(100vh - 200px); /* Adjust based on your layout */
            overflow: auto;
            border: 1px solid #ddd;
        }
        
        table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        
        thead {
            position: sticky;
            top: 0;
            z-index: 1;
            background-color: darkblue;
        }
        
        .floating-button-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .save-button {
            display: block;
            padding: 10px 20px;
            background-color: #2ecc71;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .save-button:hover {
            background-color: #27ae60;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            width: 51px!important;
        }
        .student-name-col {
            position: sticky;
            left: 0;
            background-color: #f8f9fa;
            z-index: 2;
        }
        thead .student-name-col {
            z-index: 3;
        }
        .date-column {
            width: 60px!important;
            color:#fff;
        }
        .total-column {
            min-width: 60px;
            color:#fff;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .side-modal {
            position: fixed;
            right: -300px;
            top: 0;
            width: 300px;
            height: 100%;
            background-color: #fff;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            transition: right 0.3s ease-out;
            z-index: 1000;
            overflow-y: auto;
        }
        .side-modal.open {
            right: 0;
        }
        .side-modal-content {
            padding: 20px;
        }
        .side-modal h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .side-modal label {
            display: block;
            margin-bottom: 5px;
            color: #34495e;
        }
        .side-modal input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .side-modal button {
            width: 100%;
            padding: 10px;
            background-color: darkblue;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .side-modal button:hover {
            background-color: #2980b9;
        }
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            color: #aaa;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
        }
        .close-btn:hover {
            color: #333;
        }
        #openModalBtn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999;
            padding: 10px 15px;
            background-color: darkblue;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        #openModalBtn:hover {
            background-color: #2980b9;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: Arial, sans-serif;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .back-button, .point-setter-button {
            padding: 10px 15px;
            background-color: #f0f0f0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .point-setter-button {
            background-color: darkblue;
            color: white;
        }
        .report-title {
            text-align: center;
            color: #333;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .filters-container {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .inline-form {
            display: flex;
            gap: 10px;
        }
        .form-group select, .load-button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .load-button {
            background-color: red;
            color: white;
            border: none;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .inline-form {
                flex-direction: column;
            }
            .form-group {
                width: 100%;
            }
        }
        .legend-container {
            text-align: left;
            margin-bottom: 20px;
        }
        .legend-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .legend-list li {
            display: inline-block;
            margin-left: 15px;
        }
        .legend-item {
            display: inline-block;
            width: 20px;
            height: 20px;
            line-height: 20px;
            text-align: center;
            border-radius: 50%;
            margin-right: 5px;
        }
        .present { background-color: #2ecc71; color: white; }
        .absent { background-color: #e74c3c; color: white; }
        .late { background-color: #f39c12; color: white; }
        .excused { background-color: darkblue; color: white; }
        .save-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #2ecc71;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
    </style>
</head>
<body>
<div class="main-wrapper">
    <header>
        <h1 class="header-text">DAILY ATTENDANCE REPORT OF LEARNERS</h1>
    </header>
    <div class="container">
    <?php 
        if ($saved == 1) {
            echo "<div class='success-message'>Attendance has been successfully saved!</div>";
        }
    ?>
        <div class="row">
            <div class="col-md-6">
                <div class="filters-container">
                <form method="post" action="Attendance.php">
                    <select name="school_year" id="school_year"  onchange="fetchSections(this.value)" required>
                        <option value="">SCHOOL YEAR</option>
                        <?php 
                        mysqli_data_seek($school_years_result, 0);
                        while ($year = mysqli_fetch_assoc($school_years_result)) : 
                        ?>
                            <option value="<?php echo htmlspecialchars($year['school_year']); ?>"
                                <?php echo ($year['school_year'] == $school_year) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($year['school_year']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <select name="section" id= "section"  onchange="fetchSubjects(this.value)" required>
                        <option value="">GRADE & SECTION</option>
                        <!-- <?php 
                        mysqli_data_seek($sections_result, 0);
                        while ($section_row = mysqli_fetch_assoc($sections_result)) : 
                        ?>
                            <option value="<?php echo htmlspecialchars($section_row['grade & section']); ?>"
                                <?php echo ($section_row['grade & section'] == $section) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($section_row['grade & section']); ?>
                            </option>
                        <?php endwhile; ?> -->
                    </select>

                    <select name="subject_id" id="subject_id" required>
                        <option value="">SUBJECT</option>
                     
                    </select>

                    <input type="month" name="month" value="<?php echo htmlspecialchars($month); ?>" required>

                    <button type="submit" class="load-button">LOAD ATTENDANCE</button>
                </form>
                </div>
            </div>
            <div class="col-md-6">
                   <div class="legend-container">
                    <h4>LEGEND:</h4>
                    <ul class="legend-list">
                        <li><span class="legend-item present">P</span> - PRESENT</li>
                        <li><span class="legend-item absent">A</span> - ABSENT</li>
                        <li><span class="legend-item late">L</span> - LATE</li>
                        <li><span class="legend-item excused">E</span> - EXCUSED</li>
                    </ul>
                </div>
            </div>
        </div>

    <?php

    if ($form_submitted && $school_year && $section && $subject_id && $month) {
        // Attendance Sheet View
        ?>
        <h2</h2>
        <!-- <div class="button-container">
            <button id="openModalBtn" onclick="openModal()">Point Setter</button>
        </div> -->
        <?php


        // Query to get the students in the selected section and subject
        $query = "SELECT id, learners_name FROM students WHERE `grade & section` = ? AND school_year = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("ss", $section, $school_year);
        $stmt->execute();
        $students = $stmt->get_result();

        if ($students->num_rows > 0) {
            echo "<div class='table-container'>";
            echo "<form method='post' action='save_attendance.php' id='attendance-form'>";
            echo "<table>";
            echo "<thead><tr>
            <th class='student-name-col'>No.</td>
            <th class='student-name-col'>Student Name</th>";

            // Get the total number of days in the month and iterate
            $numDays = date('t', strtotime($month));
            $days_in_month = [];
            $subid = 0;
            
            for ($i = 1; $i <= $numDays; $i++) {
                $dayOfWeek = date('N', strtotime("$month-$i"));
                if ($dayOfWeek < 6) { // Include Monday (1) to Saturday (6)
                    $dayName = date('D', strtotime("$month-$i"));
                    echo "<th class='date-column'>" . $dayName . "<br>" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</th>";
                    $days_in_month[] = $i;
                }
            }
            echo "<th class='total-column'>Total Present</th><th class='total-column'>Total Absent</th><th class='total-column'>Total Late</th><th class='total-column'>Total Excuse</th><th class='total-column'>Total Points</th></tr></thead><tbody>";

            // Loop through students
            $count=1;
            while ($student = $students->fetch_assoc()) {
               
                echo "<tr>
                <td class='student-name-col'>".$count++."</td>
                <td class='student-name-col'>" . htmlspecialchars($student['learners_name']) . "</td>";
            
                // Query to get attendance for the student
                $attendanceQuery = "SELECT * FROM attendance WHERE student_id = ? AND user_id = ? AND month = ? AND subject_id = ?";
                $attendanceStmt = $connection->prepare($attendanceQuery);
                $attendanceStmt->bind_param("iiss", $student['id'], $userid, $month, $subid);
                $attendanceStmt->execute();
                $attendance = $attendanceStmt->get_result()->fetch_assoc();
            
                $totalPresent = $totalAbsent = $totalLate = $totalExcused = $totalPoints = 0;
            
                // Generate attendance dropdown for each day
                foreach ($days_in_month as $i) {
                    $day = "day_" . str_pad($i, 2, '0', STR_PAD_LEFT);
                    $attendanceStatus = $attendance[$day] ?? 'P';
            
                    echo "<td>
                            <select name='attendance[" . $student['id'] . "][" . $day . "]' onchange='updateTotals(this)' style='width:51px'>
                                <option value='P'" . ($attendanceStatus == 'P' ? ' selected' : '') . ">P</option>
                                <option value='A'" . ($attendanceStatus == 'A' ? ' selected' : '') . ">A</option>
                                <option value='L'" . ($attendanceStatus == 'L' ? ' selected' : '') . ">L</option>
                                <option value='E'" . ($attendanceStatus == 'E' ? ' selected' : '') . ">E</option>
                            </select>
                        </td>";
            
                    // Count totals and calculate points
                    switch ($attendanceStatus) {
                        case 'P': $totalPresent++; $totalPoints += $points_present; break;
                        case 'A': $totalAbsent++; $totalPoints += $points_absent; break;
                        case 'L': $totalLate++; $totalPoints += $points_late; break;
                        case 'E': $totalExcused++; $totalPoints += $points_excused; break;
                    }
                }
                
                echo "<td class='total-present'>" . $totalPresent . "</td>";
                echo "<td class='total-absent'>" . $totalAbsent . "</td>";
                echo "<td class='total-late'>" . $totalLate . "</td>";
                echo "<td class='total-excused'>" . $totalExcused . "</td>";
                echo "<td class='total-points'>" . $totalPoints . "</td>";
                echo "</tr>";
            }
            
            echo "</tbody></table>";
            echo "<input type='hidden' name='section' value='" . htmlspecialchars($section) . "'>";
            echo "<input type='hidden' name='month' value='" . htmlspecialchars($month) . "'>";
            echo "<input type='hidden' name='subject_id' value='" . htmlspecialchars($subject_id) . "'>";
            echo "<input type='hidden' name='school_year' value='" . htmlspecialchars($school_year) . "'>";
            echo "</form>";
            echo "</div>";
            
            echo '
                <div class="floating-button-container">
                    <button type="submit" form="attendance-form" class="save-button">Save Attendance</button>
                </div>
            ';
        } else {
            echo "<p>No students found for the selected criteria.</p>";
        }
    } elseif ($form_submitted) {
        echo "<p>Please select all fields to load the attendance sheet.</p>";
    }
    ?>
</div>
</div>


<div id="pointSetterModal" class="side-modal">
    <div class="side-modal-content">
        <button class="close-btn" onclick="closeModal()">&times;</button>
        <h2>Set Points</h2>
        <label for="presentPoints">Points for Present:</label>
        <input type="number" id="presentPoints" value="<?php echo $points_present?>">
        <label for="absentPoints">Points for Absent:</label>
        <input type="number" id="absentPoints" value="<?php echo $points_absent?>">
        <label for="latePoints">Points for Late:</label>
        <input type="number" id="latePoints" value="<?php echo $points_late?>">
        <label for="excusedPoints">Points for Excused:</label>
        <input type="number" id="excusedPoints" value="<?php echo $points_excused?>">
        <select name="subject_id" id="modal_subject_id" required>
            <option value="">Select Subject</option>
            <?php foreach ($subject_names as $id => $name) : ?>
                <option value="<?php echo htmlspecialchars($id); ?>"
                        <?php echo ($id == $subject_id) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button onclick="setPoints()">Apply Points</button>
    </div>
</div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var mainSubjectSelect = document.getElementById('subject_id');
        var modalSubjectSelect = document.getElementById('modal_subject_id');
        var openModalBtn = document.getElementById('openModalBtn');
    
        if (mainSubjectSelect && modalSubjectSelect) {
            // Sync initial value
            modalSubjectSelect.value = mainSubjectSelect.value;
    
            // Update modal select when main select changes
            mainSubjectSelect.addEventListener('change', function() {
                modalSubjectSelect.value = this.value;
            });
    
            // Ensure modal select is updated when modal is opened
            if (openModalBtn) {
                openModalBtn.addEventListener('click', function() {
                    modalSubjectSelect.value = mainSubjectSelect.value;
                });
            }
        }
    });
    
    function openModal() {
        document.getElementById('pointSetterModal').classList.add('open');
    }
    
    function closeModal() {
        document.getElementById('pointSetterModal').classList.remove('open');
    }
    
    function syncSubjectSelection() {
        var mainSubjectSelect = document.getElementById('subject_id');
        var modalSubjectSelect = document.getElementById('modal_subject_id');
        
        if (mainSubjectSelect && modalSubjectSelect) {
            modalSubjectSelect.value = mainSubjectSelect.value;
        }
    }
    
    function updateAllTotals() {
        var selects = document.querySelectorAll('select[name^="attendance"]');
        selects.forEach(function(select) {
            updateTotals(select);
        });
    }
    
    function setPoints() {
        var presentPoints = parseInt(document.getElementById('presentPoints').value);
        var absentPoints = parseInt(document.getElementById('absentPoints').value);
        var latePoints = parseInt(document.getElementById('latePoints').value);
        var excusedPoints = parseInt(document.getElementById('excusedPoints').value);
        var subjectId = document.getElementById('modal_subject_id').value;
    
        // Use AJAX to send the data to the server
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "set_points.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (this.readyState === XMLHttpRequest.DONE) {
                if (this.status === 200) {
                    try {
                        var response = JSON.parse(this.responseText);
                        if (response.status === "success") {
                            console.log(response.message);
                            window.presentPoints = presentPoints;
                            window.absentPoints = absentPoints;
                            window.latePoints = latePoints;
                            window.excusedPoints = excusedPoints;
                            updateAllTotals();
                            closeModal();
                            alert("Points saved successfully!");
                        } else {
                            console.error(response.message);
                            alert("Error: " + response.message);
                        }
                    } catch (e) {
                        console.error("Error parsing JSON response:", e);
                        alert("An unexpected error occurred. Please try again.");
                    }
                } else {
                    console.error("HTTP error:", this.status, this.statusText);
                    alert("An error occurred while saving the points. Please try again.");
                }
            }
        }
        xhr.send("presentPoints=" + presentPoints + "&absentPoints=" + absentPoints + "&latePoints=" + latePoints + "&excusedPoints=" + excusedPoints + "&subjectId=" + subjectId);
    }
    
    // Update the updateTotals function to include excused totals
    function updateTotals(select) {
    var row = select.closest('tr');
    var presentCount = 0;
    var absentCount = 0;
    var lateCount = 0;
    var excusedCount = 0;
    var totalPoints = 0;

    row.querySelectorAll('select').forEach(function(s) {
        switch(s.value) {
            case 'P':
                presentCount++;
                totalPoints += parseInt(window.presentPoints || <?php echo $points_present; ?>);
                break;
            case 'A':
                absentCount++;
                totalPoints += parseInt(window.absentPoints || <?php echo $points_absent; ?>);
                break;
            case 'L':
                lateCount++;
                totalPoints += parseInt(window.latePoints || <?php echo $points_late; ?>);
                break;
            case 'E':
                excusedCount++;
                totalPoints += parseInt(window.excusedPoints || <?php echo $points_excused; ?>);
                break;
        }
    });

    row.querySelector('.total-present').textContent = presentCount;
    row.querySelector('.total-absent').textContent = absentCount;
    row.querySelector('.total-late').textContent = lateCount;
    row.querySelector('.total-excused').textContent = excusedCount;
    row.querySelector('.total-points').textContent = totalPoints;
}
    
    // Initialize point values from PHP
    window.presentPoints = <?php echo $points_present; ?>;
    window.absentPoints = <?php echo $points_absent; ?>;
    window.latePoints = <?php echo $points_late; ?>;
    window.excusedPoints = <?php echo $points_excused; ?>;
    
    function goBack() {
        window.location.href = 'Attendance.php';
    }
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('attendance-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Optional: Add validation here if needed
                return true; // Allow form submission
            });
        }
    });

    function fetchSections(schoolYear) {
        // Create AJAX request
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '../crud/fetch_sections.php?school_year=' + schoolYear, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                console.log(xhr.responseText)
                document.getElementById('section').innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }

    function fetchSubjects(gradeSection) {
        var subjectDropdown = document.getElementById('subject_id');
        subjectDropdown.innerHTML = '<option value="">Loading...</option>';

        if (gradeSection !== '') {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '../crud/fetch_subjects_attendance.php?grade_section=' + encodeURIComponent(gradeSection), true);
            
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    subjectDropdown.innerHTML = xhr.responseText;
                }
            };
            
            xhr.send();
        } else {
            subjectDropdown.innerHTML = '<option value="">All Subjects</option>';
        }
    }
    </script>

</body>
</html>

 <?php include("../crud/footer.php"); ?> 

