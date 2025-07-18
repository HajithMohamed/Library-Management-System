<?php
include '../../config/config.php';
session_start(); // Start session
include DIR_URL.'src/global/middleware.php';
$userId = $_SESSION['userId'];//Fetching userId and userType from session data
$userType = $_SESSION['userType'];
if ($userType != 'Admin') // if an invalid userType tries to access this page
{
  http_response_code(403);
  echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 Forbidden</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            background: url("../../assets/images/http403.jpg") no-repeat center center;
            background-size: contain;
            background-color: black;
        }
    </style>
</head>
<body>
</body>
</html>';
  exit();
}

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['isbn']) && isset($_GET['quantity']))
{

    include DIR_URL.'config/dbConnection.php';

    $isbn = $_GET['isbn'];
    $quantity = $_GET['quantity'];

    $sql = "UPDATE books 
            SET available = available + $quantity 
            WHERE isbn = '$isbn' ";
              
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Quantity updated successfully!'); 
        window.location.href = 'adminDashboard.php';</script>";
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $conn->close();
}
else // if someone tries to access this page directly by using it's url
{
    http_response_code(400);
    echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>400 Bad Request</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            background: url("../../assets/images/http400.jpg") no-repeat center center;
            background-size: contain;
            background-color: black;
        }
    </style>
</head>
<body>
</body>
</html>';
    exit();
}
?>
