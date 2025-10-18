<?php
include '../../config/config.php';
session_start(); // Start session
include DIR_URL.'src/global/middleware.php';
$userId = $_SESSION['userId'];//Fetching userId and userType from session data
$userType = $_SESSION['userType'];
if ($userType != 'Admin' && $userType != 'Librarian') // allow Admin and Librarian
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

$redirectToInventory = true;
if ($redirectToInventory) {
  header('Location: ManageInventory.php');
  exit();
}

$message="";

$isbn=$_POST['isbn'] ?? $_GET['isbn'] ?? '';
$bookName=$_POST['bookName'] ?? $_GET['bookName'] ?? '';
$authorName=$_POST['authorName'] ?? $_GET['authorName'] ?? '';
$publisherName=$_POST['publisherName'] ?? $_GET['publisherName'] ?? '';
$quantity=$_POST['quantity'] ?? '1';
$description=$_POST['description'] ?? '';
$category=$_POST['category'] ?? '';
$publicationYear=$_POST['publicationYear'] ?? '';
$bookImage='';

if($_SERVER["REQUEST_METHOD"]==="POST")
{
include DIR_URL.'config/dbConnection.php';

// Handle file upload for book image
if(isset($_FILES['bookImage']) && $_FILES['bookImage']['error'] == 0) {
    $uploadDir = DIR_URL . 'assets/images/books/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileExtension = strtolower(pathinfo($_FILES['bookImage']['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if(in_array($fileExtension, $allowedExtensions)) {
        $fileName = $isbn . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;
        
        if(move_uploaded_file($_FILES['bookImage']['tmp_name'], $uploadPath)) {
            $bookImage = 'assets/images/books/' . $fileName;
        }
    }
}

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
     $sql="INSERT INTO books (isbn,bookName,authorName,publisherName,available,borrowed,bookImage,description,category,publicationYear,totalCopies) 
     VALUES('$isbn','$bookName','$authorName','$publisherName','$quantity','0','$bookImage','$description','$category','$publicationYear','$quantity')";
     
     if($conn->query($sql)===TRUE)
     {
          // Insert into book statistics for tracking
          $statsSql = "INSERT INTO book_statistics (isbn, date_added, new_arrivals) VALUES('$isbn', CURDATE(), '$quantity')";
          $conn->query($statsSql);
          
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
  <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/fontawesome-free-6.7.2-web/css/all.min.css" />
  <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/formLayout.css" />
</head>
<body>
    
<div class="background-container"></div>

  <div class="container">
    <h2>Enter Book Details</h2>
    <div id="Note"><span id="Note-word">Note:</span> Fields marked with <span class="red-star">*</span> are required. If an ISBN already exists, details will auto-fill.</div>
    <form id="AddBooksForm" method="POST" enctype="multipart/form-data" onsubmit="return true;" >
      
      <div class="form-group">
        <label for="isbn"><i class="fa-solid fa-barcode" style="margin-right:8px;"></i>ISBN (13 digits):<span class="red-star">*</span></label>
        <input type="text" id="isbn" name="isbn" maxlength="13" pattern="\d{13}" title="Enter exactly 13 digits"
        required class="input" onfocus="initiate()" 
        value="<?= htmlspecialchars($isbn) ?>">
        <small class="input-hint">
        If this ISBN already exists, book details will be auto-filled.
        </small>
        <span id="bookError" class="error"><?=$message?></span>
      </div>

      

      <div class="form-group">
        <label for="bookName"><i class="fa-solid fa-book" style="margin-right:8px;"></i>Book Name:<span class="red-star">*</span></label>
        <input type="text" id="bookName" name="bookName" required class="input"
        value="<?= htmlspecialchars($bookName) ?>">
      </div>

      <div class="form-group">
        <label for="authorName"><i class="fa-solid fa-user-pen" style="margin-right:8px;"></i>Author Name:<span class="red-star">*</span></label>
        <input type="text" id="authorName" name="authorName" required class="input"
        value="<?= htmlspecialchars($authorName) ?>">
      </div>

      <div class="form-group">
        <label for="publisherName"><i class="fa-solid fa-building" style="margin-right:8px;"></i>Publisher Name:<span class="red-star">*</span></label>
        <input type="text" id="publisherName" name="publisherName" required class="input" 
        value="<?= htmlspecialchars($publisherName) ?>">
      </div>
      
      <div class="form-group">
        <label for="quantity"><i class="fa-solid fa-layer-group" style="margin-right:8px;"></i>Quantity:<span class="red-star">*</span></label>
        <input type="number" id="quantity" name="quantity" min="1" max="100" step="1"
        required class="input" 
        value="<?= htmlspecialchars($quantity) ?>">
      </div>

      <div class="form-group">
        <label for="description"><i class="fa-solid fa-align-left" style="margin-right:8px;"></i>Description:</label>
        <textarea id="description" name="description" class="input" rows="3" 
        placeholder="Enter book description..."><?= htmlspecialchars($description) ?></textarea>
      </div>

      <div class="form-group">
        <label for="category"><i class="fa-solid fa-tag" style="margin-right:8px;"></i>Category:</label>
        <select id="category" name="category" class="input">
          <option value="">Select Category</option>
          <option value="Fiction" <?= $category == 'Fiction' ? 'selected' : '' ?>>Fiction</option>
          <option value="Non-Fiction" <?= $category == 'Non-Fiction' ? 'selected' : '' ?>>Non-Fiction</option>
          <option value="Science" <?= $category == 'Science' ? 'selected' : '' ?>>Science</option>
          <option value="Technology" <?= $category == 'Technology' ? 'selected' : '' ?>>Technology</option>
          <option value="History" <?= $category == 'History' ? 'selected' : '' ?>>History</option>
          <option value="Biography" <?= $category == 'Biography' ? 'selected' : '' ?>>Biography</option>
          <option value="Education" <?= $category == 'Education' ? 'selected' : '' ?>>Education</option>
          <option value="Reference" <?= $category == 'Reference' ? 'selected' : '' ?>>Reference</option>
        </select>
      </div>

      <div class="form-group">
        <label for="publicationYear"><i class="fa-regular fa-calendar" style="margin-right:8px;"></i>Publication Year:</label>
        <input type="number" id="publicationYear" name="publicationYear" min="1800" max="<?= date('Y') ?>" 
        class="input" value="<?= htmlspecialchars($publicationYear) ?>">
      </div>

      <div class="form-group">
        <label for="bookImage"><i class="fa-regular fa-image" style="margin-right:8px;"></i>Book Cover Image:</label>
        <input type="file" id="bookImage" name="bookImage" accept="image/*" class="input">
        <small class="input-hint">Upload a cover image for the book (JPG, PNG, GIF, WebP)</small>
        <div style="margin-top:10px;display:flex;gap:12px;align-items:center;">
          <img id="coverPreview" alt="Cover Preview" style="display:none;width:96px;height:128px;object-fit:cover;border:1px solid #ddd;border-radius:6px;" />
          <span id="noPreviewText" style="color:#777;">No image selected</span>
        </div>
      </div>

      <button type="submit">Add book to the library</button>
      <a href="ManageInventory.php" style="display:inline-block;text-align:center;margin-top:10px;text-decoration:none;font-weight:bold;color:#007BFF;">&larr; Back to Manage Inventory</a>
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
    const imageInput = document.getElementById("bookImage");
    const previewImg = document.getElementById("coverPreview");
    const noPreviewText = document.getElementById("noPreviewText");

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
              document.getElementById("description").value = data.description || '';
              document.getElementById("category").value = data.category || '';
              document.getElementById("publicationYear").value = data.publicationYear || '';
              showToast("Book details fetched from existing records.");// Show a toast message
            }
          })
          .catch((error) => {
            console.error("Error fetching book details:", error);
          });
      }
    });

    imageInput.addEventListener('change', function(){
      const file = this.files && this.files[0] ? this.files[0] : null;
      if(!file){ previewImg.style.display='none'; noPreviewText.style.display='inline'; return; }
      const url = URL.createObjectURL(file);
      previewImg.src = url;
      previewImg.style.display = 'inline-block';
      noPreviewText.style.display = 'none';
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
