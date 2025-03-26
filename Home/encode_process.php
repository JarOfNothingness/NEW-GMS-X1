<?php
session_start();
include('../LoginRegisterAuthentication/connection.php');

header('Content-Type: application/json');

try {
    // Validate required fields
    $required_fields = ['learner_id', 'lrn', 'birthdate', 'sex', 'general_average'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Initialize variables
    $params = [
        'learner_id' => (int)$_POST['learner_id'],
        'last_name' => $_POST['last_name'] ?? '',
        'first_name' => $_POST['first_name'] ?? '',
        'name_extension' => $_POST['name_extension'] ?? '',
        'middle_name' => $_POST['middle_name'] ?? '',
        'lrn' => $_POST['lrn'],
        'birthdate' => $_POST['birthdate'],
        'sex' => $_POST['sex'],
        'high_school_completer' => isset($_POST['elementary_completer']) ? 1 : 0,
        'general_average' => (float)$_POST['general_average'],
        'citation' => $_POST['citation'] ?? '',
        'elementary_school_name' => $_POST['elementary_school_name'] ?? '',
        'school_id' => $_POST['school_id'] ?? '',
        'pept_passer' => isset($_POST['pept_passer']) ? 1 : 0,
        'pept_rating' => $_POST['pept_rating'] ?? '',
        'als_a_e_passer' => isset($_POST['als_a_e_passer']) ? 1 : 0,
        'als_rating' => $_POST['als_rating'] ?? '',
        'others_specify' => isset($_POST['others_specify']) ? 1 : 0,
        'exam_date' => !empty($_POST['exam_date']) ? $_POST['exam_date'] : null,
        'testing_center' => $_POST['testing_center'] ?? '',
        'adviser' => $_POST['adviser'] ?? '',
        'school' => $_POST['school'] ?? '',
        'district' => $_POST['district'] ?? '',
        'division' => $_POST['division'] ?? '',
        'region' => $_POST['region'] ?? '',
        'school_year' => $_POST['school_year'] ?? ''
    ];

    // Create the SQL query using the same keys as the params array
    $columns = implode(', ', array_keys($params));
    $placeholders = str_repeat('?,', count($params) - 1) . '?';
    
    $sql = "INSERT INTO encoded_learner_data ($columns) VALUES ($placeholders)";

    $stmt = mysqli_prepare($connection, $sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($connection));
    }

    // Create the types string based on parameter types
    $types = '';
    foreach ($params as $key => $value) {
        switch ($key) {
            case 'learner_id':
            case 'high_school_completer':
            case 'pept_passer':
            case 'als_a_e_passer':
            case 'others_specify':
                $types .= 'i'; // integer
                break;
            case 'general_average':
                $types .= 'd'; // double/decimal
                break;
            default:
                $types .= 's'; // string/default
                break;
        }
    }

    // Debug information
    error_log("Types string length: " . strlen($types));
    error_log("Parameter count: " . count($params));
    error_log("SQL: " . $sql);

    // Bind parameters using array reference trick
    $bind_params = array_merge([$stmt, $types], array_values($params));
    $bind_params_refs = array();
    foreach($bind_params as $key => $value) {
        $bind_params_refs[$key] = &$bind_params[$key];
    }
    
    call_user_func_array('mysqli_stmt_bind_param', $bind_params_refs);

    // Execute the statement
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
    }

    echo json_encode([
        'success' => true,
        'message' => 'Data encoded successfully',
        'inserted_id' => mysqli_insert_id($connection)
    ]);

} catch (Exception $e) {
    error_log("Error in encode_process.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Close statement and connection
if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
mysqli_close($connection);
?>