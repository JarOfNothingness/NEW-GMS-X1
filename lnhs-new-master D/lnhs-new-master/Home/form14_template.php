<?php
session_start();
include("../LoginRegisterAuthentication/connection.php");
include("../crud/header.php");

$userid = $_SESSION['userid'];

// Fetch school years
$school_years_query = "SELECT DISTINCT school_year FROM students WHERE user_id = ? ORDER BY school_year DESC";
$stmt = $connection->prepare($school_years_query);
$stmt->bind_param("i", $userid);
$stmt->execute();
$school_years_result = $stmt->get_result();

// Fetch sections
$sections_query = "SELECT DISTINCT `grade & section` FROM students WHERE user_id = ?";
$stmt = $connection->prepare($sections_query);
$stmt->bind_param("i", $userid);
$stmt->execute();
$sections_result = $stmt->get_result();

// Fetch subjects
$subjects_query = "SELECT id, name FROM subjects";
$subjects_result = mysqli_query($connection, $subjects_query);

// Get selected values
$school_year = $_GET['school_year'] ?? '';
$section = $_GET['section'] ?? '';
$subject_id = $_GET['subject_id'] ?? '';

// Initialize arrays for statistics
$male_stats = ['total' => 0, 'passing' => 0];
$female_stats = ['total' => 0, 'passing' => 0];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form 14 - Student Record</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f0f2f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Header Styles */
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }

        .school-name {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0 0 8px 0;
        }

        .school-address {
            font-size: 16px;
            color: #4a5568;
            margin: 0 0 4px 0;
        }

        .school-year {
            font-size: 16px;
            color: #4a5568;
            margin: 4px 0;
        }

        .form-title {
            font-size: 24px;
            font-weight: 600;
            color: #2d3748;
            margin: 20px 0;
            text-align: center;
        }

        /* Class Info Bar */
        .class-info {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            padding: 15px;
            background-color: #f8fafc;
            border-radius: 8px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-label {
            font-weight: 500;
            color: #4a5568;
        }

        .info-value {
            font-weight: 600;
            color: #2d3748;
        }

        /* Icon styles */
        .fas {
            color: #fff;
        }

        .school-name {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 8px 0;
        }

        .filters-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .load-button {
            background-color: #1a237e;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .load-button:hover {
            background-color: #0d1457;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #e0e0e0;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #1a237e;
            color: white;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .student-name {
            text-align: left;
        }

        .passed {
            color: #2e7d32;
        }

        .failed {
            color: #c62828;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
        }

        .action-button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .print-button {
            background-color: #41a4ed;
            color: white;
        }

        .back-button {
            background-color: #616161;
            color: white;
        }

        @media print {
            /* Hide sidebar */
            .sidebar {
                display: none !important;
            }
            
            /* Adjust main content to take full width */
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 0 !important;
            }

            /* Other print-specific styles */
            body {
                background-color: white;
                padding: 0;
            }

            .container {
                max-width: none;
                margin: 0;
                padding: 20px;
                box-shadow: none;
            }

            /* Hide other non-printable elements */
            .filters-container,
            .action-buttons,
            .reports-button,
            .overlay,
            .back-button,
            .print-button {
                display: none !important;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="school-name">LANAO NATIONAL HIGH SCHOOL</h1>
            <p>Lanao, Pilar, Cebu</p>
            <p>School Year: <?php echo htmlspecialchars($school_year ?: ' '); ?></p>
            <h2>Form 14 - Student Record</h2>
        </div>
        <div class="class-info">
            <div class="info-item">
                <i class="fas fa-users"></i>
                <span class="info-label">Grade & Section:</span>
                <span class="info-value"><?php echo htmlspecialchars($section ?: ' '); ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-book"></i>
                <span class="info-label">Subject:</span>
                <span class="info-value">
                    <?php
                    if ($subject_id) {
                        $subject_query = "SELECT name FROM subjects WHERE id = ?";
                        $stmt = $connection->prepare($subject_query);
                        $stmt->bind_param("i", $subject_id);
                        $stmt->execute();
                        $subject_result = $stmt->get_result();
                        $subject_name = $subject_result->fetch_assoc()['name'];
                        echo htmlspecialchars($subject_name);
                    } else {
                        echo ' ';
                    }
                    ?>
                </span>
            </div>
        </div>

        <form method="GET" action="" class="filters-container">
            <div class="filter-group">
                <select name="school_year" onchange="fetchSections(this.value)" required>
                    <option value="">Select School Year</option>
                    <?php while ($year = mysqli_fetch_assoc($school_years_result)) : ?>
                        <option value="<?php echo htmlspecialchars($year['school_year']); ?>"
                                <?php echo ($year['school_year'] == $school_year) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($year['school_year']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="filter-group">
                <select name="section" id="section" onchange="fetchSubjects(this.value)" required>
                    <option value="">Select Grade & Section</option>
                    <!-- <?php while ($section_row = mysqli_fetch_assoc($sections_result)) : ?>
                        <option value="<?php echo htmlspecialchars($section_row['grade & section']); ?>"
                                <?php echo ($section_row['grade & section'] == $section) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($section_row['grade & section']); ?>
                        </option>
                    <?php endwhile; ?> -->
                </select>
            </div>

            <div class="filter-group">
                <select name="subject_id" id="subject_id" required>
                    <option value="">Select Subject</option>
                    <!-- <?php while ($subject = mysqli_fetch_assoc($subjects_result)) : ?>
                        <option value="<?php echo htmlspecialchars($subject['id']); ?>"
                                <?php echo ($subject['id'] == $subject_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subject['name']); ?>
                        </option>
                    <?php endwhile; ?> -->
                </select>
            </div>

            <button type="submit" class="load-button">Load Records</button>
        </form>

        <?php if ($school_year && $section && $subject_id): ?>
            <?php
            // Fetch grades data
            $query = "SELECT s.id, s.learners_name, s.gender,
                        MAX(CASE WHEN asm.quarter = '1st' THEN asm.quarterly_grade END) as quarter_1,
                        MAX(CASE WHEN asm.quarter = '2nd' THEN asm.quarterly_grade END) as quarter_2,
                        MAX(CASE WHEN asm.quarter = '3rd' THEN asm.quarterly_grade END) as quarter_3,
                        MAX(CASE WHEN asm.quarter = '4th' THEN asm.quarterly_grade END) as quarter_4
                     FROM students s
                     LEFT JOIN assessment_summary asm ON s.id = asm.student_id AND asm.subject_id = ?
                     WHERE s.`grade & section` = ? 
                     AND s.school_year = ? 
                     AND s.user_id = ?
                     GROUP BY s.id, s.learners_name, s.gender
                     ORDER BY s.learners_name";

            $stmt = $connection->prepare($query);
            $stmt->bind_param("issi", $subject_id, $section, $school_year, $userid);
            $stmt->execute();
            $result = $stmt->get_result();
            ?>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>First Quarter</th>
                        <th>Second Quarter</th>
                        <th>Third Quarter</th>
                        <th>Fourth Quarter</th>
                        <th>Final Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $count = 1;
                    while ($row = $result->fetch_assoc()):
                        // Calculate final grade
                        $grades = array_filter(
                            [$row['quarter_1'], $row['quarter_2'], $row['quarter_3'], $row['quarter_4']],
                            'is_numeric'
                        );
                        
                        $final_grade = !empty($grades) ? round(array_sum($grades) / count($grades), 2) : null;
                        $remarks = $final_grade >= 75 ? 'PASSED' : 'FAILED';
                        
                        // Update statistics
                        if ($row['gender'] === 'Male') {
                            $male_stats['total']++;
                            if ($final_grade >= 75) $male_stats['passing']++;
                        } else {
                            $female_stats['total']++;
                            if ($final_grade >= 75) $female_stats['passing']++;
                        }
                    ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td class="student-name"><?php echo htmlspecialchars($row['learners_name']); ?></td>
                            <td><?php echo $row['quarter_1'] ?? 'N/A'; ?></td>
                            <td><?php echo $row['quarter_2'] ?? 'N/A'; ?></td>
                            <td><?php echo $row['quarter_3'] ?? 'N/A'; ?></td>
                            <td><?php echo $row['quarter_4'] ?? 'N/A'; ?></td>
                            <td><?php echo $final_grade ?? 'N/A'; ?></td>
                            <td class="<?php echo strtolower($remarks); ?>">
                                <?php echo $remarks; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="action-buttons">
                <button onclick="window.print()" class="action-button print-button">
                    <i class="fas fa-print"></i> Print
                </button>
                <a href="index.php" class="action-button back-button">Send to Gmail</a>
                <a href="view_attendance.php" class="action-button back-button">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add any necessary JavaScript here
        document.addEventListener('DOMContentLoaded', function() {
            // Add any initialization code here
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
            xhr.open('GET', '../crud/fetch_subjects_new.php?grade_section=' + encodeURIComponent(gradeSection), true);
            
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