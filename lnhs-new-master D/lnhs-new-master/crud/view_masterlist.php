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
            s.user_id = ?"; 
            // AND s.school_year = ? 
            // AND s.`grade & section` = ?"; // Keep only 3 placeholders

// Adjust the bind_param to match the correct number of arguments
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 's', $userid); // Bind only 3 variables
mysqli_stmt_execute($stmt);
$studentResult = mysqli_stmt_get_result($stmt);


// Query to fetch student data for the current user, removing duplicates by grouping by learner's name, grade & section, quarter, and subject
$isFilterApplied = isset($_GET['subject']) && $_GET['subject'] !== '' ||
                   isset($_GET['section']) && $_GET['section'] !== '' ||
                   isset($_GET['school_year']) && $_GET['school_year'] !== '' ||
                   isset($_GET['search']) && $_GET['search'] !== '';

// Only process the query if filters are applied
if ($isFilterApplied) {
    $query = "SELECT 
        s.id,
        s.learners_name, 
        s.`grade & section`, 
        s.gender, 
        s.school_year,
        GROUP_CONCAT(IFNULL(ss.description, '') SEPARATOR ', ') AS subjects
    FROM 
        students s
    LEFT JOIN 
        student_subjects ss ON s.id = ss.student_id
    WHERE 
        s.user_id = ?";

    $queryParams = array($userid);

    // Apply filters
    if (isset($_GET['subject']) && $_GET['subject'] !== '') {
        $query .= " AND ss.description = ?";
        $queryParams[] = $_GET['subject'];
    }

    if (isset($_GET['section']) && $_GET['section'] !== '') {
        $query .= " AND s.`grade & section` = ?";
        $queryParams[] = $_GET['section'];
    }

    if (isset($_GET['school_year']) && $_GET['school_year'] !== '') {
        $query .= " AND s.school_year = ?";
        $queryParams[] = $_GET['school_year'];
    }

    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $query .= " AND (s.learners_name LIKE ? OR ss.description LIKE ?)";
        $searchTerm = '%' . $_GET['search'] . '%';
        $queryParams[] = $searchTerm;
        $queryParams[] = $searchTerm;
    }

    $query .= " GROUP BY 
        s.id,
        s.learners_name, 
        s.`grade & section`, 
        s.gender, 
        s.school_year";

    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, str_repeat('s', count($queryParams)), ...$queryParams);
    mysqli_stmt_execute($stmt);
    $studentResult = mysqli_stmt_get_result($stmt);
}
?>

<div class="container mt-4">
    <h3 class="text-dark">Student Records</h3>

    <!-- Display selected filters (Grade & Section and School Year) dynamically -->
    <form method="GET" action="" class="mb-3">
        <div class="d-flex flex-wrap align-items-end gap-2">
            <div class="flex-grow-1">
                <label for="school_year" class="form-label">School Year:</label>
                <select class="form-select" id="school_year" name="school_year">
                    <option value="">All School Years</option>
                    <?php 
                    $schoolYearsQuery = "SELECT DISTINCT school_year FROM students WHERE user_id = ? ORDER BY school_year DESC";
                    $stmt = mysqli_prepare($connection, $schoolYearsQuery);
                    mysqli_stmt_bind_param($stmt, 'i', $userid);
                    mysqli_stmt_execute($stmt);
                    $schoolYearsResult = mysqli_stmt_get_result($stmt);
                    while ($year = mysqli_fetch_assoc($schoolYearsResult)):
                        $selected = (isset($_GET['school_year']) && $_GET['school_year'] == $year['school_year']) ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars($year['school_year']) ?>" <?= $selected ?>>
                            <?= htmlspecialchars($year['school_year']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="flex-grow-1">
                <label for="section" class="form-label">Grade & Section:</label>
                <select class="form-select" id="section" name="section">
                    <option value="">All Grade & Section</option>
                    <?php 
                    $sectionsQuery = "SELECT DISTINCT `grade & section` FROM students WHERE user_id = ? ORDER BY `grade & section`";
                    $stmt = mysqli_prepare($connection, $sectionsQuery);
                    mysqli_stmt_bind_param($stmt, 'i', $userid);
                    mysqli_stmt_execute($stmt);
                    $sectionsResult = mysqli_stmt_get_result($stmt);
                    while ($section = mysqli_fetch_assoc($sectionsResult)):
                        $selected = (isset($_GET['section']) && $_GET['section'] == $section['grade & section']) ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars($section['grade & section']) ?>" <?= $selected ?>>
                            <?= htmlspecialchars($section['grade & section']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="flex-grow-1">
                <label for="subject" class="form-label">Subject:</label>
                <select class="form-select" id="subject" name="subject">
                    <option value="">All Subjects</option>
                    <?php 
                    $subjectsQuery = "SELECT DISTINCT description FROM student_subjects WHERE student_id IN (SELECT id FROM students WHERE user_id = ?) ORDER BY description";
                    $stmt = mysqli_prepare($connection, $subjectsQuery);
                    mysqli_stmt_bind_param($stmt, 'i', $userid);
                    mysqli_stmt_execute($stmt);
                    $subjectsResult = mysqli_stmt_get_result($stmt);
                    while ($subject = mysqli_fetch_assoc($subjectsResult)):
                        $selected = (isset($_GET['subject']) && $_GET['subject'] == $subject['description']) ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars($subject['description']) ?>" <?= $selected ?>>
                            <?= htmlspecialchars($subject['description']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="view_masterlist.php" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>
    <!-- <div class="row mb-3">
        <div class="col-md-6">
            <label for="gradeSection">Grade & Section:</label>
            <input type="text" class="form-control" id="gradeSection" value="<?= htmlspecialchars($gradeSection); ?>" disabled>
        </div>
        <div class="col-md-6">
            <label for="schoolYear">School Year:</label>
            <input type="text" class="form-control" id="schoolYear" value="<?= htmlspecialchars($schoolyear); ?>" disabled>
        </div>
    </div> -->

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
