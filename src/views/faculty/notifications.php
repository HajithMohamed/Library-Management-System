<?php include APP_ROOT . '/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Notifications</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="list-group">
                        <?php foreach ($notifications as $notification) : ?>
                            <a href="#" class="list-group-item list-group-item-action <?php echo $notification['is_read'] ? '' : 'list-group-item-info'; ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <p class="mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <small><?php echo htmlspecialchars($notification['created_at']); ?></small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>