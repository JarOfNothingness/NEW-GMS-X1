<?php
session_start();
include_once("../LoginRegisterAuthentication/connection.php");
include_once("functions.php");

// Check if user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Check if teacher ID is provided
if (!isset($_GET['id'])) {
    die("Teacher ID not provided");
}

$teacherId = mysqli_real_escape_string($connection, $_GET['id']);

// Fetch teacher details
$teacherQuery = "SELECT * FROM user WHERE userid = $teacherId AND role = 'Teacher'";
$teacherResult = mysqli_query($connection, $teacherQuery);
$teacherData = mysqli_fetch_assoc($teacherResult);

if (!$teacherData) {
    die("Teacher not found");
}

// Fetch subjects and sections handled by the teacher
$assignmentsQuery = "
    SELECT DISTINCT ss.description as subject, st.`grade & section` as section
    FROM student_quiz sq
    JOIN students st ON sq.student_id = st.id
    JOIN student_subjects ss ON sq.student_id = ss.student_id
    WHERE sq.user_id = $teacherId
    ORDER BY ss.description, st.`grade & section`
";
$assignmentsResult = mysqli_query($connection, $assignmentsQuery);

if (!$assignmentsResult) {
    die("Error fetching assignments: " . mysqli_error($connection));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Teacher Details</h1>
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title"><?php echo htmlspecialchars($teacherData['name']); ?></h2>
                <p class="card-text"><strong>Username:</strong> <?php echo htmlspecialchars($teacherData['username']); ?></p>
                <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($teacherData['address']); ?></p>
                <p class="card-text"><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($teacherData['status'])); ?></p>
            </div>
        </div>

        <h3>Subjects and Sections Handled</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Section</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $currentSubject = '';
                $currentSections = array();
                while ($row = mysqli_fetch_assoc($assignmentsResult)) {
                    if ($currentSubject != $row['subject']) {
                        if (!empty($currentSubject)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($currentSubject) . "</td>";
                            echo "<td>" . htmlspecialchars(implode(", ", $currentSections)) . "</td>";
                            echo "</tr>";
                        }
                        $currentSubject = $row['subject'];
                        $currentSections = array($row['section']);
                    } else {
                        if (!in_array($row['section'], $currentSections)) {
                            $currentSections[] = $row['section'];
                        }
                    }
                }
                // Output the last subject
                if (!empty($currentSubject)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($currentSubject) . "</td>";
                    echo "<td>" . htmlspecialchars(implode(", ", $currentSections)) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <a href="manage_user.php" class="btn btn-primary">Back to User Management</a>
    </div>
</body>
</html>