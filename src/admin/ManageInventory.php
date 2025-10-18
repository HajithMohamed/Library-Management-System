<?php
include '../../config/config.php';
session_start(); // Start session
include DIR_URL.'src/global/middleware.php';
include DIR_URL.'config/dbConnection.php';
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

$isAjax = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']));
if ($isAjax) {
  header('Content-Type: application/json');
  $action = $_POST['action'];

  if ($action === 'get') {
    $isbn = $_POST['isbn'] ?? '';
    $res = $conn->query("SELECT * FROM books WHERE isbn='".$conn->real_escape_string($isbn)."'");
    if ($res && $res->num_rows > 0) {
      echo json_encode(['success'=>true,'book'=>$res->fetch_assoc()]);
    } else {
      echo json_encode(['success'=>false,'message'=>'Book not found']);
    }
    $conn->close();
    exit;
  }

  if ($action === 'create' || $action === 'update') {
    $isbn = $_POST['isbn'] ?? '';
    $bookName = $_POST['bookName'] ?? '';
    $authorName = $_POST['authorName'] ?? '';
    $publisherName = $_POST['publisherName'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 1);
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '';
    $publicationYear = $_POST['publicationYear'] ?? '';
    $bookImage = '';

    if (isset($_FILES['bookImage']) && $_FILES['bookImage']['error'] === 0) {
      $uploadDir = DIR_URL.'assets/images/books/';
      if (!file_exists($uploadDir)) { @mkdir($uploadDir, 0777, true); }
      $ext = strtolower(pathinfo($_FILES['bookImage']['name'], PATHINFO_EXTENSION));
      $allowed = ['jpg','jpeg','png','gif','webp'];
      if (in_array($ext, $allowed)) {
        $fname = $isbn.'_'.time().'.'.$ext;
        if (move_uploaded_file($_FILES['bookImage']['tmp_name'], $uploadDir.$fname)) {
          $bookImage = 'assets/images/books/'.$fname;
        }
      }
    }

    if ($action === 'create') {
      $sql = "INSERT INTO books (isbn,bookName,authorName,publisherName,available,borrowed,bookImage,description,category,publicationYear,totalCopies) VALUES('".$conn->real_escape_string($isbn)."','".$conn->real_escape_string($bookName)."','".$conn->real_escape_string($authorName)."','".$conn->real_escape_string($publisherName)."','$quantity','0','".$conn->real_escape_string($bookImage)."','".$conn->real_escape_string($description)."','".$conn->real_escape_string($category)."','".$conn->real_escape_string($publicationYear)."','$quantity')";
      if ($conn->query($sql) === TRUE) {
        $conn->query("INSERT INTO book_statistics (isbn, date_added, new_arrivals) VALUES('".$conn->real_escape_string($isbn)."', CURDATE(), '$quantity')");
        echo json_encode(['success'=>true]);
      } else {
        echo json_encode(['success'=>false,'message'=>$conn->error]);
      }
      $conn->close();
      exit;
    } else {
      $setImage = $bookImage ? ", bookImage='".$conn->real_escape_string($bookImage)."'" : '';
      $sql = "UPDATE books SET bookName='".$conn->real_escape_string($bookName)."', authorName='".$conn->real_escape_string($authorName)."', publisherName='".$conn->real_escape_string($publisherName)."', description='".$conn->real_escape_string($description)."', category='".$conn->real_escape_string($category)."', publicationYear='".$conn->real_escape_string($publicationYear)."' $setImage WHERE isbn='".$conn->real_escape_string($isbn)."'";
      if ($conn->query($sql) === TRUE) {
        echo json_encode(['success'=>true]);
      } else {
        echo json_encode(['success'=>false,'message'=>$conn->error]);
      }
      $conn->close();
      exit;
    }
  }

  if ($action === 'delete') {
    $isbn = $_POST['isbn'] ?? '';
    $imgRes = $conn->query("SELECT bookImage FROM books WHERE isbn='".$conn->real_escape_string($isbn)."'");
    if ($imgRes && $imgRes->num_rows > 0) {
      $img = $imgRes->fetch_assoc();
      if (!empty($img['bookImage']) && file_exists(DIR_URL.$img['bookImage'])) { @unlink(DIR_URL.$img['bookImage']); }
    }
    if ($conn->query("DELETE FROM books WHERE isbn='".$conn->real_escape_string($isbn)."'") === TRUE) {
      echo json_encode(['success'=>true]);
    } else {
      echo json_encode(['success'=>false,'message'=>$conn->error]);
    }
    $conn->close();
    exit;
  }
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
      <td data-label='Cover'>" . (empty($book_details["bookImage"]) ? "<div style='width:48px;height:64px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;border:1px solid #ddd;border-radius:4px;'><i class='fa-solid fa-book'></i></div>" : "<img src='".BASE_URL.htmlspecialchars($book_details["bookImage"])."' alt='Cover' style='width:48px;height:64px;object-fit:cover;border-radius:4px;border:1px solid #ddd;' />") . "</td>
      <td data-label='ISBN No.'>" . htmlspecialchars($book_details["isbn"]) . "</td>
      <td data-label='Book Name'>" . htmlspecialchars($book_details["bookName"]) . "</td>
      <td data-label='Author Name'>" . htmlspecialchars($book_details["authorName"]) . "</td>
      <td data-label='Publisher Name'>" . htmlspecialchars($book_details["publisherName"]) . "</td>
      <td data-label='Available'>" . htmlspecialchars($book_details["available"]) . "</td>
      <td data-label='Borrowed'>" . htmlspecialchars($book_details["borrowed"]) . "</td>
      <td data-label='Actions'>
      <div class='action-buttons'>
        <button type='button' class='green-btn' onclick=\"openEditModal('".htmlspecialchars($book_details["isbn"])."')\"><i class='fa-solid fa-pen-to-square'></i>&nbsp;&nbsp;Edit</button>
        <button type='button' class='red-btn' onclick=\"deleteBook('".htmlspecialchars($book_details["isbn"])."')\"><i class='fa-solid fa-trash'></i>&nbsp;&nbsp;Delete</button>
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
  <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/formLayout.css" />
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
    <div style="margin:10px 0;">
      <button type="button" class="green-btn" onclick="openCreateModal()"><i class="fa-solid fa-plus"></i>&nbsp;&nbsp;Add New Book</button>
    </div>
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
            <th>Cover</th>
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
  
  <!-- Modal for Create/Edit Book -->
  <div id="bookModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5);">
    <div class="modal-content" style="background:#fff; max-width:600px; margin:5% auto; padding:20px; border-radius:10px; position:relative;">
      <span class="close" style="position:absolute; right:15px; top:10px; cursor:pointer; font-size:24px;" onclick="closeModal()">&times;</span>
      <h2 id="modalTitle">Add Book</h2>
      <div id="Note"><span id="Note-word">Note:</span> Fields marked with <span class="red-star">*</span> are required.</div>
      <form id="bookForm" enctype="multipart/form-data">
        <input type="hidden" name="action" id="formAction" value="create" />
        <div class="form-group">
          <label for="isbn"><i class="fa-solid fa-barcode" style="margin-right:8px;"></i>ISBN (13 digits)<span class="red-star">*</span></label>
          <input type="text" class="input" name="isbn" id="isbn" maxlength="13" pattern="\d{13}" required />
        </div>
        <div class="form-group">
          <label for="bookName"><i class="fa-solid fa-book" style="margin-right:8px;"></i>Book Name<span class="red-star">*</span></label>
          <input type="text" class="input" name="bookName" id="bookName" required />
        </div>
        <div class="form-group">
          <label for="authorName"><i class="fa-solid fa-user-pen" style="margin-right:8px;"></i>Author Name<span class="red-star">*</span></label>
          <input type="text" class="input" name="authorName" id="authorName" required />
        </div>
        <div class="form-group">
          <label for="publisherName"><i class="fa-solid fa-building" style="margin-right:8px;"></i>Publisher Name<span class="red-star">*</span></label>
          <input type="text" class="input" name="publisherName" id="publisherName" required />
        </div>
        <div class="form-group">
          <label for="quantity"><i class="fa-solid fa-layer-group" style="margin-right:8px;"></i>Quantity<span class="red-star">*</span></label>
          <input type="number" class="input" name="quantity" id="quantity" min="1" value="1" />
        </div>
        <div class="form-group">
          <label for="description"><i class="fa-solid fa-align-left" style="margin-right:8px;"></i>Description</label>
          <textarea class="input" name="description" id="description" rows="3"></textarea>
        </div>
        <div class="form-group">
          <label for="category"><i class="fa-solid fa-tag" style="margin-right:8px;"></i>Category</label>
          <select class="input" name="category" id="category">
            <option value="">Select Category</option>
            <option value="Fiction">Fiction</option>
            <option value="Non-Fiction">Non-Fiction</option>
            <option value="Science">Science</option>
            <option value="Technology">Technology</option>
            <option value="History">History</option>
            <option value="Biography">Biography</option>
            <option value="Education">Education</option>
            <option value="Reference">Reference</option>
          </select>
        </div>
        <div class="form-group">
          <label for="publicationYear"><i class="fa-regular fa-calendar" style="margin-right:8px;"></i>Publication Year</label>
          <input type="number" class="input" name="publicationYear" id="publicationYear" min="1800" max="<?php echo date('Y'); ?>" />
        </div>
        <div class="form-group">
          <label for="bookImage"><i class="fa-regular fa-image" style="margin-right:8px;"></i>Book Cover Image</label>
          <input type="file" class="input" name="bookImage" id="bookImage" accept="image/*" />
          <div style="margin-top:10px;display:flex;gap:12px;align-items:center;">
            <img id="coverPreview" alt="Cover Preview" style="display:none;width:96px;height:128px;object-fit:cover;border:1px solid #ddd;border-radius:6px;" />
            <span id="noPreviewText" style="color:#777;">No image selected</span>
          </div>
        </div>
        <button type="submit" class="green-btn" style="width:100%;">Save</button>
      </form>
    </div>
  </div>

  <script>
    function openCreateModal(){
      document.getElementById('modalTitle').innerText = 'Add Book';
      document.getElementById('formAction').value = 'create';
      document.getElementById('isbn').disabled = false;
      document.getElementById('bookForm').reset();
      document.getElementById('bookModal').style.display='block';
    }
    function openEditModal(isbn){
      fetch('', { method:'POST', headers:{'Accept':'application/json'}, body:new URLSearchParams({action:'get', isbn}) })
      .then(r=>r.json()).then(data=>{
        if(data.success){
          const b = data.book;
          document.getElementById('modalTitle').innerText = 'Edit Book';
          document.getElementById('formAction').value = 'update';
          document.getElementById('isbn').value = b.isbn; document.getElementById('isbn').disabled = true;
          document.getElementById('bookName').value = b.bookName || '';
          document.getElementById('authorName').value = b.authorName || '';
          document.getElementById('publisherName').value = b.publisherName || '';
          document.getElementById('quantity').value = b.totalCopies || 1;
          document.getElementById('description').value = b.description || '';
          document.getElementById('category').value = b.category || '';
          document.getElementById('publicationYear').value = b.publicationYear || '';
          document.getElementById('bookModal').style.display='block';
        } else { alert('Failed to load book'); }
      });
    }
    function closeModal(){ document.getElementById('bookModal').style.display='none'; }
    window.onclick = function(e){ if(e.target===document.getElementById('bookModal')) closeModal(); }

    const imageInput = document.getElementById('bookImage');
    const previewImg = document.getElementById('coverPreview');
    const noPreviewText = document.getElementById('noPreviewText');

    imageInput.addEventListener('change', function(){
      const file = this.files && this.files[0] ? this.files[0] : null;
      if(!file){ previewImg.style.display='none'; noPreviewText.style.display='inline'; return; }
      const url = URL.createObjectURL(file);
      previewImg.src = url;
      previewImg.style.display = 'inline-block';
      noPreviewText.style.display = 'none';
    });

    document.getElementById('bookForm').addEventListener('submit', function(e){
      e.preventDefault();
      const fd = new FormData(this);
      fetch('', { method:'POST', body: fd }).then(r=>r.json()).then(data=>{
        if(data.success){
          alert('Saved successfully');
          location.reload();
        } else {
          alert('Error: ' + (data.message || 'Unable to save'));
        }
      });
    });

    function deleteBook(isbn){
      if(!confirm('Delete this book?')) return;
      fetch('', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=delete&isbn='+encodeURIComponent(isbn) })
      .then(r=>r.json()).then(data=>{
        if(data.success){ alert('Deleted'); location.reload(); } else { alert('Error: '+(data.message||'')); }
      });
    }
  </script>
</body>
</html>