<?php
session_start();
ob_start(); // Start output buffering

// Include the database connection and functions
include_once("../LoginRegisterAuthentication/connection.php");
include_once("functions.php");
include_once("headeradmin.php");

// Check if the connection is successful
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

$currentUserId = $_SESSION['userid'];


// Handle Activate Request
if (isset($_POST['activate'])) {
    $userid = $_POST['userid'];
    $status = 'approved'; // Set status to 'approved'
    
    $stmt = $connection->prepare("UPDATE user SET status = ? WHERE userid = ?");
    $stmt->bind_param("si", $status, $userid);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success' id='activationAlert'>User activated successfully!</div>";
        echo "<script>
                setTimeout(function() {
                    document.getElementById('activationAlert').style.display = 'none';
                }, 2000); // 2-second timer
              </script>";
    } else {
        echo "<div class='alert alert-danger'>Error activating user: " . mysqli_error($connection) . "</div>";
    }
    $stmt->close();
}

// Handle Deactivate Request
if (isset($_POST['deactivate'])) {
    $userid = $_POST['userid'];
    $status = 'rejected'; // Set status to 'rejected'
    
    $stmt = $connection->prepare("UPDATE user SET status = ? WHERE userid = ?");
    $stmt->bind_param("si", $status, $userid);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success' id='deactivationAlert'>User deactivated successfully!</div>";
        echo "<script>
                setTimeout(function() {
                    document.getElementById('deactivationAlert').style.display = 'none';
                }, 2000); // 2-second timer
              </script>";
    } else {
        echo "<div class='alert alert-danger'>Error deactivating user: " . mysqli_error($connection) . "</div>";
    }
    $stmt->close();
}

// Query to fetch all users with the role 'Admin'
$query = "SELECT * FROM user WHERE role = 'Admin' AND userid != 12 AND status = 'approved' AND userid != $currentUserId";


$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin List</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        .action-btn {
            margin: 2px;
            padding: 5px 10px;
            font-size: 0.8em;
        }
        .dashboard-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        .dashboard-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            flex: 1 1 200px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .dashboard-card i {
            font-size: 2em;
            margin-bottom: 10px;
            color: #007bff;
        }
        .dashboard-card h3 {
            margin: 0;
            font-size: 1.2em;
            color: #333;
        }
        .dashboard-card p {
            font-size: 1.5em;
            font-weight: bold;
            margin: 10px 0 0;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="my-4">Manage Admins</h1>

        <!-- Dashboard Cards -->
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <i class="fas fa-user-shield"></i>
                <h3>Total Admins</h3>
                <p><?php echo mysqli_num_rows($result); ?></p>
            </div>
        </div>

        <!-- Admin User Table -->
        <div class="table-container">
            <table id="adminTable" class="display">
                <thead>
                    <tr>
                        <th>Userid</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Address</th>
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
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo htmlspecialchars($row['role']); ?></td>
                            <td>
                                <?php if ($row['status'] == 'approved'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="userid" value="<?php echo htmlspecialchars($row['userid']); ?>">
                                        <button type="submit" name="deactivate" class="btn btn-danger btn-sm action-btn" onclick="return confirm('Are you sure you want to deactivate this account?');">Deactivate</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="userid" value="<?php echo htmlspecialchars($row['userid']); ?>">
                                        <button type="submit" name="activate" class="btn btn-success btn-sm action-btn" onclick="return confirm('Are you sure you want to activate this account?');">Activate</button>
                                    </form>
                                <?php endif; ?>
                                <a href="admin_details.php?id=<?php echo htmlspecialchars($row['userid']); ?>" class="btn btn-primary btn-sm action-btn">View Details</a>
                                <a href="activity_logs.php?id=<?php echo htmlspecialchars($row['userid']); ?>" class="btn btn-primary btn-sm action-btn">View Activity Log</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#adminTable').DataTable({
                "columnDefs": [
                    { "orderable": false, "targets": 5 } // Disable sorting for the Actions column
                ]
            });
        });
    </script>
</body>
</html>

<?php
ob_end_flush();
?>
