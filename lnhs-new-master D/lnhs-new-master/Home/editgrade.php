<?php
session_start();
include("../LoginRegisterAuthentication/connection.php");

$message = '';

// Fetch grade details for the given ID
if (isset($_GET['id'])) {
    $grade_id = $_GET['id'];
    $grade_query = "SELECT * FROM student_grades WHERE id = $grade_id";
    $grade_result = mysqli_query($connection, $grade_query);
    $grade_data = mysqli_fetch_assoc($grade_result);
}

// Handle the edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_grade'])) {
    $student_id = $_POST['student_id'] ?? '';
    $subject_id = $_POST['subject_id'] ?? '';
    $quiz_values = [];
    $performance_tasks = [];
    
    for ($i = 1; $i <= 10; $i++) {
        $quiz_values[] = floatval($_POST["quiz$i"] ?? 0);
        $performance_tasks[] = floatval($_POST["act$i"] ?? 0);
    }

    $quarterly_exam = floatval($_POST['quarterly_exam'] ?? 0);
    $written_scores_total = array_sum($quiz_values);
    $performance_task_total = array_sum($performance_tasks);
    $final_grade = ($written_scores_total * 0.30) + ($performance_task_total * 0.50) + ($quarterly_exam * 0.20);

    // Update the grade in the database
    $update_query = "UPDATE student_grades SET 
                     student_id = $student_id,
                     subject_id = $subject_id,
                     quiz1 = $quiz_values[0], quiz2 = $quiz_values[1], quiz3 = $quiz_values[2], 
                     quiz4 = $quiz_values[3], quiz5 = $quiz_values[4], quiz6 = $quiz_values[5], 
                     quiz7 = $quiz_values[6], quiz8 = $quiz_values[7], quiz9 = $quiz_values[8], 
                     quiz10 = $quiz_values[9], 
                     written_scores_total = $written_scores_total,
                     performance_task_total = $performance_task_total,
                     quarterly_exam = $quarterly_exam,
                     final_grade = $final_grade 
                     WHERE id = $grade_id";

    if (mysqli_query($connection, $update_query)) {
        // Redirect to add_grade.php with a success message
        header("Location: add_grade.php?message=Grade updated successfully");
        exit(); // Ensure no further code is executed
    } else {
        $message = "<div class='alert alert-danger'>Error: " . mysqli_error($connection) . "</div>";
    }
}

// Fetch students and subjects for the select inputs
$students_query = "SELECT id, learners_name FROM students";
$students_result = mysqli_query($connection, $students_query);

$subjects_query = "SELECT id, name FROM subjects";
$subjects_result = mysqli_query($connection, $subjects_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Grade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Edit Grade</h2>
    <?php echo $message; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="student_id">Student:</label>
            <select name="student_id" id="student_id" class="form-control">
                <option value="">Select Student</option>
                <?php while ($student = mysqli_fetch_assoc($students_result)) { ?>
                    <option value="<?php echo $student['id']; ?>" <?php echo ($student['id'] == $grade_data['student_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($student['learners_name']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group">
            <label for="subject_id">Subject:</label>
            <select name="subject_id" id="subject_id" class="form-control">
                <option value="">Select Subject</option>
                <?php while ($subject = mysqli_fetch_assoc($subjects_result)) { ?>
                    <option value="<?php echo $subject['id']; ?>" <?php echo ($subject['id'] == $grade_data['subject_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($subject['name']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <!-- Quiz Inputs -->
        <h4>Quizzes</h4>
        <?php for ($i = 1; $i <= 10; $i++) { ?>
            <div class="form-group">
                <label for="quiz<?php echo $i; ?>">Quiz <?php echo $i; ?>:</label>
                <input type="number" name="quiz<?php echo $i; ?>" id="quiz<?php echo $i; ?>" class="form-control" value="<?php echo htmlspecialchars($grade_data["quiz$i"]); ?>">
            </div>
        <?php } ?>

        <!-- Performance Task Inputs -->
        <h4>Performance Tasks</h4>
        <?php for ($i = 1; $i <= 10; $i++) { ?>
            <div class="form-group">
                <label for="act<?php echo $i; ?>">Act <?php echo $i; ?>:</label>
                <input type="number" name="act<?php echo $i; ?>" id="act<?php echo $i; ?>" class="form-control" value="<?php echo htmlspecialchars($grade_data["act$i"]); ?>">
            </div>
        <?php } ?>

        <div class="form-group">
            <label for="quarterly_exam">Quarterly Exam:</label>
            <input type="number" name="quarterly_exam" id="quarterly_exam" class="form-control" value="<?php echo htmlspecialchars($grade_data['quarterly_exam']); ?>">
        </div>

        <button type="submit" name="edit_grade" class="btn btn-primary">Save Changes</button>
    </form>
</div>

</body>
</html>
