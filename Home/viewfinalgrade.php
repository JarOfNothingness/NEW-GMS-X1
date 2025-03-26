<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include("../LoginRegisterAuthentication/connection.php");

// Retrieve ID from query string
$grade_id = $_GET['id'] ?? null;

if (!$grade_id) {
    die("Grade ID is missing.");
}

// Fetch grade details for the selected ID
$query = "SELECT sg.*, sub.name as subject_name
          FROM student_grades sg
          JOIN subjects sub ON sg.subject_id = sub.id
          WHERE sg.id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $grade_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch data
$grade = $result->fetch_assoc();

if (!$grade) {
    die("No data found for the specified ID.");
}

// Prepare data for display
$subject_name = $grade['subject_name'];
$final_grade = $grade['final_grade'];

// Fetch dropdown filter options
$year_query = "SELECT DISTINCT academic_year FROM student_grades ORDER BY academic_year";
$year_result = $connection->query($year_query);

$years = [];
while ($row = $year_result->fetch_assoc()) {
    $years[] = $row['academic_year'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary of Quarterly Grades</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .header-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .header-table td {
            border: none;
            padding: 5px;
        }
        .section-title {
            text-align: left;
            font-weight: bold;
        }
        .subject-header {
            background-color: lightgray;
            font-weight: bold;
        }
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
        .filter-form {
            margin: 20px 0;
        }
    </style>
</head>
<body>

    <h2 style="text-align: center;">Summary of Quarterly Grades</h2>

    <form method="get" action="viewfinalgrade.php" class="filter-form">
        <label for="academic_year">Academic Year:</label>
        <select name="academic_year" id="academic_year">
            <option value="">Select Academic Year</option>
            <?php foreach ($years as $year): ?>
                <option value="<?php echo htmlspecialchars($year); ?>" <?php echo (isset($_GET['academic_year']) && $_GET['academic_year'] == $year) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($year); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <!-- Add additional filter fields if needed -->
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($grade_id); ?>">
        <button type="submit">Filter</button>
    </form>

    <table class="header-table">
        <tr>
            <td><strong>SUBJECT:</strong> <?php echo htmlspecialchars($subject_name); ?></td>
            <td><strong>ACADEMIC YEAR:</strong> <?php echo htmlspecialchars($grade['academic_year']); ?></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th rowspan="2">LEARNER'S NAME</th>
                <th colspan="4" class="subject-header"><?php echo htmlspecialchars($subject_name); ?></th>
                <th rowspan="2">FINAL GRADE</th>
                <th rowspan="2">REMARK</th>
            </tr>
            <tr>
                <th>1st Quarter</th>
                <th>2nd Quarter</th>
                <th>3rd Quarter</th>
                <th>4th Quarter</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="section-title">[Learner's Name]</td>
                <td><?php echo htmlspecialchars($grade['written_exam']); ?></td>
                <td><?php echo htmlspecialchars($grade['performance_task']); ?></td>
                <td><?php echo htmlspecialchars($grade['quarterly_exam']); ?></td>
                <td><?php echo htmlspecialchars($grade['final_grade']); ?></td>
                <td><?php echo htmlspecialchars($final_grade); ?></td>
                <td><?php echo htmlspecialchars($grade['remarks']); ?></td>
            </tr>
        </tbody>
    </table>

    <a href="javascript:history.back()" class="back-button">Back</a>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
