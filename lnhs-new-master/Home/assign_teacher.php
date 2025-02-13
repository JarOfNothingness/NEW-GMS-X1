<?php
// Form to assign a teacher to a subject
// Retrieve available teachers and subjects from the database
$teachers = mysqli_query($connection, "SELECT userid, name FROM user WHERE role = 'teacher'");
$subjects = mysqli_query($connection, "SELECT id, name FROM subjects");

if ($_POST['assign_teacher']) {
    $teacher_id = $_POST['teacher_id'];
    $subject_id = $_POST['subject_id'];
    $masterlist_id = $_POST['masterlist_id']; // If using masterlist

    // Insert assignment into the table
    mysqli_query($connection, "INSERT INTO teacher_subject_assignment (teacher_id, subject_id, masterlist_id) VALUES ('$teacher_id', '$subject_id', '$masterlist_id')");
    echo "Teacher assigned successfully.";
}
?>
<form method="POST" action="">
    <label for="teacher">Select Teacher:</label>
    <select name="teacher_id">
        <?php while ($teacher = mysqli_fetch_assoc($teachers)) { ?>
            <option value="<?= $teacher['userid']; ?>"><?= $teacher['name']; ?></option>
        <?php } ?>
    </select>

    <label for="subject">Select Subject:</label>
    <select name="subject_id">
        <?php while ($subject = mysqli_fetch_assoc($subjects)) { ?>
            <option value="<?= $subject['id']; ?>"><?= $subject['name']; ?></option>
        <?php } ?>
    </select>

    <button type="submit" name="assign_teacher">Assign Teacher</button>
</form>
