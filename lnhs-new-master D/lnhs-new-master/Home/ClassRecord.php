<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

ob_start();
include("classrecordheader.php");
include("../LoginRegisterAuthentication/connection.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Record Management</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin-top: 3rem;
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .card-button {
            text-align: center;
            padding: 2rem;
            margin: 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .card-button i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .card-button h4 {
            font-size: 1.5rem;
            margin: 0;
            font-weight: 600;
        }
        .add-record {
            background-color: #28a745;
        }
        .view-statistics {
            background-color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="mb-3">Class Record Management</h1>
            <p class="lead">Efficiently manage and analyze student records</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <a href="add_grade.php" class="card-button add-record text-white text-decoration-none d-block">
                    <i class="fas fa-plus-circle"></i>
                    <h4>Add Record</h4>
                </a>
            </div>
            <div class="col-md-6">
                <a href="statistics.php" class="card-button view-statistics text-dark text-decoration-none d-block">
                    <i class="fas fa-chart-bar"></i>
                    <h4>View Statistics</h4>
                </a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
