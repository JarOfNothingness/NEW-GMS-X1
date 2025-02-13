<?php
include_once("../LoginRegisterAuthentication/connection.php");

// Fetch teachers and their subjects
$query = "
    SELECT u.userid, u.name AS teacher_name, 
           GROUP_CONCAT(DISTINCT ss.description ORDER BY ss.description ASC SEPARATOR ', ') AS subjects,
           COUNT(DISTINCT ss.description) AS subject_count
    FROM user u
    LEFT JOIN student_quiz sq ON u.userid = sq.user_id
    LEFT JOIN student_subjects ss ON sq.student_id = ss.student_id
    WHERE u.role = 'Teacher' 
    AND u.status = 'approved'
    GROUP BY u.userid, u.name
    ORDER BY u.name ASC
";

$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}

$totalTeachers = mysqli_num_rows($result);
$allSubjects = array();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Details</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
</head>
<body>

<div class="mb-4">
    <h4>Summary</h4>
    <p>Total Teachers: <strong><?php echo $totalTeachers; ?></strong></p>
    <p>Total Subjects: <strong id="totalSubjects">Calculating...</strong></p>
</div>

<table id="teacherTable" class="display">
    <thead>
        <tr>
            <th>Teacher Name</th>
            <th>Subjects</th>
            <th>Total Subjects</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
                <td>
                    <?php 
                    if (!empty($row['subjects'])) {
                        echo htmlspecialchars($row['subjects']);
                        $teacherSubjects = explode(', ', $row['subjects']);
                        $allSubjects = array_merge($allSubjects, $teacherSubjects);
                    } else {
                        echo 'No subjects assigned';
                    }
                    ?>
                </td>
                <td><?php echo $row['subject_count']; ?></td>
                <td>
                    <a href="view_class_record.php?teacher_id=<?php echo $row['userid']; ?>" class="btn btn-primary btn-sm">View Class Record</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#teacherTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
        });

        // Count unique subjects
        var uniqueSubjects = <?php echo json_encode(array_unique($allSubjects)); ?>;
        document.getElementById('totalSubjects').textContent = uniqueSubjects.length;
    });
</script>

</body>
</html>