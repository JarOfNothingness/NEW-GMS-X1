<?php
include("connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    if ($new_password === $confirm_password) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Prepare and execute the SQL statement to update the hashed password
        $sql = "UPDATE user SET hashed_password = ? WHERE username = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ss", $hashed_password, $username);
        
        if ($stmt->execute()) {
            // Use a flag to indicate successful reset
            $reset_success = true;
        } else {
            $error_message = "Error changing password: " . htmlspecialchars($connection->error);
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
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome for icons -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script> <!-- Include SweetAlert -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
        }
        .navbar {
            background-color: #002855; /* Dark blue for navbar */
            padding: 15px;
            color: white;
        }
        .navbar a {
            color: white;
            text-decoration: none; /* Remove underline */
        }
        .reset-container {
            max-width: 700px;
            margin: 70px auto; /* Center the container */
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold; /* Bold heading */
        }
        .instruction-text {
            text-align: center;
            margin-bottom: 20px;
            color: #6c757d; /* Light grey color */
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold; /* Bold labels */
        }
        .input-group {
            margin-bottom: 20px; /* Space between input fields */
            position: relative; /* Positioning context for icon */
        }
        input[type="password"] {
            padding: 10px 40px; /* Adjust padding for icon space */
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%; /* Full width */
        }
        .input-icon {
            position: absolute;
            top: 47%; /* Center vertically */
            left: 10px; /* Adjust for spacing */
            transform: translateY(-50%); /* Center the icon */
            font-size: 18px; /* Adjust icon size */
            color: #6c757d; /* Light grey color */
        }
        .btn-reset {
            width: 100%;
            padding: 10px;
            background-color: #007bff; /* Button color */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .success-msg, .error-msg {
            text-align: center;
            font-weight: bold;
        }
        .success-msg {
            color: green;
        }
        .error-msg {
            color: red;
        }
    
    </style>
</head>
<body>
    <div class="navbar">
        <!-- Optional content here -->
    </div>
    <div class="reset-container" id="reset-container">
        <h1>RESET PASSWORD</h1>

        <form method="POST" id="reset-form">
            <input type="hidden" name="username" value="<?php echo htmlspecialchars($_GET['username']); ?>">
            <div class="input-group">
                <label for="new_password">Create Password:</label>
                
                <input type="password" id="new_password" name="new_password" minlength="8" maxlength="20" required placeholder="Enter your password"  pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}" title="Must be at least 8 characters long with uppercase letter, lowercase letter, and number." required>
                
                <i class="fas fa-lock input-icon"></i> <!-- Lock icon -->
                <p style="color: red; font-size: 15px;"> Must be at least 8 characters long with uppercase letter, lowercase letter, and number.</p>
            </div>
         
            <div class="input-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" minlength="8" maxlength="20"  required placeholder="Re-enter your password"  pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}" title="Must be at least 8 characters long with uppercase letter, lowercase letter, and number." required>
                
                <p style="color: red; font-size: 15px;">  Password must match.</p>
                <i class="fas fa-lock input-icon"></i> <!-- Lock icon -->
            </div>
            
            <input type="submit" value="Set Password" class="btn-reset">
        </form>
    </div>

    <!-- Check if password reset was successful and trigger SweetAlert -->
    <?php if (isset($reset_success) && $reset_success): ?>
    <script>
        // Hide the form and container before showing the SweetAlert
        document.getElementById('reset-container').style.display = 'none';

        Swal.fire({
            icon: 'success',
            title: 'Password Reset',
            text: 'Password changed successfully.',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../Home/login.php'; // Redirect to login page
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
