<?php
session_start();
include("../LoginRegisterAuthentication/connection.php");

if (!isset($_SESSION['username'])) {
    die("Unauthorized access.");
}

$username = $_SESSION['username']; // Assuming the username is stored in session after login
$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// Fetch the user's current hashed password from the database
$sql = "SELECT hashed_password FROM user WHERE username = ?";
$stmt = $connection->prepare($sql);

if ($stmt) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($stored_hashed_password);
    $stmt->fetch();
    $stmt->close();

    // Verify if the current password matches the stored hashed password
    if (password_verify($current_password, $stored_hashed_password)) {
        // Check if the new password and confirm password match
        if ($new_password === $confirm_password) {
            // Ensure the new password is hashed before storing it
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update the password in the database
            $update_sql = "UPDATE user SET hashed_password = ? WHERE username = ?";
            $update_stmt = $connection->prepare($update_sql);
            $update_stmt->bind_param("ss", $hashed_new_password, $username);
            
            if ($update_stmt->execute()) {
                // Success: Display SweetAlert success message and redirect
                echo '<!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Password Changed</title>
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                </head>
                <body>
                    <script>
                        Swal.fire({
                            icon: "success",
                            title: "Password changed successfully!",
                            text: "You will be redirected to the account management page.",
                            timer: 2000, // Display for 2 seconds
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "adminmanageaccount.php";
                        });
                    </script>
                </body>
                </html>';
            } else {
                // Error updating the password
                echo '<!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Error</title>
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                </head>
                <body>
                    <script>
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "There was a problem updating your password. Please try again.",
                            showConfirmButton: true
                        }).then(() => {
                            window.location.href = "adminmanageaccount.php";
                        });
                    </script>
                </body>
                </html>';
            }
            
            $update_stmt->close();
        } else {
            // New passwords do not match
            echo '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Password Mismatch</title>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: "error",
                        title: "Passwords do not match",
                        text: "Please make sure the new password and confirm password fields match.",
                        showConfirmButton: true
                    }).then(() => {
                        window.location.href = "adminmanageaccount.php";
                    });
                </script>
            </body>
            </html>';
        }
    } else {
        // Current password is incorrect
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Incorrect Password</title>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: "error",
                    title: "Incorrect Password",
                    text: "The current password you entered is incorrect.",
                    showConfirmButton: true
                }).then(() => {
                    window.location.href = "adminmanageaccount.php";
                });
            </script>
        </body>
        </html>';
    }
} else {
    // SQL statement preparation failed
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>SQL Error</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: "error",
                title: "SQL Error",
                text: "Failed to prepare the SQL statement. Please try again.",
                showConfirmButton: true
            }).then(() => {
                window.location.href = "adminmanageaccount.php";
            });
        </script>
    </body>
    </html>';
}

$connection->close();
?>
