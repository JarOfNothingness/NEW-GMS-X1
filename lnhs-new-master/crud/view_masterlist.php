<?php

session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("../LoginRegisterAuthentication/connection.php");
include("header.php");

if (!isset($_SESSION['username']) || !isset($_SESSION['userid'])) {
    header("Location: ../Home/login.php");
    exit();
}

$userid = $_SESSION['userid'];

// Default query to fetch student data without any filters
$query = "SELECT 
    s.id,
    s.learners_name, 
    s.`grade & section`, 
    s.gender, 
    s.school_year
FROM 
    students s
WHERE 
    s.user_id = ?";

$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 'i', $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Check if there's a success message in session
if (isset($_SESSION['upload_success'])) {
    // Display SweetAlert
    echo "
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Upload Successful',
            text: '{$_SESSION['upload_success']}',
            confirmButtonText: 'OK'
        }).then(function() {
            // Clear the success message from session
            window.location.href = 'Crud.php'; // Redirect to clear session on reload
        });
    </script>";
    unset($_SESSION['upload_success']); // Clear the success message after showing it
}

?>
    <div class="container mt-4">
        <h3 class="text-dark">Student Records</h3>
        <div class="box1 mb-3">
            <!-- <button class="btn btn-primary" data-toggle="modal" data-target="#addStudentModal">ADD STUDENT</button>
            <button class="btn btn-success" onclick="printPage()">
                <i class="fas fa-print"></i> Print
            </button> -->
        </div>
        <?php if (mysqli_num_rows($result) == 0): ?>
            <p>No students found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>Student ID</th>
                            <th>Learner's Name</th>
                            <th>Grade </th>
                            <th>Grade & section</th>
                            <th>School Year</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 1;
                        while ($row = mysqli_fetch_assoc($result)): 
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($row['learners_name'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($row['gender'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($row['grade & section'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($row['school_year'] ?? ''); ?></td>

                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<style>
    .container {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .btn-primary, .btn-success {
        background-color: #3f51b5;
        border: none;
    }
    .table thead {
        background-color: #3f51b5;
        color: white;
    }
    .table th, .table td {
        vertical-align: middle;
        text-align: center;
    }
    .table td:first-child {
        text-align: left;
    }
</style>

<?php include("footer.php"); ?>
