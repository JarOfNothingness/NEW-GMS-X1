<?php
session_start();
include("../LoginRegisterAuthentication/connection.php");
include("functions.php");

// Check if the user is logged in and has Admin privileges
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['userid'];  // Admin ID

if (isset($_GET['userid']) && isset($_GET['action'])) {
    $userid = $_GET['userid'];
    $action = $_GET['action'];
    $comments = isset($_POST['comments']) ? $_POST['comments'] : '';

    // Disable autocommit to ensure manual transaction management
    mysqli_autocommit($connection, false);  // Start transaction

    // Determine action (approve/disapprove) and set SQL query accordingly
    if ($action === 'approve') {
        $sql_update = "UPDATE user SET status = 'approved' WHERE userid = ?";
    } elseif ($action === 'disapprove') {
        $sql_update = "UPDATE user SET status = 'rejected' WHERE userid = ?";
    } else {
        // Invalid action
        header("Location: adminpendingrequestapproval.php?status=failure");
        exit();
    }

    // Prepare and execute the update query
    $stmt = $connection->prepare($sql_update);
    if ($stmt === false) {
        // Log the error and rollback the transaction
        error_log("SQL error: " . $connection->error);
        mysqli_rollback($connection);
        header("Location: adminpendingrequestapproval.php?status=failure");
        exit();
    }

    $stmt->bind_param("i", $userid);
    
    if ($stmt->execute()) {
        // Log history
        $sql_history = "INSERT INTO approval_history (userid, admin_id, comments, action_date) VALUES (?, ?, ?, NOW())";
        $stmt_history = $connection->prepare($sql_history);
        if ($stmt_history === false) {
            // Error in history insertion, rollback transaction
            error_log("SQL history error: " . $connection->error);
            mysqli_rollback($connection);
            $stmt->close();
            header("Location: adminpendingrequestapproval.php?status=failure");
            exit();
        }

        $stmt_history->bind_param("iis", $userid, $admin_id, $comments);
        $stmt_history->execute();
        $stmt_history->close();

        // Commit the transaction
        if (mysqli_commit($connection)) {
            // Close statement and redirect on success
            $stmt->close();
            header("Location: adminpendingrequestapproval.php?status=success&action=$action");
            activities($connection,"User Approved",$userid,$_SESSION['userid']);
            exit();
        } else {
            // Commit failed, rollback the transaction
            mysqli_rollback($connection);
            $stmt->close();
            header("Location: adminpendingrequestapproval.php?status=failure");
            activities($connection,"User Disapproved",$userid,$_SESSION['userid']);
            exit();
        }
    } else {
        // Query execution failed, rollback the transaction
        mysqli_rollback($connection);
        $stmt->close();
        header("Location: adminpendingrequestapproval.php?status=failure");
        exit();
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Approval</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .btn-approve, .btn-disapprove {
            padding: 5px 10px;
            color: #fff;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            margin: 0 5px;
        }

        .btn-approve {
            background-color: #28a745;
        }

        .btn-approve:hover {
            background-color: #218838;
        }

        .btn-disapprove {
            background-color: #dc3545;
        }

        .btn-disapprove:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pending User Approvals</h1>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['userid']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                        <td>
                            <a href="admin_approval.php?userid=<?php echo htmlspecialchars($row['userid']); ?>&action=approve" class="btn-approve">Approve</a>
                            <a href="admin_approval.php?userid=<?php echo htmlspecialchars($row['userid']); ?>&action=disapprove" class="btn-disapprove">Disapprove</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
