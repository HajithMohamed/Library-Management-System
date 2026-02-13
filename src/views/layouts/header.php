<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Library Management System' ?> - University Library</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">


    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css">

    <!-- SweetAlert2 CDN -->
    <script src="<?= BASE_URL ?>assets/js/sweetalert2-cdn.js"></script>

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 10px 30px rgba(0, 0, 0, 0.2);
            --shadow-lg: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }

        /* Animated Background */
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            overflow: hidden;
        }

        .video-fallback {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .fallback-gradient {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }

        /* Mesh Gradient Overlay */
        .mesh-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background:
                radial-gradient(at 40% 20%, rgba(102, 126, 234, 0.3) 0px, transparent 50%),
                radial-gradient(at 80% 0%, rgba(118, 75, 162, 0.3) 0px, transparent 50%),
                radial-gradient(at 0% 50%, rgba(240, 147, 251, 0.3) 0px, transparent 50%),
                radial-gradient(at 80% 50%, rgba(245, 87, 108, 0.3) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(79, 172, 254, 0.3) 0px, transparent 50%),
                radial-gradient(at 80% 100%, rgba(0, 242, 254, 0.3) 0px, transparent 50%);
            animation: meshMove 20s ease infinite;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        @keyframes meshMove {

            0%,
            100% {
                opacity: 0.8;
            }

            50% {
                opacity: 1;
            }
        }

        /* Floating Particles */
        .particles-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: float-particle 15s infinite ease-in-out;
        }

        @keyframes float-particle {

            0%,
            100% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                transform: translateY(-100vh) translateX(50px);
                opacity: 0;
            }
        }

        /* Modern Navbar - FIXED Z-INDEX */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1000;
        }

        .navbar>.container {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }

        .navbar.scrolled {
            padding: 0.5rem 0;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.15);
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.4rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .navbar-brand i {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link {
            color: #4a5568 !important;
            font-weight: 600;
            padding: 0.5rem 1rem !important;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: 12px;
            z-index: -1;
        }

        .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }

        .nav-link:hover::before {
            opacity: 1;
        }

        .nav-link i {
            margin-right: 0.5rem;
        }

        /* Notification Icon */
        .notification-icon {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .notification-icon:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: scale(1.05);
        }

        .notification-icon i {
            font-size: 1.3rem;
            color: #4a5568;
        }

        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
            color: white;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 2px 5px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(245, 87, 108, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        /* Profile Dropdown */
        .profile-dropdown {
            display: flex;
            align-items: center;
            padding: 0.25rem !important;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .profile-dropdown::after {
            display: none !important;
            /* Hide the dropdown arrow */
        }

        .profile-dropdown:hover {
            transform: none;
            opacity: 0.9;
        }

        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid transparent;
            transition: opacity 0.3s ease;
            cursor: pointer;
        }

        .profile-img:hover {
            opacity: 0.9;
        }

        .default-avatar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
        }

        /* Mobile Profile Wrapper */
        .mobile-profile-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-left: auto;
            margin-right: 0.5rem;
        }

        .mobile-profile-wrapper .profile-dropdown {
            padding: 0 !important;
        }

        .mobile-profile-wrapper .dropdown-menu {
            right: 0;
            left: auto;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
        }

        /* FIXED: Dropdown Menu Z-Index */
        .dropdown-menu {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            padding: 0.5rem;
            margin-top: 0.5rem;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            animation: dropdownSlide 0.3s ease;
            z-index: 1050;
        }

        .profile-menu {
            min-width: 200px;
        }

        /* FIXED: Ensure dropdown items are above other content */
        .nav-item.dropdown {
            position: relative;
            z-index: 1050;
        }

        @keyframes dropdownSlide {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item {
            padding: 0.75rem 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 600;
            color: #4a5568;
        }

        .dropdown-item:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            color: #667eea;
            transform: translateX(5px);
        }

        .dropdown-item.logout-item:hover {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
            color: #ef4444;
        }

        .dropdown-item i {
            margin-right: 0.5rem;
            width: 20px;
        }

        .dropdown-divider {
            margin: 0.5rem 0;
            opacity: 0.1;
        }

        /* Button Styles */
        .btn-primary {
            background: var(--primary-gradient) !important;
            border: none;
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        /* Alert Styles */
        .alert {
            border: none;
            border-radius: 16px;
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            animation: slideInDown 0.3s ease;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert i {
            font-size: 1.5rem;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
            color: #059669;
            border-left: 4px solid #10b981;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
            color: #dc2626;
            border-left: 4px solid #ef4444;
        }

        .alert-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.1));
            color: #d97706;
            border-left: 4px solid #f59e0b;
        }

        /* Responsive Design */
        @media (max-width: 991px) {
            .navbar-collapse {
                background: white;
                padding: 1rem;
                border-radius: 16px;
                margin-top: 1rem;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                flex-basis: 100%;
            }

            .nav-link {
                margin: 0.25rem 0;
            }

            .mobile-profile-wrapper {
                z-index: 1050;
            }

            .mobile-profile-wrapper .profile-img {
                width: 38px;
                height: 38px;
            }

            .mobile-profile-wrapper .dropdown-menu {
                position: absolute !important;
                right: 0 !important;
                left: auto !important;
                margin-top: 0.5rem !important;
            }
        }

        @media (max-width: 576px) {
            .mobile-profile-wrapper .profile-img {
                width: 35px;
                height: 35px;
            }

            .default-avatar {
                font-size: 1rem;
            }

            .notification-icon {
                width: 35px;
                height: 35px;
            }

            .notification-icon i {
                font-size: 1.1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Animated Background -->
    <div class="video-background">
        <div class="video-fallback">
            <div class="fallback-gradient"></div>
        </div>
    </div>

    <!-- Mesh Overlay -->
    <div class="mesh-overlay"></div>

    <!-- Floating Particles -->
    <div class="particles-container" id="particles"></div>

    <!-- Modern Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>">
                <i class="fas fa-book-open"></i> University Library
            </a>

            <!-- Profile Icon for Mobile (shown outside menu) -->
            <?php if (isset($_SESSION['userId'])): ?>
                <?php
                // Fetch unread notification count
                require_once __DIR__ . '/../../models/Notification.php';
                $notificationModel = new \App\Models\Notification();
                $unreadCount = $notificationModel->getUnreadCount($_SESSION['userId']);
                ?>
                <div class="mobile-profile-wrapper d-lg-none">
                    <!-- Notification Icon -->
                    <a href="<?= BASE_URL ?>user/notifications" class="notification-icon">
                        <i class="fas fa-bell"></i>
                        <?php if ($unreadCount > 0): ?>
                            <span class="notification-badge"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span>
                        <?php endif; ?>
                    </a>

                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle profile-dropdown" href="#" id="mobileProfileDropdown"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php
                            $profileImage = $_SESSION['profileImage'] ?? null;
                            if ($profileImage && file_exists($profileImage)) {
                                echo '<img src="' . BASE_URL . $profileImage . '" alt="Profile" class="profile-img">';
                            } else {
                                echo '<div class="profile-img default-avatar">' . strtoupper(substr($_SESSION['userId'], 0, 1)) . '</div>';
                            }
                            ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end profile-menu">
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>user/profile">
                                    <i class="fas fa-user-circle"></i> Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>user/dashboard">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>user/notifications">
                                    <i class="fas fa-bell"></i> Notifications
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item logout-item" href="<?= BASE_URL ?>logout">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isset($_SESSION['userId'])): ?>
                        <?php if ($_SESSION['userType'] === 'Admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= BASE_URL ?>admin/dashboard">
                                    <i class="fas fa-chart-line"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= BASE_URL ?>admin/books">
                                    <i class="fas fa-book"></i> Books
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= BASE_URL ?>admin/users">
                                    <i class="fas fa-users"></i> Users
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= BASE_URL ?>e-resources">
                                    <i class="fas fa-file-pdf"></i> E-Resources
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= BASE_URL ?>user/dashboard">
                                    <i class="fas fa-chart-line"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= BASE_URL ?>user/books">
                                    <i class="fas fa-search"></i> Browse
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= BASE_URL ?>e-resources">
                                    <i class="fas fa-file-pdf"></i> E-Resources
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= BASE_URL ?>user/return">
                                    <i class="fas fa-undo"></i> Returns
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= BASE_URL ?>user/fines">
                                    <i class="fas fa-receipt"></i> Fines
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>about">
                                <i class="fas fa-info-circle"></i> About
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>library">
                                <i class="fas fa-book-reader"></i> Library
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>contact">
                                <i class="fas fa-envelope"></i> Contact
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['userId'])): ?>
                        <!-- Notification Icon for desktop -->
                        <li class="nav-item d-none d-lg-block">
                            <a href="<?= BASE_URL ?>user/notifications" class="nav-link notification-icon">
                                <i class="fas fa-bell"></i>
                                <?php if ($unreadCount > 0): ?>
                                    <span class="notification-badge"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <!-- Profile dropdown for desktop only -->
                        <li class="nav-item dropdown d-none d-lg-block">
                            <a class="nav-link dropdown-toggle profile-dropdown" href="#" id="navbarDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <?php
                                // Check if user has profile image, otherwise use default
                                $profileImage = $_SESSION['profileImage'] ?? null;
                                if ($profileImage && file_exists($profileImage)) {
                                    echo '<img src="' . BASE_URL . $profileImage . '" alt="Profile" class="profile-img">';
                                } else {
                                    // Default avatar with first letter
                                    echo '<div class="profile-img default-avatar">' . strtoupper(substr($_SESSION['userId'], 0, 1)) . '</div>';
                                }
                                ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end profile-menu">
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>user/profile">
                                        <i class="fas fa-user-circle"></i> Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>user/dashboard">
                                        <i class="fas fa-tachometer-alt"></i> Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>user/notifications">
                                        <i class="fas fa-bell"></i> Notifications
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item logout-item" href="<?= BASE_URL ?>logout">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>login">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white ms-2" href="<?= BASE_URL ?>signup">
                                <i class="fas fa-user-plus"></i> Sign Up
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>


    <!-- Modern Toast/Modal Notifications -->
    <?php
    require_once __DIR__ . '/../../helpers/NotificationHelper.php';
    use App\Helpers\NotificationHelper;
    $notifications = NotificationHelper::getNotifications();
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const notifications = <?php echo json_encode($notifications); ?>;
            notifications.forEach(function (n) {
                let icon = n.type;
                let toast = (n.type === 'success' || n.type === 'info' || n.type === 'warning');
                let title = n.type.charAt(0).toUpperCase() + n.type.slice(1);
                let options = {
                    icon: icon,
                    title: title,
                    text: n.message,
                    toast: toast,
                    position: 'top-end',
                    showConfirmButton: !toast,
                    timer: toast ? 4000 : undefined,
                    timerProgressBar: toast,
                    showCloseButton: true,
                    customClass: {
                        popup: toast ? 'swal2-toast' : ''
                    }
                };
                Swal.fire(options);
            });
        });
    </script>

    <!-- Main Content -->
    <main class="py-4">

        <script>
            // Generate floating particles
            const particlesContainer = document.getElementById('particles');
            if (particlesContainer && window.innerWidth > 768) {
                for (let i = 0; i < 20; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'particle';
                    particle.style.left = Math.random() * 100 + '%';
                    particle.style.animationDelay = Math.random() * 10 + 's';
                    particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
                    particlesContainer.appendChild(particle);
                }
            }

            // Navbar scroll effect
            window.addEventListener('scroll', function () {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            // Auto-dismiss alerts after 5 seconds
            setTimeout(function () {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        </script>