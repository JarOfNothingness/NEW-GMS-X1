
<?php
include("../LoginRegisterAuthentication/connection.php");

$section = $_POST['section'];
$subject = $_POST['subject'];
$year = $_POST['year'];
$grade = $_POST['grade'];

// Prepare the base query
$query = "SELECT sg.*, s.learners_name 
          FROM student_grades sg
          JOIN students s ON sg.student_id = s.id 
          WHERE 1=1";

// Add filters based on user input
if ($section != '') {
    $query .= " AND s.section = '$section'";
}

if ($subject != '') {
    $query .= " AND s.subject = '$subject'";
}

if ($year != '') {
    $query .= " AND s.school_year = '$year'";
}

if ($grade != '') {
    $query .= " AND s.grade = '$grade'";
}

$grades_result = mysqli_query($connection, $query);

if (mysqli_num_rows($grades_result) > 0) {
    while ($row = mysqli_fetch_assoc($grades_result)) {
        echo "<tr>
                <td>{$row['learners_name']}</td>";
        for ($i = 1; $i <= 10; $i++) {
            echo "<td><input type='number' class='grade-input' value='{$row['quiz'.$i]}' onchange='updateGrade({$row['id']}, \"quiz{$i}\", this.value)'></td>";
        }
        for ($i = 1; $i <= 10; $i++) {
            echo "<td><input type='number' class='grade-input' value='{$row['act'.$i]}' onchange='updateGrade({$row['id']}, \"act{$i}\", this.value)'></td>";
        }
        echo "<td><input type='number' class='grade-input' value='{$row['quarterly_exam']}' onchange='updateGrade({$row['id']}, \"quarterly_exam\", this.value)'></td>
              <td class='grade-result initial-grade'>{$row['final_grade']}</td>
              <td class='grade-result final-grade'>{$row['transmuted_grade']}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='22'>No records found</td></tr>";
}
?>
