<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Navbar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        /* ========================================
           SIDEBAR STYLES
        ======================================== */

        /* Left Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar.collapsed {
            width: 80px;
        }

        /* Sidebar Scrollbar Styling */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Sidebar Header */
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-logo i {
            font-size: 1.8rem;
            color: #667eea;
        }

        .sidebar-logo h2 {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            white-space: nowrap;
        }

        .sidebar.collapsed .sidebar-logo h2 {
            display: none;
        }

        .sidebar-toggle {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }

        .sidebar-toggle:active {
            transform: scale(0.95);
        }

        /* Sidebar Navigation */
        .sidebar-nav {
            padding: 1rem 0;
            padding-bottom: 180px;
            /* Add space for the footer */
        }

        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 600;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .nav-section-title {
            opacity: 0;
            height: 0;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.875rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-link:hover::before,
        .nav-link.active::before {
            transform: scaleY(1);
        }

        .nav-link i {
            font-size: 1.25rem;
            width: 24px;
            text-align: center;
            flex-shrink: 0;
        }

        .nav-link span {
            white-space: nowrap;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .nav-link span {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        .nav-badge {
            margin-left: auto;
            background: #ef4444;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            min-width: 20px;
            text-align: center;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .nav-badge {
            opacity: 0;
            width: 0;
            padding: 0;
            overflow: hidden;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 280px;
            padding: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(15, 23, 42, 0.98);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            z-index: 1001;
        }

        .sidebar.collapsed .sidebar-footer {
            width: 80px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.1rem;
            flex-shrink: 0;
            cursor: pointer;
            transition: all 0.3s ease;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .user-info {
            flex: 1;
            overflow: hidden;
            transition: opacity 0.3s ease;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.6);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar.collapsed .user-info {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        /* Logout Button */
        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.75rem 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.5);
        }

        .logout-btn:active {
            transform: translateY(0);
        }

        .logout-btn i {
            font-size: 1rem;
        }

        .logout-btn span {
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .logout-btn {
            padding: 0.75rem;
        }

        .sidebar.collapsed .logout-btn span {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        /* ========================================
           RESPONSIVE STYLES
        ======================================== */

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .sidebar-footer {
                width: 280px;
            }

            /* Mobile Overlay */
            .mobile-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                backdrop-filter: blur(2px);
            }

            .mobile-overlay.show {
                display: block;
            }
        }

        /* Tooltip for collapsed sidebar (optional enhancement) */
        .nav-link::after {
            content: attr(data-title);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-size: 0.85rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            margin-left: 1rem;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .nav-link:hover::after {
            opacity: 1;
        }
    </style>
</head>

<body>
    <?php
    // Get current admin info
    $adminName = $_SESSION['username'] ?? 'Admin';
    $adminEmail = $_SESSION['emailId'] ?? '';
    $adminInitial = strtoupper(substr($adminName, 0, 1));

    // Get current page for active nav highlighting
    $currentPage = basename($_SERVER['PHP_SELF'], '.php');
    ?>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileSidebar()"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-book-reader"></i>
                <h2>LibraryMS</h2>
            </div>
            <button class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="sidebar-nav">
            <!-- Main Section -->
            <div class="nav-section">
                <div class="nav-section-title">Main</div>

                <div class="nav-item">
                    <a href="<?= BASE_URL ?>admin/dashboard"
                        class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>" data-title="Dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="<?= BASE_URL ?>admin/analytics"
                        class="nav-link <?= $currentPage === 'analytics' ? 'active' : '' ?>" data-title="Analytics">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics</span>
                    </a>
                </div>
            </div>

            <!-- Library Management Section -->
            <div class="nav-section">
                <div class="nav-section-title">Library Management</div>

                <div class="nav-item">
                    <a href="<?= BASE_URL ?>admin/books"
                        class="nav-link <?= $currentPage === 'books' ? 'active' : '' ?>" data-title="Books">
                        <i class="fas fa-book"></i>
                        <span>Books</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="<?= BASE_URL ?>admin/eresources/manage"
                        class="nav-link <?= $currentPage === 'e-resources' ? 'active' : '' ?>" data-title="E-Resources">
                        <i class="fas fa-file-pdf"></i>
                        <span>E-Resources</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="<?= BASE_URL ?>admin/borrowed-books"
                        class="nav-link <?= $currentPage === 'borrowed-books' ? 'active' : '' ?>"
                        data-title="Borrowed Books">
                        <i class="fas fa-book-reader"></i>
                        <span>Borrowed Books</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="<?= BASE_URL ?>admin/borrow-requests"
                        class="nav-link <?= $currentPage === 'borrow-requests' ? 'active' : '' ?>"
                        data-title="Borrow Requests">
                        <i class="fas fa-hand-paper"></i>
                        <span>Borrow Requests</span>
                        <?php
                        // Get pending requests count
                        global $mysqli;
                        $pendingCount = 0;
                        if (isset($mysqli)) {
                            $result = $mysqli->query("SELECT COUNT(*) as count FROM borrow_requests WHERE status = 'Pending'");
                            if ($result) {
                                $pendingCount = $result->fetch_assoc()['count'];
                            }
                        }
                        if ($pendingCount > 0):
                            ?>
                            <span class="nav-badge"><?= $pendingCount ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="<?= BASE_URL ?>admin/renewal-requests"
                        class="nav-link <?= $currentPage === 'renewal-requests' ? 'active' : '' ?>"
                        data-title="Renewal Requests">
                        <i class="fas fa-sync-alt"></i>
                        <span>Renewal Requests</span>
                        <?php
                        // Get pending renewal requests count
                        $pendingRenewalCount = 0;
                        if (isset($mysqli)) {
                            $rrCheck = $mysqli->query("SHOW TABLES LIKE 'renewal_requests'");
                            if ($rrCheck && $rrCheck->num_rows > 0) {
                                $rrResult = $mysqli->query("SELECT COUNT(*) as count FROM renewal_requests WHERE status = 'Pending'");
                                if ($rrResult) {
                                    $pendingRenewalCount = $rrResult->fetch_assoc()['count'];
                                }
                            }
                        }
                        if ($pendingRenewalCount > 0):
                            ?>
                            <span class="nav-badge"><?= $pendingRenewalCount ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="<?= BASE_URL ?>admin/book-recommendations"
                        class="nav-link <?= $currentPage === 'book-recommendations' ? 'active' : '' ?>" data-title="Book Recommendations">
                        <i class="fas fa-lightbulb"></i>
                        <span>Book Recommendations</span>
                        <?php
                        // Get pending book recommendations count
                        $pendingRecommendations = 0;
                        if (isset($mysqli)) {
                            $result = $mysqli->query("SELECT COUNT(*) as count FROM book_recommendations WHERE status = 'pending'");
                            if ($result) {
                                $pendingRecommendations = $result->fetch_assoc()['count'];
                            }
                        }
                        if ($pendingRecommendations > 0):
                            ?>
                            <span class="nav-badge" style="background:#fa709a;"><?= $pendingRecommendations ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>

            <!-- User Management Section -->
            <div class="nav-section">
                <div class="nav-section-title">User Management</div>

                <div class="nav-item">
                    <a href="<?= BASE_URL ?>admin/users"
                        class="nav-link <?= $currentPage === 'users' ? 'active' : '' ?>" data-title="Users">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                </div>
            </div>

            <!-- Financial Section -->
            <div class="nav-section">
                <div class="nav-section-title">Financial</div>

                <div class="nav-item">
                    <a href="<?= BASE_URL ?>admin/fines"
                        class="nav-link <?= $currentPage === 'fines' ? 'active' : '' ?>" data-title="Fines">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Fines</span>
                        <?php
                        // Get pending fines count
                        $pendingFines = 0;
                        if (isset($mysqli)) {
                            $result = $mysqli->query("SELECT COUNT(*) as count FROM transactions WHERE fineAmount > 0 AND fineStatus = 'pending'");
                            if ($result) {
                                $pendingFines = $result->fetch_assoc()['count'];
                            }
                        }
                        if ($pendingFines > 0):
                            ?>
                            <span class="nav-badge"><?= $pendingFines ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>

            <!-- Reports & Settings Section -->
            <div class="nav-section">
                <div class="nav-section-title">System</div>

                <div class="nav-item">
                    <a href="<?= BASE_URL ?>admin/reports"
                        class="nav-link <?= $currentPage === 'reports' ? 'active' : '' ?>" data-title="Reports">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="<?= BASE_URL ?>admin/notifications"
                        class="nav-link <?= $currentPage === 'notifications' ? 'active' : '' ?>"
                        data-title="Notifications">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                        <?php
                        // Get unread notifications count
                        $unreadCount = 0;
                        if (isset($mysqli)) {
                            $adminId = $_SESSION['userId'] ?? '';
                            $result = $mysqli->query("SELECT COUNT(*) as count FROM notifications WHERE isRead = 0 AND (userId = '$adminId' OR userId IS NULL)");
                            if ($result) {
                                $unreadCount = $result->fetch_assoc()['count'];
                            }
                        }
                        if ($unreadCount > 0):
                            ?>
                            <span class="nav-badge"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="<?= BASE_URL ?>admin/maintenance"
                        class="nav-link <?= $currentPage === 'maintenance' ? 'active' : '' ?>" data-title="Maintenance">
                        <i class="fas fa-tools"></i>
                        <span>Maintenance</span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <a href="<?= BASE_URL ?>admin/profile" style="text-decoration:none;">
                <div class="user-profile">
                    <div class="user-avatar"><?= $adminInitial ?></div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($adminName) ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                </div>
            </a>
            <a href="<?= BASE_URL ?>logout" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <script>
        // Toggle sidebar collapse
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');

            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }

        // Toggle mobile sidebar
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');

            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('show');
        }

        // Close sidebar on mobile
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');

            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('show');
        }

        // Load saved sidebar state on page load
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

            if (sidebarCollapsed) {
                sidebar.classList.add('collapsed');
            }

            // Set active link based on current URL
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link');

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href'))) {
                    link.classList.add('active');
                }
            });

            // Close mobile sidebar when clicking on a link
            navLinks.forEach(link => {
                link.addEventListener('click', function () {
                    if (window.innerWidth <= 768) {
                        closeSidebar();
                    }
                });
            });
        });

        // Handle window resize
        window.addEventListener('resize', function () {
            if (window.innerWidth > 768) {
                const overlay = document.getElementById('mobileOverlay');
                const sidebar = document.getElementById('sidebar');

                overlay.classList.remove('show');
                sidebar.classList.remove('mobile-open');
            }
        });
    </script>
</body>

</html>