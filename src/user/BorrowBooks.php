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

$message="";

$isbn = $_POST['isbn'] ?? $_GET['isbn'] ?? '';
$bookName = $_POST['bookName'] ?? $_GET['bookName'] ?? '';
$authorName = $_POST['authorName'] ?? $_GET['authorName'] ?? '';
$publisherName = $_POST['publisherName'] ?? $_GET['publisherName'] ?? '';
$borrowDate=date("Y-m-d");
$returnDate=date("Y-m-d", strtotime("+7 days"));

if($_SERVER["REQUEST_METHOD"]==="POST")
{
include DIR_URL.'config/dbConnection.php';
$sql="SELECT * FROM books WHERE isbn='$isbn' ";
$result2=$conn->query($sql);
if($result2->num_rows==0)//requested book does not exist in the library
{
  $message="Invalid request! This book does not exist in the library.";
}
else // requested book to be borrowed exists in the library
{
  $book_details = $result2->fetch_assoc();
  if( $book_details['bookName']!=$bookName || $book_details['authorName']!=$authorName || $book_details['publisherName']!=$publisherName) // book details do not match with the ISBN entered
  {
    $message="Book details doesn't match with ISBN. Please enter the correct ISBN or check the book details.";
  }
  else // book details match with the ISBN entered.
  {
    $sql="SELECT * FROM transactions WHERE userId='$userId' AND isbn='$isbn' ";
    $result=$conn->query($sql);
    if($result->num_rows>0)//user has already borrowed the book before
    {
      $message="Invalid request! You have already borrowed a copy of this book.";
    }
    else//user has not borrowed this book
    {
      $available=$book_details['available'];
      if($available==0)//requested book has no available copy to be borrowed
      {
        $message="This book is currently out of stock.Please try again later!";
      }
      else
      {
        $sql3="UPDATE books SET available = available -1,borrowed=borrowed+1 
        WHERE isbn = '$isbn' ";

        $tid="TXN".time()."_".substr(md5($userId),0,8); // creating an unique transaction id to record the transaction in transactions table
        $fine=0; // initially the fine is 0 for the first 7 days when the user borrows a book
        
        $sql4="INSERT INTO transactions(tid,userId,isbn,fine,borrowDate,returnDate,lastFinePaymentDate) 
        VALUES('$tid','$userId','$isbn','$fine','$borrowDate','$returnDate','')";
        if($conn->query($sql3)===TRUE && $conn->query($sql4)===TRUE)
        {
          echo "<script>alert('Book(s) issued successfully. Return Date is ".date("d/m/Y", strtotime($returnDate))." .'); 
          window.location.href='userDashboard.php';</script>";
        }
        else
        {
          echo "Error: ".$sql."<br>".$conn->error;
        }
      }
    }
  }
}
$conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Borrow Books</title>
  <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/formLayout.css" />
  <script>
    //removing error message
    function initiate()
    {
      document.getElementById("bookError").innerHTML="";
    }
  </script>
</head>
<body>

<div class="background-container"></div>

  <div class="container">
    <h2>Enter Book Details</h2>
    <form id="issueBooksForm" method="POST" onsubmit="return true;" >

     <div class="form-group">
        <label for="isbn">ISBN (13 digits):<span class="red-star">*</span></label>
        <input type="text" id="isbn" name="isbn" maxlength="13" pattern="\d{13}" title="Enter exactly 13 digits"
        required class="input" onfocus="initiate()" 
        value="<?= htmlspecialchars($isbn) ?>">
        <span id="bookError" class="error"><?=$message?></span>
      </div>

      <div class="form-group">
        <label for="bookName">Book Name:<span class="red-star">*</span></label>
        <input type="text" id="bookName" name="bookName" required class="input"
        value="<?= htmlspecialchars($bookName) ?>">
      </div>
  
      <div class="form-group">
        <label for="authorName">Author Name:<span class="red-star">*</span></label>
        <input type="text" id="authorName" name="authorName" required class="input"
        value="<?= htmlspecialchars($authorName) ?>">
      </div>

      <div class="form-group">
        <label for="publisherName">Publisher Name:<span class="red-star">*</span></label>
        <input type="text" id="publisherName" name="publisherName" required class="input" 
        value="<?= htmlspecialchars($publisherName) ?>">
      </div>

      <div class="form-group">
        <label for="borrowDate">Issue Date:</label>
        <input type="text" id="borrowDate" name="borrowDate" readonly class="input" 
        value="<?= htmlspecialchars(date('d/m/Y', strtotime($borrowDate))) ?>">
      </div>

      <div class="form-group">
        <label for="returnDate">Return Date:</label>
        <input type="text" id="returnDate" name="returnDate" readonly class="input" 
        value="<?= htmlspecialchars(date('d/m/Y', strtotime($returnDate))) ?>">
      </div>

      <div id="Note"><b id="Note-word"><u>Note:</b></u> Return date is automatically set to 7 days from the Issue date. On failure to return book
      within said return date, fine is calculated as Rs.10 for each day after the return date till the book
      is returned.</div>

      <button type="submit">Borrow Book</button>
    </form>
  </div>
</body>
</html>