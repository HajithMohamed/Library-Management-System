<div class="sidebar">
    <div class="logo-container">
        <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Library Logo" class="logo-img">
        <h3>Admin Portal</h3>
    </div>
    
    <h4 class="my-4 text-center">Library Admin</h4>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>" 
               href="<?= BASE_URL ?>admin/dashboard">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'books' ? 'active' : '' ?>" 
               href="<?= BASE_URL ?>admin/books">
                <i class="fas fa-book"></i> Books
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'users' ? 'active' : '' ?>" 
               href="<?= BASE_URL ?>admin/users">
                <i class="fas fa-users"></i> Members
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'transactions' ? 'active' : '' ?>" 
               href="<?= BASE_URL ?>admin/transactions">
                <i class="fas fa-exchange-alt"></i> Transactions
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'borrow-requests' ? 'active' : '' ?>" 
               href="<?= BASE_URL ?>admin/borrow-requests">
                <i class="fas fa-clipboard-list"></i> Requests
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'reports' ? 'active' : '' ?>" 
               href="<?= BASE_URL ?>admin/reports">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'fines' ? 'active' : '' ?>" 
               href="<?= BASE_URL ?>admin/fines">
                <i class="fas fa-money-bill-wave"></i> Fines
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'notifications' ? 'active' : '' ?>" 
               href="<?= BASE_URL ?>admin/notifications">
                <i class="fas fa-bell"></i> Notifications
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'maintenance' ? 'active' : '' ?>" 
               href="<?= BASE_URL ?>admin/maintenance">
                <i class="fas fa-tools"></i> Maintenance
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>
