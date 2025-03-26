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
 <div class="main-wrapper">
    <header>
        <h1 class="header-text">Create Class</h1>
    </header>
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
                    <select class="form-control" id="section" name="section" onchange="updateSubjects();" required>
                        <?php
                        $sections = ["Maharlika", "Rizal", "Bonifacio", "Mabini", "Lapu-Lapu", "Del Pilar", "Aguinaldo", "Jacinto", "Silang", "Luna"];
                        shuffle($sections);
                        foreach ($sections as $section) {
                            echo "<option value='$section'>$section</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" id="label_subjects" hidden>Subjects</label>
                    <div id="subjectCheckboxes">
                        <!-- Checkboxes will be dynamically added here -->
                    </div>
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
                                        <th>Grade</th>
                                        <th>Grade & Section</th>
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
        $(document).ready(function() {
            $("#next1").click(function() {
                const checkboxMath = document.getElementById('subject_math');
                const checkboxScience = document.getElementById('subject_science');
                const checkboxEnglish = document.getElementById('subject_english');
                const checkboxAP = document.getElementById('subject_araling_panlipunan');
                const checkboxMapeh = document.getElementById('subject_mapeh');
                const checkboxTle = document.getElementById('subject_tle');
                const checkboxFilipino = document.getElementById('subject_filipino');
                const checkboxValues = document.getElementById('subject_values');

                if (checkboxMath.checked) {
                    $("#step1").hide();
                    $("#step2").show();
                } else if(checkboxScience.checked){
                    $("#step1").hide();
                    $("#step2").show();
                }else if(checkboxEnglish.checked){
                    $("#step1").hide();
                    $("#step2").show();
                }else if(checkboxAP.checked){
                    $("#step1").hide();
                    $("#step2").show();
                }else if(checkboxMapeh.checked){
                    $("#step1").hide();
                    $("#step2").show();
                }else if(checkboxTle.checked){
                    $("#step1").hide();
                    $("#step2").show();
                }else if(checkboxFilipino.checked){
                    $("#step1").hide();
                    $("#step2").show();
                }else if(checkboxValues.checked){
                    $("#step1").hide();
                    $("#step2").show();
                }
                else {
                    alert('Please select Subject');
                }

              
            });

            $("#prev1").click(function() {
                $("#step2").hide();
                $("#step1").show();
            });
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

<?php

// Add student to the database
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excelFile'])) {
    // Process file and insert student records here
    // For now, assuming the student data has been inserted into the 'students' table already.

    // Fetch the student_id of the last inserted student
    $student_id = mysqli_insert_id($connection);

    // Insert student subjects
    $subjects_query = "SELECT id FROM subjects";
    $subjects_result = mysqli_query($connection, $subjects_query);

    while ($subject = mysqli_fetch_assoc($subjects_result)) {
        $subject_id = $subject['id'];
        
        // Insert into student_subjects table
        $insert_query = "INSERT INTO student_subjects (student_id, subject_id, description) 
                         VALUES (?, ?, ?)";
        $description = "Assigned Subject"; // Default description
        $stmt = mysqli_prepare($connection, $insert_query);
        mysqli_stmt_bind_param($stmt, 'iis', $student_id, $subject_id, $description);
        mysqli_stmt_execute($stmt);
    }

    $_SESSION['upload_success'] = "Student enrolled in all subjects.";
    header("Location: Crud.php"); // Redirect after success
    exit();
}
?>


<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<style>
    .main-wrapper{
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding-left: 10px;
        padding-right: 10px;
    }
    header {
            background-color: #4a69bd;
            padding: 20px;
            color: #fff;
            font-size: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            border-radius: 15px;
    }

    .header-text {
            text-align: center;
            margin: 0;
    }

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
    #subjectCheckboxes {
        display: flex;
        flex-wrap: wrap;
    }
</style>

<script>
function updateSubjects() {
    var gradeSection = document.getElementById('grade');
    var subjectLabel = document.getElementById('label_subjects');
    var subjectCheckboxes = document.getElementById('subjectCheckboxes');
   
    
    if (!gradeSection || !subjectCheckboxes)  return;

    // Get selected grade
    var selectedGrade = gradeSection.value.split(' ')[1];

    console.log(selectedGrade);
    
    // Define subject lists
    var commonSubjects = ['Math', 'Science', 'English', 'Araling Panlipunan', 'Mapeh', 'TLE', 'Filipino'];
    var grade7Subjects = [...commonSubjects, 'Values'];
    var upperGradeSubjects = [...commonSubjects, 'ESP'];
    
    // Select appropriate subjects based on grade
    var subjects = selectedGrade === '7' ? grade7Subjects : upperGradeSubjects;

    // Clear existing checkboxes
    subjectCheckboxes.innerHTML = '';

    // Create checkbox container
    var checkboxContainer = document.createElement('div');
    checkboxContainer.className = 'row';

    // Create checkboxes
    subjects.forEach(function(subject) {
        var col = document.createElement('div');
        col.className = 'col-md-6 mb-2';

        var checkboxDiv = document.createElement('div');
        checkboxDiv.className = 'form-check';

        var input = document.createElement('input');
        input.type = 'checkbox';
        input.className = 'form-check-input';
        input.id = 'subject_' + subject.toLowerCase().replace(/\s+/g, '_');
        input.name = 'subjects[]';
        input.value = subject;

        console.log(input.id)

        var label = document.createElement('label');
        label.className = 'form-check-label';
        label.htmlFor = input.id;
        label.textContent = subject;

        checkboxDiv.appendChild(input);
        checkboxDiv.appendChild(label);
        col.appendChild(checkboxDiv);
        checkboxContainer.appendChild(col);
    });

    subjectCheckboxes.appendChild(checkboxContainer);
    subjectLabel.removeAttribute("hidden");
}
</script>

<?php include("footer.php"); ?>
