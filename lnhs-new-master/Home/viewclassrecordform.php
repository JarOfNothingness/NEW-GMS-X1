<?php
session_start(); // Start the session at the very top

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('../crud/header.php');
include("../LoginRegisterAuthentication/connection.php");

// Define the ID and query to fetch data
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query = "SELECT * FROM class_record WHERE id = $id"; // Adjust the table and query as needed
$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}

$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Record</title>
    <style>
        /* General styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        .form137-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .form137-container .left img, .form137-container .right img {
            height: 100px;
        }

        .center h2 {
            margin: 0;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
        }

        thead {
            background-color: #3498db;
            color: #fff;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #2980b9;
            color: #fff;
        }

        .school-info td {
            background-color: #2ecc71;
            color: white;
            font-weight: bold;
            text-align: left;
        }

        .section-header {
            background-color: #e74c3c;
            color: #fff;
        }

        .Container-info {
            background-color: #f39c12;
            font-weight: bold;
            text-align: center;
            color: white;
        }

        /* Styling the link button */
        a {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }

        a:hover {
            background-color: #2980b9;
        }

        /* Row colors for Male and Female */
        .Male-row {
            background-color: #d4edda; /* Light green */
        }

        .Female-row {
            background-color: #f8d7da; /* Light red */
        }
    </style>
</head>
<body>

<div class="form137-container">
    <div class="left">
        <img src="./images/Logo.png" alt="Logo">
    </div>
    <div class="center">
        <h2>CLASS RECORD</h2>
        <h5>(Formerly known as)</h5>
    </div>
    <div class="right">
        <img src="./images/depedlogo.png" alt="DepEd Logo">
    </div>
</div>

<table>
    <thead>
        <tr class="school-info">
            <td colspan="15">REGION: <?php echo htmlspecialchars($data['region']); ?></td>
            <td colspan="10">DIVISION: <?php echo htmlspecialchars($data['division']); ?></td>
            <td colspan="15">SCHOOL NAME: <?php echo htmlspecialchars($data['school_name']); ?></td>
            <td colspan="8">SCHOOL ID: <?php echo htmlspecialchars($data['school_id']); ?></td>
            <td colspan="6">SCHOOL YEAR: <?php echo htmlspecialchars($data['school_year']); ?></td>
        </tr>
        <tr class="school-info">
            <td colspan="3">FIRST QUARTER</td>
            <td colspan="17">GRADE & SECTION: <?php echo htmlspecialchars($data['grade_section']); ?></td>
            <td colspan="18" class="section-header">TEACHER: <?php echo htmlspecialchars($data['teacher']); ?></td>
            <td colspan="19" class="section-header">SUBJECT: <?php echo htmlspecialchars($data['subject']); ?></td>
        </tr>
        <tr class="Container-info">
            <td colspan="10">LEARNER'S NAME</td>
            <td colspan="13">WRITTEN WORKS</td>
            <td colspan="13">PERFORMANCE TASKS</td>
            <td colspan="5">QUARTERLY ASSESSMENT</td>
            <td colspan="5">INITIAL GRADE</td>
            <td colspan="5">QUARTERLY GRADE</td>
        </tr>
        <tr>
            <td colspan="10"></td>
            <?php for ($i = 1; $i <= 10; $i++): ?>
                <td colspan="1"><?php echo $i; ?></td>
            <?php endfor; ?>
            <td>TOTAL</td>
            <td>PS</td>
            <td>WS</td>
            <?php for ($i = 1; $i <= 10; $i++): ?>
                <td colspan="1"><?php echo $i; ?></td>
            <?php endfor; ?>
            <td>TOTAL</td>
            <td>PS</td>
            <td>WS</td>
            <td>1</td>
            <td>PS</td>
            <td>WS</td>
        </tr>
        <tr>
            <td colspan="10">HIGHEST POSSIBLE SCORE</td>
            <?php 
            $scores = [
                'written_works' => [35, 20, 20, 20, 20],
                'performance_tasks' => [50, 100, 100, 50, 65],
                'quarterly_assessment' => [365, 100.00],
                'initial_grade' => [40, 100.00],
                'quarterly_grade' => [20.00]
            ];
            foreach ($scores as $key => $values) {
                foreach ($values as $value) {
                    echo "<td colspan='2'>" . htmlspecialchars($value) . "</td>";
                }
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <tr class="Male-row">
            <td>1</td>
            <td>Male</td>
            <td colspan="50">Data</td>
        </tr>
        <tr class="Female-row">
            <td>1</td>
            <td>Female</td>
            <td colspan="50">Data</td>
        </tr>
    </tbody>
</table>

<a href="r.php">Back</a>

<?php include('../crud/footer.php'); ?>

</body>
</html>
