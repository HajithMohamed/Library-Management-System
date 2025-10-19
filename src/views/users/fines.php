<?php
$pageTitle = 'Your Fines';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    .fines-container { padding: 24px 0; }
    .card { background: #fff; border-radius: 12px; box-shadow: 0 6px 18px rgba(0,0,0,.08); overflow: hidden; }
    .card-header { padding: 16px 20px; border-bottom: 1px solid #e5e7eb; background: linear-gradient(135deg, rgba(102,126,234,.05), rgba(118,75,162,.05)); }
    .card-title { margin: 0; font-size: 1.25rem; font-weight: 700; color: #1f2937; }
    .card-body { padding: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px 10px; border-bottom: 1px solid #f1f5f9; text-align: left; }
    th { font-weight: 700; color: #374151; background: #f8fafc; }
    .total { text-align: right; font-weight: 800; color: #111827; margin-top: 16px; }
    .btn-pay { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: 0; padding: 8px 12px; border-radius: 8px; cursor: pointer; }
    .empty { padding: 24px; text-align: center; color: #6b7280; }
</style>

<div class="container fines-container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pending Fines</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($borrowedBooks)) { ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ISBN</th>
                                        <th>Book</th>
                                        <th>Borrowed On</th>
                                        <th>Calculated Fine</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($borrowedBooks as $book) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars($book['isbn']) ?></td>
                                        <td><?= htmlspecialchars($book['bookName'] ?? ($book['title'] ?? 'Book')) ?></td>
                                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($book['borrowDate']))) ?></td>
                                        <td><?= number_format((float)($book['calculated_fine'] ?? 0), 2) ?></td>
                                        <td>
                                            <?php $fine = (float)($book['calculated_fine'] ?? 0); if ($fine > 0) { ?>
                                            <form method="POST" action="<?= BASE_URL ?>user/pay-fine" style="display:inline">
                                                <input type="hidden" name="tid" value="<?= htmlspecialchars($book['tid'] ?? '') ?>">
                                                <input type="hidden" name="amount" value="<?= htmlspecialchars($fine) ?>">
                                                <button type="submit" class="btn-pay">
                                                    <i class="fas fa-money-bill-wave"></i> Pay
                                                </button>
                                            </form>
                                            <?php } else { ?>
                                                <span style="color:#10b981;font-weight:600">No Fine</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="total">Total Fine: <?= number_format((float)($totalFine ?? 0), 2) ?></div>
                    <?php } else { ?>
                        <div class="empty">No pending fines. Great job!</div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>


