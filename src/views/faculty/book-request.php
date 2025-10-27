<?php include APP_ROOT . '/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Request a New Book</h6>
                </div>
                <div class="card-body">
                    <form action="/faculty/book-request" method="POST">
                        <div class="form-group">
                            <label for="book_title">Book Title</label>
                            <input type="text" name="book_title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="author">Author</label>
                            <input type="text" name="author" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="isbn">ISBN</label>
                            <input type="text" name="isbn" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="reason">Reason for Request</label>
                            <textarea name="reason" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Your Requests</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php foreach ($requests as $request) : ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($request['book_title']); ?>
                                <span class="badge bg-<?php echo strtolower($request['status']) === 'approved' ? 'success' : (strtolower($request['status']) === 'rejected' ? 'danger' : 'warning'); ?>"><?php echo htmlspecialchars($request['status']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>