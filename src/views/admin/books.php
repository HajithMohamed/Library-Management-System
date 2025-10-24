<?php
// Prevent direct access
if (!defined('APP_ROOT')) {
    exit('Direct access not allowed');
}

// Set default values to prevent undefined variable errors
$books = $books ?? [];
$publishers = $publishers ?? [];
$pageTitle = $pageTitle ?? 'Books Management';

// Include header
include APP_ROOT . '/views/layouts/admin-header.php';
?>

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

    /* Modern Color Palette */
    :root {
        --primary-color: #6366f1;
        --primary-dark: #4f46e5;
        --primary-light: #818cf8;
        --secondary-color: #8b5cf6;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --dark-color: #1f2937;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
    }

    /* Main Layout Container */
    .admin-layout {
        display: flex;
        min-height: 100vh;
        background: #f0f2f5;
    }

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
    }

    /* Sidebar Navigation */
    .sidebar-nav {
        padding: 1rem 0;
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
    }

    .sidebar.collapsed .nav-section-title {
        display: none;
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
    }

    .nav-link span {
        white-space: nowrap;
    }

    .sidebar.collapsed .nav-link span {
        display: none;
    }

    .nav-badge {
        margin-left: auto;
        background: #ef4444;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .sidebar.collapsed .nav-badge {
        display: none;
    }

    /* Sidebar Footer */
    .sidebar-footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(0, 0, 0, 0.2);
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 0.75rem;
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
        flex-shrink: 0;
    }

    .user-info {
        flex: 1;
    }

    .user-name {
        font-weight: 600;
        font-size: 0.95rem;
    }

    .user-role {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.6);
    }

    .sidebar.collapsed .user-info {
        display: none;
    }

    /* Main Content Area */
    .main-content {
        flex: 1;
        margin-left: 280px;
        transition: margin-left 0.3s ease;
        min-height: 100vh;
    }

    .sidebar.collapsed ~ .main-content {
        margin-left: 80px;
    }

    /* Top Header */
    .top-header {
        background: white;
        padding: 1.5rem 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .header-left h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }

    .breadcrumb {
        display: flex;
        gap: 0.5rem;
        color: #64748b;
        font-size: 0.9rem;
    }

    .header-right {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .header-btn {
        background: white;
        border: 1px solid #e2e8f0;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        color: #64748b;
        text-decoration: none;
    }

    .header-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #64748b;
    }

    /* Page Content */
    .page-content {
        padding: 2rem;
    }

    /* Control Bar */
    .control-bar {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .search-box {
        flex: 1;
        min-width: 250px;
        position: relative;
    }

    .search-box i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
    }

    .search-box input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 3rem;
        border: 2px solid var(--gray-300);
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.2s ease;
    }

    .search-box input:focus {
        border-color: var(--primary-color);
        outline: 0;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .filter-group {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .filter-select {
        padding: 0.75rem 1rem;
        border: 2px solid var(--gray-300);
        border-radius: 12px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        background: white;
        cursor: pointer;
    }

    .filter-select:focus {
        border-color: var(--primary-color);
        outline: 0;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    /* Add Button */
    .btn-add {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 12px -1px rgba(99, 102, 241, 0.4);
    }

    /* Table Container */
    .table-container {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        background: var(--gray-50);
        color: var(--gray-700);
        font-weight: 600;
        padding: 1rem;
        text-align: left;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--gray-200);
        white-space: nowrap;
    }

    .table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid var(--gray-100);
    }

    .table tbody tr:hover {
        background: var(--gray-50);
    }

    .table tbody td {
        padding: 1rem;
        color: var(--gray-800);
        vertical-align: middle;
    }

    .book-cover {
        width: 50px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .book-title {
        font-weight: 600;
        color: var(--dark-color);
    }

    .status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
    }

    .status-badge.available {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge.unavailable {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-badge.low-stock {
        background: #fef3c7;
        color: #92400e;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        font-size: 0.9rem;
    }

    .btn-edit {
        background: #dbeafe;
        color: #1e40af;
    }

    .btn-edit:hover {
        background: #3b82f6;
        color: white;
    }

    .btn-delete {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-delete:hover {
        background: #ef4444;
        color: white;
    }

    /* Modern Modal Styling */
    .modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(17, 24, 39, 0.7);
        backdrop-filter: blur(4px);
        animation: fadeIn 0.2s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal.show {
        display: flex !important;
        align-items: center;
        justify-content: center;
    }

    .modal-dialog {
        position: relative;
        width: 90%;
        max-width: 900px;
        margin: 2rem auto;
        animation: slideUp 0.3s ease-out;
    }

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-content {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        background-color: #ffffff;
        border-radius: 20px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        max-height: 90vh;
        overflow: hidden;
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 2rem 2rem 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    }

    .modal-title {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .modal-header .btn-close {
        padding: 0.5rem;
        margin: 0;
        background: rgba(255, 255, 255, 0.2);
        border: 0;
        border-radius: 10px;
        font-size: 1.25rem;
        font-weight: 700;
        color: #ffffff;
        opacity: 0.9;
        cursor: pointer;
        transition: all 0.2s;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-header .btn-close:hover {
        opacity: 1;
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }

    .modal-body {
        position: relative;
        flex: 1 1 auto;
        padding: 2rem;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .modal-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 1.5rem 2rem;
        border-top: 1px solid var(--gray-200);
        background: var(--gray-50);
        gap: 1rem;
    }

    /* Modern Scrollbar */
    .modal-body::-webkit-scrollbar {
        width: 8px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: var(--gray-100);
        border-radius: 10px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 10px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
    }

    /* Form Grid */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .form-grid.full-width {
        grid-template-columns: 1fr;
    }

    .form-group {
        margin-bottom: 0;
    }

    /* Modern Form Styling */
    .form-label {
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.95rem;
    }

    .form-label i {
        color: var(--primary-color);
        font-size: 0.9rem;
    }

    .form-control,
    .form-select {
        display: block;
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: var(--gray-900);
        background-color: #ffffff;
        border: 2px solid var(--gray-300);
        border-radius: 12px;
        transition: all 0.2s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        outline: 0;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        background-color: #ffffff;
    }

    .form-control:hover:not(:focus),
    .form-select:hover:not(:focus) {
        border-color: var(--gray-400);
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    /* Image Upload Preview */
    .image-upload-container {
        display: flex;
        gap: 2rem;
        align-items: flex-start;
    }

    .image-preview {
        flex-shrink: 0;
    }

    .preview-box {
        width: 150px;
        height: 200px;
        border: 2px dashed var(--gray-300);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: var(--gray-50);
    }

    .preview-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .preview-placeholder {
        text-align: center;
        color: var(--gray-400);
    }

    .preview-placeholder i {
        font-size: 3rem;
        margin-bottom: 0.5rem;
    }

    /* Modern Button Styling */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-weight: 600;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        user-select: none;
        border: 2px solid transparent;
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 12px;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-primary {
        color: #ffffff;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-color: transparent;
        box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
    }

    .btn-primary:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 12px -1px rgba(99, 102, 241, 0.4);
    }

    .btn-secondary {
        color: var(--gray-700);
        background-color: white;
        border-color: var(--gray-300);
    }

    .btn-secondary:hover:not(:disabled) {
        background-color: var(--gray-50);
        border-color: var(--gray-400);
    }

    /* Spinner */
    .spinner-border {
        width: 1rem;
        height: 1rem;
        border: 2px solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: spinner 0.75s linear infinite;
    }

    .spinner-border-sm {
        width: 0.875rem;
        height: 0.875rem;
        border-width: 2px;
    }

    @keyframes spinner {
        to { transform: rotate(360deg); }
    }

    /* Mobile Responsive */
    @media (max-width: 1024px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.mobile-open {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: 0;
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .mobile-overlay.show {
            display: block;
        }

        .top-header {
            padding: 1rem;
        }

        .header-left h1 {
            font-size: 1.25rem;
        }

        .page-content {
            padding: 1rem;
        }

        .control-bar {
            flex-direction: column;
        }

        .search-box {
            width: 100%;
        }

        .filter-group {
            width: 100%;
        }

        .filter-select {
            flex: 1;
        }

        .table-container {
            overflow-x: auto;
        }

        .image-upload-container {
            flex-direction: column;
        }
    }

    /* Mobile Menu Button */
    .mobile-menu-btn {
        display: none;
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #1e293b;
        cursor: pointer;
        padding: 0.5rem;
    }

    @media (max-width: 768px) {
        .mobile-menu-btn {
            display: block;
        }
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: var(--gray-500);
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    .empty-state h3 {
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
        color: var(--gray-700);
    }

    /* Alert */
    .alert {
        padding: 1rem 1.5rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
        border: 2px solid;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-color: #10b981;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border-color: #ef4444;
    }

    .alert .btn-close {
        margin-left: auto;
        background: transparent;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        opacity: 0.5;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .alert .btn-close:hover {
        opacity: 1;
    }
</style>

<!-- Mobile Overlay -->
<div class="mobile-overlay" onclick="toggleMobileSidebar()"></div>

<!-- Admin Layout -->
<div class="admin-layout">
    <!-- Left Sidebar -->
    <?php include APP_ROOT . '/views/admin/admin-navbar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="mobile-menu-btn" onclick="toggleMobileSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Books Management</h1>
                <div class="breadcrumb">
                    <span>Home</span>
                    <span>/</span>
                    <span>Books</span>
                </div>
            </div>
            <div class="header-right">
                <a href="<?= BASE_URL ?>admin/dashboard" class="header-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Dashboard</span>
                </a>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content">
            <!-- Control Bar -->
            <div class="control-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search books by ISBN, title, or author...">
                </div>
                
                <div class="filter-group">
                    <select id="publisherFilter" class="filter-select">
                        <option value="">All Publishers</option>
                        <?php if (!empty($publishers)): ?>
                            <?php foreach ($publishers as $publisher): ?>
                                <option value="<?= htmlspecialchars($publisher) ?>">
                                    <?= htmlspecialchars($publisher) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    
                    <select id="statusFilter" class="filter-select">
                        <option value="">All Status</option>
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>

                <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addBookModal">
                    <i class="fas fa-plus"></i>
                    Add New Book
                </button>
            </div>

            <!-- Books Table -->
            <div class="table-container">
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ISBN</th>
                                <th>Barcode</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Publisher</th>
                                <th>Total Copies</th>
                                <th>Available</th>
                                <th>Borrowed</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($books)): ?>
                                <?php foreach ($books as $book): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($book['isbn']) ?></code></td>
                                        <td>
                                            <?php if (!empty($book['barcode'])): ?>
                                                <code style="background: #f0f0f0; padding: 2px 6px; border-radius: 4px;">
                                                    <?= htmlspecialchars($book['barcode']) ?>
                                                </code>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="book-title">
                                            <?= htmlspecialchars($book['bookName']) ?>
                                            <?php if ($book['isTrending']): ?>
                                                <span class="badge" style="background: #fbbf24; color: #92400e; margin-left: 0.5rem;">
                                                    <i class="fas fa-fire"></i> Trending
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($book['isSpecial'] && !empty($book['specialBadge'])): ?>
                                                <span class="badge" style="background: #8b5cf6; color: white; margin-left: 0.5rem;">
                                                    <?= htmlspecialchars($book['specialBadge']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($book['authorName']) ?></td>
                                        <td><?= htmlspecialchars($book['publisherName']) ?></td>
                                        <td><?= htmlspecialchars($book['totalCopies']) ?></td>
                                        <td>
                                            <?php if ($book['available'] > 0): ?>
                                                <span class="status-badge available"><?= $book['available'] ?></span>
                                            <?php else: ?>
                                                <span class="status-badge unavailable">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($book['borrowed'] > 0): ?>
                                                <span class="badge" style="background: #fbbf24; color: #92400e;"><?= $book['borrowed'] ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($book['available'] > 5): ?>
                                                <span class="status-badge available">
                                                    <i class="fas fa-check-circle"></i> In Stock
                                                </span>
                                            <?php elseif ($book['available'] > 0): ?>
                                                <span class="status-badge low-stock">
                                                    <i class="fas fa-exclamation-triangle"></i> Low Stock
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge unavailable">
                                                    <i class="fas fa-times-circle"></i> Out of Stock
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if (!empty($book['barcode'])): ?>
                                                <button class="btn-action" style="background: #dbeafe; color: #1e40af;" 
                                                        onclick="viewBarcode('<?= htmlspecialchars($book['isbn']) ?>', '<?= htmlspecialchars($book['barcode']) ?>', '<?= htmlspecialchars(addslashes($book['bookName'])) ?>')"
                                                        title="View Barcode">
                                                    <i class="fas fa-barcode"></i>
                                                </button>
                                                <?php endif; ?>
                                                <button class="btn-action btn-edit" 
                                                        onclick='openEditModal(<?= json_encode($book, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn-action btn-delete" 
                                                        onclick="deleteBook('<?= htmlspecialchars($book['isbn']) ?>', '<?= htmlspecialchars(addslashes($book['bookName'])) ?>')"
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10">
                                        <div class="empty-state">
                                            <i class="fas fa-book-open"></i>
                                            <h3>No Books Found</h3>
                                            <p>Start by adding your first book to the library</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>
    </main>
</div>

<!-- Add Book Modal -->
<div class="modal" id="addBookModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i>
                    Add New Book
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">×</button>
            </div>
            <form id="addBookForm" onsubmit="return handleAddBook(event)" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="add_isbn" class="form-label">
                                <i class="fas fa-barcode"></i>
                                ISBN <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="add_isbn" name="isbn" required 
                                   placeholder="Enter ISBN (e.g., 9780134685991)">
                            <small class="text-muted">Barcode will be auto-generated</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="add_bookName" class="form-label">
                                <i class="fas fa-book"></i>
                                Book Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="add_bookName" name="bookName" required
                                   placeholder="Enter book title">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="add_author" class="form-label">
                                <i class="fas fa-user"></i>
                                Author Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="add_author" name="authorName" required
                                   placeholder="Enter author name">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="add_publisher" class="form-label">
                                <i class="fas fa-building"></i>
                                Publisher <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="add_publisher" name="publisherName" required
                                   placeholder="Enter publisher name">
                        </div>
                        
                        <div class="col-md-12">
                            <label for="add_description" class="form-label">
                                <i class="fas fa-align-left"></i>
                                Description
                            </label>
                            <textarea class="form-control" id="add_description" name="description" rows="3"
                                      placeholder="Enter book description (optional)"></textarea>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="add_totalCopies" class="form-label">
                                <i class="fas fa-copy"></i>
                                Total Copies <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="add_totalCopies" name="totalCopies" 
                                   min="1" value="1" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="add_available" class="form-label">
                                <i class="fas fa-check-circle"></i>
                                Available Copies <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="add_available" name="available" 
                                   min="0" value="1" required>
                        </div>
                        
                        <div class="col-md-12">
                            <label for="add_coverImage" class="form-label">
                                <i class="fas fa-image"></i>
                                Book Cover Image
                            </label>
                            <div class="image-upload-container">
                                <div class="image-preview">
                                    <div class="preview-box" id="add_imagePreview" style="display: none;">
                                        <img id="add_previewImage" src="" alt="Preview">
                                    </div>
                                    <div class="preview-box" id="add_imagePlaceholder">
                                        <div class="preview-placeholder">
                                            <i class="fas fa-image"></i>
                                            <p>Preview</p>
                                        </div>
                                    </div>
                                </div>
                                <div style="flex: 1;">
                                    <input type="file" class="form-control" id="add_coverImage" name="coverImage" accept="image/*">
                                    <small class="text-muted">Accepted formats: JPG, PNG, GIF, WebP. Max size: 5MB</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="addBookBtn">
                        <i class="fas fa-save"></i>
                        Add Book
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Book Modal -->
<div class="modal" id="editBookModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i>
                    Edit Book
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">×</button>
            </div>
            <form id="editBookForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="edit_isbn" name="isbn">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label for="edit_isbn_display" class="form-label">ISBN</label>
                            <input type="text" class="form-control" id="edit_isbn_display" disabled>
                            <small class="text-muted">ISBN cannot be changed</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="edit_barcode_display" class="form-label">Barcode</label>
                            <input type="text" class="form-control" id="edit_barcode_display" disabled>
                            <small class="text-muted">Auto-generated</small>
                        </div>
                        
                        <div class="col-md-12">
                            <label for="edit_bookName" class="form-label">
                                Book Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="edit_bookName" name="bookName" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="edit_author" class="form-label">
                                Author <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="edit_author" name="authorName" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="edit_publisher" class="form-label">
                                Publisher <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="edit_publisher" name="publisherName" required>
                        </div>
                        
                        <div class="col-md-12">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="edit_totalCopies" class="form-label">
                                Total Copies <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="edit_totalCopies" name="totalCopies" required min="1">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="edit_available" class="form-label">
                                Available Copies <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="edit_available" name="available" required min="0">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="edit_borrowed" class="form-label">
                                Borrowed Copies
                            </label>
                            <input type="number" class="form-control" id="edit_borrowed" name="borrowed" min="0" value="0">
                        </div>
                        
                        <div class="col-md-12">
                            <label for="edit_coverImage" class="form-label">Cover Image</label>
                            <input type="file" class="form-control" id="edit_coverImage" name="coverImage" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image. Max 5MB.</small>
                        </div>
                        
                        <div class="col-md-12">
                            <div id="edit_currentImage" style="display: none;">
                                <label class="form-label">Current Cover:</label>
                                <div class="text-center">
                                    <img id="edit_currentImageDisplay" src="" alt="Current Cover" class="img-thumbnail" 
                                         style="max-width: 250px; max-height: 250px; object-fit: contain;">
                                </div>
                            </div>
                            
                            <div id="edit_imagePreview" style="display: none;">
                                <label class="form-label">New Cover Preview:</label>
                                <div class="text-center">
                                    <img id="edit_previewImage" src="" alt="Preview" class="img-thumbnail" 
                                         style="max-width: 250px; max-height: 250px; object-fit: contain;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Update Book
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Barcode View Modal -->
<div class="modal" id="barcodeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-barcode"></i> Book Barcode
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">×</button>
            </div>
            <div class="modal-body text-center p-4" id="barcodeContent">
                <!-- Barcode content loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printBarcode()">
                    <i class="fas fa-print"></i> Print Barcode
                </button>
                <button type="button" class="btn btn-success" onclick="downloadBarcode()">
                    <i class="fas fa-download"></i> Download
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Sidebar toggle functions (same as dashboard)
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('collapsed');
    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
}

function toggleMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.mobile-overlay');
    sidebar.classList.toggle('mobile-open');
    overlay.classList.toggle('show');
}

// Load sidebar state
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
    }
});

// Bootstrap Modal Polyfill
const bootstrap = {
    Modal: class {
        constructor(element) {
            this.element = element;
        }
        
        show() {
            this.element.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        
        hide() {
            this.element.classList.remove('show');
            document.body.style.overflow = '';
        }
        
        static getInstance(element) {
            return new bootstrap.Modal(element);
        }
    }
};

// Modal close button functionality
document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(btn => {
    btn.addEventListener('click', function() {
        const modal = this.closest('.modal');
        bootstrap.Modal.getInstance(modal).hide();
    });
});

// Modal toggle button functionality
document.querySelectorAll('[data-bs-toggle="modal"]').forEach(btn => {
    btn.addEventListener('click', function() {
        const targetId = this.getAttribute('data-bs-target');
        const modal = document.querySelector(targetId);
        new bootstrap.Modal(modal).show();
    });
});

// Close modal on outside click
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            bootstrap.Modal.getInstance(this).hide();
        }
    });
});

// Image Preview for Add Book
document.getElementById('add_coverImage').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const file = this.files[0];
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!allowedTypes.includes(file.type)) {
            alert('Invalid file type. Please upload JPG, PNG, GIF, or WebP image.');
            this.value = '';
            document.getElementById('add_imagePreview').style.display = 'none';
            document.getElementById('add_imagePlaceholder').style.display = 'flex';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('add_previewImage').src = event.target.result;
            document.getElementById('add_imagePreview').style.display = 'block';
            document.getElementById('add_imagePlaceholder').style.display = 'none';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('add_imagePreview').style.display = 'none';
        document.getElementById('add_imagePlaceholder').style.display = 'flex';
    }
});

// Image Preview for Edit Book
document.getElementById('edit_coverImage').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const file = this.files[0];
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!allowedTypes.includes(file.type)) {
            alert('Invalid file type. Please upload JPG, PNG, GIF, or WebP image.');
            this.value = '';
            document.getElementById('edit_imagePreview').style.display = 'none';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('edit_previewImage').src = event.target.result;
            document.getElementById('edit_imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('edit_imagePreview').style.display = 'none';
    }
});

// Handle Add Book Form Submission
function handleAddBook(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const submitBtn = document.getElementById('addBookBtn');
    const originalBtnText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';
    
    fetch('<?= BASE_URL ?>admin/books/add', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Book added successfully with barcode: ' + (data.barcode || ''));
            bootstrap.Modal.getInstance(document.getElementById('addBookModal')).hide();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAlert('danger', data.message || 'Failed to add book');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
    
    return false;
}

// Open Edit Modal with book data
function openEditModal(book) {
    document.getElementById('edit_isbn').value = book.isbn;
    document.getElementById('edit_isbn_display').value = book.isbn;
    document.getElementById('edit_barcode_display').value = book.barcode || 'Auto-generated';
    document.getElementById('edit_bookName').value = book.bookName;
    document.getElementById('edit_author').value = book.authorName;
    document.getElementById('edit_publisher').value = book.publisherName;
    document.getElementById('edit_description').value = book.description || '';
    document.getElementById('edit_totalCopies').value = book.totalCopies;
    document.getElementById('edit_available').value = book.available;
    document.getElementById('edit_borrowed').value = book.borrowed || 0;
    
    if (book.bookImage) {
        document.getElementById('edit_currentImageDisplay').src = '<?= BASE_URL ?>public/uploads/books/' + book.bookImage;
        document.getElementById('edit_currentImage').style.display = 'block';
    } else {
        document.getElementById('edit_currentImage').style.display = 'none';
    }
    
    document.getElementById('edit_imagePreview').style.display = 'none';
    document.getElementById('edit_coverImage').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('editBookModal'));
    modal.show();
}

// Handle Edit Book Form Submission
document.getElementById('editBookForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';
    
    fetch('<?= BASE_URL ?>admin/books/edit', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('editBookModal')).hide();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAlert('danger', data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
});

// Delete Book
function deleteBook(isbn, bookName) {
    if (!confirm(`Are you sure you want to delete "${bookName}"? This action cannot be undone.`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('isbn', isbn);
    
    fetch('<?= BASE_URL ?>admin/books/delete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
    });
}

// View Barcode
function viewBarcode(isbn, barcodeValue, bookName) {
    const cleanIsbn = isbn.replace(/-/g, '');
    const labelPath = '<?= BASE_URL ?>public/uploads/barcodes/' + cleanIsbn + '_label.png';
    
    const content = `
        <div class="barcode-display">
            <h4 class="mb-4">${bookName}</h4>
            <div class="mb-4">
                <img src="${labelPath}" 
                     alt="Barcode Label" 
                     style="max-width: 100%; border: 2px solid #e5e7eb; border-radius: 8px;"
                     onerror="this.src='<?= BASE_URL ?>api/generate-barcode?isbn=${isbn}'">
            </div>
            <div class="alert alert-info">
                <strong>Barcode Value:</strong> <code style="font-size: 1.1rem;">${barcodeValue}</code><br>
                <strong>ISBN:</strong> <code>${isbn}</code>
            </div>
            <div class="mt-3">
                <p class="text-muted">Scan this barcode to quickly identify and manage this book</p>
            </div>
        </div>
    `;
    
    document.getElementById('barcodeContent').innerHTML = content;
    window.currentBarcodeLabel = labelPath;
    new bootstrap.Modal(document.getElementById('barcodeModal')).show();
}

function printBarcode() {
    const printContent = document.getElementById('barcodeContent').innerHTML;
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Print Barcode</title>');
    printWindow.document.write('<style>body{text-align:center;font-family:Arial;padding:20px;}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(printContent);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

function downloadBarcode() {
    if (window.currentBarcodeLabel) {
        const link = document.createElement('a');
        link.href = window.currentBarcodeLabel;
        link.download = 'barcode_label.png';
        link.click();
    }
}

// Show Alert
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.style.animation = 'slideIn 0.3s ease-out';
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => alertDiv.remove(), 300);
    }, 5000);
}

// Reset forms when modal is closed
document.getElementById('addBookModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('addBookForm').reset();
    document.getElementById('add_imagePreview').style.display = 'none';
    document.getElementById('add_imagePlaceholder').style.display = 'flex';
});

document.getElementById('editBookModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('editBookForm').reset();
    document.getElementById('edit_imagePreview').style.display = 'none';
    document.getElementById('edit_currentImage').style.display = 'none';
});

// Search and Filter Functionality
function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const publisherFilter = document.getElementById('publisherFilter').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    
    const rows = document.querySelectorAll('.table tbody tr');
    
    rows.forEach(row => {
        if (row.cells.length === 1) return;
        
        const isbn = row.cells[0].textContent.toLowerCase();
        const bookName = row.cells[2].textContent.toLowerCase();
        const author = row.cells[3].textContent.toLowerCase();
        const publisher = row.cells[4].textContent.toLowerCase();
        const availableText = row.cells[6].textContent.trim();
        const available = parseInt(availableText);
        
        const matchesSearch = searchTerm === '' || 
            isbn.includes(searchTerm) || 
            bookName.includes(searchTerm) || 
            author.includes(searchTerm);
        
        const matchesPublisher = publisherFilter === '' || publisher.includes(publisherFilter);
        
        let matchesStatus = true;
        if (statusFilter === 'available') {
            matchesStatus = available > 0;
        } else if (statusFilter === 'unavailable') {
            matchesStatus = available === 0;
        }
        
        if (matchesSearch && matchesPublisher && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

document.getElementById('searchInput').addEventListener('input', applyFilters);
document.getElementById('publisherFilter').addEventListener('change', applyFilters);
document.getElementById('statusFilter').addEventListener('change', applyFilters);

// Add slide animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
</script>

<?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>

