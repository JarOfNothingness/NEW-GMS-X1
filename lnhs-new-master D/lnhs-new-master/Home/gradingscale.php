<?php

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include("../LoginRegisterAuthentication/connection.php");
include("../crud/header.php");



// Fetch data from the grading_scale table
$sql = "SELECT * FROM grading_scale ORDER BY transmuted_grade DESC";
$result = $connection->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading Scale Chart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f4f8;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 1000px;
            margin: 50px auto;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            border-top: 5px solid #4a90e2;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
        }
        .view-only-badge {
            background-color: #f39c12;
            color: #fff;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 20px;
            display: inline-block;
        }
        .table-container {
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        th, td {
            padding: 15px;
            text-align: center;
            border: none;
        }
        thead th {
            background-color: #4a90e2;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        tbody tr:hover {
            background-color: #e8f0fe;
            transition: background-color 0.3s ease;
        }
        .education-icon {
            font-size: 2rem;
            color: #4a90e2;
            margin-bottom: 20px;
        }
        .grade-range {
            font-weight: bold;
            color: #2c3e50;
        }
        .transmuted-grade {
            color: #e74c3c;
            font-weight: bold;
        }
    </style>
</head>

<body>
    
    <div class="container">
        <div class="text-center">
            <i class="fas fa-chart-line education-icon"></i>
        </div>
        <h1>CHART FOR THE COMPUTATION OF THE QUARTERLY GRADE</h1>
        <div class="text-center mb-4">
            <span class="view-only-badge">
                <i class="fas fa-eye"></i> View Only
            </span>
        </div>
        <div class="table-container">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Initial Grade</th>
                        <th>Transmuted Grade</th>
                        <th>Initial Grade</th>
                        <th>Transmuted Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rowCount = 0;
                    $maxRows = ceil($result->num_rows / 2);
                    $leftColumn = [];
                    $rightColumn = [];

                    while($row = $result->fetch_assoc()) {
                        $gradeRange = $row['initial_grade_min'] . " — " . $row['initial_grade_max'];
                        if ($row['initial_grade_min'] == 100 && $row['initial_grade_max'] == 100) {
                            $gradeRange = "100";
                        }
                        
                        if ($rowCount < $maxRows) {
                            $leftColumn[] = [$gradeRange, $row['transmuted_grade']];
                        } else {
                            $rightColumn[] = [$gradeRange, $row['transmuted_grade']];
                        }
                        $rowCount++;
                    }

                    for ($i = 0; $i < $maxRows; $i++) {
                        echo "<tr>";
                        if (isset($leftColumn[$i])) {
                            echo "<td class='grade-range'>" . $leftColumn[$i][0] . "</td>";
                            echo "<td class='transmuted-grade'>" . $leftColumn[$i][1] . "</td>";
                        } else {
                            echo "<td></td><td></td>";
                        }
                        if (isset($rightColumn[$i])) {
                            echo "<td class='grade-range'>" . $rightColumn[$i][0] . "</td>";
                            echo "<td class='transmuted-grade'>" . $rightColumn[$i][1] . "</td>";
                        } else {
                            echo "<td></td><td></td>";
                        }
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>