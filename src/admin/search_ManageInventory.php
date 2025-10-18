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
if ($_SERVER["REQUEST_METHOD"] !== "GET" || !isset($_GET['query'])) // if someone tries to directly access this page using it's url
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
    exit;
}

// Keep showing the "library is empty" message if the user searches anything while the library is empty
$sql="SELECT * FROM books ORDER BY bookName ASC";
$result=$conn->query($sql);
if($result->num_rows==0)//check if there are any books in the library
{
     echo "<tr><td colspan='8'><center>The library is empty...</center></td></tr>";
     exit;
}

$query=$_GET['query']; // Get the search query from the AJAX request
$sql="SELECT * FROM books WHERE isbn LIKE ? OR bookName LIKE ? OR authorName LIKE ? OR publisherName LIKE ?
 ORDER BY bookName ASC";
$stmt=$conn->prepare($sql);
$searchTerm=$query."%";
$stmt->bind_param('ssss',$searchTerm,$searchTerm,$searchTerm,$searchTerm);
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

    while ($book_details = $result->fetch_assoc()) 
    {
        $quantityNotZero= ((int)$book_details["available"]!=0);
        echo "<tr>
      <td data-label='Sl.No.'>" . $index . "</td>
      <td data-label='ISBN No.'>" . highlightMatch($book_details["isbn"],$highlight) . "</td>
      <td data-label='Book Name'>" . highlightMatch($book_details["bookName"],$highlight) . "</td>
      <td data-label='Author Name'>" . highlightMatch($book_details["authorName"],$highlight) . "</td>
      <td data-label='Publisher Name'>" . highlightMatch($book_details["publisherName"],$highlight) . "</td>
      <td data-label='Available'>" . htmlspecialchars($book_details["available"]) . "</td>
      <td data-label='Borrowed'>" . htmlspecialchars($book_details["borrowed"]) . "</td>
      <td data-label='Actions'>
      <div class='action-buttons'>
      <form action='AddBooks.php' method='GET'>
      <input type='hidden' name='isbn' value='".htmlspecialchars($book_details["isbn"])."'>
      <input type='hidden' name='bookName' value='".htmlspecialchars($book_details["bookName"])."'>
      <input type='hidden' name='authorName' value='".htmlspecialchars($book_details["authorName"])."'>
      <input type='hidden' name='publisherName' value='".htmlspecialchars($book_details  ["publisherName"])."'>
      <button type='submit' name='add' class='green-btn'><i class='fa-solid fa-plus'></i>&nbsp;&nbsp;Add More</button>
      </form>     
      <form action='RemoveBooks.php' method='GET'>
      <input type='hidden' name='isbn' value='".htmlspecialchars($book_details["isbn"])."'>
      <input type='hidden' name='bookName' value='".htmlspecialchars($book_details["bookName"])."'>
      <input type='hidden' name='authorName' value='".htmlspecialchars($book_details["authorName"])."'>
      <input type='hidden' name='publisherName' value='".htmlspecialchars($book_details  ["publisherName"])."'>
      <button type='submit' name='remove' class='red-btn' " . ($quantityNotZero ? "" : "disabled") . "><i class='fa-solid fa-minus'></i>&nbsp;&nbsp;Remove</button>
      </form>    
      </div>
      </td>
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
