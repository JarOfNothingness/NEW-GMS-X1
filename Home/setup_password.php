<?php
session_start();
include("../LoginRegisterAuthentication/connection.php");

if (!isset($_GET['userid'])) {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

$userid = $_GET['userid'];

if (isset($_POST['set_password'])) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password === $confirm_password) {
        // Hash the password (use password_hash)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update the user hashed_password (remove the reference to 'password' column)
        $update_query = "UPDATE user SET hashed_password = ? WHERE userid = ?";
        $stmt = $connection->prepare($update_query);
        if ($stmt === false) {
            die('Prepare failed: ' . $connection->error);
        }

        $stmt->bind_param("si", $hashed_password, $userid);

        if ($stmt->execute()) {
            // Store the success message in a variable and trigger SweetAlert in the HTML later
            $success_message = "Password successfully created.";
        } else {
            $error_message = "Error updating password: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $error_message = "Passwords do not match.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <style>
        body {
            background-color: #f8f9fa; 
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            max-width: 500px; 
            background-color: white; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        .input-group-text {
            background-color: #e9ecef;
            border: none;
        }
        h2 {
            color: #343a40; 
            font-weight: bold; 
            text-align: center; 
            margin-bottom: 30px;
            font-size: 1.8rem;
        }
        .form-label {
            font-weight: bold;
        }
        .guide-text {
            font-size: 12px;
            color: red; /* Updated to red */
            margin-top: 5px;
        }
        .password-match-error {
            color: red;
            font-size: 14px;
            text-align: center;
            display: none;
        }
        .btn-primary {
            width: 100%; 
            padding: 12px; 
            font-size: 16px; 
            background-color: #002855;
            border-color: #002855;
        }
        .btn-primary:hover {
            background-color: #004080;
        }
        .alert {
            text-align: center;
        }
        .mb-3 {
            margin-bottom: 20px;
        }
        .form-control {
            padding: 12px;
        }
        .form-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .navbar {
            background-color: #002855; 
            padding: 20px 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            position: fixed;
            top: 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        /* Custom error styling */
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
            border-radius: 5px;
            padding: 10px;
        }
    </style>
    <script>
        function validatePasswordLength() {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const errorMessage = document.querySelector('.password-match-error');

            // Check password length and matching
            if (password.value.length < 8) {
                return false;
            }
            if (password.value !== confirmPassword.value) {
                errorMessage.style.display = 'block';

                // Set a timer to hide the message after 2 seconds
                setTimeout(() => {
                    errorMessage.style.display = 'none';
                }, 2000);
                return false;
            }
            return true;
        }

        // Display SweetAlert if success_message exists and hide the form and container
        document.addEventListener('DOMContentLoaded', function () {
            <?php if (!empty($success_message)): ?>
                document.querySelector('.container').style.display = 'none';

                Swal.fire({
                    icon: "success",
                    title: "Password Set",
                    text: "<?php echo $success_message; ?>",
                    confirmButtonText: "OK"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "login.php";
                    }
                });
            <?php endif; ?>
        });
    </script>
</head>
<body>
<div class="navbar">
    <div>
        <h4 style="color:white;"></h4>
    </div>
</div>
    <div class="container">
     <h2>SET YOUR PASSWORD</h2>
        <?php if ($error_message): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" onsubmit="return validatePasswordLength()">
            <div class="mb-3">
                <label for="password" class="form-label">Create Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Create Password" minlength="8" maxlength="20" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}" title="Must be at least 8 characters long with uppercase letter, lowercase letter, and number." required>
                </div>
                <div class="guide-text">
                    Must be at least 8 characters long with an uppercase letter, lowercase letter, and number.
                </div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" minlength="8" maxlength="20" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}" title="Must be at least 8 characters long with uppercase letter, lowercase letter, and number." required>
                </div>
                <div class="guide-text">
                    Passwords must match.
                </div>
            </div>
           
            <!-- Password match error message -->
            <p class="password-match-error">Passwords do not match.</p>

            <button type="submit" name="set_password" class="btn btn-primary">Set Password</button>
        </form>
    </div>
</body>
</html>
