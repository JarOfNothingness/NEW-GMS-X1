<?php
session_start();
session_unset();
session_destroy();

// Clear cookies if necessary
if (isset($_COOKIE['username'])) {
    setcookie('username', '', time() - 3600, "/"); // Expire the username cookie
}

// Instead of immediate redirection, we'll output the SweetAlert script
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        Swal.fire({
            icon: "success",
            title: "Logged out successfully!",
            text: "You will be redirected to the login page.",
            timer: 2000,  // 2 seconds before redirect
            showConfirmButton: false
        }).then(() => {
            window.location.href = "login.php";
        });
    </script>
</body>
</html>';
exit();
?>
