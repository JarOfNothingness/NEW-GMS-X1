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
// Query to get activities with user information
$sql = "SELECT a.id, a.admin_id, a.userid, a.logs, a.datetime, u.name, u.role 
        FROM activities a
      
        LEFT JOIN user u ON a.admin_id = u.userid 
          WHERE a.admin_id = ".$_GET['id']." 
        ORDER BY a.datetime DESC";
$result = $connection->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }

        .logs-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .role-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .role-admin {
            background-color: #dc3545;
            color: white;
        }

        .role-teacher {
            background-color: #28a745;
            color: white;
        }

        .timestamp {
            color: #666;
            font-size: 14px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .search-box {
            padding: 10px;
            margin-bottom: 20px;
        }

        .search-box input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
    }

    .back-button:hover {
        background-color: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        color: white;
        text-decoration: none;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .header-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .header h1 {
        margin: 0;
        color: #333;
        font-size: 24px;
    }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="header-content">
            <h1><i class="fas fa-history"></i> Activity Logs</h1>
        </div>
        <a href="admin_list.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Back to Admin List
        </a>
    </div>

        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search logs..." onkeyup="searchLogs()">
        </div>

        <div class="logs-container">
            <?php if ($result->num_rows > 0): ?>
                <table id="logsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                           
                            <th>Activity</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                            
             
                                <td><?php echo htmlspecialchars($row['logs']); ?></td>
                                <td class="timestamp">
                                    <i class="far fa-clock"></i>
                                    <?php echo date('M d, Y h:i A', strtotime($row['datetime'])); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox fa-3x"></i>
                    <p>No activity logs found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function searchLogs() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('logsTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let txtValue = '';
                
                for (let j = 0; j < td.length; j++) {
                    txtValue += td[j].textContent || td[j].innerText;
                }
                
                if (txtValue.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = '';
                } else {
                    tr[i].style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>

<?php
$connection->close();
?>