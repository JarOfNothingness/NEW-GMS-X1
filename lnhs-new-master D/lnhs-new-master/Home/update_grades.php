<?php 
include('../crud/header.php'); 
include("../LoginRegisterAuthentication/connection.php");

// Ensure that an ID is provided in the URL
if (isset($_GET['id'])) {
    $grade_id = intval($_GET['id']);

    // Fetch the existing grade record including the subject name
    $query = "SELECT sg.*, s.learners_name, sub.name as subject_name 
              FROM student_grades sg
              JOIN students s ON sg.student_id = s.id
              JOIN subjects sub ON sg.subject_id = sub.id 
              WHERE sg.id = $grade_id";
    $result = mysqli_query($connection, $query);
    if (!$result || mysqli_num_rows($result) == 0) {
        die("Record not found or query failed: " . mysqli_error($connection));
    }

    $row = mysqli_fetch_assoc($result);

    // Fetch all students and subjects for the dropdowns
    $students_query = "SELECT id, learners_name FROM students";
    $students_result = mysqli_query($connection, $students_query);

    $subjects_query = "SELECT id, name FROM subjects";
    $subjects_result = mysqli_query($connection, $subjects_query);
} else {
    die("No ID provided.");
}

// Handle the update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_grade'])) {
    $student_id = intval($_POST['student_id']);
    $subject_id = intval($_POST['subject_id']);
    $written_exam = floatval($_POST['written_exam']);
    $performance_task = floatval($_POST['performance_task']);
    $quarterly_exam = floatval($_POST['quarterly_exam']);

    // Define subject-specific weights
    $subject_weights = [
        'English' => ['written_exam' => 0.30, 'performance_task' => 0.50, 'quarterly_exam' => 0.20],
        'Math' => ['written_exam' => 0.40, 'performance_task' => 0.40, 'quarterly_exam' => 0.20],
        'Science' => ['written_exam' => 0.40, 'performance_task' => 0.40, 'quarterly_exam' => 0.20],
        'Filipino' => ['written_exam' => 0.30, 'performance_task' => 0.50, 'quarterly_exam' => 0.20],
        // Add other subjects as needed
    ];

    // Determine the subject name
    $subject_query = "SELECT name FROM subjects WHERE id = $subject_id";
    $subject_result = mysqli_query($connection, $subject_query);
    if ($subject_result && mysqli_num_rows($subject_result) > 0) {
        $subject_name = mysqli_fetch_assoc($subject_result)['name'];

        // Fetch weights for the selected subject
        if (isset($subject_weights[$subject_name])) {
            $weights = $subject_weights[$subject_name];
        } else {
            die("Subject weights not defined for $subject_name.");
        }
    } else {
        die("Subject not found.");
    }

    // Calculate final grade with subject-specific weights
    $final_grade = ($written_exam * $weights['written_exam']) +
                    ($performance_task * $weights['performance_task']) +
                    ($quarterly_exam * $weights['quarterly_exam']);

    // Sanitize final grade
    $final_grade = mysqli_real_escape_string($connection, number_format($final_grade, 2));

    // Update query
    $update_query = "UPDATE student_grades SET 
                     student_id = $student_id,
                     subject_id = $subject_id,
                     written_exam = '$written_exam',
                     performance_task = '$performance_task',
                     quarterly_exam = '$quarterly_exam',
                     final_grade = '$final_grade'
                     WHERE id = $grade_id";

    if (mysqli_query($connection, $update_query)) {
        echo "<div class='alert alert-success'>Grade record updated successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating record: " . mysqli_error($connection) . "</div>";
    }
}
?>

<div class="container mt-5">
    <h2>Update Grade Record</h2>

    <!-- Update Form -->
    <form method="POST" action="">
        <div class="form-group">
            <label for="student_id">Student:</label>
            <select name="student_id" id="student_id" class="form-control" required>
                <option value="">Select Student</option>
                <?php while ($student = mysqli_fetch_assoc($students_result)) { ?>
                    <option value="<?php echo htmlspecialchars($student['id']); ?>" 
                        <?php echo $student['id'] == $row['student_id'] ? 'selected' : ''; ?> >
                        <?php echo htmlspecialchars($student['learners_name']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="subject_id">Subject:</label>
            <select name="subject_id" id="subject_id" class="form-control" required>
                <option value="">Select Subject</option>
                <?php while ($subject = mysqli_fetch_assoc($subjects_result)) { ?>
                    <option value="<?php echo htmlspecialchars($subject['id']); ?>" 
                        <?php echo $subject['id'] == $row['subject_id'] ? 'selected' : ''; ?> >
                        <?php echo htmlspecialchars($subject['name']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="written_exam">Written Exam:</label>
            <input type="number" name="written_exam" id="written_exam" class="form-control" step="0.01" required 
                   value="<?php echo htmlspecialchars($row['written_exam']); ?>" 
                   oninput="calculateFinalGrade()">
        </div>
        <div class="form-group">
            <label for="performance_task">Performance Task:</label>
            <input type="number" name="performance_task" id="performance_task" class="form-control" step="0.01" required 
                   value="<?php echo htmlspecialchars($row['performance_task']); ?>" 
                   oninput="calculateFinalGrade()">
        </div>
        <div class="form-group">
            <label for="quarterly_exam">Quarterly Exam:</label>
            <input type="number" name="quarterly_exam" id="quarterly_exam" class="form-control" step="0.01" required 
                   value="<?php echo htmlspecialchars($row['quarterly_exam']); ?>" 
                   oninput="calculateFinalGrade()">
        </div>
        <div class="form-group">
            <label for="final_grade">Final Grade:</label>
            <input type="number" name="final_grade" id="final_grade" class="form-control" step="0.01" required readonly
                   value="<?php echo htmlspecialchars($row['final_grade']); ?>">
        </div>
        <input type="submit" name="update_grade" class="btn btn-primary" value="Update Record">
    </form>

    <!-- Go Back to Class Record Link -->
    <div class="mt-3">
        <a href="add_grade.php?student_id=<?php echo htmlspecialchars($row['student_id']); ?>&subject_id=<?php echo htmlspecialchars($row['subject_id']); ?>" class="btn btn-success">Add New Grade</a>
        <a href="FinalGrade.php?student_id=<?php echo htmlspecialchars($row['student_id']); ?>" class="btn btn-secondary">Back</a>
    </div>
</div>

<script>
function calculateFinalGrade() {
    var writtenExam = parseFloat(document.getElementById('written_exam').value) || 0;
    var performanceTask = parseFloat(document.getElementById('performance_task').value) || 0;
    var quarterlyExam = parseFloat(document.getElementById('quarterly_exam').value) || 0;

    // Define weights dynamically for the selected subject
    var weights = <?php echo json_encode($subject_weights[$row['subject_name']]); ?>;

    var finalGrade = (writtenExam * weights['written_exam']) +
                     (performanceTask * weights['performance_task']) +
                     (quarterlyExam * weights['quarterly_exam']);

    document.getElementById('final_grade').value = finalGrade.toFixed(2);
}
</script>

<?php include('../crud/footer.php'); ?>
