<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Book Management</h5>
        <a href="<?= BASE_URL ?>admin/books/add" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Book
        </a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-8">
                <div class="input-group">
                    <input type="text" id="searchBook" class="form-control" placeholder="Search by title, author, or ISBN...">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-control" id="filterCategory">
                    <option value="">All Categories</option>
                    <?php
                    $categories = [];
                    foreach ($books as $book) {
                        if (!empty($book['category']) && !in_array($book['category'], $categories)) {
                            $categories[] = $book['category'];
                        }
                    }
                    sort($categories);
                    foreach ($categories as $category) {
                        echo "<option value=\"" . htmlspecialchars($category) . "\">" . htmlspecialchars($category) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ISBN</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Available</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="booksTableBody">
                    <?php if (count($books) > 0): ?>
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <td><?= htmlspecialchars($book['isbn']) ?></td>
                                <td><?= htmlspecialchars($book['bookName']) ?></td>
                                <td><?= htmlspecialchars($book['authorName']) ?></td>
                                <td><?= htmlspecialchars($book['category'] ?? 'Uncategorized') ?></td>
                                <td><?= (int)$book['available'] ?></td>
                                <td><?= (int)$book['totalCopies'] ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>admin/books/edit?isbn=<?= urlencode($book['isbn']) ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-book" data-isbn="<?= htmlspecialchars($book['isbn']) ?>" data-title="<?= htmlspecialchars($book['bookName']) ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No books found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Book Modal -->
<div class="modal fade" id="deleteBookModal" tabindex="-1" aria-labelledby="deleteBookModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteBookModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the book "<span id="bookTitle"></span>"?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteBookForm" method="POST" action="<?= BASE_URL ?>admin/books/delete">
                    <input type="hidden" id="deleteIsbn" name="isbn" value="">
                    <button type="submit" class="btn btn-danger">Delete Book</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle delete book button
    $('.delete-book').click(function() {
        const isbn = $(this).data('isbn');
        const title = $(this).data('title');
        
        $('#bookTitle').text(title);
        $('#deleteIsbn').val(isbn);
        $('#deleteBookModal').modal('show');
    });
    
    // Handle search function
    $('#searchBtn').click(function() {
        filterBooks();
    });
    
    $('#searchBook').keyup(function(e) {
        if (e.keyCode === 13) {
            filterBooks();
        }
    });
    
    $('#filterCategory').change(function() {
        filterBooks();
    });
    
    function filterBooks() {
        const searchTerm = $('#searchBook').val().toLowerCase();
        const category = $('#filterCategory').val();
        
        $('#booksTableBody tr').each(function() {
            const isbn = $(this).find('td:nth-child(1)').text().toLowerCase();
            const title = $(this).find('td:nth-child(2)').text().toLowerCase();
            const author = $(this).find('td:nth-child(3)').text().toLowerCase();
            const bookCategory = $(this).find('td:nth-child(4)').text();
            
            const matchesSearch = isbn.includes(searchTerm) || title.includes(searchTerm) || author.includes(searchTerm);
            const matchesCategory = category === '' || bookCategory === category;
            
            if (matchesSearch && matchesCategory) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }
});
</script>
