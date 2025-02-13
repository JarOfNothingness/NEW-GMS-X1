<?php
include('../LoginRegisterAuthentication/connection.php');

// Fetch learners for dropdown
$learners_query = "SELECT id, learners_name, gender, grade, section FROM students";
$learners_result = $connection->query($learners_query);

// Fetch subjects for dropdown
$subjects_query = "SELECT id, name FROM subjects";
$subjects_result = $connection->query($subjects_query);

// Set default values
$region = "VII";
$division = "Cebu Province";
$school_id = "303031";

// Initialize variables for error messages
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get learner ID from the dropdown
    $learner_id = $_POST['learner_id'] ?? 0;

    // Fetch selected learner details
    $learner_query = "SELECT learners_name, gender, grade, section FROM students WHERE id = ?";
    $stmt = $connection->prepare($learner_query);
    $stmt->bind_param("i", $learner_id);
    $stmt->execute();
    $learner_data = $stmt->get_result()->fetch_assoc();

    // Prepare form data
    $learners_name = $learner_data['learners_name'] ?? '';
    $gender = $learner_data['gender'] ?? '';
    $grade = $learner_data['grade'] ?? '';
    $section = $learner_data['section'] ?? '';

    // Fetch grades for the selected learner
    $grades_query = "SELECT subject_id, written_exam, performance_task, quarterly_exam, final_grade, remarks FROM student_grades WHERE student_id = ?";
    $stmt = $connection->prepare($grades_query);
    $stmt->bind_param("i", $learner_id);
    $stmt->execute();
    $grades_result = $stmt->get_result();

    // Prepare remarks based on grades
    $remarks = [];
    while ($grade_data = $grades_result->fetch_assoc()) {
        $subject_name_query = "SELECT name FROM subjects WHERE id = ?";
        $subject_stmt = $connection->prepare($subject_name_query);
        $subject_stmt->bind_param("i", $grade_data['subject_id']);
        $subject_stmt->execute();
        $subject_name = $subject_stmt->get_result()->fetch_assoc()['name'];

        // Determine remarks based on the final grade
        $final_grade = $grade_data['final_grade'];
        if ($final_grade >= 75) {
            $remarks[$subject_name] = 'Passed';
        } else {
            $remarks[$subject_name] = 'Failed';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Form 137</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header img {
            width: 80px; /* Adjust as needed */
        }
        .title {
            text-align: center;
            font-size: 24px;
            margin: 20px 0;
            font-weight: bold;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #5cb85c;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #4cae4c;
        }
        .form-output {
            margin-top: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: #e9ecef;
        }
        .output-item {
            margin-bottom: 10px;
            font-size: 16px;
        }
        .error-message {
            color: red;
        }
        .success-message {
            color: green;
        }
        @media print {
            button {
                display: none; /* Hide the button when printing */
            }
        }
    </style>
</head>
<body>

<div class="header">
    <img src="./images/Logo.png" alt="Logo Left">
    <h2 class="title">Form 137</h2>
    <img src="./images/depedlogo.png" alt="Logo Right">
</div>

<div class="container">
    <form action="add_form137.php" method="POST">
        <label for="learner_id">Learner's Name:</label>
        <select id="learner_id" name="learner_id" required onchange="this.form.submit()">
            <option value="">Select a learner</option>
            <?php while ($row = $learners_result->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= $row['learners_name'] ?></option>
            <?php endwhile; ?>
        </select>

        <input type="hidden" name="region" value="<?= $region ?>">
        <input type="hidden" name="division" value="<?= $division ?>">
        <input type="hidden" name="school_id" value="<?= $school_id ?>">

        <label>Gender:</label>
        <input type="text" name="gender" value="<?= isset($gender) ? $gender : '' ?>" readonly>

        <label>Grade:</label>
        <input type="text" name="grade" value="<?= isset($grade) ? $grade : '' ?>" readonly>

        <label>Section:</label>
        <input type="text" name="section" value="<?= isset($section) ? $section : '' ?>" readonly>

        <label for="subject_id">Subject:</label>
        <select id="subject_id" name="subject_id" required>
            <option value="">Select a subject</option>
            <?php while ($row = $subjects_result->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Submit</button>
    </form>

    <?php if ($error_message): ?>
        <div class="error-message"><?= $error_message ?></div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="success-message"><?= $success_message ?></div>
    <?php endif; ?>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($learners_name)): ?>
        <div class="form-output">
            <h3>Form 137 Output</h3>
            <div class="output-item"><strong>Learner's Name:</strong> <?= $learners_name ?></div>
            <div class="output-item"><strong>Region:</strong> <?= $region ?></div>
            <div class="output-item"><strong>Division:</strong> <?= $division ?></div>
            <div class="output-item"><strong>School ID:</strong> <?= $school_id ?></div>
            <div class="output-item"><strong>Gender:</strong> <?= $gender ?></div>
            <div class="output-item"><strong>Grade:</strong> <?= $grade ?></div>
            <div class="output-item"><strong>Section:</strong> <?= $section ?></div>
            <div class="output-item"><strong>Grades:</strong></div>
            <?php if (empty($remarks)): ?>
                <div class="output-item">No grades available.</div>
            <?php else: ?>
                <?php foreach ($remarks as $subject => $remark): ?>
                    <div class="output-item"><strong><?= $subject ?>:</strong> <?= $remark ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
