<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
<?php include('../LoginRegisterAuthentication/connection.php'); ?>
<?php include('../crud/header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 400px;
        }

        h2 {
            font-size: 24px;
            color: #4a4a4a;
            margin-bottom: 20px;
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 14px;
            color: #333;
            margin-bottom: 8px;
        }

        input[type="password"] {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 20px;
            width: 100%;
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
        }

        input[type="submit"],
        .cancel-btn {
            padding: 12px 20px;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-align: center;
            width: 48%;
        }

        input[type="submit"] {
            background-color: #4a3fc2;
            color: white;
        }

        input[type="submit"]:hover {
            background-color: #3a2fb2;
        }

        .cancel-btn {
            background-color: transparent;
            color: #4a3fc2;
            border: 2px solid #4a3fc2;
        }

        .cancel-btn:hover {
            background-color: #f1f1f1;
        }

        .input-wrapper {
            position: relative;
        }

        .error {
            color: red;
            font-size: 12px;
            display: none;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Change Password</h2>
        <form action="process_change_password.php" method="post" id="changePasswordForm">
            <div class="input-wrapper">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>

            <div class="input-wrapper">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
                <span class="error" id="passwordError">Password must be at least 8 characters long and contain at least one uppercase letter.</span>
            </div>

            <div class="input-wrapper">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <span class="error" id="confirmPasswordError">Passwords do not match.</span>
            </div>

            <div class="btn-container">
                <button type="button" class="cancel-btn" onclick="window.location.href='dashboard.php'">Cancel</button>
                <input type="submit" value="Change">
            </div>
        </form>
    </div>

    <script>
        document.getElementById('changePasswordForm').addEventListener('submit', function (e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            // Regular expression for password validation (at least 8 characters, one uppercase)
            const passwordRegex = /^(?=.*[A-Z]).{8,}$/;

            let valid = true;

            // Check if new password meets the criteria
            if (!passwordRegex.test(newPassword)) {
                document.getElementById('passwordError').style.display = 'block';
                valid = false;
            } else {
                document.getElementById('passwordError').style.display = 'none';
            }

            // Check if confirm password matches the new password
            if (newPassword !== confirmPassword) {
                document.getElementById('confirmPasswordError').style.display = 'block';
                valid = false;
            } else {
                document.getElementById('confirmPasswordError').style.display = 'none';
            }

            // Prevent form submission if not valid
            if (!valid) {
                e.preventDefault();
            }
        });
    </script>

</body>
</html>

<?php include('../crud/footer.php'); ?>
