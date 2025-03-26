<?php
session_start(); // Start the session at the beginning

// include("LoginRegisterAuthentication/connection.php");
include("../LoginRegisterAuthentication/connection.php");

$error_msg = ""; // Initialize error message variable

// Check if there's an error message in the session (persist it across page reloads)
if (isset($_SESSION['error_msg'])) {
    $error_msg = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']); // Clear it after displaying
}

// Check if the login form is submitted
if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $remember_me = isset($_POST["remember_me"]);

    // Check if username and password are not empty
    if (!empty($username) && !empty($password)) {
        $sql = "SELECT * FROM user WHERE username=?";
        $statement = mysqli_stmt_init($connection);
        if (mysqli_stmt_prepare($statement, $sql)) {
            mysqli_stmt_bind_param($statement, "s", $username);
            mysqli_stmt_execute($statement);
            $resultdata = mysqli_stmt_get_result($statement);
            if ($row = mysqli_fetch_assoc($resultdata)) {
                // Check if the account is rejected
                if ($row['status'] === 'rejected') {
                    $_SESSION['error_msg'] = "Your account is deactivated. Please contact the administrator.";
                    header("Location: login.php");
                    exit();
                }

                // Verify the hashed password
                if (password_verify($password, $row['hashed_password'])) {

                    // Set session variables for the user
                    $_SESSION['user_id'] = $row['userid'];
                    $_SESSION['userid'] = $row['userid'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['role'] = $row['role'];
                    $_SESSION['teacher_name'] = $row['name'];  // Store teacher's name in the session

                    // Set cookies if "Remember Me" is checked (only store non-sensitive data)
                    if ($remember_me) {
                        setcookie('username', $username, time() + (86400 * 30), "/"); // Store the username for 30 days
                    } else {
                        // Clear cookies if "Remember Me" is not checked
                        if (isset($_COOKIE['username'])) {
                            setcookie('username', '', time() - 3600, "/");
                        }
                    }

                    // Display success message using SweetAlert and redirect after a short delay
                    echo '<!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Login Success</title>
                        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                    </head>
                    <body>
                        <script>
                            Swal.fire({
                                icon: "success",
                                title: "Login Successful!",
                                text: "Redirecting you now...",
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = "' . ($row['role'] === 'Admin' ? 'adminhomepage.php' : 'dashboard.php') . '";
                            });
                        </script>
                    </body>
                    </html>';
                    exit(); // Ensure no further code is executed after showing SweetAlert
                } else {
                    $_SESSION['error_msg'] = "Invalid username or password.";
                    header("Location: login.php");
                    exit();
                }
            } else {
                $_SESSION['error_msg'] = "Invalid username or password.";
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['error_msg'] = "An error occurred. Please try again later.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error_msg'] = "Please enter both username and password.";
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #e8f0fe;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #002855;
            padding: 10px 20px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .navbar h1 {
            margin: 0;
            font-size: 24px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
        }

        .navbar a:hover {
            background-color: #0056b3;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 50px);
        }

        .login-container {
            background-color: #ffffff;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 100%;
            box-sizing: border-box;
        }

        .logo {
            display: block;
            margin: 0 auto 20px;
            max-width: 150px;
            height: auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #002855;
        }

        .input-container {
            position: relative;
            margin-bottom: 30px;
        }

        .input-container label {
            font-size: 18px;
            margin-bottom: 5px;
            display: block;
            color: #002855;
            position: absolute;
            left: 10px;
            top: 15px;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .input-container input {
            padding: 12px 10px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }

        .input-container input:focus + label,
        .input-container input:not(:placeholder-shown) + label {
            top: -20px;
            left: 10px;
            font-size: 14px;
            color: #0056b3;
        }

        .input-container .fa {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }

        input[type="submit"] {
            width: 100%;
            margin-right: 100px;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background-color: #0056b3;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #003f7f;
        }

        .error-box {
            display: <?php echo !empty($error_msg) ? 'block' : 'none'; ?>;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 30px;
            border: 2px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            max-width: 300px;
            text-align: center;
            z-index: 1000;
        }

        .error-box p {
            margin-bottom: 20px;
            color: #333;
        }

        .error-box button {
            background-color: #ccc;
            color: #333;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .error-box button:hover {
            background-color: #999;
            color: #fff;
        }

    </style>
</head>
<body>
<div class="navbar">
    <h1>Education Portal</h1>
    <div>
        <a href="../LoginRegisterAuthentication/register.php">Create Account</a>
    </div>
</div>

<div class="container">
    <form class="login-container" method="POST">
        <img src="Images/Logo.png.png" alt="Logo" class="logo">
        <h1>Login</h1>

        <!-- Error message display -->
        <?php if (!empty($error_msg)): ?>
            <div class="error-box">
                <p><?php echo $error_msg; ?></p>
                <button onclick="document.querySelector('.error-box').style.display = 'none';">Okay</button>
            </div>
        <?php endif; ?>

        <div class="input-container">
            <input type="text" name="username" id="username" placeholder=" " value="<?php echo isset($_COOKIE['username']) ? htmlspecialchars($_COOKIE['username']) : ''; ?>" required>
            <label for="username">
                <i class="fas fa-user"></i> Username
            </label>
        </div>

        <div class="input-container">
            <input type="password" name="password" id="password" placeholder=" " value="<?php echo isset($_COOKIE['password']) ? htmlspecialchars($_COOKIE['password']) : ''; ?>" required>
            <label for="password">
                <i class="fas fa-lock"></i> Password
            </label>
        </div>

        <div>
            <input type="checkbox" name="remember_me" id="remember_me" <?php echo isset($_COOKIE['username']) ? 'checked' : ''; ?>>
            <label for="remember_me">Remember Me</label>
        </div>
        <input type="submit" name="login" value="Login">
        <p><a href="../LoginRegisterAuthentication/Forgotpassword.php">Forgot your password?</a></p>

    </form>
</div>
</body>
</html>
