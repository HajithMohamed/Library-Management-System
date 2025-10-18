<?php
include '../../config/config.php';
session_start(); // Start session
include DIR_URL.'src/global/middleware.php';
include DIR_URL.'config/dbConnection.php';
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
$sql = "SELECT books.isbn, books.bookName, books.authorName, books.publisherName, books.available, transactions.tid 
FROM books
LEFT OUTER JOIN transactions 
  ON books.isbn = transactions.isbn AND transactions.userId = '$userId' 
ORDER BY books.bookName ASC";;
$result=$conn->query($sql);
if($result->num_rows==0)//check if the library does not have any books
{
     echo "<tr><td colspan='7'><center>The library is empty...</center></td></tr>";
     exit;
}

$query=$_GET['query']; // Get the search query from the AJAX request
$sql = "SELECT books.isbn, books.bookName, books.authorName, books.publisherName, books.available, transactions.tid 
FROM books
LEFT OUTER JOIN transactions 
  ON books.isbn = transactions.isbn AND transactions.userId = ? 
WHERE books.isbn LIKE ? OR books.bookName LIKE ? OR books.authorName LIKE ? OR books.publisherName LIKE ?
ORDER BY books.bookName ASC";
$stmt=$conn->prepare($sql);
$searchTerm=$query."%";
$stmt->bind_param('sssss',$userId,$searchTerm,$searchTerm,$searchTerm,$searchTerm);
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
        $canBeBorrowed= ($book_details["tid"]===NULL && (int)$book_details["available"]!=0);// marks the books which are not borroowed by the current user and have an available copy to be borrowed
        echo "<tr>
      <td data-label='Sl.No.'>" . $index . "</td>
      <td data-label='ISBN No.'>" . highlightMatch($book_details["isbn"],$highlight) . "</td>
      <td data-label='Book Name'>" . highlightMatch($book_details["bookName"],$highlight) . "</td>
      <td data-label='Author Name'>" . highlightMatch($book_details["authorName"],$highlight) . "</td>
      <td data-label='Publisher Name'>" . highlightMatch($book_details["publisherName"],$highlight) . "</td>
      <td data-label='Available'>" . htmlspecialchars($book_details["available"]) . "</td>
      <td data-label='Actions'>
      <div class='action-buttons'>
      <form action='BorrowBooks.php' method='GET'>
      <input type='hidden' name='isbn' value='".htmlspecialchars($book_details["isbn"])."'>
      <input type='hidden' name='bookName' value='".htmlspecialchars($book_details["bookName"])."'>
      <input type='hidden' name='authorName' value='".htmlspecialchars($book_details["authorName"])."'>
      <input type='hidden' name='publisherName' value='".htmlspecialchars($book_details  ["publisherName"])."'>
      <button type='submit' name='borrow' class='green-btn' " . ($canBeBorrowed ? "" : "disabled") . "><i class='fa-solid fa-book'></i>&nbsp;&nbsp;Borrow Book</button>
      </form>     
      </div>
      </td>
      </tr>";
        $index++;
    }
} 
else 
{
    echo "<tr><td colspan='7'><center>No results found.</center></td></tr>";
}

$stmt->close();
$conn->close();
?>
