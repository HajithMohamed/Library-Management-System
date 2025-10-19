<?php
$pageTitle = 'Return Books';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    .return-container { padding: 24px 0; }
    .card { background: #fff; border-radius: 12px; box-shadow: 0 6px 18px rgba(0,0,0,.08); overflow: hidden; }
    .card-header { padding: 16px 20px; border-bottom: 1px solid #e5e7eb; background: linear-gradient(135deg, rgba(102,126,234,.05), rgba(118,75,162,.05)); }
    .card-title { margin: 0; font-size: 1.25rem; font-weight: 700; color: #1f2937; }
    .card-body { padding: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px 10px; border-bottom: 1px solid #f1f5f9; text-align: left; }
    th { font-weight: 700; color: #374151; background: #f8fafc; }
    .btn-return { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: 0; padding: 8px 12px; border-radius: 8px; cursor: pointer; }
    .empty { padding: 24px; text-align: center; color: #6b7280; }
</style>

<div class="container return-container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Return Borrowed Books</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($books)) { ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ISBN</th>
                                        <th>Book</th>
                                        <th>Author</th>
                                        <th>Borrowed On</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($books as $book) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars($book['isbn']) ?></td>
                                        <td><?= htmlspecialchars($book['bookName']) ?></td>
                                        <td><?= htmlspecialchars($book['authorName']) ?></td>
                                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($book['borrowDate']))) ?></td>
                                        <td>
                                            <form method="POST" action="<?= BASE_URL ?>user/return" style="display:inline">
                                                <input type="hidden" name="isbn" value="<?= htmlspecialchars($book['isbn']) ?>">
                                                <button type="submit" class="btn-return">
                                                    <i class="fas fa-undo"></i> Return
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <div class="empty">You have no borrowed books to return.</div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>


