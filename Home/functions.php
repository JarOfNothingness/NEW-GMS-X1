<?php
// functions.php
function updateUserData($connection, $userid, $data) {
    $query = "UPDATE user SET name = ?, username = ?, address = ?, role = ? WHERE userid = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ssssi", $data['name'], $data['username'], $data['address'], $data['role'], $userid);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success' role='alert'>User updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error updating user: " . mysqli_error($connection) . "</div>";
    }

    $stmt->close();
}


function getUserData($connection, $userid) {
    $stmt = $connection->prepare("SELECT * FROM user WHERE userid = ?");
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    $stmt->close();
    return $userData;
}


// functions.php

function getAnnouncements($connection) {
    $query = "SELECT title, content, created_at FROM announcements ORDER BY created_at DESC";
    $result = mysqli_query($connection, $query);
    
    if (!$result) {
        die("Error fetching announcements: " . mysqli_error($connection));
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function logActivity($username, $action, $role) {
    global $connection;
    $timestamp = date("Y-m-d H:i:s");
    $query = "INSERT INTO user_activity_log (username, action, timestamp, role) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'ssss', $username, $action, $timestamp, $role);
    mysqli_stmt_execute($stmt);
}


function updateFinalGrades($connection) {
    // Delete existing records in final_grades table
    $connection->query("DELETE FROM final_grades");

    // Fetch data from enrollments and student_grades
    $query = "
        SELECT 
            e.learners_name,
            e.subject AS subject_name,
            sg.quarter AS quarter,
            sg.written_exam,
            sg.performance_task,
            sg.quarterly_exam,
            sg.final_grade
        FROM enrollments e
        JOIN student_grades sg ON e.id = sg.student_id
        WHERE e.school_year = YEAR(CURDATE())
    ";

    $result = $connection->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $learners_name = $connection->real_escape_string($row['learners_name']);
            $subject_name = $connection->real_escape_string($row['subject_name']);
            $quarter = intval($row['quarter']);
            $final_grade = floatval($row['final_grade']);

            // Debugging output
            echo "<pre>Inserting/Updating: Learner: $learners_name, Subject: $subject_name, Quarter: $quarter, Final Grade: $final_grade</pre>";

            $query_insert = "
                INSERT INTO final_grades (learners_name, subject_name, quarter_1, quarter_2, quarter_3, quarter_4, final_grade)
                VALUES (
                    '$learners_name',
                    '$subject_name',
                    IF($quarter = 1, $final_grade, NULL),
                    IF($quarter = 2, $final_grade, NULL),
                    IF($quarter = 3, $final_grade, NULL),
                    IF($quarter = 4, $final_grade, NULL),
                    $final_grade
                )
                ON DUPLICATE KEY UPDATE
                    quarter_1 = IF($quarter = 1, $final_grade, quarter_1),
                    quarter_2 = IF($quarter = 2, $final_grade, quarter_2),
                    quarter_3 = IF($quarter = 3, $final_grade, quarter_3),
                    quarter_4 = IF($quarter = 4, $final_grade, quarter_4),
                    final_grade = $final_grade
            ";

            if (!$connection->query($query_insert)) {
                echo "<pre>Insert Error: " . $connection->error . "</pre>";
            }
        }
    } else {
        echo "<pre>Fetch Error: " . $connection->error . "</pre>";
    }
}
function generate_csv_report($data) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="student_report.csv"');

    $output = fopen('php://output', 'w');

    // Write the CSV header
    fputcsv($output, ['Student Name', 'Subject', 'Quarter', 'Written Exam', 'Performance Task', 'Quarterly Exam', 'Final Grade', 'Remarks']);

    foreach ($data as $student_name => $subjects) {
        foreach ($subjects as $subject_name => $subject_data) {
            foreach ($subject_data['grades'] as $quarter => $grade) {
                fputcsv($output, [
                    $student_name,
                    $subject_name,
                    $quarter,
                    $grade['written_exam'] ?? '',
                    $grade['performance_task'] ?? '',
                    $grade['quarterly_exam'] ?? '',
                    $grade['final_grade'] ?? '',
                    get_remarks($grade['final_grade'])
                ]);
            }
        }
    }

    fclose($output);
}



function displayLearnerData($learnerData) {
    // Assuming $learnerData is an associative array with learner information
    echo '<span>
        LAST NAME: ' . htmlspecialchars($learnerData['last_name']) . '<br>
        First name: ' . htmlspecialchars($learnerData['first_name']) . '<br>
        Name extn (jr): ' . htmlspecialchars($learnerData['name_extension']) . '<br>
        Middle name: ' . htmlspecialchars($learnerData['middle_name']) . '<br>
        Learner Reference Number (LRN): ' . htmlspecialchars($learnerData['lrn']) . '<br>
        Birthdate(mm/dd/yyyy): ' . htmlspecialchars($learnerData['birthdate']) . '<br>
        Sex: ' . htmlspecialchars($learnerData['sex']) . '<br>
    </span>';

    echo '<h1 class="form137">ELIGIBILITY FOR JHS ENROLLMENT</h1>
    <div class="eligibility-box">
        <span>
            <input type="checkbox" id="elementaryCompleter" name="elementary_completer" value="yes"' . ($learnerData['elementary_completer'] ? ' checked' : '') . '>
            Elementary School Completer: <br>
            General Average: ' . htmlspecialchars($learnerData['general_average']) . '<br>
            Citation (If Any): ' . htmlspecialchars($learnerData['citation']) . '<br>
            Name of Elementary School: ' . htmlspecialchars($learnerData['elementary_school_name']) . '<br>
            School ID: ' . htmlspecialchars($learnerData['school_id']) . '<br>
            Address of School: ' . htmlspecialchars($learnerData['school_address']) . '<br>
        </span>
        <h5>Other Credentials Presented:</h5>
        <input type="checkbox" id="PEPT Passer" name="PEPT_Passer" value="yes"' . ($learnerData['pept_passer'] ? ' checked' : '') . '>
        PEPT Passer: <br>
        Rating: ' . htmlspecialchars($learnerData['pept_rating']) . '<br>
        <input type="checkbox" id="ALS A & E Passer" name="ALSA&Ep_PasserRating" value="yes"' . ($learnerData['als_a_e_passer'] ? ' checked' : '') . '>
        ALS A & E Passer: <br>
        Rating: ' . htmlspecialchars($learnerData['als_rating']) . '<br>
        <input type="checkbox" id="Others(Pls Specify)" name="Others_(PlsSpecify)" value="yes"' . ($learnerData['others_specify'] ? ' checked' : '') . '>
        Others(Pls Specify): ' . htmlspecialchars($learnerData['others_specify_text']) . '<br>
        Date Of Examination/Assessment (mm/dd/yyyy): ' . htmlspecialchars($learnerData['exam_date']) . '<br>
        Name and Address of Testing Center: ' . htmlspecialchars($learnerData['testing_center']) . '<br>
    </div>';
    
    echo '<div class="scholastic-record-container">
        <h1 class="form137">Scholastic Record</h1>
        <span>
            School: ' . htmlspecialchars($learnerData['school']) . '<br>
            School ID: ' . htmlspecialchars($learnerData['school_id']) . '<br>
            District: ' . htmlspecialchars($learnerData['district']) . '<br>
            Division: ' . htmlspecialchars($learnerData['division']) . '<br>
            Region: ' . htmlspecialchars($learnerData['region']) . '<br>
            Classified as Grade: ' . htmlspecialchars($learnerData['grade']) . '<br>
            Section: ' . htmlspecialchars($learnerData['section']) . '<br>
            School year: ' . htmlspecialchars($learnerData['school_year']) . '<br>
            Name of Adviser/Teacher: ' . htmlspecialchars($learnerData['adviser']) . '<br>
            Signature: ' . htmlspecialchars($learnerData['signature']) . '<br>
        </span>';

    // Example for displaying grades in a table
    echo '<table class="form-container">
        <thead>
            <tr>
                <th rowspan="2">Learning Areas</th>
                <th colspan="4">Quarterly Rating</th>
                <th rowspan="2">Final Rating</th>
                <th rowspan="2"></th>
            </tr>
            <tr>
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($learnerData['grades'] as $grade) {
        echo '<tr>
            <td>' . htmlspecialchars($grade['subject']) . '</td>
            <td>' . htmlspecialchars($grade['quarter1']) . '</td>
            <td>' . htmlspecialchars($grade['quarter2']) . '</td>
            <td>' . htmlspecialchars($grade['quarter3']) . '</td>
            <td>' . htmlspecialchars($grade['quarter4']) . '</td>
            <td>' . htmlspecialchars($grade['final_rating']) . '</td>
            <td>' . htmlspecialchars($grade['status']) . '</td>
        </tr>';
    }

    echo '</tbody>
        </table>';
    
    // Example for final grades display
    echo '<div class="final-grade">
        <div class="left"></div>
        <div class="center">
            <div class="average">General Average</div>
            <div class="total">' . htmlspecialchars($learnerData['general_average']) . '</div>
        </div>
        <div class="right">Promoted</div>
    </div>';
}



function encodeData($data) {
    return htmlspecialchars(base64_encode($data));
}


function activities($connection,$logs,$userid,$admin_id){
    $query = "INSERT INTO activities (userid, logs, admin_id, datetime ) VALUES (?, ?, ?, NOW())";
    
    // Create a prepared statement
    $stmt = mysqli_prepare($connection, $query);
    
    if ($stmt) {
        // Bind parameters to the prepared statement
        mysqli_stmt_bind_param($stmt, "isi", $userid,$logs, $admin_id);
        
        // Execute the statement
        $result = mysqli_stmt_execute($stmt);
        
        // Close the statement
        mysqli_stmt_close($stmt);
        
        return $result;
    } else {
        return false;
    }
}