<?php
include '../../config/config.php';
session_start(); // Start session
include DIR_URL.'src/global/middleware.php';
include DIR_URL.'config/dbConnection.php';
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
if ($_SERVER["REQUEST_METHOD"] !== "GET" || !isset($_GET['query'])) // if someone tries to directly access this page using its url
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

// Keep showing the "No users yet" message if the user searches anything while the library has no members
$sql="SELECT * FROM users ORDER BY userType ASC,userId ASC";
$result=$conn->query($sql);
if($result->num_rows==0)//check if there are any books in the library
{
     echo "<tr><td colspan='8'><center>There are no users registered yet...</center></td></tr>";
     exit;
}

$query = $_GET['query']; // Get the search query from the AJAX request

$sql="SELECT * FROM users WHERE userType LIKE ? OR userId LIKE ? OR gender LIKE ? OR dob LIKE ?
  OR phoneNumber LIKE ? OR emailId LIKE ? OR address LIKE ? ORDER BY userType ASC,userId ASC";
$stmt=$conn->prepare($sql);
$searchTerm=$query."%";
$stmt->bind_param('sssssss',$searchTerm,$searchTerm,$searchTerm,$searchTerm,$searchTerm,$searchTerm,$searchTerm);
$stmt->execute();
$result=$stmt->get_result();

// Function to highlight matches
function highlightMatch($text, $highlight) {
    $escapedText = htmlspecialchars($text);
    $escapedHighlight = preg_quote($highlight, '/');

    if (stripos($escapedText, $highlight) === 0) {
        return "<span class='highlight'>" . substr($escapedText, 0, strlen($highlight)) . "</span>" .
               substr($escapedText, strlen($highlight));
    }
    return $escapedText;
}


if ($result->num_rows > 0) 
{
    $index = 1;
    $highlight = htmlspecialchars($query, ENT_QUOTES); // Prevent HTML issues

    while ($row = $result->fetch_assoc()) 
    {
        echo "<tr>
        <td data-label='Sl.No.'>" . $index . "</td>
        <td data-label='User Type'>" . highlightMatch($row["userType"],$highlight) . "</td>
        <td data-label='User Id'>" . highlightMatch($row["userId"],$highlight) . "</td>
        <td data-label='Gender'>" . highlightMatch($row["gender"],$highlight) . "</td>
        <td data-label='Date of Birth'>" . (empty($row["dob"]) ? "N/A" : highlightMatch($row["dob"],$highlight)) . "</td>
        <td data-label='Phone Number'>" . highlightMatch($row["phoneNumber"],$highlight) . "</td>
        <td data-label='Email Id'>" . highlightMatch($row["emailId"],$highlight) . "</td>
        <td data-label='Address'>" . (empty($row["address"]) ? "N/A" : highlightMatch($row["address"],$highlight)) . "</td>
      </tr>";
        $index++;
    }
} 
else 
{
    echo "<tr><td colspan='8'><center>No results found.</center></td></tr>";
}

$stmt->close();
$conn->close();
?>
