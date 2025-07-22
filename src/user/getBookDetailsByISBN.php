<?php
include '../../config/config.php';
session_start(); // Start session
include DIR_URL.'src/global/middleware.php';
$userId = $_SESSION['userId'];//Fetching userId and userType from session data
$userType = $_SESSION['userType'];
if ($userType != 'Faculty' && $userType != 'Student') // if an invalid userType tries to access this page
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['isbn'])) 
{
    include DIR_URL.'config/dbConnection.php';

    $isbn = $_POST['isbn'];
    $stmt = $conn->prepare("SELECT bookName, authorName, publisherName FROM books WHERE isbn = ?");
    $stmt->bind_param("s", $isbn);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $response = ['success' => false];

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response = [
            'success' => true,
            'bookName' => $row['bookName'],
            'authorName' => $row['authorName'],
            'publisherName' => $row['publisherName']
        ];
    }
    echo json_encode($response);
    exit;
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
