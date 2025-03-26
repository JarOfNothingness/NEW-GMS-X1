<?php
// Include database connection
include('../LoginRegisterAuthentication/connection.php');

// Get filter values from POST request
$schoolYear = isset($_POST['school_year']) ? $_POST['school_year'] : '';
$gradeSection = isset($_POST['grade_section']) ? $_POST['grade_section'] : '';
$gender = isset($_POST['gender']) ? $_POST['gender'] : '';

// Prepare base query
$query = "SELECT 
            e.*,
            YEAR(e.birthdate) as batch_year,
            s.`grade & section` as grade_section,
            s.gender as gender,
            CASE 
                WHEN e.high_school_completer = 1 THEN 'Yes'
                ELSE 'No'
            END as high_school_status,
            CASE 
                WHEN e.pept_passer = 1 THEN 'Yes'
                ELSE 'No'
            END as pept_status,
            CASE 
                WHEN e.als_a_e_passer = 1 THEN 'Yes'
                ELSE 'No'
            END as als_status
          FROM encoded_learner_data e
          INNER JOIN students s ON s.id = e.learner_id
          WHERE 1";

// Add filters dynamically
if (!empty($schoolYear)) {
    $query .= " AND e.school_year = '$schoolYear'";
}
if (!empty($gradeSection)) {
    $query .= " AND s.`grade & section` = '$gradeSection'";
}
if (!empty($gender)) {
    $query .= " AND e.sex = '$gender'";
}

// Order by last name and first name
$query .= " ORDER BY e.last_name ASC, e.first_name ASC";

// Execute query
$result = mysqli_query($connection, $query);

$total_records = mysqli_num_rows($result);
$male_students = 0;
$female_students = 0;
$high_school_completers = 0;
$pept_passers = 0;
$als_passers = 0;

// Prepare data for DataTable
$data = [];
while($row = mysqli_fetch_assoc($result)) {
    if($row['sex'] == 'Male') $male_students++;
    if($row['sex'] == 'Female') $female_students++;
    if($row['high_school_completer'] == 1) $high_school_completers++;
    if($row['pept_passer'] == 1) $pept_passers++;
    if($row['als_a_e_passer'] == 1) $als_passers++;
}

// Update statistics
$data['male_students'] = $male_students;
$data['female_students'] = $female_students;
$data['high_school_completers'] = $high_school_completers;
$data['total_records'] = $total_records;

// Return JSON response
echo json_encode($data);
?>