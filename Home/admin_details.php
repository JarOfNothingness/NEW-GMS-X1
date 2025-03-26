<?php
session_start();
include_once("../LoginRegisterAuthentication/connection.php");
include_once("functions.php");

// Check if user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Check if admin ID is provided
if (!isset($_GET['id'])) {
    die("Admin ID not provided");
}

$adminId = mysqli_real_escape_string($connection, $_GET['id']);

// Fetch admin details
$adminQuery = "SELECT * FROM user WHERE userid = $adminId AND role = 'Admin'";
$adminResult = mysqli_query($connection, $adminQuery);
$adminData = mysqli_fetch_assoc($adminResult);

if (!$adminData) {
    die("Admin not found");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Admin Details</h1>
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title"><?php echo htmlspecialchars($adminData['name']); ?></h2>
                <p class="card-text"><strong>Username:</strong> <?php echo htmlspecialchars($adminData['username']); ?></p>
                <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($adminData['address']); ?></p>
                <p class="card-text"><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($adminData['status'])); ?></p>
            </div>
        </div>

        <!-- Any specific admin-related details can go here -->

        <a href="admin_list.php" class="btn btn-primary">Back to User Management</a>
    </div>
</body>
</html>
