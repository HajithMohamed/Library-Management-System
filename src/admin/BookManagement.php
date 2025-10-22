<?php
include '../../config/config.php';
session_start();
include DIR_URL.'src/global/middleware.php';
$userId = $_SESSION['userId'];
$userType = $_SESSION['userType'];

if ($userType != 'Admin') {
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

include DIR_URL.'config/dbConnection.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'get_book':
            $isbn = $_POST['isbn'];
            $sql = "SELECT * FROM books WHERE isbn = '$isbn'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                echo json_encode(['success' => true, 'book' => $result->fetch_assoc()]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Book not found']);
            }
            exit();
            
        case 'update_book':
            $isbn = $_POST['isbn'];
            $bookName = $_POST['bookName'];
            $authorName = $_POST['authorName'];
            $publisherName = $_POST['publisherName'];
            $description = $_POST['description'];
            $category = $_POST['category'];
            $publicationYear = $_POST['publicationYear'];
            
            // Handle file upload
            $bookImage = '';
            if (isset($_FILES['bookImage']) && $_FILES['bookImage']['error'] == 0) {
                $uploadDir = DIR_URL . 'assets/images/books/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileExtension = strtolower(pathinfo($_FILES['bookImage']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    $fileName = $isbn . '_' . time() . '.' . $fileExtension;
                    $uploadPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['bookImage']['tmp_name'], $uploadPath)) {
                        $bookImage = 'assets/images/books/' . $fileName;
                    }
                }
            }
            
            $sql = "UPDATE books SET 
                    bookName = '$bookName',
                    authorName = '$authorName',
                    publisherName = '$publisherName',
                    description = '$description',
                    category = '$category',
                    publicationYear = '$publicationYear'";
            
            if ($bookImage) {
                $sql .= ", bookImage = '$bookImage'";
            }
            
            $sql .= " WHERE isbn = '$isbn'";
            
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['success' => true, 'message' => 'Book updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating book: ' . $conn->error]);
            }
            exit();
            
        case 'delete_book':
            $isbn = $_POST['isbn'];
            
            // Get book image path to delete file
            $sql = "SELECT bookImage FROM books WHERE isbn = '$isbn'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $book = $result->fetch_assoc();
                if ($book['bookImage'] && file_exists(DIR_URL . $book['bookImage'])) {
                    unlink(DIR_URL . $book['bookImage']);
                }
            }
            
            $sql = "DELETE FROM books WHERE isbn = '$isbn'";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['success' => true, 'message' => 'Book deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting book: ' . $conn->error]);
            }
            exit();
    }
}

// Get all books for display
$sql = "SELECT * FROM books ORDER BY bookName ASC";
$result = $conn->query($sql);
$books = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}

// Get statistics
$statsSql = "SELECT 
    COUNT(*) as total_books,
    SUM(available) as total_available,
    SUM(borrowed) as total_borrowed,
    SUM(totalCopies) as total_copies
    FROM books";
$statsResult = $conn->query($statsSql);
$stats = $statsResult->fetch_assoc();

// Get recent activity
$recentSql = "SELECT 
    bs.isbn, b.bookName, bs.date_added, bs.new_arrivals, bs.total_borrowed, bs.total_returned
    FROM book_statistics bs
    JOIN books b ON bs.isbn = b.isbn
    ORDER BY bs.date_added DESC
    LIMIT 10";
$recentResult = $conn->query($recentSql);
$recentActivity = [];
if ($recentResult->num_rows > 0) {
    while ($row = $recentResult->fetch_assoc()) {
        $recentActivity[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Management</title>
    <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/fontawesome-free-6.7.2-web/css/all.min.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/tableLayoutWithSearch.css" />
    <style>
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 2em;
        }
        
        .stat-card p {
            margin: 0;
            opacity: 0.9;
        }
        
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .book-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .book-card:hover {
            transform: translateY(-5px);
        }
        
        .book-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        
        .book-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        
        .btn-edit {
            background: #007bff;
            color: white;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-view {
            background: #28a745;
            color: white;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        
        .search-container {
            margin-bottom: 20px;
        }
        
        .search-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="background-container"></div>
    
    <div class="container">
        <h1>Book Management System</h1>
        
        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <h3><?php echo $stats['total_books']; ?></h3>
                <p>Total Books</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['total_available']; ?></h3>
                <p>Available Books</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['total_borrowed']; ?></h3>
                <p>Borrowed Books</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['total_copies']; ?></h3>
                <p>Total Copies</p>
            </div>
        </div>
        
        <!-- Search and Add Book -->
        <div class="search-container">
            <input type="text" id="searchInput" class="search-input" placeholder="Search books by name, author, or ISBN...">
            <button onclick="openAddBookModal()" class="btn" style="background: #28a745; color: white; margin-top: 10px;">
                <i class="fas fa-plus"></i> Add New Book
            </button>
        </div>
        
        <!-- Books Grid -->
        <div class="book-grid" id="bookGrid">
            <?php foreach ($books as $book): ?>
            <div class="book-card" data-search="<?php echo strtolower($book['bookName'] . ' ' . $book['authorName'] . ' ' . $book['isbn']); ?>">
                <?php if ($book['bookImage']): ?>
                <img src="<?php echo BASE_URL . $book['bookImage']; ?>" alt="Book Cover" class="book-image">
                <?php else: ?>
                <div class="book-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #666;">
                    <i class="fas fa-book" style="font-size: 3em;"></i>
                </div>
                <?php endif; ?>
                
                <h3><?php echo htmlspecialchars($book['bookName']); ?></h3>
                <p><strong>Author:</strong> <?php echo htmlspecialchars($book['authorName']); ?></p>
                <p><strong>Publisher:</strong> <?php echo htmlspecialchars($book['publisherName']); ?></p>
                <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></p>
                <p><strong>Available:</strong> <?php echo $book['available']; ?> | <strong>Borrowed:</strong> <?php echo $book['borrowed']; ?></p>
                <?php if ($book['category']): ?>
                <p><strong>Category:</strong> <?php echo htmlspecialchars($book['category']); ?></p>
                <?php endif; ?>
                
                <div class="book-actions">
                    <button onclick="viewBook('<?php echo $book['isbn']; ?>')" class="btn btn-view">
                        <i class="fas fa-eye"></i> View
                    </button>
                    <button onclick="editBook('<?php echo $book['isbn']; ?>')" class="btn btn-edit">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button onclick="deleteBook('<?php echo $book['isbn']; ?>')" class="btn btn-delete">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Add/Edit Book Modal -->
    <div id="bookModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Add New Book</h2>
            <form id="bookForm" enctype="multipart/form-data">
                <input type="hidden" id="action" name="action" value="update_book">
                <input type="hidden" id="bookIsbn" name="isbn">
                
                <div class="form-group">
                    <label for="bookName">Book Name *</label>
                    <input type="text" id="bookName" name="bookName" required>
                </div>
                
                <div class="form-group">
                    <label for="authorName">Author Name *</label>
                    <input type="text" id="authorName" name="authorName" required>
                </div>
                
                <div class="form-group">
                    <label for="publisherName">Publisher Name *</label>
                    <input type="text" id="publisherName" name="publisherName" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
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
                    <label for="publicationYear">Publication Year</label>
                    <input type="number" id="publicationYear" name="publicationYear" min="1800" max="<?php echo date('Y'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="bookImage">Book Cover Image</label>
                    <input type="file" id="bookImage" name="bookImage" accept="image/*">
                </div>
                
                <button type="submit" class="btn" style="background: #007bff; color: white; width: 100%;">
                    Save Book
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const bookCards = document.querySelectorAll('.book-card');
            
            bookCards.forEach(card => {
                const searchData = card.getAttribute('data-search');
                if (searchData.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Modal functions
        function openAddBookModal() {
            document.getElementById('modalTitle').textContent = 'Add New Book';
            document.getElementById('bookForm').reset();
            document.getElementById('bookModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('bookModal').style.display = 'none';
        }
        
        function viewBook(isbn) {
            // Implementation for viewing book details
            alert('View book: ' + isbn);
        }
        
        function editBook(isbn) {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_book&isbn=' + isbn
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const book = data.book;
                    document.getElementById('modalTitle').textContent = 'Edit Book';
                    document.getElementById('bookIsbn').value = book.isbn;
                    document.getElementById('bookName').value = book.bookName;
                    document.getElementById('authorName').value = book.authorName;
                    document.getElementById('publisherName').value = book.publisherName;
                    document.getElementById('description').value = book.description || '';
                    document.getElementById('category').value = book.category || '';
                    document.getElementById('publicationYear').value = book.publicationYear || '';
                    document.getElementById('bookModal').style.display = 'block';
                } else {
                    alert('Error loading book details');
                }
            });
        }
        
        function deleteBook(isbn) {
            if (confirm('Are you sure you want to delete this book?')) {
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=delete_book&isbn=' + isbn
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Book deleted successfully');
                        location.reload();
                    } else {
                        alert('Error deleting book: ' + data.message);
                    }
                });
            }
        }
        
        // Form submission
        document.getElementById('bookForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Book saved successfully');
                    location.reload();
                } else {
                    alert('Error saving book: ' + data.message);
                }
            });
        });
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('bookModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>





