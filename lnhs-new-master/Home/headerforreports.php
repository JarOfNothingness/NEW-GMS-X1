<?php
// Start PHP with no output or whitespace before it

// Example of setting headers if needed
// header("Content-Type: text/html; charset=UTF-8"); // Optional: Define content type

// Ensure no HTML or output before this
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Students Masterlist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 60px; /* Initial width showing only icons */
            background-color: #333;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            transition: width 0.3s;
            overflow-x: hidden;
            white-space: nowrap;
        }

        .sidebar:hover {
            width: 250px; /* Expand on hover */
        }

        .sidebar a {
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background-color: #575757;
        }

        .sidebar a span {
            display: none; /* Hide text initially */
            margin-left: 10px;
        }

        .sidebar:hover a span {
            display: inline; /* Show text on hover */
        }

        .main-content {
            margin-left: 60px;
            padding: 20px;
            width: calc(100% - 60px);
            transition: margin-left 0.3s, width 0.3s;
        }

        .sidebar:hover ~ .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
        }

        .dashboard-header {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
            font-size: 32px;
        }

        .form-links {
            position: fixed;
            top: 20px;
            right: 20px;
        }

        .form-links a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
        }

       
        .form137{
            text-align: center;
            background-color: #d6dc2a;
            text-transform: uppercase;
        }
        .form-container{
            width: 100%;
            text-align: center;
        }
        .form-container td,.form-container th{
            border: 1px solid black;
        }
        .final-grade{
            display: grid;
            grid-template-columns: 1fr 1fr 29%;
            width: 100%;
            border: 1px solid black;
            border-top: none;
        }
        .final-grade .center{
            display: grid;
            grid-template-columns: 1fr 60%;
            text-align: right;
        
        }
        .final-grade .right{
            text-align: right;
            margin-right: 70px;
        }
        .form137-container{
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .form137-container .center{
           text-align: center;
        }
        
    </style>
</head>
<body>

 
   