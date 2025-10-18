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

$message="";
$rows = "";
//By default,all books in the library are shown
$sql = "SELECT books.isbn, books.bookName, books.authorName, books.publisherName, books.available, transactions.tid 
FROM books 
LEFT OUTER JOIN transactions 
  ON books.isbn = transactions.isbn AND transactions.userId = '$userId' 
ORDER BY books.bookName ASC";
$result=$conn->query($sql);
if($result->num_rows>0)//check if there are any books in the library
{
  $index=1;
  while ($book_details = $result->fetch_assoc()) 
  {
    $canBeBorrowed= ($book_details["tid"]===NULL && (int)$book_details["available"]!=0); // marks the books which are not borroowed by the current user and have an available copy to be borrowed
    $rows .= "<tr>
      <td data-label='Sl.No.'>" . $index . "</td>
      <td data-label='ISBN No.'>" . htmlspecialchars($book_details["isbn"]) . "</td>
      <td data-label='Book Name'>" . htmlspecialchars($book_details["bookName"]) . "</td>
      <td data-label='Author Name'>" . htmlspecialchars($book_details["authorName"]) . "</td>
      <td data-label='Publisher Name'>" . htmlspecialchars($book_details["publisherName"]) . "</td>
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
else//the library does not have any books
{
  $message="<tr><td colspan='7'><center>The library is empty...</center></td></tr>";
}
$conn->close();
?>
<!DOCTYPE html>  
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Books</title>
  <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/fontawesome-free-6.7.2-web/css/all.min.css" />
  <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/tableLayoutWithSearch.css" />
  <script>
  // Using AJAX for real time search
  function searchBooks() 
  {
    const query=document.getElementById('search').value;
    const xhr=new XMLHttpRequest();
    xhr.open('GET','search_SearchBooks.php?query='+query,true);
    xhr.onreadystatechange=function()
    {
      if(xhr.readyState===4 && xhr.status===200)
      {
        document.getElementById('searchResults').innerHTML=xhr.responseText;
      }
    };
    xhr.send();
  }
  </script>
</head>
<body>

<div class="background-container"></div>

  <main class="content-wrapper">
    <h1> Search Books</h1>
    <!-- Search Bar -->
    <div class="search-container">

      <input type="text" id="search" name="search" placeholder="Search here..." required 
      onkeyup="searchBooks()"/>

      <input type="button" id="search-button" value="Search" onclick="searchBooks()"/>
    </div>

  <!-- Table Display -->
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Sl.No.</th>
          <th>ISBN No.</th>
          <th>Book Name</th>
          <th>Author Name</th>
          <th>Publisher Name</th>
          <th>Quantity Available</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="searchResults">
        <?=$message?>
        <?=$rows?>
      </tbody>
    </table>
  </div>
</main>
</body>
</html>
