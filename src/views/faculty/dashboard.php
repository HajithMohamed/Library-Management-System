<?php

include APP_ROOT . '/views/layouts/header.php';

?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-3">
            <?php include APP_ROOT . '/views/layouts/sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <h1>Faculty Dashboard</h1>
            <p>Welcome, <?= htmlspecialchars($user['username']) ?>!</p>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Borrowed Books</h5>
                            <p class="card-text"><?= $stats['borrowed_books'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Overdue Books</h5>
                            <p class="card-text"><?= $stats['overdue_books'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Fines</h5>
                            <p class="card-text">$<?= number_format($stats['total_fines'], 2) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="mt-4">Borrowed Books</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Book Name</th>
                        <th>Borrow Date</th>
                        <th>Return Date</th>
                        <th>Fine</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($borrowedBooks as $book) : ?>
                        <tr>
                            <td><?= htmlspecialchars($book['bookName']) ?></td>
                            <td><?= $book['borrowDate'] ?></td>
                            <td><?= $book['returnDate'] ?? 'Not Returned' ?></td>
                            <td>$<?= number_format($userService->calculateFine($book['borrowDate']), 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2 class="mt-4">Transaction History</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Book Name</th>
                        <th>Borrow Date</th>
                        <th>Return Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactionHistory as $transaction) : ?>
                        <tr>
                            <td><?= htmlspecialchars($transaction['tid']) ?></td>
                            <td><?= htmlspecialchars($transaction['bookName']) ?></td>
                            <td><?= $transaction['borrowDate'] ?></td>
                            <td><?= $transaction['returnDate'] ?? 'Not Returned' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php

include APP_ROOT . '/views/layouts/footer.php';

?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Summary Cards -->
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Books Borrowed</p>
                                <h5 class="font-weight-bolder">
                                    <?php echo count($borrowedBooks); ?>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                <i class="ni ni-book-bookmark text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Books Overdue</p>
                                <h5 class="font-weight-bolder">
                                    <?php echo count($overdueBooks); ?>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                <i class="ni ni-time-alarm text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Reserved Books</p>
                                <h5 class="font-weight-bolder">
                                    <?php echo count($reservedBooks); ?>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                <i class="ni ni-calendar-grid-58 text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Notifications</p>
                                <h5 class="font-weight-bolder">
                                    <?php echo count($notifications); ?>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                <i class="ni ni-bell-55 text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar and Quick Links -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="form-group">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search for books by title, author, ISBN...">
                    <span class="input-group-text"><i class="ni ni-zoom-split-in"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="d-flex justify-content-end">
                <a href="/faculty/history" class="btn btn-primary mb-0">Borrow History</a>
                <a href="/faculty/request" class="btn btn-secondary mb-0 ms-2">Request Book</a>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Borrowed Books -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Borrowed Books</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Book</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($borrowedBooks as $book) : ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($book['title']); ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($book['dueDate']); ?></p>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Notifications</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($notifications as $notification) : ?>
                            <li class="list-group-item border-0 d-flex justify-content-between ps-4 mb-2 border-radius-lg">
                                <div class="d-flex flex-column">
                                    <h6 class="mb-1 text-dark font-weight-bold text-sm"><?php echo htmlspecialchars($notification['message']); ?></h6>
                                    <span class="text-xs"><?php echo htmlspecialchars($notification['createdAt']); ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Transaction History</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Transaction ID</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Book Title</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Issue Date</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Return Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactionHistory as $transaction) : ?>
                                    <tr>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($transaction['transactionId']); ?></p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($transaction['title']); ?></p>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span class="badge badge-sm bg-gradient-success"><?php echo htmlspecialchars($transaction['borrowDate']); ?></span>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span class="badge badge-sm bg-gradient-secondary"><?php echo htmlspecialchars($transaction['returnDate'] ?? 'Not Returned'); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>