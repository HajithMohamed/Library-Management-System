<?php
// Prevent direct access
if (!defined('APP_ROOT')) {
    exit('Direct access not allowed');
}

// Include header
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    /* Fix Modal Display Issues */
    .modal {
    display: none;
    z-index: 1050;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto; /* Changed from 'hidden' to 'auto' to enable scrolling if content overflows */
    background-color: rgba(0, 0, 0, 0.5);
    }

    /* Additional rules for better scrollable modal support (add these if not present) */
    .modal-dialog {
        max-height: 90vh; /* Limit dialog height to viewport */
        overflow-y: auto; /* Enable vertical scrolling for the dialog */
    }

    .modal-body {
        max-height: 60vh; /* Limit body height */
        overflow-y: auto; /* Enable scrolling within the body */
    }

    /* Custom scrollbar for better UX */
    .modal::-webkit-scrollbar,
    .modal-dialog::-webkit-scrollbar,
    .modal-body::-webkit-scrollbar {
        width: 8px;
    }

    .modal::-webkit-scrollbar-track,
    .modal-dialog::-webkit-scrollbar-track,
    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .modal::-webkit-scrollbar-thumb,
    .modal-dialog::-webkit-scrollbar-thumb,
    .modal-body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }

    .modal::-webkit-scrollbar-thumb:hover,
    .modal-dialog::-webkit-scrollbar-thumb:hover,
    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    .modal .show {
        display: block !important;
    }
    
    .modal-dialog {
        position: relative;
        width: auto;
        max-width: 800px;
        margin: 1.75rem auto;
        max-height: calc(100vh - 3.5rem);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        pointer-events: auto;
        background-color: #fff;
        border-radius: 16px;
        outline: 0;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        max-height: calc(100vh - 3.5rem);
      
    }
    
    .modal-header {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: space-between;
        padding: 1.5rem;
        border-bottom: 1px solid #dee2e6;
        border-radius: 16px 16px 0 0;
    }
    
    .modal-header .btn-close {
        padding: 0.5rem;
        margin: -0.5rem -0.5rem -0.5rem auto;
        background: transparent;
        border: 0;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        opacity: 0.8;
        cursor: pointer;
    }
    
    .modal-header .btn-close:hover {
        opacity: 1;
    }
    
    .modal-body {
        position: relative;
        flex: 1 1 auto;
        padding: 1.5rem;
        overflow-y: auto;
        overflow-x: hidden;
        max-height: calc(100vh - 250px);
    }
    
    .modal-footer {
        display: flex;
        flex-shrink: 0;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        padding: 1.25rem;
        border-top: 1px solid #dee2e6;
        border-radius: 0 0 16px 16px;
        background: #f8f9fa;
        gap: 0.5rem;
    }
    
    /* Custom Scrollbar */
    .modal-body::-webkit-scrollbar {
        width: 10px;
    }
    
    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .modal-body::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
    }
    
    .modal-body::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    }
    
    /* Form Styling */
    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.75rem;
        display: block;
    }
    
    .form-control,
    .form-select {
        display: block;
        width: 100%;
        padding: 0.875rem 1.25rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #212529;
        background-color: #fff;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .form-control:focus,
    .form-select:focus {
        border-color: #667eea;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
    }
    
    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }
    
    /* Button Styling */
    .btn {
        display: inline-block;
        font-weight: 600;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        user-select: none;
        border: 1px solid transparent;
        padding: 0.875rem 1.75rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 8px;
        transition: all 0.15s ease-in-out;
        cursor: pointer;
    }
    
    .btn-primary {
        color: #fff;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    
    .btn-secondary {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
    }
    
    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }
    
    /* Image Preview */
    #add_imagePreview,
    #edit_imagePreview,
    #edit_currentImage {
        margin-top: 1rem;
        padding: 1rem;
        background: #f9fafb;
        border-radius: 8px;
        border: 2px dashed #e5e7eb;
        text-align: center;
    }
    
    .text-danger {
        color: #dc3545;
    }
    
    .text-muted {
        color: #6c757d;
    }
    
    small {
        font-size: 0.875rem;
    }
    
    /* Improve grid spacing */
    .row.g-4 {
        --bs-gutter-x: 1.5rem;
        --bs-gutter-y: 1.5rem;
    }
</style>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Book Management</h2>
            <p class="text-muted mb-0">Manage your library collection</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookModal">
            <i class="fas fa-plus me-2"></i>Add New Book
        </button>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Total Books</h6>
                            <h3 class="mb-0"><?= $stats['totalBooks'] ?? 0 ?></h3>
                        </div>
                        <i class="fas fa-book fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Available</h6>
                            <h3 class="mb-0"><?= $stats['availableBooks'] ?? 0 ?></h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Borrowed</h6>
                            <h3 class="mb-0"><?= $stats['borrowedBooks'] ?? 0 ?></h3>
                        </div>
                        <i class="fas fa-exchange-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Publishers</h6>
                            <h3 class="mb-0"><?= $stats['categories'] ?? 0 ?></h3>
                        </div>
                        <i class="fas fa-building fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>admin/books" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="q" placeholder="Search by title, author, ISBN..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Publisher</label>
                    <select class="form-select" name="category">
                        <option value="">All Publishers</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= (($_GET['category'] ?? '') === $cat) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="available" <?= (($_GET['status'] ?? '') === 'available') ? 'selected' : '' ?>>Available</option>
                        <option value="borrowed" <?= (($_GET['status'] ?? '') === 'borrowed') ? 'selected' : '' ?>>Borrowed</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Books Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Cover</th>
                            <th>ISBN</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Publisher</th>
                            <th>Total</th>
                            <th>Available</th>
                            <th>Borrowed</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($books)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No books found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($books as $book): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($book['bookImage'])): ?>
                                            <img src="<?= BASE_URL ?>public/uploads/books/<?= htmlspecialchars($book['bookImage']) ?>" 
                                                 alt="<?= htmlspecialchars($book['bookName']) ?>" 
                                                 class="img-thumbnail" 
                                                 style="width: 50px; height: 70px; object-fit: cover;"
                                                 onerror="this.src='<?= BASE_URL ?>public/assets/images/default-book.jpg'">
                                        <?php else: ?>
                                            <div class="bg-secondary d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 70px; border-radius: 4px;">
                                                <i class="fas fa-book text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($book['isbn']) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($book['bookName']) ?></strong>
                                        <?php if (!empty($book['isTrending'])): ?>
                                            <span class="badge bg-danger ms-1">
                                                <i class="fas fa-fire"></i> Trending
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($book['isSpecial'])): ?>
                                            <span class="badge bg-success ms-1">
                                                <i class="fas fa-star"></i> <?= htmlspecialchars($book['specialBadge'] ?? 'Special') ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($book['authorName']) ?></td>
                                    <td><?= htmlspecialchars($book['publisherName']) ?></td>
                                    <td><?= htmlspecialchars($book['totalCopies']) ?></td>
                                    <td>
                                        <?php if ($book['available'] > 0): ?>
                                            <span class="badge bg-success"><?= $book['available'] ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning"><?= $book['borrowed'] ?? 0 ?></span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" onclick="viewBook('<?= htmlspecialchars($book['isbn']) ?>')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" onclick='openEditModal(<?= json_encode($book) ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteBook('<?= htmlspecialchars($book['isbn']) ?>', '<?= htmlspecialchars(addslashes($book['bookName'])) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['totalPages'] > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($pagination['page'] <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?><?= !empty($_GET['q']) ? '&q=' . urlencode($_GET['q']) : '' ?><?= !empty($_GET['category']) ? '&category=' . urlencode($_GET['category']) : '' ?><?= !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '' ?>">
                                Previous
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                            <?php if ($i == 1 || $i == $pagination['totalPages'] || ($i >= $pagination['page'] - 2 && $i <= $pagination['page'] + 2)): ?>
                                <li class="page-item <?= ($i == $pagination['page']) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= !empty($_GET['q']) ? '&q=' . urlencode($_GET['q']) : '' ?><?= !empty($_GET['category']) ? '&category=' . urlencode($_GET['category']) : '' ?><?= !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php elseif ($i == $pagination['page'] - 3 || $i == $pagination['page'] + 3): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= ($pagination['page'] >= $pagination['totalPages']) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?><?= !empty($_GET['q']) ? '&q=' . urlencode($_GET['q']) : '' ?><?= !empty($_GET['category']) ? '&category=' . urlencode($_GET['category']) : '' ?><?= !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '' ?>">
                                Next
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Book Modal -->
<div class="modal fade" id="addBookModal" tabindex="-1" aria-labelledby="addBookModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addBookModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add New Book
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <form id="addBookForm" enctype="multipart/form-data" onsubmit="return handleAddBook(event)">
                <div class="modal-body">
                    <div class="row g-9">
                        <div class="col-md-6">
                            <label for="add_isbn" class="form-label">
                                ISBN <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="add_isbn" 
                                   name="isbn" 
                                   required 
                                   maxlength="13" 
                                   pattern="[0-9]{10,13}"
                                   placeholder="Enter 10 or 13 digit ISBN">
                            <small class="text-muted">10 or 13 digit ISBN number</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="add_bookName" class="form-label">
                                Book Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="add_bookName" 
                                   name="bookName" 
                                   required 
                                   maxlength="255"
                                   placeholder="Enter book title">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="add_author" class="form-label">
                                Author <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="add_author" 
                                   name="authorName" 
                                   required 
                                   maxlength="255"
                                   placeholder="Enter author name">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="add_publisher" class="form-label">
                                Publisher <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="add_publisher" 
                                   name="publisherName" 
                                   required 
                                   maxlength="255"
                                   placeholder="Enter publisher name">
                        </div>
                        
                        <div class="col-md-12">
                            <label for="add_description" class="form-label">Description</label>
                            <textarea class="form-control" 
                                      id="add_description" 
                                      name="description" 
                                      rows="5" 
                                      maxlength="1000"
                                      placeholder="Enter book description (optional)"></textarea>
                            <small class="text-muted">Maximum 1000 characters</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="add_totalCopies" class="form-label">
                                Total Copies <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="add_totalCopies" 
                                   name="totalCopies" 
                                   required 
                                   min="1" 
                                   value="1">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="add_coverImage" class="form-label">
                                Cover Image
                            </label>
                            <input type="file" 
                                   class="form-control" 
                                   id="add_coverImage" 
                                   name="coverImage" 
                                   accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <small class="text-muted">Max 5MB. JPG, PNG, GIF, WebP</small>
                        </div>
                        
                        <div class="col-md-12">
                            <div id="add_imagePreview" style="display: none;">
                                <label class="form-label">Preview:</label>
                                <div class="text-center">
                                    <img id="add_previewImage" 
                                         src="" 
                                         alt="Preview" 
                                         class="img-thumbnail" 
                                         style="max-width: 250px; max-height: 250px; object-fit: contain;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="addBookBtn">
                        <i class="fas fa-save me-2"></i>Add Book
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Book Modal -->
<div class="modal fade" id="editBookModal" tabindex="-1" aria-labelledby="editBookModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editBookModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Book
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editBookForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="edit_isbn" name="isbn">
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="edit_isbn_display" class="form-label">ISBN</label>
                            <input type="text" class="form-control" id="edit_isbn_display" disabled>
                            <small class="text-muted">ISBN cannot be changed</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="edit_bookName" class="form-label">
                                Book Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="edit_bookName" name="bookName" required maxlength="255">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="edit_author" class="form-label">
                                Author <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="edit_author" name="authorName" required maxlength="255">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="edit_publisher" class="form-label">
                                Publisher <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="edit_publisher" name="publisherName" required maxlength="255">
                        </div>
                        
                        <div class="col-md-12">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="5" maxlength="1000"></textarea>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="edit_totalCopies" class="form-label">
                                Total Copies <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="edit_totalCopies" name="totalCopies" required min="1">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="edit_available" class="form-label">
                                Available Copies <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="edit_available" name="available" required min="0">
                        </div>
                        
                        <div class="col-md-12">
                            <label for="edit_coverImage" class="form-label">Cover Image</label>
                            <input type="file" class="form-control" id="edit_coverImage" name="coverImage" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <small class="text-muted">Leave empty to keep current image. Max 5MB.</small>
                        </div>
                        
                        <div class="col-md-12">
                            <div id="edit_currentImage" style="display: none;">
                                <label class="form-label">Current Cover:</label>
                                <div class="text-center">
                                    <img id="edit_currentImageDisplay" src="" alt="Current Cover" class="img-thumbnail" style="max-width: 250px; max-height: 250px; object-fit: contain;">
                                </div>
                            </div>
                            
                            <div id="edit_imagePreview" style="display: none;">
                                <label class="form-label">New Cover Preview:</label>
                                <div class="text-center">
                                    <img id="edit_previewImage" src="" alt="Preview" class="img-thumbnail" style="max-width: 250px; max-height: 250px; object-fit: contain;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Book
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Image preview for Add form
document.getElementById('add_coverImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 5242880) {
            alert('File size must be less than 5MB');
            this.value = '';
            document.getElementById('add_imagePreview').style.display = 'none';
            return;
        }
        
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Invalid file type. Please upload JPG, PNG, GIF, or WebP image.');
            this.value = '';
            document.getElementById('add_imagePreview').style.display = 'none';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('add_previewImage').src = event.target.result;
            document.getElementById('add_imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('add_imagePreview').style.display = 'none';
    }
});

// Handle Add Book Form Submission
function handleAddBook(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const submitBtn = document.getElementById('addBookBtn');
    const originalBtnText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';
    
    fetch('<?= BASE_URL ?>admin/books/add', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Book added successfully!');
            bootstrap.Modal.getInstance(document.getElementById('addBookModal')).hide();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            alert(data.message || 'Failed to add book');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
    
    return false;
}

// Validate available copies
document.getElementById('edit_available').addEventListener('input', function() {
    const totalCopies = parseInt(document.getElementById('edit_totalCopies').value);
    const availableCopies = parseInt(this.value);
    
    if (availableCopies > totalCopies) {
        this.setCustomValidity('Available copies cannot exceed total copies');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('edit_totalCopies').addEventListener('input', function() {
    const availableCopies = parseInt(document.getElementById('edit_available').value);
    const totalCopies = parseInt(this.value);
    
    if (availableCopies > totalCopies) {
        document.getElementById('edit_available').setCustomValidity('Available copies cannot exceed total copies');
    } else {
        document.getElementById('edit_available').setCustomValidity('');
    }
});

// Open Edit Modal with book data
function openEditModal(book) {
    // Populate form fields
    document.getElementById('edit_isbn').value = book.isbn;
    document.getElementById('edit_isbn_display').value = book.isbn;
    document.getElementById('edit_bookName').value = book.bookName;
    document.getElementById('edit_author').value = book.authorName;
    document.getElementById('edit_publisher').value = book.publisherName;
    document.getElementById('edit_description').value = book.description || '';
    document.getElementById('edit_totalCopies').value = book.totalCopies;
    document.getElementById('edit_available').value = book.available;
    
    // Show current image if exists
    if (book.bookImage) {
        document.getElementById('edit_currentImageDisplay').src = '<?= BASE_URL ?>public/uploads/books/' + book.bookImage;
        document.getElementById('edit_currentImage').style.display = 'block';
    } else {
        document.getElementById('edit_currentImage').style.display = 'none';
    }
    
    // Hide preview
    document.getElementById('edit_imagePreview').style.display = 'none';
    document.getElementById('edit_coverImage').value = '';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('editBookModal'));
    modal.show();
}

// Handle Edit Book Form Submission
document.getElementById('editBookForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
    
    fetch('<?= BASE_URL ?>admin/books/edit', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('editBookModal')).hide();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAlert('danger', data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
});

// Delete Book
function deleteBook(isbn, bookName) {
    if (!confirm(`Are you sure you want to delete "${bookName}"? This action cannot be undone.`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('isbn', isbn);
    
    fetch('<?= BASE_URL ?>admin/books/delete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
    });
}

// Show Alert
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Reset form when modal is closed
document.getElementById('addBookModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('addBookForm').reset();
    document.getElementById('add_imagePreview').style.display = 'none';
});

document.getElementById('editBookModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('editBookForm').reset();
    document.getElementById('edit_imagePreview').style.display = 'none';
    document.getElementById('edit_currentImage').style.display = 'none';
});
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>