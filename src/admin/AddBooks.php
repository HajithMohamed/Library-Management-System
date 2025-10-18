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

$isbn=$_POST['isbn'] ?? $_GET['isbn'] ?? '';
$bookName=$_POST['bookName'] ?? $_GET['bookName'] ?? '';
$authorName=$_POST['authorName'] ?? $_GET['authorName'] ?? '';
$publisherName=$_POST['publisherName'] ?? $_GET['publisherName'] ?? '';
$quantity=$_POST['quantity'] ?? '1';

if($_SERVER["REQUEST_METHOD"]==="POST")
{
include DIR_URL.'config/dbConnection.php';
$sql="SELECT * FROM books WHERE isbn='$isbn' ";
$result=$conn->query($sql);
if($result->num_rows>0)//check if the requested book exists in the library
{
  $book_details = $result->fetch_assoc();
  if( $book_details['bookName']==$bookName && $book_details['authorName']==$authorName && $book_details['publisherName']==$publisherName) // Entered book details match with the isbn entered
  {
    echo "<script>
      if (confirm('This book already exists in the library. Do you want to add more quantity of this book into the library?')) {
      window.location.href = 'updateQuantity.php?isbn=$isbn&quantity=$quantity';
      }
      </script>";
  }
  else // Entered book details do not match with the isbn entered
  {
    $message="Book details don't match with ISBN. Please enter the correct ISBN or check the book details.";
  }
}
else// requested book does not exist in the library
{
     $sql="INSERT INTO books (isbn,bookName,authorName,publisherName,available,borrowed) 
     VALUES('$isbn','$bookName','$authorName','$publisherName','$quantity','0')";
     if($conn->query($sql)===TRUE)
     {
          echo "<script>alert('Book(s) added successfully'); 
          window.location.href='adminDashboard.php';</script>";
     }
     else
     {
          echo "Error: ".$sql."<br>".$conn->error;
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
  <title>Add Books</title>
  <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/formLayout.css" />
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
        <small class="input-hint">
        If this ISBN already exists, book details will be auto-filled.
        </small>
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
      </div>

      <button type="submit">Add book to the library</button>
    </form>
  </div>
  <script>
    //removing book details don't match with isbn error message
    function initiate()
    {
      document.getElementById("bookError").innerHTML="";
    }
    
    //using AJAX to fetch book details on entering isbn
    document.addEventListener("DOMContentLoaded", function () {
    const isbnField = document.getElementById("isbn");

    isbnField.addEventListener("input", function () {
      const isbn = isbnField.value.trim();

      if (isbn.length === 13 && /^\d{13}$/.test(isbn)) 
      {
        // Call backend only if 13 digits and all numeric
        fetch("getBookDetailsByISBN.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: "isbn=" + encodeURIComponent(isbn),
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) 
            {
              document.getElementById("bookName").value = data.bookName;
              document.getElementById("authorName").value = data.authorName;
              document.getElementById("publisherName").value = data.publisherName;
              showToast("Book details fetched from existing records.");// Show a toast message
            }
          })
          .catch((error) => {
            console.error("Error fetching book details:", error);
          });
      }
    });

    isbnField.addEventListener("focus", function () {
      document.getElementById("bookError").innerHTML = "";
    });
  });

// Show a toast message on fetching book details on giving isbn
function showToast(message) {
  const toast = document.createElement("div");
  toast.innerText = message;
  toast.style.position = "fixed";
  toast.style.top = "30px";                // Show at the top
  toast.style.left = "50%";               // Center horizontally
  toast.style.transform = "translateX(-50%)";
  toast.style.backgroundColor = "#333";
  toast.style.color = "#fff";
  toast.style.padding = "10px 20px";
  toast.style.borderRadius = "5px";
  toast.style.zIndex = "1000";
  toast.style.boxShadow = "0 2px 10px rgba(0,0,0,0.2)";
  toast.style.fontSize = "0.95rem";
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);  // Remove after 3 seconds
}
  </script>
</body>
</html>
