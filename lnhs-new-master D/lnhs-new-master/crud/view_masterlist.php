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

// Query to fetch grade, section, and school year of the first student record for the current user
$query = "SELECT 
            s.`grade & section`, 
            s.school_year 
          FROM 
            students s 
          WHERE 
            s.user_id = ? 
          "; // Assuming you only need the first record for the current user

$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 'i', $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$gradeSection = '';
$schoolyear = '';

// Check if there is any record and fetch the values
if ($row = mysqli_fetch_assoc($result)) {
    $gradeSection = $row['grade & section'];
    $schoolyear = $row['school_year'];
}

// Query to fetch student data based on filters
$query = "SELECT 
            s.id,
            s.learners_name, 
            s.gender,
            s.`grade & section`,
            s.school_year
          FROM 
            students s
          WHERE 
            s.user_id = ? 
            AND s.school_year = ? 
            AND s.`grade & section` = ?"; // Keep only 3 placeholders

// Adjust the bind_param to match the correct number of arguments
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 'sss', $userid, $schoolyear, $gradeSection); // Bind only 3 variables
mysqli_stmt_execute($stmt);
$studentResult = mysqli_stmt_get_result($stmt);
?>

<div class="container mt-4">
    <h3 class="text-dark">Student Records</h3>

    <!-- Display selected filters (Grade & Section and School Year) dynamically -->
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="gradeSection">Grade & Section:</label>
            <input type="text" class="form-control" id="gradeSection" value="<?= htmlspecialchars($gradeSection); ?>" disabled>
        </div>
        <div class="col-md-6">
            <label for="schoolYear">School Year:</label>
            <input type="text" class="form-control" id="schoolYear" value="<?= htmlspecialchars($schoolyear); ?>" disabled>
        </div>
    </div>

    <?php if (mysqli_num_rows($studentResult) == 0): ?>
        <p>No students found.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>Student ID</th>
                        <th>Learner's Name</th>
                        <th>Gender</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    while ($row = mysqli_fetch_assoc($studentResult)): 
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($row['learners_name'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($row['gender'] ?? ''); ?></td>
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
