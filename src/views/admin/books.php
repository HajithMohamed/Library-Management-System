<!-- Enhanced Books Management Page - Matching Users Management Design -->

<style>
    
    
    .page-header {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        color: white;
    }
    
    .page-header h1 {
        margin: 0;
        font-size: 2rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }
    
    .stat-card.blue {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .stat-card.green {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .stat-card.orange {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    
    .stat-card.cyan {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        color: white;
    }
    
    .stat-content h4 {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0 0 0.5rem 0;
    }
    
    .stat-content p {
        margin: 0;
        font-size: 1rem;
        opacity: 0.95;
    }
    
    .stat-icon {
        font-size: 3rem;
        opacity: 0.3;
    }
    
    .search-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }
    
    .search-card h5 {
        margin: 0 0 1.5rem 0;
        color: #1f2937;
        font-weight: 600;
    }
    
    .search-form {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr auto;
        gap: 1rem;
        align-items: end;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
    }
    
    .form-group label {
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }
    
    .form-control {
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .btn-group {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    
    .btn-secondary {
        background: #6b7280;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #4b5563;
    }
    
    .btn-success {
        background: #10b981;
        color: white;
    }
    
    .btn-info {
        background: #06b6d4;
        color: white;
    }
    
    .btn-warning {
        background: #f59e0b;
        color: white;
    }
    
    .btn-danger {
        background: #ef4444;
        color: white;
    }
    
    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
    }
    
    .table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .table-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .table-header h5 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .badge.bg-primary {
        background: #667eea;
    }
    
    .badge.bg-trending {
        background: #ef4444;
    }
    
    .badge.bg-special {
        background: #10b981;
    }
    
    .badge.bg-available {
        background: #06b6d4;
    }
    
    .badge.bg-unavailable {
        background: #6b7280;
    }
    
    .badge.bg-warning {
        background: #f59e0b;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    thead {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
    }
    
    th {
        padding: 1rem 1.5rem;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 0.95rem;
    }
    
    td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        color: #4b5563;
    }
    
    tbody tr:hover {
        background: #f9fafb;
    }
    
    .book-img {
        width: 60px;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .book-title {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }
    
    .book-author {
        font-size: 0.85rem;
        color: #6b7280;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }
    
    .alert {
        padding: 1rem 1.25rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-left: 4px solid #10b981;
    }
    
    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }
    
    .back-btn {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        backdrop-filter: blur(10px);
    }
    
    .back-btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    
    @media (max-width: 768px) {
        .search-form {
            grid-template-columns: 1fr;
        }
        
        .stats-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid" style="padding: 1rem;">
    <?php include APP_ROOT . '/views/layouts/header.php'; ?>
    <!-- Page Header -->
    <div class="page-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>
                <i class="fas fa-books"></i> Books Management
            </h1>
            <a href="<?php echo BASE_URL; ?>admin/dashboard" class="btn back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="stats-container">
        <div class="stat-card blue">
            <div class="stat-content">
                <h4><?php echo $pagination['total'] ?? 0; ?></h4>
                <p>Total Books</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-book"></i>
            </div>
        </div>

        <div class="stat-card green">
            <div class="stat-content">
                <h4>
                    <?php 
                    $available = 0;
                    foreach($books as $book) {
                        $available += $book['available'] ?? 0;
                    }
                    echo $available;
                    ?>
                </h4>
                <p>Available Copies</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>

        <div class="stat-card orange">
            <div class="stat-content">
                <h4>
                    <?php 
                    $borrowed = 0;
                    foreach($books as $book) {
                        $borrowed += $book['borrowed'] ?? 0;
                    }
                    echo $borrowed;
                    ?>
                </h4>
                <p>Borrowed Copies</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-book-reader"></i>
            </div>
        </div>

        <div class="stat-card cyan">
            <div class="stat-content">
                <h4><?php echo count($categories ?? []); ?></h4>
                <p>Categories</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-tags"></i>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="search-card">
        <h5><i class="fas fa-search"></i> Search and Filters</h5>
        <form method="GET" action="<?php echo BASE_URL; ?>admin/books" class="search-form">
            <div class="form-group">
                <label for="search">Search Books</label>
                <input type="text" 
                       class="form-control" 
                       id="search" 
                       name="q" 
                       value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" 
                       placeholder="Search by title, author, or ISBN">
            </div>
            
            <div class="form-group">
                <label for="category">Category</label>
                <select class="form-control" id="category" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" 
                                <?php echo (isset($_GET['category']) && $_GET['category'] === $cat) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="available" <?php echo (isset($_GET['status']) && $_GET['status'] === 'available') ? 'selected' : ''; ?>>Available</option>
                    <option value="borrowed" <?php echo (isset($_GET['status']) && $_GET['status'] === 'borrowed') ? 'selected' : ''; ?>>Out of Stock</option>
                    <option value="trending" <?php echo (isset($_GET['status']) && $_GET['status'] === 'trending') ? 'selected' : ''; ?>>Trending</option>
                    <option value="special" <?php echo (isset($_GET['status']) && $_GET['status'] === 'special') ? 'selected' : ''; ?>>Special</option>
                </select>
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="<?php echo BASE_URL; ?>admin/books" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Books Table -->
    <div class="table-card">
        <div class="table-header">
            <h5>
                <i class="fas fa-list"></i> Books List
                <span class="badge bg-primary"><?php echo count($books); ?> records</span>
            </h5>
            <a href="<?php echo BASE_URL; ?>admin/books/add" class="btn btn-success">
                <i class="fas fa-plus"></i> Add New Book
            </a>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Book Details</th>
                        <th>ISBN</th>
                        <th>Category</th>
                        <th>Copies</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($books)): ?>
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo BASE_URL; ?>public/uploads/books/<?php echo htmlspecialchars($book['bookImage'] ?? 'default-book.jpg'); ?>" 
                                         alt="Book cover" 
                                         class="book-img"
                                         onerror="this.src='<?php echo BASE_URL; ?>public/assets/images/default-book.jpg'">
                                </td>
                                <td>
                                    <div class="book-title"><?php echo htmlspecialchars($book['bookName']); ?></div>
                                    <div class="book-author">by <?php echo htmlspecialchars($book['authorName']); ?></div>
                                    <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <?php if ($book['isTrending']): ?>
                                            <span class="badge bg-trending">
                                                <i class="fas fa-fire"></i> Trending
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($book['isSpecial']): ?>
                                            <span class="badge bg-special">
                                                <i class="fas fa-star"></i> <?php echo htmlspecialchars($book['specialBadge'] ?: 'Special'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($book['isbn']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($book['category']); ?></span>
                                </td>
                                <td>
                                    <div><strong>Total:</strong> <?php echo $book['totalCopies']; ?></div>
                                    <div style="color: #10b981;"><strong>Available:</strong> <?php echo $book['available']; ?></div>
                                    <div style="color: #f59e0b;"><strong>Borrowed:</strong> <?php echo $book['borrowed'] ?? 0; ?></div>
                                </td>
                                <td>
                                    <?php if ($book['available'] > 0): ?>
                                        <span class="badge bg-available">
                                            <i class="fas fa-check"></i> Available
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-unavailable">
                                            <i class="fas fa-times"></i> Out of Stock
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" 
                                                class="btn btn-info btn-sm" 
                                                onclick="viewBookDetails('<?php echo htmlspecialchars($book['isbn']); ?>')">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <a href="<?php echo BASE_URL; ?>admin/books/edit?isbn=<?php echo urlencode($book['isbn']); ?>" 
                                           class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button type="button" 
                                                class="btn btn-danger btn-sm" 
                                                onclick="deleteBook('<?php echo htmlspecialchars($book['isbn']); ?>', '<?php echo htmlspecialchars($book['bookName']); ?>')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-book"></i>
                                    <h3>No books found</h3>
                                    <p>Start by adding your first book to the library</p>
                                    <a href="<?php echo BASE_URL; ?>admin/books/add" class="btn btn-primary" style="margin-top: 1rem;">
                                        <i class="fas fa-plus"></i> Add Your First Book
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
        <div style="display: flex; justify-content: center; margin-top: 2rem; gap: 0.5rem;">
            <?php if ($pagination['page'] > 1): ?>
                <a href="?page=<?php echo $pagination['page'] - 1; ?><?php echo isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : ''; ?><?php echo isset($_GET['category']) ? '&category='.urlencode($_GET['category']) : ''; ?>" 
                   class="btn btn-secondary">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                <a href="?page=<?php echo $i; ?><?php echo isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : ''; ?><?php echo isset($_GET['category']) ? '&category='.urlencode($_GET['category']) : ''; ?>" 
                   class="btn <?php echo $i === $pagination['page'] ? 'btn-primary' : 'btn-secondary'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                <a href="?page=<?php echo $pagination['page'] + 1; ?><?php echo isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : ''; ?><?php echo isset($_GET['category']) ? '&category='.urlencode($_GET['category']) : ''; ?>" 
                   class="btn btn-secondary">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Book Details Modal -->
<div class="modal fade" id="bookDetailsModal" tabindex="-1" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title"><i class="fas fa-book"></i> Book Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="closeModal('bookDetailsModal')"></button>
            </div>
            <div class="modal-body" id="bookDetailsContent">
                <!-- Book details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('bookDetailsModal')">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Book Modal -->
<div class="modal fade" id="deleteBookModal" tabindex="-1" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header" style="background: #ef4444; color: white;">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h5>
                <button type="button" class="btn-close" onclick="closeModal('deleteBookModal')"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>admin/books/delete">
                <div class="modal-body">
                    <input type="hidden" name="isbn" id="deleteIsbn">
                    <p>Are you sure you want to delete "<strong id="deleteBookName"></strong>"?</p>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This action cannot be undone. The book must not have any active borrows.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('deleteBookModal')">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Book
                    </button>
                </div>
            </form>
        </div>
    </div>
    
</div>

<script>
function viewBookDetails(isbn) {
    fetch('<?php echo BASE_URL; ?>admin/books/api/details?isbn=' + encodeURIComponent(isbn))
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Failed to load book details');
                return;
            }
            
            const content = `
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 2rem;">
                    <div>
                        <img src="<?php echo BASE_URL; ?>public/uploads/books/${data.bookImage || 'default-book.jpg'}" 
                             alt="Book cover" 
                             style="width: 100%; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    </div>
                    <div>
                        <h3 style="margin-top: 0;">${data.bookName}</h3>
                        <p><strong>Author:</strong> ${data.authorName}</p>
                        <p><strong>Publisher:</strong> ${data.publisherName}</p>
                        <p><strong>ISBN:</strong> ${data.isbn}</p>
                        <p><strong>Category:</strong> ${data.category}</p>
                        <p><strong>Description:</strong> ${data.description || 'No description available'}</p>
                        <hr>
                        <p><strong>Total Copies:</strong> ${data.totalCopies}</p>
                        <p><strong>Available:</strong> ${data.available}</p>
                        <p><strong>Borrowed:</strong> ${data.borrowed || 0}</p>
                    </div>
                </div>
            `;
            
            document.getElementById('bookDetailsContent').innerHTML = content;
            showModal('bookDetailsModal');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load book details');
        });
}

function deleteBook(isbn, bookName) {
    document.getElementById('deleteIsbn').value = isbn;
    document.getElementById('deleteBookName').textContent = bookName;
    showModal('deleteBookModal');
}

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = 'flex';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
    modal.style.position = 'fixed';
    modal.style.top = '0';
    modal.style.left = '0';
    modal.style.width = '100%';
    modal.style.height = '100%';
    modal.style.background = 'rgba(0, 0, 0, 0.5)';
    modal.style.zIndex = '1000';
    modal.classList.add('show');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = 'none';
    modal.classList.remove('show');
}

// Close modal on background click
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        closeModal(event.target.id);
    }
});
</script>
