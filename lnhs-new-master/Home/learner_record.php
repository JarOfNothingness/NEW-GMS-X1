<?php
session_start(); // Start the session at the very top

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('../LoginRegisterAuthentication/connection.php'); // Adjust the path as necessary

// Ensure that the ID is set and valid
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch learner data from the encoded_learner_data table
$query = "SELECT * FROM encoded_learner_data WHERE learner_id = ?";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Learner not found.");
}

$learnerData = mysqli_fetch_assoc($result);

// Fetch grades related to the learner
$gradesQuery = "SELECT * FROM encoded_learner_data WHERE learner_id = ?";
$gradesStmt = mysqli_prepare($connection, $gradesQuery);
mysqli_stmt_bind_param($gradesStmt, 'i', $id);
mysqli_stmt_execute($gradesStmt);
$gradesResult = mysqli_stmt_get_result($gradesStmt);

if (!$gradesResult) {
    die("Error fetching grades: " . mysqli_error($connection));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learner's Permanent Record</title>
    <link rel="stylesheet" href="styles.css"> <!-- Adjust the path as necessary -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        h1, h2, h3 {
            text-align: center;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background: #f9f9f9;
        }
        .form-container {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .form-container td, .form-container th {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        .form-container th {
            background-color: #f2f2f2;
        }
        .final-grade {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #ccc;
            background: #eaeaea;
        }
        .text-center {
            text-align: center;
            margin-top: 30px;
        }
        .print-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .print-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Learner's Permanent Record</h1>
    <h3>(Formerly Form 137)</h3>

    <h2>Learner's Information</h2>
    <table class="form-container">
        <tr>
            <td>LAST NAME</td>
            <td><?php echo htmlspecialchars($learnerData['last_name']); ?></td>
        </tr>
        <tr>
            <td>FIRST NAME</td>
            <td><?php echo htmlspecialchars($learnerData['first_name']); ?></td>
        </tr>
        <tr>
            <td>NAME EXTENSION</td>
            <td><?php echo htmlspecialchars($learnerData['name_extension']); ?></td>
        </tr>
        <tr>
            <td>MIDDLE NAME</td>
            <td><?php echo htmlspecialchars($learnerData['middle_name']); ?></td>
        </tr>
        <tr>
            <td>LEARNER REFERENCE NUMBER (LRN)</td>
            <td><?php echo htmlspecialchars($learnerData['lrn']); ?></td>
        </tr>
        <tr>
            <td>BIRTHDATE (mm/dd/yyyy)</td>
            <td><?php echo htmlspecialchars($learnerData['birthdate']); ?></td>
        </tr>
        <tr>
            <td>SEX</td>
            <td><?php echo htmlspecialchars($learnerData['sex']); ?></td>
        </tr>
    </table>

    <h2>Eligibility for JHS Enrollment</h2>
    <table class="form-container">
        <tr>
            <td>Elementary School Completer</td>
            <td><input type="checkbox" <?php echo $learnerData['elementary_completer'] ? 'checked' : ''; ?> disabled></td>
        </tr>
        <tr>
            <td>General Average</td>
            <td><?php echo htmlspecialchars($learnerData['general_average']); ?></td>
        </tr>
        <tr>
            <td>Citation (If Any)</td>
            <td><?php echo htmlspecialchars($learnerData['citation']); ?></td>
        </tr>
        <tr>
            <td>Name of Elementary School</td>
            <td><?php echo htmlspecialchars($learnerData['elementary_school_name']); ?></td>
        </tr>
        <tr>
            <td>School ID</td>
            <td><?php echo htmlspecialchars($learnerData['school_id']); ?></td>
        </tr>
        <tr>
            <td>Address of School</td>
            <td><?php echo htmlspecialchars($learnerData['school_address']); ?></td>
        </tr>
        <tr>
            <td>PEPT Passer</td>
            <td><input type="checkbox" <?php echo $learnerData['pept_passer'] ? 'checked' : ''; ?> disabled></td>
        </tr>
        <tr>
            <td>PEPT Rating</td>
            <td><?php echo htmlspecialchars($learnerData['pept_rating']); ?></td>
        </tr>
        <tr>
            <td>ALS A & E Passer</td>
            <td><input type="checkbox" <?php echo $learnerData['als_a_e_passer'] ? 'checked' : ''; ?> disabled></td>
        </tr>
        <tr>
            <td>ALS Rating</td>
            <td><?php echo htmlspecialchars($learnerData['als_rating']); ?></td>
        </tr>
        <tr>
            <td>Others (Please Specify)</td>
            <td><input type="checkbox" <?php echo $learnerData['others_specify'] ? 'checked' : ''; ?> disabled></td>
        </tr>
        <tr>
            <td>Date of Examination/Assessment</td>
            <td><?php echo htmlspecialchars($learnerData['exam_date']); ?></td>
        </tr>
        <tr>
            <td>Name and Address of Testing Center</td>
            <td><?php echo htmlspecialchars($learnerData['testing_center']); ?></td>
        </tr>
    </table>

    <h2>Scholastic Record</h2>
    <table class="form-container">
        <thead>
            <tr>
                <th>Learning Areas</th>
                <th>Written Exam</th>
                <th>Performance Task</th>
                <th>Quarterly Exam</th>
                <th>Final Grade</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($grade = mysqli_fetch_assoc($gradesResult)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($grade['subject_id']); ?></td>
                    <td><?php echo htmlspecialchars($grade['written_exam']); ?></td>
                    <td><?php echo htmlspecialchars($grade['performance_task']); ?></td>
                    <td><?php echo htmlspecialchars($grade['quarterly_exam']); ?></td>
                    <td><?php echo htmlspecialchars($grade['final_grade']); ?></td>
                    <td><?php echo htmlspecialchars($grade['remarks']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="final-grade">
        <div class="left">
            <strong>General Average:</strong>
        </div>
        <div class="center">
            <strong><?php echo htmlspecialchars($learnerData['general_average']); ?></strong>
        </div>
        <div class="right">
            <strong>Status:</strong> Promoted
        </div>
    </div>

    <div class="text-center">
        <p>____________________</p>
        <p><?php echo htmlspecialchars($learnerData['signature']); ?></p>
        <p>Signature of Learner</p>
        <p><?php echo htmlspecialchars($learnerData['adviser']); ?></p>
        <p>Adviser</p>
        <p><?php echo htmlspecialchars($learnerData['school_year']); ?></p>
        <p>School Year</p>
    </div>

    <button class="print-btn" onclick="window.print()">Print Record</button>
</div>

</body>
</html>
