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

$message="";
$rows = "";
//By default,all books in the library are shown
$sql="SELECT * FROM books ORDER BY bookName ASC";
$result=$conn->query($sql);
if($result->num_rows>0)//checking whether the library has any books
{
  $index=1;
  while ($book_details = $result->fetch_assoc()) 
  {
    $quantityNotZero= ((int)$book_details["available"]!=0); //checking if available quantity is not zero
    
    $rows .= "<tr>
      <td data-label='Sl.No.'>" . $index . "</td>
      <td data-label='ISBN No.'>" . htmlspecialchars($book_details["isbn"]) . "</td>
      <td data-label='Book Name'>" . htmlspecialchars($book_details["bookName"]) . "</td>
      <td data-label='Author Name'>" . htmlspecialchars($book_details["authorName"]) . "</td>
      <td data-label='Publisher Name'>" . htmlspecialchars($book_details["publisherName"]) . "</td>
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
else//the library does not have any books
{
  $message="<tr><td colspan='8'><center>The library is empty...</center></td></tr>";
}
$conn->close();
?>
<!DOCTYPE html>  
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Inventory</title>
  <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/fontawesome-free-6.7.2-web/css/all.min.css" />
  <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/tableLayoutWithSearch.css" />
  <script>
  // Using AJAX for real time search
  function searchBooks() 
  {
    const query=document.getElementById('search').value;
    const xhr=new XMLHttpRequest();
    xhr.open('GET','search_ManageInventory.php?query='+query,true);
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
    <h1> Manage Inventory</h1>
    <!-- Search Bar -->
    <div class="search-container">
      <input type="text" id="search" name="search" placeholder="Search for books..." required onkeyup="searchBooks()"/>
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
            <th>Available</th>
            <th>Borrowed</th>
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