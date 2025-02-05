<?php
session_start(); // Start the session at the very top

// Check if the session is correctly set
if (!isset($_SESSION['username']) || !isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}
include('../Home/functions.php');
include('headerforadmincrud.php'); 
include("../LoginRegisterAuthentication/connection.php"); 

$userid = $_SESSION['userid'];

// Query to fetch student data for the current user
$query = "SELECT * FROM students WHERE user_id = ?";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 'i', $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}

// Fetch the admin's name to display
$userId = $_SESSION['userid'];
$userQuery = "SELECT name FROM user WHERE userid = ?";
$stmt = mysqli_prepare($connection, $userQuery);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$userResult = mysqli_stmt_get_result($stmt);

$adminName = '';
if ($userResult && mysqli_num_rows($userResult) > 0) {
    $user = mysqli_fetch_assoc($userResult);
    $adminName = $user['name']; // Get the admin's name
}

// Set teacher's name in session for print function
$_SESSION['admin_name'] = $adminName;

?>

<style>
    .name-container {
    display: flex;
    align-items: center;
}

.name-field {
    width: 30%; /* Adjust width as necessary */
    margin-right: 5px; /* Space between fields */
}

.slash {
    margin: 0 5px; /* Space around the slash */
    font-weight: bold; /* Make the slash bold */
}
 /* Styling similar to the provided design */
 body {
        font-family: Arial, sans-serif;
        background-color: #f0f0f0;
    }
    .container {
        width: 90%;
        margin: 0 auto;
        margin-top: 50px;
    }
    .btn {
        padding: 10px 20px;
        margin-bottom: px;
        font-weight: bold;
        border: none;
        cursor: pointer;
    }
    .btn-primary {
        background-color: #3f51b5;
        color: white;
    }
    .btn-success {
        background-color: #4CAF50;
        color: white;
    }
    .btn-print {
        background-color: #4CAF50;
        color: white;
        position: absolute;
        right: 10%;
        top: 20px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        background-color: #fff;
    }
    th, td {
        border: 1px solid #ddd;
        text-align: left;
        padding: 8px;
    }
    th {
        background-color: #3f51b5;
        color: white;
        font-weight: bold;
    }
    tr:hover {
        background-color: #f1f1f1;
    }
    input[type="text"] {
        padding: 0px;
        width: 80%;
        border: 1px solid #ccc;
    }
    .input-group-append input[type="submit"] {
        background-color: #3f51b5;
        color: white;
        padding: 10px 20px;
        border: none;
    }

</style>

<br>
<div class="box1">
  
    
    <button class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">ADD STUDENTS</button>

    <button class="btn btn-success" onclick="printPage()">
    <i class="fas fa-print"></i> Print</button>
    <script>
function printPage() {
    // Get the teacher's name (admin name) from the session using PHP
    var adminName = "<?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'N/A'; ?>";

    var printWindow = window.open('', '', 'height=600,width=800');

    printWindow.document.write('<html><head><title>Print</title>');
    
    // Add styles for table alignment
    printWindow.document.write('<style>');
    printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 5px; }');
    printWindow.document.write('th, td { border: 1px solid black; padding: 5px; }');
    printWindow.document.write('th { background-color: #f2f2f2; text-align: center; }');
    printWindow.document.write('td { text-align: center; }');
    printWindow.document.write('td.name-column { text-align: left; }');
    printWindow.document.write('</style>');
    
    printWindow.document.write('</head><body>');

    // Add a container for logos and header
    printWindow.document.write('<div style="width: 100%; height: 150px; position: relative; margin-bottom: 0px;">');

    // Add left logo (DepEd logo)
    printWindow.document.write('<div style="position: absolute; left: 10px; top: 10px; display: flex; align-items: center;">');
    printWindow.document.write('<img src="kagawaran-removebg-preview.png" alt="DepEd Logo" style="height: 100px; margin-right: 30px;">');
    printWindow.document.write('<div style="font-size: 16px;">');
    printWindow.document.write('Region: <span style="border: 1px solid black; padding: 5px; display: inline-block;">VII</span>');
    printWindow.document.write('&nbsp;&nbsp;');
    printWindow.document.write('Division: <span style="border: 1px solid black; padding: 5px; display: inline-block;">Cebu Province</span>');
    printWindow.document.write('</div>');
    printWindow.document.write('</div>');

    // School Name label with boxed School Name
    printWindow.document.write('<div style="position: absolute; left: 135px; top: 80px; font-size: 16px;">');
    printWindow.document.write('School Name: <span style="border: 1px solid black; padding: 5px; display: inline-block;">Lanao National High School</span>');
    printWindow.document.write('</div>');

    // Add right logo (School logo) larger and beside the School ID label
    printWindow.document.write('<div style="position: absolute; right: 10px; top: 10px; display: flex; align-items: center;">');
    printWindow.document.write('<div style="font-size: 16px; margin-right: 20px;">School ID: <span style="border: 1px solid black; padding: 5px; display: inline-block;">303031</span></div>');
    printWindow.document.write('<img src="depedlogobgwhite.png" alt="School Logo" style="height: 100px; margin-left: 10px;">');
    printWindow.document.write('</div>');

    printWindow.document.write('</div>');
    
    // Create the table for printing with matching headers to the displayed table
    printWindow.document.write('<table>');
    printWindow.document.write('<thead>');
    
    // Add a header row for the teacher's name
    printWindow.document.write('<tr><th colspan="6" style="text-align: left;">Admin: ' + adminName + '</th></tr>');

    // Add the column headers
    printWindow.document.write('<tr>');
    printWindow.document.write('<th>#</th>'); // Numbering column
    printWindow.document.write('<th>Learner\'s Name</th>');
    printWindow.document.write('<th>Grade & Section</th>');
    printWindow.document.write('<th>School Year</th>');
    printWindow.document.write('<th>Gender</th>');
    printWindow.document.write('</tr>');
    printWindow.document.write('</thead>');

    // Get the rows from the existing table
    var rows = document.querySelectorAll('.table-responsive tbody tr');
    rows.forEach(function(row, index) {
        var cols = row.querySelectorAll('td');
        printWindow.document.write('<tr>');
        printWindow.document.write('<td>' + (index + 1) + '</td>'); // Numbering
        printWindow.document.write('<td class="name-column">' + cols[0].innerText + '</td>'); // Learner's Name
        printWindow.document.write('<td>' + cols[1].innerText + '</td>'); // Grade & Section
        printWindow.document.write('<td>' + cols[2].innerText + '</td>'); // School Year
        printWindow.document.write('<td>' + cols[3].innerText + '</td>'); // Gender
        printWindow.document.write('</tr>');
    });

    printWindow.document.write('</table>');
    printWindow.document.write('</body></html>');
    
    printWindow.document.close();
    printWindow.print();
}
</script>




</div>




</br>
<!-- Filter and Search Form -->
<form method="GET" action="" class="mb-3">
    <div class="d-flex flex-wrap justify-content-center align-items-center">
        
       <!-- Dropdown for School Year -->
<div class="dropdown mb-2 mr-2">
    <button class="btn btn-secondary dropdown-toggle" type="button" id="schoolYearDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <?php echo isset($_GET['school_year']) && $_GET['school_year'] ? $_GET['school_year'] : 'School Year'; ?>
    </button>
    <ul class="dropdown-menu" aria-labelledby="schoolYearDropdown">
    <li>
            <a class="dropdown-item" href="<?php echo '?' . http_build_query(array_merge($_GET, ['school_year' => ''])); ?>">
                All School Years
            </a>
        </li>
        <?php 
        $startYear = 2020;
        $endYear = 2028;

        for ($year = $startYear; $year <= $endYear; $year++) {
            $nextYear = $year + 1;
            $schoolYear = "{$year}-{$nextYear}";
            $queryParams = array_merge($_GET, ['school_year' => $schoolYear]);
            $url = '?' . http_build_query($queryParams);
            echo "<li><a class=\"dropdown-item\" href=\"{$url}\">{$schoolYear}</a></li>";
        }
        ?>
    </ul>
</div>


        <div class="dropdown mb-2 mr-2">
    <button class="btn btn-secondary dropdown-toggle" type="button" id="sectionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <?php echo isset($_GET['section']) && $_GET['section'] ? $_GET['section'] : 'Grade & Section'; ?>
    </button>
    <ul class="dropdown-menu" aria-labelledby="sectionDropdown">
    <li>
    <a class="dropdown-item" href="?section=&<?php echo http_build_query(array_diff_key($_GET, array_flip(['section']))); ?>">
        All Grade & Section
    </a>
</li>

        <li><a class="dropdown-item" href="?section=Gumamela<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>&<?php echo http_build_query(array_diff_key($_GET, array_flip(['section', 'search']))); ?>">7-Gumamela</a></li>
        <li><a class="dropdown-item" href="?section=Sampaguita<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>&<?php echo http_build_query(array_diff_key($_GET, array_flip(['section', 'search']))); ?>">7-Sampaguita</a></li>
        <li><a class="dropdown-item" href="?section=Rose<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>&<?php echo http_build_query(array_diff_key($_GET, array_flip(['section', 'search']))); ?>">7-Rose</a></li>
        <li><a class="dropdown-item" href="?section=Narra<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>&<?php echo http_build_query(array_diff_key($_GET, array_flip(['section', 'search']))); ?>">8-Narra</a></li>
        <li><a class="dropdown-item" href="?section=Yakal<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>&<?php echo http_build_query(array_diff_key($_GET, array_flip(['section', 'search']))); ?>">8-Yakal</a></li>
        <li><a class="dropdown-item" href="?section=Diamond<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>&<?php echo http_build_query(array_diff_key($_GET, array_flip(['section', 'search']))); ?>">9-Diamond</a></li>
        <li><a class="dropdown-item" href="?section=Sapphire<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>&<?php echo http_build_query(array_diff_key($_GET, array_flip(['section', 'search']))); ?>">9-Sapphire</a></li>
        <li><a class="dropdown-item" href="?section=Jupiter<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>&<?php echo http_build_query(array_diff_key($_GET, array_flip(['section', 'search']))); ?>">10-Jupiter</a></li>
        <li><a class="dropdown-item" href="?section=Venus<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>&<?php echo http_build_query(array_diff_key($_GET, array_flip(['section', 'search']))); ?>">10-Venus</a></li>
       
    </ul>
</div>
        <div class="dropdown mb-2 mr-2">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="genderDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <?php echo isset($_GET['gender']) && $_GET['gender'] ? $_GET['gender'] : 'Gender'; ?>
            </button>
            <ul class="dropdown-menu" aria-labelledby="genderDropdown">
                 <!-- Option for All Genders -->
        <li>
            <a class="dropdown-item" href="?gender=<?php echo '' . (isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''); ?>&<?php echo http_build_query(array_diff_key($_GET, array_flip(['gender', 'search']))); ?>">
                All Genders
            </a>
        </li>
                <li><a class="dropdown-item" href="?gender=Male<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>&<?php echo http_build_query(array_diff_key($_GET, array_flip(['gender', 'search']))); ?>">Male</a></li>
                <li><a class="dropdown-item" href="?gender=Female<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>&<?php echo http_build_query(array_diff_key($_GET, array_flip(['gender', 'search']))); ?>">Female</a></li>
  
            </ul>
        </div>

       

       

        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search by Learners Name" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
            <div class="input-group-append">
                <input type="submit" class="btn btn-primary" value="Search">
            </div>
        </div>
    </div>
</form>

<style>
/* Hides unnecessary columns in the table */
table th:nth-child(8),
table td:nth-child(8),
table th:nth-child(10),
table td:nth-child(10) {
    display: none; /* Hide the 8th and 10th columns */
}
</style>

<div class="table-responsive">
    <table class="table table-hover table-bordered table-striped">
        <thead>
            <tr>
                <th class="black">Learner's Name</th>               
                <th class="black">Grade & Section</th> <!-- Combined Grade & Section -->
                <th class="black">School Year</th>
                <th class="black">Gender</th>
            </tr>
        </thead>
        <tbody>
        <?php
        // Retrieve filter values from GET parameters
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $school_level = isset($_GET['school_level']) ? $_GET['school_level'] : '';
        $school_year = isset($_GET['school_year']) ? $_GET['school_year'] : '';
        $grade = isset($_GET['grade']) ? $_GET['grade'] : '';
        $section = isset($_GET['section']) ? $_GET['section'] : '';
        $gender = isset($_GET['gender']) ? $_GET['gender'] : '';
        $subject = isset($_GET['subject']) ? $_GET['subject'] : '';
        $quarter = isset($_GET['quarter']) ? $_GET['quarter'] : '';

        // Build the query with dynamic filters
        $query = "SELECT * FROM students WHERE 1=1";

        if ($search) {
            $query .= " AND learners_name LIKE '%$search%'";
        }
        if ($school_level) {
            $query .= " AND school_level = '$school_level'";
        }
        if ($school_year) {
            $query .= " AND school_year = '$school_year'";
        }
        if ($grade) {
            $query .= " AND grade = '$grade'";
        }
        if ($section) {
            $query .= " AND section = '$section'";
        }
        if ($gender) {
            $query .= " AND gender = '$gender'";
        }
        if ($subject) {
            $query .= " AND subject = '$subject'";
        }
        if ($quarter) {
            $query .= " AND quarter = '$quarter'";
        }

        $query .= " ORDER BY id DESC";

        $result = mysqli_query($connection, $query);

        if (!$result) {
            die("Query failed: " . mysqli_error($connection));
        } else {
            while ($row = mysqli_fetch_assoc($result)) {
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['learners_name']); ?></td>                    
                    <td><?php echo htmlspecialchars($row['grade'] . ' ' . $row['section']); ?></td> <!-- Combined Grade & Section -->
                    <td><?php echo htmlspecialchars($row['school_year']); ?></td>
                    <td><?php echo htmlspecialchars($row['gender']); ?></td> <!-- Fixed: Correctly echo gender -->
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
    </table>
</div>



<!-- Modal for Adding Student -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add New Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="insert_data.php" id="addStudentForm" onsubmit="combineNames()">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <div class="name-container">
                            <input type="text" class="form-control name-field" id="last_name" name="last_name" placeholder="Last Name" required>
                            <span class="slash">/</span>
                            <input type="text" class="form-control name-field" id="first_name" name="first_name" placeholder="First Name" required>
                            <span class="slash">/</span>       
                            <input type="text" class="form-control name-field" id="middle_name" name="middle_name" placeholder="Middle Name">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="grade_section" class="form-label">Grade Level & Section</label>
                        <select class="form-control" id="grade_section" name="grade_section" onchange="updateSubjects();" required>
                            <option value="">Select Grade & Section</option>
                            <!-- Combined Grade & Section will be dynamically populated -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="school_year" class="form-label">School Year</label>
                        <select class="form-control" id="school_year" name="school_year" required>
                            <?php 
                            $startYear = 2024;
                            $endYear = 2028;

                            for ($year = $startYear; $year <= $endYear; $year++) {
                                $nextYear = $year + 1;
                                $schoolYear = "{$year}-{$nextYear}";
                                echo "<option value=\"{$schoolYear}\">{$schoolYear}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-control" id="gender" name="gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <select class="form-control" id="subject" name="subject" required>
                            <option value="">Select Subject</option>
                            <!-- Subjects will be dynamically updated -->
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function combineNames() {
    var lastName = document.getElementById('last_name').value.trim();
    var firstName = document.getElementById('first_name').value.trim();
    var middleName = document.getElementById('middle_name').value.trim();
    
    // Combine the names into a single string in the correct order
    var fullName = lastName + ", " + firstName + (middleName ? " " + middleName : '');

    // Set the combined name into a hidden input to send it
    var learnersNameInput = document.createElement('input');
    learnersNameInput.type = 'hidden';
    learnersNameInput.name = 'learners_name';
    learnersNameInput.value = fullName;
    
    document.getElementById('addStudentForm').appendChild(learnersNameInput);
}

document.addEventListener('DOMContentLoaded', function() {
    updateGradeSectionDropdown();
});

function updateGradeSectionDropdown() {
    var gradeSectionDropdown = document.getElementById('grade_section');
    var gradeSections = {
        '7-': ['Gumamela', 'Sampaguita', 'Rose'],
        '8-': ['Narra', 'Yakal'],
        '9-': ['Diamond', 'Sapphire'],
        '10-': ['Jupiter', 'Venus']
    };

    // Clear the existing options
    gradeSectionDropdown.innerHTML = '<option value="">Select Grade & Section</option>';

    // Populate combined grade & section options
    for (var grade in gradeSections) {
        if (gradeSections.hasOwnProperty(grade)) {
            gradeSections[grade].forEach(function(section) {
                var option = document.createElement('option');
                option.value = grade + ' ' + section;  // Combined value like "7th Gumamela"
                option.textContent = grade + ' ' + section;  // Display like "7th Gumamela"
                gradeSectionDropdown.appendChild(option);
            });
        }
    }
}

function updateSubjects() {
    var gradeSection = document.getElementById('grade_section').value;
    var subjectDropdown = document.getElementById('subject');
    var subjects = ['Math', 'Science', 'English', 'Araling Panlipunan',  'Mapeh', 'TLE', 'Filipino', 'ESP', 'Values'];

    // Determine the grade from the combined grade_section value
    var grade = gradeSection.split(' ')[0]; // Extract the grade part ("7th", "8th", etc.)

    // Modify subjects based on selected grade
    if (grade === "7-") {
        subjects = subjects.filter(function(subject) {
            return subject !== 'ESP';  // Remove "ESP" for grade 7
        });
    } else if (grade === "8-" || grade === "9-" || grade === "10-") {
        subjects = subjects.filter(function(subject) {
            return subject !== 'Values';  // Remove "Values" for grades 8-10
        });
    }

    // Clear the existing subject options
    subjectDropdown.innerHTML = '<option value="">Select Subject</option>';

    // Populate the subject dropdown with the updated subjects
    subjects.forEach(function(subject) {
        var option = document.createElement('option');
        option.value = subject;
        option.textContent = subject;
        subjectDropdown.appendChild(option);
    });
}
// AJAX function to check if a student already exists
function checkDuplicateLearner() {
    var lastName = document.getElementById('last_name').value.trim();
    var firstName = document.getElementById('first_name').value.trim();
    var middleName = document.getElementById('middle_name').value.trim();
    var fullName = lastName + ", " + firstName + (middleName ? " " + middleName : '');

    if (fullName) {
        $.ajax({
            url: 'checker.php',
            type: 'GET',
            data: {
                checkDuplicate: true,
                learners_name: fullName
            },
            success: function(response) {
                var data = JSON.parse(response);
                if (data.exists) {
                    alert('Student already exists!');
                    $('#duplicateWarning').show();
                    $('#addStudentButton').attr('disabled', true);
                } else {
                    $('#duplicateWarning').hide();
                    $('#addStudentButton').attr('disabled', false);
                }
            },
            error: function() {
                alert('An error occurred while checking for duplicates.');
            }
        });
    }
}


document.addEventListener('DOMContentLoaded', function() {
    updateGradeSectionDropdown();
    // Attach keyup event for duplicate check
    document.getElementById('last_name').addEventListener('keyup', checkDuplicateLearner);
    document.getElementById('first_name').addEventListener('keyup', checkDuplicateLearner);
    document.getElementById('middle_name').addEventListener('keyup', checkDuplicateLearner);
});
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

