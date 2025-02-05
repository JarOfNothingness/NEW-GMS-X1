<?php
session_start();

if (!isset($_SESSION['username']) || !isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

include('header.php'); 
include("../LoginRegisterAuthentication/connection.php"); 

$userid = $_SESSION['userid'];

if (empty($userid)) {
    echo "User ID is not set. Session userid: " . htmlspecialchars($userid);
    exit();
}

// Fetch unique values for dropdowns
$studentsQuery = "SELECT DISTINCT s.learners_name FROM students s WHERE s.user_id = ?";
$subjectsQuery = "SELECT DISTINCT ss.description FROM student_subjects ss JOIN students s ON ss.student_id = s.id WHERE s.user_id = ?";
$sectionsQuery = "SELECT DISTINCT s.`grade & section` FROM students s WHERE s.user_id = ?";
$schoolYearsQuery = "SELECT DISTINCT s.school_year FROM students s WHERE s.user_id = ?";

// Prepare and execute statements for dropdowns
$stmt = mysqli_prepare($connection, $studentsQuery);
mysqli_stmt_bind_param($stmt, 'i', $userid);
mysqli_stmt_execute($stmt);
$studentsResult = mysqli_stmt_get_result($stmt);

$stmt = mysqli_prepare($connection, $subjectsQuery);
mysqli_stmt_bind_param($stmt, 'i', $userid);
mysqli_stmt_execute($stmt);
$subjectsResult = mysqli_stmt_get_result($stmt);

$stmt = mysqli_prepare($connection, $sectionsQuery);
mysqli_stmt_bind_param($stmt, 'i', $userid);
mysqli_stmt_execute($stmt);
$sectionsResult = mysqli_stmt_get_result($stmt);

$stmt = mysqli_prepare($connection, $schoolYearsQuery);
mysqli_stmt_bind_param($stmt, 'i', $userid);
mysqli_stmt_execute($stmt);
$schoolYearsResult = mysqli_stmt_get_result($stmt);

// Capture selected filters from GET request
$selectedStudentName = isset($_GET['learners_name']) ? mysqli_real_escape_string($connection, $_GET['learners_name']) : '';
$selectedSubject = isset($_GET['subject']) ? mysqli_real_escape_string($connection, $_GET['subject']) : '';
$selectedSection = isset($_GET['section']) ? mysqli_real_escape_string($connection, $_GET['section']) : '';
$selectedSchoolYear = isset($_GET['school_year']) ? mysqli_real_escape_string($connection, $_GET['school_year']) : '';
$searchQuery = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';

// Build query based on selected filters
$enrollmentsQuery = "
    SELECT DISTINCT s.id,s.learners_name, ss.description AS subject, s.`grade & section`, s.school_year, s.gender 
    FROM students s
    JOIN student_subjects ss ON s.id = ss.student_id
    WHERE s.user_id = ?";

$queryParams = array($userid);

if ($selectedStudentName) {
    $enrollmentsQuery .= " AND s.learners_name = ?";
    $queryParams[] = $selectedStudentName;
}
if ($selectedSubject) {
    $enrollmentsQuery .= " AND ss.description = ?";
    $queryParams[] = $selectedSubject;
}
if ($selectedSection) {
    $enrollmentsQuery .= " AND s.`grade & section` = ?";
    $queryParams[] = $selectedSection;
}
if ($selectedSchoolYear) {
    $enrollmentsQuery .= " AND s.school_year = ?";
    $queryParams[] = $selectedSchoolYear;
}
if ($searchQuery) {
    $enrollmentsQuery .= " AND (s.learners_name LIKE ? OR ss.description LIKE ?)";
    $queryParams[] = "%$searchQuery%";
    $queryParams[] = "%$searchQuery%";
}

// Prepare and execute the enrollment query
$stmt = mysqli_prepare($connection, $enrollmentsQuery);
mysqli_stmt_bind_param($stmt, str_repeat('s', count($queryParams)), ...$queryParams);
mysqli_stmt_execute($stmt);
$enrollmentsResult = mysqli_stmt_get_result($stmt);

if (!$enrollmentsResult) {
    die('Error: ' . mysqli_error($connection));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Enrollment Records</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
        }
        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-end;
            margin-bottom: 15px;
        }
        .filter-item {
            flex: 1;
            min-width: 200px;
        }
        .filter-item label {
            margin-bottom: 5px;
            display: block;
        }
        .search-form {
            flex: 2;
            min-width: 300px;
        }
        .table-enrollment {
            width: 100%;
            background-color: #0A4D68;
            color: white;
        }
        .table-enrollment thead th {
            background-color: #16657f;
            color: white;
        }
        .table-enrollment tbody td {
            font-family: Arial, sans-serif;
        }
        .btn-filter {
            height: 38px; /* Match the height of the dropdowns */
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center">Student's Subjects</h2>
        <a href="Crud.php" class="btn btn-secondary mb-3">Back</a>
        <form method="GET" action="studentsubjectsrecords.php" class="mb-3">
            <div class="filter-container">

                <div class="filter-item">
                    <label for="subject">Subject:</label>
                    <select class="form-control" id="subject" name="subject">
                        <option value="">All Subjects</option>
                        <?php while ($subject = mysqli_fetch_assoc($subjectsResult)): ?>
                            <option value="<?php echo htmlspecialchars($subject['description']); ?>" <?php if ($selectedSubject == $subject['description']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($subject['description']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="filter-item">
                    <label for="section">Grade & Section:</label>
                    <select class="form-control" id="section" name="section">
                        <option value="">All Grade & Section</option>
                        <?php while ($section = mysqli_fetch_assoc($sectionsResult)): ?>
                            <option value="<?php echo htmlspecialchars($section['grade & section']); ?>" <?php if ($selectedSection == $section['grade & section']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($section['grade & section']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="filter-item">
                    <label for="school_year">School Year:</label>
                    <select class="form-control" id="school_year" name="school_year">
                        <option value="">All School Years</option>
                        <?php while ($schoolYear = mysqli_fetch_assoc($schoolYearsResult)): ?>
                            <option value="<?php echo htmlspecialchars($schoolYear['school_year']); ?>" <?php if ($selectedSchoolYear == $schoolYear['school_year']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($schoolYear['school_year']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="filter-item search-form">
                    <label for="search">Search:</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                </div>
                <div class="filter-item">
                    <button type="submit" class="btn btn-primary btn-filter">Apply Filters</button>
                    <a href="studentsubjectsrecords.php" class="btn btn-secondary btn-filter">Reset</a>
                </div>
                <div class="filter-item">
                    
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table-enrollment table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Gender</th>
                        <th>Subject</th>
                        <th>Section</th>
                        <th>School Year</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count=1; while ($enrollment = mysqli_fetch_assoc($enrollmentsResult)): ?>
                        <tr>
                            <td>
                                <?php echo $count++ ?>
                            </td>
                            <td><?php echo htmlspecialchars($enrollment['learners_name']); ?></td>
                            <td><?php echo htmlspecialchars($enrollment['gender']); ?></td>
                            <td><?php echo htmlspecialchars($enrollment['subject']); ?></td>
                            <td><?php echo htmlspecialchars($enrollment['grade & section']); ?></td>
                            <td><?php echo htmlspecialchars($enrollment['school_year']); ?></td>
                            
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>