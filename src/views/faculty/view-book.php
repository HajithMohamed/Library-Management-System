<?php include APP_ROOT . '/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="<?php echo htmlspecialchars($book['image']); ?>" class="img-fluid rounded-start" alt="book image">
                        </div>
                        <div class="col-md-8">
                            <h2 class="card-title"><?php echo htmlspecialchars($book['bookName']); ?></h2>
                            <p class="card-text"><strong>Author:</strong> <?php echo htmlspecialchars($book['authorName']); ?></p>
                            <p class="card-text"><strong>Publisher:</strong> <?php echo htmlspecialchars($book['publisherName']); ?></p>
                            <p class="card-text"><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></p>
                            <p class="card-text"><strong>Category:</strong> <?php echo htmlspecialchars($book['category']); ?></p>
                            <p class="card-text"><strong>Publication Year:</strong> <?php echo htmlspecialchars($book['publicationYear']); ?></p>
                            <p class="card-text"><strong>Description:</strong> <?php echo htmlspecialchars($book['description']); ?></p>
                            <p class="card-text"><strong>Total Copies:</strong> <?php echo htmlspecialchars($book['totalCopies']); ?></p>
                            <p class="card-text"><strong>Available Copies:</strong> <?php echo htmlspecialchars($book['available']); ?></p>
                            <a href="/faculty/reserve/<?php echo htmlspecialchars($book['isbn']); ?>" class="btn btn-primary">Reserve Book</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>