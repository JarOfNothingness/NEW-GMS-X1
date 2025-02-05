<?php
session_start();
include("../LoginRegisterAuthentication/connection.php");

// Check if the admin is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Default filters
$action_filter = isset($_GET['action_filter']) ? $_GET['action_filter'] : '';
$search_query = isset($_GET['search_query']) ? $_GET['search_query'] : '';
$year_filter = isset($_GET['year_filter']) ? $_GET['year_filter'] : '';
$month_filter = isset($_GET['month_filter']) ? $_GET['month_filter'] : '';

// Construct the base query to fetch status as 'action'
$query = "SELECT ah.id, u.name, u.username, u.status AS action, ah.comments, ah.action_date, u.role 
          FROM approval_history ah
          JOIN user u ON ah.userid = u.userid
          WHERE 1 = 1";

// Apply action filter if set
if (!empty($action_filter)) {
    $query .= " AND u.status = '$action_filter'";
}

// Apply year and month filter if set
if (!empty($year_filter)) {
    $query .= " AND YEAR(ah.action_date) = '$year_filter'";
}

if (!empty($month_filter)) {
    $query .= " AND MONTH(ah.action_date) = '$month_filter'";
}

// Apply search filter
if (!empty($search_query)) {
    $query .= " AND u.username LIKE '%$search_query%'";
}

$query .= " ORDER BY ah.action_date DESC";
$result = mysqli_query($connection, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval/Disapproval History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS and JS for table pagination and row selection -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>

    <style>
        body {
            background-color: #f7f7f7;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            margin-top: 50px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .action-approved {
            color: green;
            font-weight: bold;
        }
        .action-rejected {
            color: red;
            font-weight: bold;
        }
        .no-data {
            text-align: center;
            font-size: 18px;
            color: #999;
        }
        .filter-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .filter-form {
            display: flex;
            gap: 10px;
        }
        .filter-form input, .filter-form select {
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .filter-form button {
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
        }
        .back-button {
            margin-bottom: 20px;
        }

        /* Styling for print view */
        @media print {
            .filter-container, .back-button, .print-button {
                display: none;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid black;
                padding: 5px;
            }
            th {
                background-color: #f2f2f2;
                text-align: center;
            }
            td {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Approval/Disapproval History</h1>
<!-- Back Button -->
<button class="btn btn-secondary back-button" onclick="window.location.href='adminpendingrequestapproval.php'">
    Back to Pending Approvals
</button>


    
        <!-- Filter Form -->
        <div class="filter-container">
            <form method="GET" class="filter-form">
                <select name="action_filter">
                    <option value="">All Actions</option>
                    <option value="approved" <?php if ($action_filter == 'approved') echo 'selected'; ?>>Approved</option>
                    <option value="rejected" <?php if ($action_filter == 'rejected') echo 'selected'; ?>>Rejected</option>
                </select>

                <!-- Year Filter -->
                <select name="year_filter">
                    <option value="">Select Year</option>
                    <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
                        <option value="<?php echo $i; ?>" <?php if ($year_filter == $i) echo 'selected'; ?>><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>

                <!-- Month Filter -->
                <select name="month_filter">
                    <option value="">Select Month</option>
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php if ($month_filter == $i) echo 'selected'; ?>><?php echo date("F", mktime(0, 0, 0, $i, 10)); ?></option>
                    <?php endfor; ?>
                </select>

                

                <button type="submit">Filter</button>
            </form>
        </div>

        <!-- Table for displaying approval/disapproval history -->
        <table id="historyTable" class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Name</th>
                    <th>Username</th>
                    <th>Action</th> <!-- Echoes the status from user table -->
                    <th>Reason</th>
                    <th>Action Date</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td>
                                <span class="<?php echo ($row['action'] === 'approved') ? 'action-approved' : 'action-rejected'; ?>">
                                    <?php echo htmlspecialchars($row['action']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['comments']); ?></td>
                            <td><?php echo htmlspecialchars($row['action_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['role']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="no-data">No approval/disapproval history available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Initialize DataTables -->
    <script>
        $(document).ready(function() {
            $('#historyTable').DataTable({
                "pageLength": 10,
                "lengthMenu": [[5, 10, 25, 50], [5, 10, 25, 50]],
                "order": [[5, 'desc']]  // Sort by Action Date
            });
        });

        function printPage() {
            window.print();
        }
    </script>
</body>
</html>
