<?php
session_start(); // Start the session at the very top

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>


<?php include("../LoginRegisterAuthentication/connection.php"); ?>
<?php include("../crud/header.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading Scale for Junior High School (JHS)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f4f8;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            border-top: 5px solid #4a90e2;
        }
        h1 {
            color: black;
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
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
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
        tbody td:first-child {
            font-weight: bold;
            color: #2c3e50;
        }
        .table-container {
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .education-icon {
            font-size: 2rem;
            color: #4a90e2;
            margin-bottom: 20px;
        }
    </style>
   </head>
<body>
    <div class="container">
        <div class="text-center">
            <i class="fas fa-graduation-cap education-icon"></i>
        </div>
        <h1>Grading Scale </h1>
        <div class="text-center mb-4">
            <span class="view-only-badge">
                <i class="fas fa-eye"></i> View Only
            </span>
        </div>
        <div class="table-container">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Written</th>
                        <th>Performance Task</th>
                        <th>Quarterly Exams</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>English</td>
                        <td>30%</td>
                        <td>50%</td>
                        <td>20%</td>
                    </tr>
                    <tr>
                        <td>Math</td>
                        <td>40%</td>
                        <td>40%</td>
                        <td>20%</td>
                    </tr>
                    <tr>
                        <td>Science</td>
                        <td>40%</td>
                        <td>40%</td>
                        <td>20%</td>
                    </tr>
                    <tr>
                        <td>Filipino</td>
                        <td>30%</td>
                        <td>50%</td>
                        <td>20%</td>
                    </tr>
                    <tr>
                        <td>TLE</td>
                        <td>20%</td>
                        <td>60%</td>
                        <td>20%</td>
                    </tr>
                    <tr>
                        <td>Mapeh</td>
                        <td>20%</td>
                        <td>60%</td>
                        <td>20%</td>
                    </tr>
                    <tr>
                        <td>Araling Panlipunan</td>
                        <td>30%</td>
                        <td>50%</td>
                        <td>20%</td>
                    </tr>
                    <tr>
                        <td>ESP</td>
                        <td>30%</td>
                        <td>50%</td>
                        <td>20%</td>
                    </tr>
                    <tr>
                        <td>VALUES</td>
                        <td>30%</td>
                        <td>50%</td>
                        <td>20%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
   
<?php include('../crud/footer.php'); ?>

    
</body>
</html>
    