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

$message="";
$message2="";

$isbn=$_POST['isbn'] ?? $_GET['isbn'] ?? '';
$bookName=$_POST['bookName'] ?? $_GET['bookName'] ?? '';
$authorName=$_POST['authorName'] ?? $_GET['authorName'] ?? '';
$publisherName=$_POST['publisherName'] ?? $_GET['publisherName'] ?? '';
$quantity=$_POST['quantity'] ?? '1';

if($_SERVER["REQUEST_METHOD"]==="POST")
{
include DIR_URL.'config/dbConnection.php';
$sql="SELECT * FROM books WHERE isbn='$isbn'";
$result=$conn->query($sql);
if($result->num_rows==0)//check if requested book does not exist in the library
{
  $message="Invalid request ! This book does not exist in the library.";
}
else// requested book to be removed/updated exists in the library
{
  $book_details=mysqli_fetch_assoc($result);
  
  if( $book_details['bookName']!=$bookName || $book_details['authorName']!=$authorName || $book_details['publisherName']!=$publisherName) // book details do not match with the ISBN entered.
  {
    $message="Book details doesn't match with ISBN. Please enter the correct ISBN or check the book details.";
  }
  else // book details match with the ISBN entered.
  {
    $quantityAvailable=$book_details['available'];//quantity available in the library at present

    if($quantity < $quantityAvailable)//deletion quantity requested is less than the quantity available in the library
    {
      $sql2 = "UPDATE books SET available = $quantityAvailable - $quantity WHERE isbn = '$isbn' ";
      if($conn->query($sql2)===TRUE)
      {
        echo "<script>alert('Book(s) removed successfully'); 
        window.location.href='adminDashboard.php';</script>";
      }
      else
      {
        echo "Error: ".$sql2."<br>".$conn->error;
      }
    }
    else if($quantity == $quantityAvailable)//deletion quantity requested is same as the quantity avaialable in the library
    {
      if($book_details['borrowed'] > 0)//book to be deleted entirely from library is borrowed by someone
      {
        $sql3 = "UPDATE books SET available = '0' WHERE isbn = '$isbn' ";
        
        if ($conn->query($sql3) === TRUE) 
        {
          echo "<script>alert('Book(s) removed successfully!'); 
          window.location.href = 'adminDashboard.php';</script>";
        } 
        else 
        {
          echo "Error updating record: " . $conn->error;
        }
      }
      else//book to be removed entirely from library is not borrowed by anyone
      {
        $sql3="DELETE FROM books WHERE isbn='$isbn' ";
        if($conn->query($sql3)===TRUE)
        {
          echo "<script>alert('Book(s) removed successfully'); 
          window.location.href='adminDashboard.php';</script>";
        }
        else
        {
          echo "Error: ".$sql3."<br>".$conn->error;
        }
      }
    }
    else//deletion quantity requested is more than the quantity available in the library
    {
      $message2="Invalid qunatity entered";
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
  <title>Remove Books</title>
  <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/formLayout.css" />

  <script>
    //removing book details do not match with isbn error message
    function initiate()
    {
      document.getElementById("bookError").innerHTML="";
    }
    //removing invalid quantity error message
    function initiate2()
    {
     document.getElementById("quantityError").innerHTML="";
    }
</script>
</head>
<body>

<div class="background-container"></div>

  <div class="container">
    <h2>Enter Book Details</h2>
    <form id="AddBooksForm" method="POST" onsubmit="return true;" >

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
        <label for="quantity">Quantity:<span class="red-star">*</span></label>
        <input type="number" id="quantity" name="quantity" min="1" max="100" step="1"
        required class="input" 
        value="<?= htmlspecialchars($quantity) ?>">
        <span id="quantityError" class="error"><?=$message2?></span>
      </div>

      <button type="submit">Remove book from the library</button>
    </form>
  </div>
</body>
</html>