<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include_once("../LoginRegisterAuthentication/connection.php");
include_once("headeradmin.php");
include_once("functions.php");

// Handle form submission
if (isset($_POST['submit_announcement'])) {
    // Validate and sanitize inputs
    $title = mysqli_real_escape_string($connection, trim($_POST['title']));
    $content = mysqli_real_escape_string($connection, trim($_POST['content']));
    $expiration_date = !empty($_POST['expiration_date']) ? 
        date('Y-m-d H:i:s', strtotime($_POST['expiration_date'])) : null;

    if (empty($title) || empty($content)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Title and content are required.'
            });
        </script>";
    } else {
        // Insert query with correct fields including status
        $insertQuery = "INSERT INTO announcements (title, content, created_at, expiration_date, status) 
                       VALUES (?, ?, NOW(), ?, 'active')";
        
        if ($stmt = $connection->prepare($insertQuery)) {
            $stmt->bind_param("sss", $title, $content, $expiration_date);
            
            if ($stmt->execute()) {
                $_SESSION['success_msg'] = "Announcement added successfully.";
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Announcement added successfully.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        window.location.reload();
                    });
                </script>";
                activities($connection, "Added an Announcement", 0, $_SESSION['userid']);
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Database Error: " . mysqli_error($connection) . "'
                    });
                </script>";
            }
            $stmt->close();
        }
    }
}

// Handle deletion
if (isset($_POST['delete_announcement'])) {
    $id = (int)$_POST['announcement_id'];
    $deleteQuery = "DELETE FROM announcements WHERE id = ? AND status = 'active'";
    
    if ($stmt = $connection->prepare($deleteQuery)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: 'Announcement has been removed.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.reload();
                });
            </script>";
            activities($connection, "Deleted Announcement", 0, $_SESSION['userid']);
        }
        $stmt->close();
    }
}

// Fetch existing announcements
$query = "SELECT *, 
          CASE 
            WHEN expiration_date IS NOT NULL THEN 
                TIMESTAMPDIFF(SECOND, NOW(), expiration_date)
            ELSE NULL 
          END as seconds_remaining 
          FROM announcements 
          WHERE status = 'active' 
          AND (expiration_date IS NULL OR expiration_date > NOW())
          ORDER BY created_at DESC";

$result = mysqli_query($connection, $query);

// Check for query execution error
if (!$result) {
    error_log("Query Error: " . mysqli_error($connection));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .announcement-item {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .badge-expiring {
            background-color: #ffd700;
            color: #000;
        }
        .badge-active {
            background-color: #28a745;
            color: #fff;
        }
        .btn-delete {
            background-color: #dc3545;
            color: #fff;
        }
        .expiration-badge {
            font-size: 0.8em;
            padding: 0.3em 0.6em;
            border-radius: 10px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Create Announcement</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="announcementForm">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="expiration_date" class="form-label">Expiration Date (Optional)</label>
                        <input type="datetime-local" class="form-control" id="expiration_date" name="expiration_date">
                    </div>
                    <button type="submit" name="submit_announcement" class="btn btn-primary">Create Announcement</button>
                </form>
            </div>
        </div>

        <div class="mt-4">
            <h3>Current Announcements</h3>
            <?php 
            if ($result && mysqli_num_rows($result) > 0): 
                while ($row = mysqli_fetch_assoc($result)): 
            ?>
                <div class="announcement-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                        <?php if ($row['expiration_date']): ?>
                            <span class="badge <?php echo $row['seconds_remaining'] < 259200 ? 'badge-expiring' : 'badge-active'; ?>">
                                <?php 
                                    $days = floor($row['seconds_remaining'] / 86400);
                                    echo $days . " days remaining";
                                ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="mt-2"><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">
                            Posted: <?php echo date('F j, Y g:i A', strtotime($row['created_at'])); ?>
                            <?php if ($row['expiration_date']): ?>
                                <br>Expires: <?php echo date('F j, Y g:i A', strtotime($row['expiration_date'])); ?>
                            <?php endif; ?>
                        </small>
                        <form method="POST" action="" class="d-inline">
                            <input type="hidden" name="announcement_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_announcement" class="btn btn-sm btn-delete" 
                                    onclick="return confirm('Are you sure you want to delete this announcement?')">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            <?php 
                endwhile; 
            else: 
            ?>
                <p>No announcements available.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Initialize Flatpickr
        flatpickr("#expiration_date", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            time_24hr: true
        });

        // Form validation
        document.getElementById('announcementForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            
            if (!title || !content) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please fill in all required fields.'
                });
            }
        });
    </script>
</body>
</html>