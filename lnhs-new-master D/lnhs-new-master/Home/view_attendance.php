<?php
session_start();
include("../LoginRegisterAuthentication/connection.php");
include("../crud/header.php");

// include("LoginRegisterAuthentication/connection.php");
// include("crud/header.php");

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

// Get selected values (if any)
$school_year = $_POST['school_year'] ?? '';
$section = $_POST['section'] ?? '';
$subject_id = $_POST['subject_id'] ?? '';
$subject_id_other = 0;
$month = $_POST['month'] ?? date('Y-m');

$form_submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');

// Only fetch attendance data if the form is submitted
if ($form_submitted && $school_year && $section && $subject_id && $month) {
    // Fetch subject name
    $subject_query = "SELECT name FROM subjects WHERE id = ?";
    $subject_stmt = $connection->prepare($subject_query);
    $subject_stmt->bind_param("i", $subject_id);
    $subject_stmt->execute();
    $subject_result = $subject_stmt->get_result();
    $subject_name = $subject_result->fetch_assoc()['name'];

    // Fetch students and their attendance
    $query = "SELECT s.id, s.learners_name, a.* 
              FROM students s
              LEFT JOIN attendance a ON s.id = a.student_id AND a.month = ? AND a.subject_id = ?
              WHERE s.`grade & section` = ? AND s.school_year = ?
              ORDER BY s.learners_name";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("siss", $month, $subject_id_other, $section, $school_year);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch point values
    $point_query = "SELECT points_present, points_absent, points_late, points_excused 
                    FROM point_setter 
                    WHERE subject_id = ? 
                    AND userid = ?
                    LIMIT 1";
    $point_stmt = $connection->prepare($point_query);
    $point_stmt->bind_param("ii", $userid,$subject_id);
    $point_stmt->execute();
    $point_result = $point_stmt->get_result();
    $point_row = $point_result->fetch_assoc();

    $points_present = $point_row['points_present'] ?? 10;
    $points_absent = $point_row['points_absent'] ?? 0;
    $points_late = $point_row['points_late'] ?? 5;
    $points_excused = $point_row['points_excused'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance</title>
    <style>
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
        .filters-container {
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .inline-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        .form-group select, .form-group input[type="month"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .load-button {
            width: 100%;
            padding: 10px 15px;
            background-color: darkblue;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .load-button:hover {
            background-color: #2980b9;
        }
        .summary {
            background-color: #ecf0f1;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .summary h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .summary p {
            margin: 5px 0;
        }
        .legend-container {
            background-color: #fff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .legend-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
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
        .table-container {
            overflow-x: auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }
        th {
            background-color: #1a237e;
            color: #fff;
            font-weight: bold;
            padding: 8px 4px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .student-name {
            text-align: left;
            font-weight: bold;
        }
        .date-column .day-name {
            display: block;
            font-size: 0.8em;
        }
        .total-column {
            background-color: darkblue;
            color: #fff;
            font-weight: bold;
        }
        .status-P { color: #27ae60; }
        .status-A { color: #e74c3c; }
        .status-L { color: #f39c12; }
        .status-E { color: darkblue; }
        .present { background-color: #27ae60; color: #000; }
        .absent { background-color: #e74c3c; color: #000; }
        .late { background-color: #f39c12; color: #000; }
        .excused { background-color: darkblue; color: #000; }
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: darkblue;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
            margin-top: 20px;
        }
        .back-button:hover {
            background-color: #2980b9;
        }
        .overlay {
            position: fixed;
            top: 0;
            right: -200px; /* Reduced width */
            width: 200px; /* Smaller width */
            height: 100%;
            background-color: #f0f0f0;
            z-index: 1000;
            transition: right 0.3s ease-in-out;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
        }

        .overlay-content {
            padding: 10px;
        }

        .overlay-header {
            background-color: darkblue;
            color: white;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 16px;
        }

        .overlay-button {
            display: block;
            width: 100%;
            padding: 8px;
            margin-bottom: 5px;
            background-color: #e0e0e0;
            border: none;
            text-align: left;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 14px;
        }

        .overlay-button:hover {
            background-color: #d0d0d0;
        }

        .reports-button {
            position: fixed;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            background-color: darkblue;
            color: white;
            padding: 10px;
            cursor: pointer;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            border: none;
            border-top-left-radius: 5px;
            border-bottom-left-radius: 5px;
            font-size: 14px;
        }

        .close-button {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
        }

        .action-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .print-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2ecc71;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
            cursor: pointer;
        }

        .print-button:hover {
            background-color: #27ae60;
        }

        .totals-table {
            display: none;
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .totals-table th, .totals-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        .totals-table th {
            background-color: #1a237e;
            color: #fff;
        }

        @media print {
            /* ... (Keep existing print styles) ... */

            .main-table .total-column {
                display: none;
            }

            .totals-table {
                display: table;
                page-break-inside: avoid;
            }

            .totals-table th {
                background-color: #1a237e !important;
                color: #fff !important;
            }
            .filters-container {
                display: block !important;
                margin-bottom: 20px;
            }
            .reports-button,
            .overlay,
            .action-buttons,
            .sidebar,
            .back-button,
            .print-button,
            .filters-container select,
            .filters-container input[type="month"],
            .filters-container button {
                display: none !important;
            }

            /* Ensure the logo is visible in print */
            .logo {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
            }

            /* Additional print-specific styles */
            body {
                padding: 0;
                margin: 0;
            }

            .container {
                width: 100%;
                max-width: none;
                padding: 0;
                margin: 0;
            }

            /* Ensure table fits on the page */
            .table-container {
                overflow: visible;
                page-break-inside: auto;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }
            .table-container table {
                width: 100%;
                border-collapse: collapse;
            }
            .table-container th,
            .table-container td {
                border: 1px solid #e0e0e0;
                padding: 12px;
                text-align: center;
            }
            .table-container th {
                background-color: #1a237e !important;
                color: #fff !important;
                font-weight: bold;
            }
            .table-container .student-name {
                text-align: left;
            }
            .table-container .total-column {
                background-color: #1a237e !important;
                color: #fff !important;
            }
            .table-container .status-P { color: #4CAF50 !important; }
            .table-container .status-A { color: #F44336 !important; }
            .table-container .status-L { color: #FF9800 !important; }
            .table-container .status-E { color: #2196F3 !important; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="report-title">School Form 2 (SF2) Daily Attendance Report of Learners</h2>
        <button id="reportsButton" class="reports-button" onclick="toggleOverlay()">Reports</button>

        <!-- Updated compact sidebar report menu -->
        <div id="reportsOverlay" class="overlay">
            <div class="overlay-header">
                <span>Reports</span>
                <button class="close-button" onclick="toggleOverlay()">&times;</button>
            </div>
            <div class="overlay-content">
                <a href="form14_template.php" class="overlay-button">FORM 14</a>
                <!-- <button class="overlay-button">FORM 2</button> -->
                <a href="form137.php" class="overlay-button">FORM 137</a>
            </div>
        </div>
        <div class="filters-container">
            <form method="post" action="" class="inline-form">
                <div class="form-group">
                    <select name="school_year" id="school_year" onchange="fetchSections(this.value)" required>
                        <option value="">SCHOOL YEAR</option>
                        <?php while ($year = mysqli_fetch_assoc($school_years_result)) : ?>
                            <option value="<?php echo htmlspecialchars($year['school_year']); ?>"
                                <?php echo ($year['school_year'] == $school_year) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($year['school_year']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <select name="section" id="section" onchange="fetchSubjects(this.value)" required>
                        <option value="">GRADE & SECTION</option>
                        <!-- <?php while ($section_row = mysqli_fetch_assoc($sections_result)) : ?>
                            <option value="<?php echo htmlspecialchars($section_row['grade & section']); ?>"
                                <?php echo ($section_row['grade & section'] == $section) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($section_row['grade & section']); ?>
                            </option>
                        <?php endwhile; ?> -->
                    </select>
                </div>
                <div class="form-group">
                    <select name="subject_id" id="subject_id">
                        <option value="">SUBJECT</option>
                        <!-- <?php while ($subject = mysqli_fetch_assoc($subjects_result)) : ?>
                            <option value="<?php echo htmlspecialchars($subject['id']); ?>"
                                <?php echo ($subject['id'] == $subject_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($subject['name']); ?>
                            </option>
                        <?php endwhile; ?> -->
                    </select>
                </div>
                <div class="form-group">
                    <input type="month" name="month" value="<?php echo htmlspecialchars($month); ?>" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="load-button">LOAD ATTENDANCE</button>
                </div>
            </form>
        </div>

        <?php if ($form_submitted && $school_year && $section && $subject_id && $month): ?>
            <div class="legend-container">
                <h4>LEGEND:</h4>
                <ul class="legend-list">
                    <li><span class="legend-item present">P</span> PRESENT</li>
                    <li><span class="legend-item absent">A</span> ABSENT</li>
                    <li><span class="legend-item late">L</span> LATE</li>
                    <li><span class="legend-item excused">E</span> EXCUSED</li>
                </ul>
            </div>

            <div class="table-container">
                <table class="main-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Student Name</th>
                            <?php
                            $numDays = date('t', strtotime($month));
                            for ($i = 1; $i <= $numDays; $i++) {
                                $dayOfWeek = date('N', strtotime("$month-$i"));
                                if ($dayOfWeek < 7) { // Include Monday (1) to Saturday (6)
                                    $dayName = date('D', strtotime("$month-$i"));
                                    echo "<th class='date-column'>";
                                    echo "<span class='day-name'>" . $dayName . "</span>";
                                    echo str_pad($i, 2, '0', STR_PAD_LEFT);
                                    echo "</th>";
                                }
                            }
                            ?>
                            <th class="total-column">Present</th>
                            <th class="total-column">Absent</th>
                            <th class="total-column">Late</th>
                            <th class="total-column">Excused</th>
                            <th class="total-column">Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $count = 1;
                        $allTotals = array();
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $count . "</td>";
                            echo "<td class='student-name'>" . htmlspecialchars($row['learners_name']) . "</td>";
                            
                            $totalPresent = $totalAbsent = $totalLate = $totalExcused = $totalPoints = 0;
                            
                            for ($i = 1; $i <= $numDays; $i++) {
                                $dayOfWeek = date('N', strtotime("$month-$i"));
                                if ($dayOfWeek < 7) {
                                    $day = "day_" . str_pad($i, 2, '0', STR_PAD_LEFT);
                                    $status = $row[$day] ?? '-';
                                    echo "<td class='status-{$status}'>" . $status . "</td>";
                                    
                                    switch ($status) {
                                        case 'P': $totalPresent++; $totalPoints += $points_present; break;
                                        case 'A': $totalAbsent++; $totalPoints += $points_absent; break;
                                        case 'L': $totalLate++; $totalPoints += $points_late; break;
                                        case 'E': $totalExcused++; $totalPoints += $points_excused; break;
                                    }
                                }
                            }
                            
                            echo "<td class='total-column'>" . $totalPresent . "</td>";
                            echo "<td class='total-column'>" . $totalAbsent . "</td>";
                            echo "<td class='total-column'>" . $totalLate . "</td>";
                            echo "<td class='total-column'>" . $totalExcused . "</td>";
                            echo "<td class='total-column'>" . $totalPoints . "</td>";
                            echo "</tr>";

                            $allTotals[] = array(
                                'name' => $row['learners_name'],
                                'present' => $totalPresent,
                                'absent' => $totalAbsent,
                                'late' => $totalLate,
                                'excused' => $totalExcused,
                                'points' => $totalPoints
                            );

                            $count++;
                        }
                        ?>
                    </tbody>
                </table>

                <!-- Separate table for totals (visible only in print view) -->
                <table class="totals-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Student Name</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Late</th>
                            <th>Excused</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($allTotals as $index => $total) {
                            echo "<tr>";
                            echo "<td>" . ($index + 1) . "</td>";
                            echo "<td>" . htmlspecialchars($total['name']) . "</td>";
                            echo "<td>" . $total['present'] . "</td>";
                            echo "<td>" . $total['absent'] . "</td>";
                            echo "<td>" . $total['late'] . "</td>";
                            echo "<td>" . $total['excused'] . "</td>";
                            echo "<td>" . $total['points'] . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($form_submitted): ?>
            <p>Please select all fields to load the attendance sheet.</p>
        <?php endif; ?>


        <a href="Attendance.php" class="back-button">Back to Attendance</a>
        <button onclick="printAttendance()" class="print-button">Print Attendance</button>
    </div>
    <script>

        function toggleOverlay() {
            var overlay = document.getElementById('reportsOverlay');
            var button = document.getElementById('reportsButton');
            if (overlay.style.right === '0px') {
                overlay.style.right = '-200px'; // Updated to match new width
                button.textContent = 'Reports';
            } else {
                overlay.style.right = '0px';
                button.textContent = 'Close';
            }
        }
        function printAttendance() {
            var teacherName = "Anna"; // You might want to make this dynamic based on the logged-in teacher
            var schoolYear = "<?php echo addslashes($school_year); ?>";
            var gradeSection = "<?php echo addslashes($section); ?>";
            var subject = "<?php echo addslashes($subject_name); ?>";
            var month = "<?php echo date('F Y', strtotime($month)); ?>";
            var printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write('<html><head><title>Print Attendance</title>');
        
            // Add styles for table alignment
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-family: Arial, sans-serif; }');
            printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 5px; }');
            printWindow.document.write('th, td { border: 1px solid black; padding: 5px; }');
            printWindow.document.write('th { background-color: #f2f2f2; text-align: center; }');
            printWindow.document.write('td { text-align: center; }');
            printWindow.document.write('td.name-column { text-align: left; }');
            printWindow.document.write('.additional-details { display: flex; flex-wrap: wrap; gap: 400px; margin-top: 20px; margin-bottom: 20px; }');
            printWindow.document.write('.additional-details p { margin: 0; }');
            printWindow.document.write('.additional-details span { font-weight: bold; color: #1a237e; }');
            printWindow.document.write('.additional-details2 { display: flex; flex-wrap: wrap; gap: 400px; margin-top: 20px; margin-bottom: 20px; }');
            printWindow.document.write('.additional-details2 p { margin: 0; }');
            printWindow.document.write('.additional-details2 span { font-weight: bold; color: #1a237e; }');
            printWindow.document.write('.filters-container { margin-bottom: 20px; }');
            printWindow.document.write('.filters-container select, .filters-container input[type="month"], .filters-container button { display: none; }');
            printWindow.document.write('</style>');
        
            printWindow.document.write('</head><body>');
            // Add a container for logos and header
            printWindow.document.write('<div style="width: 100%; height: 150px; position: relative; margin-bottom: 20px;">');
            // Add left logo (DepEd logo) larger and beside the Region label
            printWindow.document.write('<div style="position: absolute; left: 10px; top: 10px; display: flex; align-items: center;">');
            printWindow.document.write('<img src="kagawaran-removebg-preview.png" alt="DepEd Logo" style="height: 100px; margin-right: 30px;">');
            printWindow.document.write('<div style="font-size: 16px;">');
            printWindow.document.write('Region: <span style="border: 1px solid black; padding: 5px; display: inline-block;">VII</span>');
            printWindow.document.write('&nbsp;&nbsp;');
            printWindow.document.write('Division: <span style="border: 1px solid black; padding: 5px; display: inline-block;">Cebu Province</span>');
            printWindow.document.write('</div>');
            printWindow.document.write('</div>');
            // School Name label with boxed Lanao National High School
            printWindow.document.write('<div style="position: absolute; left: 135px; top: 80px; font-size: 16px;">');
            printWindow.document.write('School Name: <span style="border: 1px solid black; padding: 5px; display: inline-block;">Lanao National High School</span>');
            printWindow.document.write('</div>');
            // Add right logo (School logo) larger and beside the School ID label
            printWindow.document.write('<div style="position: absolute; right: 10px; top: 10px; display: flex; align-items: center;">');
            printWindow.document.write('<div style="font-size: 16px; margin-right: 20px;">School ID: <span style="border: 1px solid black; padding: 5px; display: inline-block;">303031</span></div>');
            printWindow.document.write('<img src="depedlogobgwhite.png" alt="School Logo" style="height: 100px; margin-left: 10px;">');
            printWindow.document.write('</div>');
            printWindow.document.write('</div>');
            // Add filters container
            printWindow.document.write(document.querySelector('.filters-container').outerHTML);

            // Add additional details
        
            printWindow.document.write('<div class="additional-details">');
            printWindow.document.write('<p>SCHOOL YEAR: <span>' + schoolYear + '</span></p>');
            printWindow.document.write('<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;GRADE & SECTION: <span>' + gradeSection + '</span></p>');
            printWindow.document.write('</div>');
            printWindow.document.write('<div class="additional-details2">');
            printWindow.document.write('<p>SUBJECT: <span>' + subject + '</span></p>');
            printWindow.document.write('<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MONTH: <span>' + month + '</span></p>');
            printWindow.document.write('</div>');

            

            // Add the main content
            printWindow.document.write(document.querySelector('.table-container').outerHTML);

            // Add teacher's name at the bottom
            printWindow.document.write('<div style="margin-top: 20px; text-align: right;">');
            printWindow.document.write('<p>Teacher: ' + teacherName + '</p>');
            printWindow.document.write('</div>');

            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            
            // Wait for images to load before printing
            printWindow.onload = function() {
                printWindow.print();
                printWindow.close();
            };
        }
        function autoLoadAttendance() {
            const urlParams = new URLSearchParams(window.location.search);
            
            // Check if we should auto-load
            if (urlParams.get('load') === '1') {
                // Set form values
                const schoolYear = urlParams.get('school_year');
                const section = urlParams.get('section');
                const subjectId = urlParams.get('subject_id');
                const month = urlParams.get('month');

                // Find the form elements
                const form = document.querySelector('form');
                if (form) {
                    // Set values
                    if (form.querySelector('select[name="school_year"]')) {
                        form.querySelector('select[name="school_year"]').value = schoolYear;
                    }
                    if (form.querySelector('select[name="section"]')) {
                        form.querySelector('select[name="section"]').value = section;
                    }
                    if (form.querySelector('select[name="subject_id"]')) {
                        form.querySelector('select[name="subject_id"]').value = subjectId;
                    }
                    if (form.querySelector('input[name="month"]')) {
                        form.querySelector('input[name="month"]').value = month;
                    }
                }
            }
        }

        // Call the function when document is loaded
        document.addEventListener('DOMContentLoaded', function() {
            autoLoadAttendance();
        });

    </script>
    <script>
        function fetchSections(schoolYear) {
            // Create AJAX request
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'crud/fetch_sections.php?school_year=' + schoolYear, true);
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
                xhr.open('GET', 'crud/fetch_subjects_new.php?grade_section=' + encodeURIComponent(gradeSection), true);
                
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

<!-- <?php include("../crud/footer.php"); ?> -->

<?php include("crud/footer.php"); ?>