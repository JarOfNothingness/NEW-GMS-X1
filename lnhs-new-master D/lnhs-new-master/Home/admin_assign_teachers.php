<?php
include("../LoginRegisterAuthentication/connection.php");
include("headeradmin.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teacher_id = $_POST['teacher_id'];
    $student_id = $_POST['student_id'];

    // Fetch all subjects and sections the student is enrolled in from the 'enrollments' table
    $sql_enrollments = "SELECT subject, section FROM enrollments WHERE learners_name = 
                        (SELECT learners_name FROM students WHERE id = ?)";
    $stmt_enrollments = $connection->prepare($sql_enrollments);
    $stmt_enrollments->bind_param("i", $student_id);
    $stmt_enrollments->execute();
    $result_enrollments = $stmt_enrollments->get_result();

    if ($result_enrollments->num_rows > 0) {
        // Assign the teacher to the student and all subjects and sections from enrollments
        while ($row = $result_enrollments->fetch_assoc()) {
            $subject_name = $row['subject'];
            $section = $row['section'];

            // Get the subject ID based on the subject name from the 'subjects' table
            $sql_subject = "SELECT id FROM subjects WHERE name = ?";
            $stmt_subject = $connection->prepare($sql_subject);
            $stmt_subject->bind_param("s", $subject_name);
            $stmt_subject->execute();
            $result_subject = $stmt_subject->get_result();

            if ($result_subject->num_rows > 0) {
                $subject_row = $result_subject->fetch_assoc();
                $subject_id = $subject_row['id'];

                // Insert assignment into 'teacher_assignments' with teacher_id, student_id, subject_id, and section
                $sql_assign = "INSERT INTO teacher_assignments (teacher_id, student_id, subject_id, section) 
                               VALUES (?, ?, ?, ?)";
                $stmt_assign = $connection->prepare($sql_assign);
                $stmt_assign->bind_param("iiis", $teacher_id, $student_id, $subject_id, $section);
                $stmt_assign->execute();
                $stmt_assign->close();
            }
        }
        echo "<div class='alert alert-success'>Assignment successful for all enrolled subjects and sections.</div>";
    } else {
        echo "<div class='alert alert-danger'>This student has no enrolled subjects or sections.</div>";
    }

    $stmt_enrollments->close();
}

// Fetch teachers and students
$teachers = $connection->query("SELECT userid, name FROM user WHERE role = 'teacher'");
$students = $connection->query("SELECT id AS student_id, learners_name AS name FROM students");

if (!$teachers || !$students) {
    die("Error: " . $connection->error);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Teachers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
        }

        .form-label {
            font-weight: bold;
        }

        .btn-assign {
            margin-top: 20px;
        }

        .card {
            margin-top: 30px;
        }

        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3>Assign Teachers to Students, Subjects, and Sections</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="teacher_id" class="form-label">Select Teacher:</label>
                        <select id="teacher_id" name="teacher_id" class="form-select" required>
                            <option value="" disabled selected>Select a Teacher</option>
                            <?php while ($row = $teachers->fetch_assoc()) { ?>
                                <option value="<?php echo htmlspecialchars($row['userid']); ?>">
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="student_id" class="form-label">Select Student:</label>
                        <select id="student_id" name="student_id" class="form-select" required>
                            <option value="" disabled selected>Select a Student</option>
                            <?php while ($row = $students->fetch_assoc()) { ?>
                                <option value="<?php echo htmlspecialchars($row['student_id']); ?>">
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-assign">Assign</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
