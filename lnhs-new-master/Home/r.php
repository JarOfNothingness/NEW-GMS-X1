<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('../crud/header.php');
include("../LoginRegisterAuthentication/connection.php");

$learners_name = isset($_GET['learners_name']) ? $_GET['learners_name'] : '';

$query = "SELECT s.learners_name, s.id, a.*
          FROM students s
          JOIN attendance a ON s.id = a.student_id
          WHERE 1=1";

if ($learners_name) {
    $query .= " AND s.learners_name LIKE '%" . mysqli_real_escape_string($connection, $learners_name) . "%'";
}

$query .= " ORDER BY s.learners_name ASC";
$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}

$student_query = "SELECT DISTINCT learners_name FROM students ORDER BY learners_name ASC";
$students_result = mysqli_query($connection, $student_query);

if (!$students_result) {
    die("Query failed: " . mysqli_error($connection));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 50px;
        }
        h2 {
            color: #007bff;
            margin-bottom: 30px;
        }
        .filter-form {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .table {
            background-color: #ffffff;
        }
        .table thead th {
            background-color: #007bff;
            color: #ffffff;
            border-color: #007bff;
        }
        .btn-action {
            width: 100%;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center">Student Reports</h2>

    <form method="GET" action="" class="filter-form">
        <div class="row align-items-end">
            <div class="col-md-8">
                <label for="learners_name" class="form-label">Learners Name:</label>
                <select name="learners_name" id="learners_name" class="form-select">
                    <option value="">Select Learners Name</option>
                    <?php while ($student = mysqli_fetch_assoc($students_result)) { ?>
                        <option value="<?php echo htmlspecialchars($student['learners_name']); ?>"
                            <?php if ($learners_name === $student['learners_name']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($student['learners_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Learners Name</th>
                    <th>Form 2</th>
                    <th>Form 14</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['learners_name']); ?></td>
                    <td>
                        <a href="view_form2.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-info btn-sm btn-action">
                            <i class="fas fa-eye"></i> View Form 2
                        </a>
                    </td>
                    <td>
                        <a href="view_form14.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-info btn-sm btn-action">
                            <i class="fas fa-eye"></i> View Form 14
                        </a>
                    </td>
                    <td>
                        <a href="index.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-primary btn-sm btn-action">
                            <i class="fas fa-envelope"></i> Send Report to Gmail
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php include('../crud/footer.php'); ?>