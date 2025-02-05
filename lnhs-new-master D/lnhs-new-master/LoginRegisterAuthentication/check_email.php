<?php
include("connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $address = $_POST["address"];

    // Query to check if the email exists
    $sql = "SELECT 1 FROM user WHERE address = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $address);
    $stmt->execute();
    $stmt->store_result();

    // Output 'exists' if email is found, otherwise 'not_exists'
    if ($stmt->num_rows > 0) {
        echo 'exists';
    } else {
        echo 'not_exists';
    }

    $stmt->close();
}
?>
