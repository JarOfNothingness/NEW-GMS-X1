<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('../crud/header.php');
include("../LoginRegisterAuthentication/connection.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = "SELECT s.learners_name, s.`grade & section` as grade_section,
                 a.subject_id, a.month,
                 a.day_01, a.day_02, a.day_03, a.day_04, a.day_05,
                 a.day_06, a.day_07, a.day_08, a.day_09, a.day_10,
                 a.day_11, a.day_12, a.day_13, a.day_14, a.day_15,
                 a.day_16, a.day_17, a.day_18, a.day_19, a.day_20,
                 a.day_21, a.day_22, a.day_23, a.day_24, a.day_25,
                 a.day_26, a.day_27, a.day_28, a.day_29, a.day_30,
                 a.day_31, a.total_present, a.total_absent,
                 a.total_late, a.total_excused,
                 sq.raw_score, sq.max_score, sq.quarter
          FROM students s
          JOIN attendance a ON s.id = a.student_id
          LEFT JOIN student_quiz sq ON s.id = sq.student_id
          WHERE s.id = $id";

$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}

$row = mysqli_fetch_assoc($result);

if (!$row) {
    die("No data found for the selected student.");
}

$learners_name = htmlspecialchars($row['learners_name']);
$grade_section = htmlspecialchars($row['grade_section']);

// Rest of your HTML and PHP code remains the same
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Form 2 (SF2) Daily Attendance Report of Learners</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
        }
        .section-header {
            background-color: #f9f9f9;
            font-weight: bold;
            text-align: left;
            padding: 8px;
        }
        .remarks-column {
            text-align: left;
            padding-left: 10px;
        }
        .empty-cell {
            background-color: #f9f9f9;
            border: none;
        }
        .back-button {
            display: block;
            margin: 20px auto;
            text-align: center;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>School Form 2 (SF2) Daily Attendance Report of Learners</h1>
    <p>(This replaces Form1, Form 2 & STS Form 4 - Absents and Dropout Profile)</p>
</div>

<table>
    <thead>
        <tr>
            <td colspan="45" class="section-header">
                SCHOOL ID: [Insert School ID] | SCHOOL YEAR: [Insert School Year] | Report for the Month of: <?php echo htmlspecialchars($row['month']); ?> | Learner Attendance Conversion Tool: [Insert Tool]
            </td>
        </tr>
        <tr>
            <td colspan="15">Name of School: LANAO NATIONAL HIGHSCHOOL</td>
            <td colspan="15" class="section-header">Grade Level & Section: <?php echo $grade_section; ?></td>
            <td colspan="15" class="section-header">Subject: [Insert Subject]</td>
        </tr>
        <tr>
            <th rowspan="2">No.</th>
            <th rowspan="2">Name</th>
            <th colspan="30">1st row for data</th>
            <th colspan="4">Total for the Month</th>
            <th colspan="9" class="remarks-column">REMARKS (IF NLPA, state reason; if TRANSFERRED IN/OUT, write the name of school.)</th>
        </tr>
        <tr>
            <th colspan="5"></th>
            <th colspan="5"></th>
            <th colspan="5"></th>
            <th colspan="2"></th>
            <th colspan="5"></th>
            <th colspan="2"></th>
            <th colspan="2"></th>
            <th colspan="4"></th>
            <th colspan="2">Absent</th>
            <th colspan="2">Present</th>
            <th colspan="9"></th>
        </tr>
        <tr>
            <th></th>
            <th></th>
            <th>M</th>
            <th>T</th>
            <th>W</th>
            <th>TH</th>
            <th>F</th>
            <th>S</th>
            <th>M</th>
            <th>T</th>
            <th>W</th>
            <th>TH</th>
            <th>F</th>
            <th>S</th>
            <th>M</th>
            <th>T</th>
            <th>W</th>
            <th>TH</th>
            <th>F</th>
            <th>S</th>
            <th>M</th>
            <th>T</th>
            <th>W</th>
            <th>TH</th>
            <th>F</th>
            <th>S</th>
            <th>M</th>
            <th>T</th>
            <th>W</th>
            <th>TH</th>
            <th>F</th>
            <th>S</th>
            <th colspan="13"></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td><?php echo $learners_name; ?></td>
            <td><?php echo htmlspecialchars($row['day_01']); ?></td>
            <td><?php echo htmlspecialchars($row['day_02']); ?></td>
            <td><?php echo htmlspecialchars($row['day_03']); ?></td>
            <td><?php echo htmlspecialchars($row['day_04']); ?></td>
            <td><?php echo htmlspecialchars($row['day_05']); ?></td>
            <td><?php echo htmlspecialchars($row['day_06']); ?></td>
            <td><?php echo htmlspecialchars($row['day_07']); ?></td>
            <td><?php echo htmlspecialchars($row['day_08']); ?></td>
            <td><?php echo htmlspecialchars($row['day_09']); ?></td>
            <td><?php echo htmlspecialchars($row['day_10']); ?></td>
            <td><?php echo htmlspecialchars($row['day_11']); ?></td>
            <td><?php echo htmlspecialchars($row['day_12']); ?></td>
            <td><?php echo htmlspecialchars($row['day_13']); ?></td>
            <td><?php echo htmlspecialchars($row['day_14']); ?></td>
            <td><?php echo htmlspecialchars($row['day_15']); ?></td>
            <td><?php echo htmlspecialchars($row['day_16']); ?></td>
            <td><?php echo htmlspecialchars($row['day_17']); ?></td>
            <td><?php echo htmlspecialchars($row['day_18']); ?></td>
            <td><?php echo htmlspecialchars($row['day_19']); ?></td>
            <td><?php echo htmlspecialchars($row['day_20']); ?></td>
            <td><?php echo htmlspecialchars($row['day_21']); ?></td>
            <td><?php echo htmlspecialchars($row['day_22']); ?></td>
            <td><?php echo htmlspecialchars($row['day_23']); ?></td>
            <td><?php echo htmlspecialchars($row['day_24']); ?></td>
            <td><?php echo htmlspecialchars($row['day_25']); ?></td>
            <td><?php echo htmlspecialchars($row['day_26']); ?></td>
            <td><?php echo htmlspecialchars($row['day_27']); ?></td>
            <td><?php echo htmlspecialchars($row['day_28']); ?></td>
            <td><?php echo htmlspecialchars($row['day_29']); ?></td>
            <td><?php echo htmlspecialchars($row['day_30']); ?></td>
            <td><?php echo htmlspecialchars($row['day_31']); ?></td>
            <td><?php echo htmlspecialchars($row['raw_score']); ?></td>
            <td><?php echo htmlspecialchars($row['max_score']); ?></td>
            <td><?php echo htmlspecialchars($row['quarter']); ?></td>
            <td><?php echo htmlspecialchars($row['total_absent']); ?></td>
            <td><?php echo htmlspecialchars($row['total_present']); ?></td>
            <td><?php echo htmlspecialchars($row['total_late']); ?></td>
            <td><?php echo htmlspecialchars($row['total_excused']); ?></td>
            <td><?php echo htmlspecialchars($row['remarks']); ?></td>
        </tr>
    </tbody>
</table>

<a href="r.php" class="back-button">Back</a>

</body>
</html>
