<?php
include("connection.php");

$error_message = '';
$security_question = ''; // To store the fetched security question

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["action"]) && $_POST["action"] == "verify_email") {
        $address = trim(strtolower($_POST["address"]));
        
        // Fetch user by address and security question
        $sql = "SELECT u.username, q.question
                FROM user u
                JOIN security_questions q ON u.security_question_id = q.id
                WHERE LOWER(u.address) = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("s", $address);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($username, $question);
        
        if ($stmt->num_rows == 1) {
            $stmt->fetch();
            echo json_encode(["status" => "success", "question" => $question]);
            exit;
        } else {
            echo json_encode(["status" => "error", "message" => "No user found with that address."]);
            exit;
        }
    } else {
        $address = trim(strtolower($_POST["address"]));
        $security_answer = trim($_POST["security_answer"]);

        // Fetch user by address and security question
        $sql = "SELECT u.username, u.security_answer, q.question
                FROM user u
                JOIN security_questions q ON u.security_question_id = q.id
                WHERE LOWER(u.address) = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("s", $address);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($username, $stored_answer, $question);
        
        if ($stmt->num_rows == 1) {
            $stmt->fetch();
            $security_question = $question; // Store the fetched question for display
            
            // Verify the security answer using case-insensitive comparison
            if (strcasecmp($security_answer, $stored_answer) == 0) {
                // Redirect to password reset page
                header("Location: resetpassword.php?username=" . urlencode($username));
                exit();
            } else {
                $error_message = "Security answer is incorrect.";
            }
        } else {
            $error_message = "No user found with that address.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        
        .navbar {
            background-color: #002855;
            padding: 25px 25px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .container {
            max-width: 600px;
            margin: 100px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: bold;
        }

        .input-group {
            display: flex;
            align-items: center;
        }

        .input-group .form-control {
            border-radius: 0 4px 4px 0;
        }

        .input-icon {
            display: flex;
            align-items: center;
            background-color: #f1f1f1;
            border: 1px solid #ced4da;
            border-radius: 4px 0 0 4px;
            padding: 10px;
            color: #007bff;
        }

        .input-icon i {
            margin: 0;
        }

        .btn-primary {
            width: 100%;
            padding: 10px;
            background-color: darkblue;
            border: none;
        }

        .error-message {
            color: #dc3545;
            margin-bottom: 20px;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
<div class="navbar">
    <div>
        <h4 style="color:white;"></h4>
        <a href="../Home/login.php" class="text-white">Back to Login</a>
    </div>
</div>
    <div class="container">
        <div id="forgotPasswordSection">
            <h2>FORGOT PASSWORD <span class="question-icon">❔</span></h2>
            <p>Forgot your password? Don't worry! Enter your email below, and we'll help you find your account. Please also provide the answer you registered with your answer key.</p>
        </div>

        <div id="error-message" class="error-message"><?php echo htmlspecialchars($error_message); ?></div>

        <form id="forgotPasswordForm" method="POST">
            <div id="emailSection">
                <div class="form-group">
                    <label for="address">Email Address:</label>
                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <input type="email" id="address" name="address" class="form-control" required placeholder="Email Address">
                    </div>
                </div>
                <button type="button" class="btn btn-primary" onclick="verifyEmail()">Submit</button>
            </div>

            <div id="securityQuestionSection" class="hidden">
                <h2>*Provide The Answer Key From Your Security Question*</h2>
                
                <p id="securityQuestionText"></p>
                <div class="form-group">
                    <label for="security_answer">Security Answer:</label>
                    <input type="password" id="security_answer" name="security_answer" class="form-control" required>
                </div>
                <input type="submit" value="Submit" class="btn btn-primary">
            </div>
        </form>
    </div>

    <script>
    function verifyEmail() {
        var address = document.getElementById('address').value;
        var errorMessage = document.getElementById('error-message');

        // Reset error message
        errorMessage.textContent = '';

        // Check if email is entered
        if (address.trim() === '') {
            alert('Please enter your email address.');
            return;
        }

        // Display SweetAlert loading spinner
        Swal.fire({
            title: 'Verifying Email',
            html: 'Please wait while we verify your email...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        // Simulate a 2-second delay before making the AJAX call
        setTimeout(function() {
            // Make an AJAX call to check if email exists and fetch security question
            $.ajax({
                url: 'ForgotPassword.php',
                method: 'POST',
                data: {
                    action: 'verify_email',
                    address: address
                },
                dataType: 'json',
                success: function(response) {
                    Swal.close(); // Close the loading spinner
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Email Verified',
                            text: 'Please answer your security question.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            $('#forgotPasswordSection').addClass('hidden');
                            $('#emailSection').addClass('hidden');
                            $('#securityQuestionSection').removeClass('hidden');
                            $('#securityQuestionText').text(response.question);
                        });
                    } else {
                        errorMessage.textContent = response.message;
                    }
                },
                error: function() {
                    Swal.close();
                    errorMessage.textContent = 'An error occurred. Please try again.';
                }
            });
        }, 2000); // 2 seconds delay
    }
    </script>
</body>
</html>
