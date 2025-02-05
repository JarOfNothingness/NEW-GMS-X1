<?php 
    $host = "localhost";
    $username = "root";
    $password = "lnhs@2024";
    $databaseNimuDiri = "lnhs";
    $connection = mysqli_connect($host,$username,$password,$databaseNimuDiri);
    if($connection != true){
        die("Error");
    }

?>