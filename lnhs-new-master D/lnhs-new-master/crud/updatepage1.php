<?php
session_start();

ob_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('../Home/headeradmin.php');
include("../LoginRegisterAuthentication/connection.php");

$row = [];

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connection, $_GET['id']);

    // Execute the SELECT query
    $query = "SELECT * FROM students WHERE id = '$id'";
    $result = mysqli_query($connection, $query);

    if (!$result) {
        die("Query failed: " . mysqli_error($connection));
    } else {
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
        } else {
            echo "No records found for ID: $id";
        }
    }
}

if (isset($_POST['updatestudents'])) {
    $idnew = mysqli_real_escape_string($connection, $_GET['id']);
    $learners_name = mysqli_real_escape_string($connection, $_POST['learners_name']);
    $region = mysqli_real_escape_string($connection, $_POST['region']);
    $division = mysqli_real_escape_string($connection, $_POST['division']);
    $school_id = mysqli_real_escape_string($connection, $_POST['school_id']);
    $school_year = mysqli_real_escape_string($connection, $_POST['school_year']);
    $grade = mysqli_real_escape_string($connection, $_POST['grade']);
    $school_level = mysqli_real_escape_string($connection, $_POST['school_level']);
    $gender = mysqli_real_escape_string($connection, $_POST['gender']);
    $subject = mysqli_real_escape_string($connection, $_POST['subject']);
    $section = mysqli_real_escape_string($connection, $_POST['section']);

    // Prepare SQL statement
    $stmt = $connection->prepare("UPDATE students SET learners_name = ?, region = ?, division = ?, school_id = ?, school_year = ?, grade = ?, school_level = ?, gender = ?, subject = ?, section = ? WHERE id = ?");
    $stmt->bind_param("ssssssssssi", $learners_name, $region, $division, $school_id, $school_year, $grade, $school_level, $gender, $subject, $section, $idnew);

    if ($stmt->execute()) {
        header('Location: AdminCrud.php?update_msg=You Have Successfully Updated The Data');
        exit();
    } else {
        die("Query failed: " . $stmt->error);
    }

    $stmt->close();
    $connection->close();
}

ob_end_flush();
?>


<form action="updatepage1.php?id=<?php echo isset($id) ? $id : ''; ?>" method="post">
    <div class="form-group">
        <label for="learners_name">Learners Name</label>
        <input type="text" name="learners_name" class="form-control" value="<?php echo isset($row['learners_name']) ? htmlspecialchars($row['learners_name']) : '' ?>" >
    </div>
    <style>
    /* Apply CSS from above */
    .hidden {
        opacity: 0;
        position: absolute;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }
</style>
    
    <div class="form-group hidden">
        <label for="region">Region</label>
        <input type="text" name="region" class="form-control" value="<?php echo isset($row['region']) ? htmlspecialchars($row['region']) : '' ?>" readonly>
    </div>

    <div class="form-group hidden">
        <label for="division">Division</label>
        <input type="text" name="division" class="form-control" value="<?php echo isset($row['division']) ? htmlspecialchars($row['division']) : '' ?>" readonly>
    </div>

    <div class="form-group">
        <label for="school_id"></label>
        <input type="text" name="school_id" class="form-control" value="<?php echo isset($row['school_id']) ? htmlspecialchars($row['school_id']) : '' ?>"hidden>
    </div>

    <div class="mb-3">
    <label for="school_year" class="form-label">School Year</label>
    <select class="form-control" id="school_year" name="school_year" required>
        <?php 
        $startYear = 2020;
        $endYear = 2024;

        for ($year = $startYear; $year <= $endYear; $year++) {
            $nextYear = $year + 1;
            $schoolYear = "{$year}-{$nextYear}";
            echo "<option value=\"{$schoolYear}\">{$schoolYear}</option>";
        }
        ?>
    </select>
</div>


    <div class="form-group">
        <label for="grade">Grade Level</label>
        <select name="grade" class="form-control">
            <option value="7th" <?php echo isset($row['grade']) && $row['grade'] == '7th' ? 'selected' : ''; ?>>7th</option>
            <option value="8th" <?php echo isset($row['grade']) && $row['grade'] == '8th' ? 'selected' : ''; ?>>8th</option>
            <option value="9th" <?php echo isset($row['grade']) && $row['grade'] == '9th' ? 'selected' : ''; ?>>9th</option>
            <option value="10th" <?php echo isset($row['grade']) && $row['grade'] == '10th' ? 'selected' : ''; ?>>10th</option>
            <option value="11th" <?php echo isset($row['grade']) && $row['grade'] == '11th' ? 'selected' : ''; ?>>11th</option>
            <option value="12th" <?php echo isset($row['grade']) && $row['grade'] == '12th' ? 'selected' : ''; ?>>12th</option>
        </select>
    </div>

    <div class="form-group hidden">
        <label for="school_level">School Level</label>
        <select name="school_level" class="form-control">
            <option value="SHS" <?php echo isset($row['school_level']) && $row['school_level'] == 'SHS' ? 'selected' : ''; ?>>SHS</option>
            <option value="JHS" <?php echo isset($row['school_level']) && $row['school_level'] == 'JHS' ? 'selected' : ''; ?>>JHS</option>
        </select>
    </div>

    <div class="form-group hidden">
        <label for="gender">Gender</label>
        <select name="gender" class="form-control">
            <option value="Male" <?php echo isset($row['gender']) && $row['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
            <option value="Female" <?php echo isset($row['gender']) && $row['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
        </select>
    </div>

    <div class="form-group hidden">
        <label for="subject">Subject</label>
        <select name="subject" class="form-control">
            <option value="Math" <?php echo isset($row['subject']) && $row['subject'] == 'Math' ? 'selected' : ''; ?>>Math</option>
            <option value="Science" <?php echo isset($row['subject']) && $row['subject'] == 'Science' ? 'selected' : ''; ?>>Science</option>
            <option value="English" <?php echo isset($row['subject']) && $row['subject'] == 'English' ? 'selected' : ''; ?>>English</option>
            <option value="History" <?php echo isset($row['subject']) && $row['subject'] == 'History' ? 'selected' : ''; ?>>History</option>
            <option value="PE" <?php echo isset($row['subject']) && $row['subject'] == 'PE' ? 'selected' : ''; ?>>PE</option>
            <option value="Advanced Math" <?php echo isset($row['subject']) && $row['subject'] == 'Advanced Math' ? 'selected' : ''; ?>>Advanced Math</option>
            <option value="Advanced Science" <?php echo isset($row['subject']) && $row['subject'] == 'Advanced Science' ? 'selected' : ''; ?>>Advanced Science</option>
            <option value="Philosophy" <?php echo isset($row['subject']) && $row['subject'] == 'Philosopy' ? 'selected' : ''; ?>>Philosophy</option>
            <option value="Economics" <?php echo isset($row['subject']) && $row['subject'] == 'Economics' ? 'selected' : ''; ?>>Economics</option>
            </select>
    </div>

    <div class="form-group hidden">
    <label for="section">Section</label>
        <select name="section" class="form-control">
            <option value="Section A" <?php echo isset($row['section']) && $row['section'] == 'Section A' ? 'selected' : ''; ?>>Section A</option>
            <option value="Section B" <?php echo isset($row['section']) && $row['section'] == 'Section B' ? 'selected' : ''; ?>>Section B</option>
            <option value="Section C" <?php echo isset($row['section']) && $row['section'] == 'Section C' ? 'selected' : ''; ?>>Section C</option>
            <option value="Section X" <?php echo isset($row['section']) && $row['section'] == 'Section X' ? 'selected' : ''; ?>>Section X</option>
            <option value="Section Y" <?php echo isset($row['section']) && $row['section'] == 'Section Y' ? 'selected' : ''; ?>>Section Y</option>
            <option value="Section Z" <?php echo isset($row['section']) && $row['section'] == 'Section Z' ? 'selected' : ''; ?>>Section Z</option>
            </select>
    </div>

    <input type="submit" class="btn btn-success" name="updatestudents" value="UPDATE">
</form>

<?php include ('footer.php'); ?>
