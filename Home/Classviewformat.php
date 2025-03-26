<?php
session_start(); // Start the session at the very top

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('../crud/header.php'); 
include("../LoginRegisterAuthentication/connection.php");

// Fetch students for the dropdown filter
$students_query = "SELECT id, learners_name FROM students"; 
$students_result = mysqli_query($connection, $students_query);

if (!$students_result) {
    die("Query failed: " . mysqli_error($connection));
}

// Determine the selected student ID from the form submission
$selected_student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

// Fetch class records based on the selected student ID
$class_records_query = "SELECT * FROM class_records";
if ($selected_student_id) {
    $class_records_query .= " WHERE student_id = $selected_student_id";
}
$class_records_result = mysqli_query($connection, $class_records_query);

if (!$class_records_result) {
    die("Query failed: " . mysqli_error($connection));
}
?>

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

    tr {
        border-bottom: 1px solid #ddd;
    }

    td {
        padding: 10px;
        text-align: center;
        border: 1px solid #ddd;
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
        background-color: #blue;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        text-align: center;
    }

    a:hover {
        background-color: #2980b9;
    }
    .Male-row {
        background-color: #dff0d8;
    }
    .Female-row {
        background-color: #f2dede;
    }
</style>

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

<!-- Filter Form -->
<form method="GET" action="" class="form-inline mb-3">
    <div class="form-group mx-2">
        <label for="student_id" class="sr-only">Student:</label>
        <select name="student_id" id="student_id" class="form-control" onchange="this.form.submit()">
            <option value="">Select Student</option>
            <?php while ($student = mysqli_fetch_assoc($students_result)) { ?>
                <option value="<?php echo htmlspecialchars($student['id']); ?>" <?php if ($selected_student_id == $student['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($student['learners_name']); ?>
                </option>
            <?php } ?>
        </select>
    </div>
</form>

<table>
    <thead>
        <tr class="school-info">
            <td colspan="15">REGION:</td>
            <td colspan="10">DIVISION:</td>
            <td colspan="15">SCHOOL NAME:</td>
            <td colspan="8">SCHOOL ID</td>
            <td colspan="6">SCHOOL YEAR:</td>
        </tr>
        <tr class="school-info">
            <td colspan="3">FIRST QUARTER</td>
            <td colspan="17">GRADE & SECTION:</td>
            <td colspan="18" class="section-header">TEACHER:</td>
            <td colspan="19" class="section-header">SUBJECT:</td>
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
            <td colspan="1">1</td>
            <td colspan="1">2</td>
            <td colspan="1">3</td>
            <td colspan="1">4</td>
            <td colspan="1">5</td>
            <td colspan="1">6</td>
            <td colspan="1">7</td>
            <td colspan="1">8</td>
            <td colspan="1">9</td>
            <td colspan="1">10</td>
            <td>TOTAL</td>
            <td>PS</td>
            <td>WS</td>
            <td colspan="1">1</td>
            <td colspan="1">2</td>
            <td colspan="1">3</td>
            <td colspan="1">4</td>
            <td colspan="1">5</td>
            <td colspan="1">6</td>
            <td colspan="1">7</td>
            <td colspan="1">8</td>
            <td colspan="1">9</td>
            <td colspan="1">10</td>
            <td>TOTAL</td>
            <td>PS</td>
            <td>WS</td>
            <td>1</td>
            <td>PS</td>
            <td>WS</td>
        </tr>
        <tr>
            <td colspan="10">HIGHEST POSSIBLE SCORE</td>
            <td colspan="2">35</td>
            <td colspan="2">20</td>
            <td colspan="2">20</td>
            <td colspan="2">20</td>
            <td colspan="2">20</td>
            <td colspan="1">115</td>
            <td colspan="1">100.00</td>
            <td colspan="1">30%</td>
            <td>50</td>
            <td>100</td>
            <td>100</td>
            <td>50</td>
            <td>65</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>365</td>
            <td>100.00</td>
            <td>50%</td>
            <td>40</td>
            <td>100.00</td>
            <td>20%</td>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($class_records_result)) { ?>
            <tr class="<?php echo ($row['gender'] === 'Male') ? 'Male-row' : 'Female-row'; ?>">
                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                <td><?php echo htmlspecialchars($row['written_exam']); ?></td>
                <td><?php echo htmlspecialchars($row['performance_task']); ?></td>
                <td><?php echo htmlspecialchars($row['quarterly_exam']); ?></td>
                <td><?php echo number_format($row['final_grade'], 2); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<a href="r.php">Back</a>

<?php include('../crud/footer.php'); ?>
