<?php
session_start();
include("connection.php");

// Fetch security questions for dropdown
$sql_questions = "SELECT * FROM security_questions";
$result_questions = mysqli_query($connection, $sql_questions);
$questions = mysqli_fetch_all($result_questions, MYSQLI_ASSOC);

// Variable to hold any SweetAlert script to display later
$sweetalert_script = '';

// Check if the form was submitted
if (isset($_POST["submit"])) {
    $fullname = $_POST["name"];
    $address = $_POST["address"];
    $role = $_POST["role"];
    $username = $_POST["username"];
    $temporary_password = bin2hex(random_bytes(8)); // Generate a temporary password
    $hashed_password = password_hash($temporary_password, PASSWORD_DEFAULT); // Hash the temporary password
    $security_question_id = $_POST["security_question"];
    $security_answer = $_POST["security_answer"];

    // Validate if all required fields are filled
    if (empty($fullname) || empty($address) || empty($role) || empty($username) || empty($security_question_id) || empty($security_answer)) {
        $error_msg = "Error: Please fill in all required fields.";
    } else {
        // Check if the username, address, or name already exists in the database
        $sql_check = "SELECT * FROM user WHERE username = ? OR address = ? OR name = ?";
        $stmt_check = $connection->prepare($sql_check);
        $stmt_check->bind_param("sss", $username, $address, $fullname);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // Prepare SweetAlert script if a record already exists
            $sweetalert_script = "
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Account Exists',
                    text: 'A user with this name, email, or username already exists. Please try different credentials.',
                    confirmButtonText: 'OK'
                });
            </script>";
        } else {
            // Insert new user with hashed temporary password and status 'pending'
            $sql_create = "INSERT INTO user (name, address, hashed_password, username, role, status, security_question_id, security_answer) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?)";
            $statement = mysqli_stmt_init($connection);
            if (mysqli_stmt_prepare($statement, $sql_create)) {
                mysqli_stmt_bind_param($statement, "sssssis", $fullname, $address, $hashed_password, $username, $role, $security_question_id, $security_answer);
                if (mysqli_stmt_execute($statement)) {
                    // Store the user ID in the session for later use
                    $_SESSION['userid'] = mysqli_insert_id($connection);
                    header("Location: loading.php"); // Redirect to loading page
                    exit();
                } else {
                    $error_msg = "Error: Could not execute the statement.";
                }
                mysqli_stmt_close($statement);
            } else {
                $error_msg = "Error: Could not prepare the statement.";
            }
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
     <!-- Include SweetAlert -->
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fffdfe;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #002855;
            padding: 20px;
            color: white;
            text-align: center;
        }
        .register-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #eeeeff;
            border-radius: 0px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h1 {
            margin-top: 0;
            margin-right: 20px;
            color: #4e4e7a;
            font-family: 'Lato', sans-serif;
            font-style: normal;
            text-align: center;
        }
        .input-container {
            position: relative;
            margin-bottom: 15px;
        }
        .input-container input, .input-container select {
            width: 100%;
            padding: 10px 40px; /* Extra padding to leave space for the icon */
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .input-container .fa {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }
        .error-msg {
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }
        .btn-success {
            background-color: #0056b3;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
    </style>
</head>
<body>
    
    <div class="navbar">
        <a href="../Home/login.php" style="color: white; text-decoration: none;">Back to Login</a>
    </div>

    <div class="register-container">
        <h1>Create Account</h1>
        <form method="POST">
            <?php if (isset($error_msg)): ?>
                <p class="error-msg"><?php echo $error_msg; ?></p>
            <?php endif; ?>

            <div class="input-container">
                <i class="fa fa-users"></i>
                <select name="role" id="role" required>
                    <option value="Teacher" <?php echo isset($_POST["role"]) && $_POST["role"] === "Teacher" ? 'selected' : ''; ?>>Teacher</option>
                    <option value="Admin" <?php echo isset($_POST["role"]) && $_POST["role"] === "Admin" ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>

            <div class="input-container">
                <i class="fa fa-user"></i>
                <input type="text" name="name" placeholder="Full Name" value="<?php echo isset($_POST["name"]) ? htmlspecialchars($_POST["name"]) : ''; ?>" 
                required onkeypress="return isLetter(event)">
            </div>

            <div class="input-container">
                <i class="fa fa-envelope"></i>
                <input type="email" name="address" placeholder="Email Address" value="<?php echo isset($_POST["address"]) ? htmlspecialchars($_POST["address"]) : ''; ?>" required>
            </div>

            <div class="input-container">
                <i class="fa fa-user-circle"></i>
                <input type="text" name="username" placeholder="Username" minlength="6" maxlength="20" value="<?php echo isset($_POST["username"]) ? htmlspecialchars($_POST["username"]) : ''; ?>" pattern="[a-zA-Z0-9._]+" required>
            </div>

            <div class="input-container">
                <i class="fa fa-question-circle"></i>
                <select name="security_question" required>
                    <option value="">Select a Security Question</option>
                    <?php foreach ($questions as $question): ?>
                        <option value="<?php echo htmlspecialchars($question['id']); ?>"><?php echo htmlspecialchars($question['question']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="input-container">
                <i class="fa fa-lock"></i>
                <input type="text" name="security_answer" placeholder="Security Answer" required>
            </div>

            <input type="submit" name="submit" value="Create Account" class="btn-success">
        </form>
    </div>

    <!-- Output SweetAlert script if set -->
    <?php echo $sweetalert_script; ?>
    <script>
function isLetter(evt) {
    const char = String.fromCharCode(evt.which);
    // Allow only letters and spaces
    return /^[A-Za-z\s]+$/.test(char) || evt.which === 0; // 0 for non-character keys (like backspace)
}
</script>
</body>
</html>
