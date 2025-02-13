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

<body>
    <div class="container mt-5">
        
        <form method="POST" action="upload_excel.php" id="uploadExcelForm" enctype="multipart/form-data">
            <!-- Step 1 -->
            <div id="step1" class="step">
                <h3>Create Class</h3>
                <div class="mb-3">
    <label for="schoolyear" class="form-label">School Year</label>
    <select class="form-control" id="schoolyear" name="schoolyear" required>
        <?php
        $currentYear = date("Y");
        for ($i = 0; $i < 10; $i++) {
            $startYear = $currentYear + $i;
            $endYear = $startYear + 1;
            echo "<option value='$startYear-$endYear'>$startYear-$endYear</option>";
        }
        ?>
    </select>
</div>

<div class="mb-3">
    <label for="grade" class="form-label">Grade</label>
    <select class="form-control" id="grade" name="grade" required>
        <?php
        for ($grade = 7; $grade <= 10; $grade++) {
            echo "<option value='Grade $grade'>Grade $grade</option>";
        }
        ?>
    </select>
</div>

<div class="mb-3">
    <label for="section" class="form-label">Section</label>
    <select class="form-control" id="section" name="section" required>
        <?php
        $sections = ["Maharlika", "Rizal", "Bonifacio", "Mabini", "Lapu-Lapu", "Del Pilar", "Aguinaldo", "Jacinto", "Silang", "Luna"];
        shuffle($sections);
        foreach ($sections as $section) {
            echo "<option value='$section'>$section</option>";
        }
        ?>
    </select>
</div>
                <button type="button" class="btn btn-primary" id="next1">Next</button>
            </div>
            
            <!-- Step 2 -->
            <div id="step2" class="step" style="display:none;">
                <h3>Add Student</h3>
                <div class="mb-3">
                    <label for="excelFile" class="form-label">Upload Excel File</label>
                    <input type="file" class="form-control" id="excelFile" name="excelFile" accept=".xls, .xlsx" required>
                    <small class="form-text text-muted">Only .xls and .xlsx files are allowed.</small>
                </div>
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="prev1">Previous</button>
                <button type="submit" class="btn btn-primary" id="submitButton">Upload</button>
            </div>
            <div id="step3" class="step" style="display:none;">
            <div class="container mt-4">
                <h3 class="text-dark">Student Records</h3>
                <div class="box1 mb-3">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addStudentModal">ADD STUDENT</button>
                    <button class="btn btn-success" onclick="printPage()">
                        <i class="fas fa-print"></i> Print
                    </button>
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
            </div>
        </form>
    </div>

    <script>
        // var uploadSuccess = <?php echo isset($_SESSION['isSuccess']) ? ($_SESSION['isSuccess'] ? 'true' : 'false') : 'false'; ?>;

        $(document).ready(function() {
           
            // Step 1 to Step 2
            $("#next1").click(function() {
                $("#step1").hide();
                $("#step2").show();
            });

            // Step 2 to Step 3
            $("#next2").click(function() {
                const username = $("#username").val();
                const email = $("#email").val();
                const password = $("#password").val();

                $("#review_username").text(username);
                $("#review_email").text(email);
                $("#review_password").text(password);

            });

            // Step 2 back to Step 1
            $("#prev1").click(function() {
                $("#step2").hide();
                $("#step1").show();
            });

            // // Step 3 back to Step 2
            // if (uploadSuccess) {
            //     $("#step1").hide();
            //     $("#step2").hide();
            //     $("#step3").show();
            // }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

<!-- Modal for Uploading Excel File -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Upload Excel File</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="upload_excel.php" id="uploadExcelForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="excelFile" class="form-label">Upload Excel File</label>
                        <input type="file" class="form-control" id="excelFile" name="excelFile" accept=".xls, .xlsx" required>
                        <small class="form-text text-muted">Only .xls and .xlsx files are allowed.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submitButton">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
