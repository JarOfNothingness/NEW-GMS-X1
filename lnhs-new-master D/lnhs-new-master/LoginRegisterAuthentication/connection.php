<?php 
    $host = "localhost";
    $username = "root";
    $password = "";
    $databaseNimuDiri = "lnhs";
    $connection = mysqli_connect($host,$username,$password,$databaseNimuDiri);
    if($connection != true){
        die("Error");
    }

?>