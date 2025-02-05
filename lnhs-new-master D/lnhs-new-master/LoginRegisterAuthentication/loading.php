<?php
session_start();
include("connection.php");

if (!isset($_SESSION['userid'])) {
    header("Location: ../Home/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading</title>

    <!-- Include SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background-color: #002855;
            padding: 15px 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            position: fixed;
            top: 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar h1 {
            margin: 0;
            font-size: 1.5rem;
        }

        .loading-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            height: 100%;
        }

        .spinner {
            border: 8px solid #f3f3f3;
            border-radius: 50%;
            border-top: 8px solid #002855;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .message {
            font-size: 1.2rem;
            color: #333;
        }

        .message span {
            color: #002855;
            font-weight: bold;
        }

    </style>

<script>
    function hideLoading() {
        const loadingContainer = document.querySelector('.loading-container');
        loadingContainer.style.display = 'none';
    }

    function showStatusAlert(status, userid) {
        hideLoading();  // Hide loading before showing alert
        if (status === 'approved') {
            Swal.fire({
                icon: 'success',
                title: 'You Have Been Approved',
                text: 'You can now set up your password!',
                confirmButtonText: 'Proceed'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../Home/setup_password.php?userid=" + userid;
                }
            });
        } else if (status === 'rejected') {
            Swal.fire({
                icon: 'error',
                title: 'Request Rejected',
                text: 'Your request has been rejected. Please contact support or register again.',
                confirmButtonText: 'Okay'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "register.php?error=rejected";
                }
            });
        }
    }

    let polling = setInterval(checkStatus, 3000); // 3 seconds

function checkStatus() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "check_status.php?t=" + new Date().getTime(), true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            var status = response.status;
            var userid = <?php echo json_encode($_SESSION['userid']); ?>;

            if (status === 'approved' || status === 'rejected') {
                clearInterval(polling); // Stop polling
                showStatusAlert(status, userid);
            }
        }
    };
    xhr.send();
}

</script>

</head>
<body>
    <div class="navbar">
        <h1>Processing Request</h1>
    </div>

    <div class="loading-container">
        <div class="spinner"></div>
        <p class="message">Hold tight! <span>We're processing your request now.</span> Once complete, you'll be able to create your password and access your account. Thanks for your patience!</p>
    </div>
</body>
</html>
