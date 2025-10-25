<?php
$pageTitle = 'Users Management';
include APP_ROOT . '/views/layouts/admin-header.php';
$currentAdminId = $_SESSION['userId'] ?? '';
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
        --info-color: #06b6d4;
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

    /* Left Sidebar - Same as Dashboard */
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

    /* Statistics Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.75rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: var(--card-gradient);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .stat-card.purple { --card-gradient: linear-gradient(90deg, #667eea, #764ba2); }
    .stat-card.green { --card-gradient: linear-gradient(90deg, #10b981, #059669); }
    .stat-card.orange { --card-gradient: linear-gradient(90deg, #f59e0b, #d97706); }
    .stat-card.blue { --card-gradient: linear-gradient(90deg, #06b6d4, #0891b2); }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--card-gradient);
        color: white;
        font-size: 1.5rem;
    }

    .stat-info h3 {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
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

    .table-user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1rem;
    }

    .status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
    }

    .status-badge.verified {
        background: #d1fae5;
        color: #065f46;
    }

    .status-badge.unverified {
        background: #fef3c7;
        color: #92400e;
    }

    .badge {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
    }

    .badge.bg-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .badge.bg-success {
        background: #10b981;
        color: white;
    }

    .badge.bg-warning {
        background: #f59e0b;
        color: white;
    }

    .badge.bg-info {
        background: #06b6d4;
        color: white;
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

    .btn-view {
        background: #dbeafe;
        color: #1e40af;
    }

    .btn-view:hover {
        background: #3b82f6;
        color: white;
    }

    .btn-edit {
        background: #fef3c7;
        color: #92400e;
    }

    .btn-edit:hover {
        background: #f59e0b;
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

    /* Modal Styling */
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

    .modal-dialog.modal-lg {
        max-width: 900px;
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

    /* Form Styling */
    .form-label {
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
        display: block;
        font-size: 0.95rem;
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
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        cursor: pointer;
    }

    .form-check-label {
        cursor: pointer;
        font-weight: 500;
        color: var(--gray-700);
    }

    /* Button Styling */
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

    .btn-primary {
        color: #ffffff;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-color: transparent;
        box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 12px -1px rgba(99, 102, 241, 0.4);
    }

    .btn-secondary {
        color: var(--gray-700);
        background-color: white;
        border-color: var(--gray-300);
    }

    .btn-secondary:hover {
        background-color: var(--gray-50);
        border-color: var(--gray-400);
    }

    .btn-danger {
        color: white;
        background: var(--danger-color);
        border-color: transparent;
        box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3);
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 12px -1px rgba(239, 68, 68, 0.4);
    }

    /* Grid Layout */
    .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -0.75rem;
    }

    .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
        padding: 0 0.75rem;
        margin-bottom: 1.5rem;
    }

    .col-md-12 {
        flex: 0 0 100%;
        max-width: 100%;
        padding: 0 0.75rem;
        margin-bottom: 1.5rem;
    }

    /* Alert */
    .alert {
        padding: 1rem 1.5rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        border: 2px solid;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border-color: #ef4444;
    }

    /* Modal Styling - CRITICAL Z-INDEX FIX */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        z-index: 1100; /* Higher than sidebar (1000) and overlay (999) */
        overflow-y: auto;
        padding: 1rem;
    }

    .modal.show {
        display: flex !important;
        align-items: center;
        justify-content: center;
    }

    .modal-dialog {
        position: relative;
        width: 100%;
        max-width: 600px;
        margin: auto;
        z-index: 1101;
    }

    .modal-dialog.modal-lg {
        max-width: 800px;
    }

    .modal-content {
        position: relative;
        background: white;
        border-radius: 20px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        animation: modalSlideIn 0.3s ease-out;
        overflow: hidden;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-20px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .modal-header {
        padding: 1.5rem 2rem;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-title {
        font-size: 1.25rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0;
    }

    .modal-body {
        padding: 2rem;
        max-height: calc(100vh - 300px);
        overflow-y: auto;
    }

    .modal-footer {
        padding: 1.5rem 2rem;
        background: var(--gray-50);
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 1rem;
        border-top: 1px solid var(--gray-200);
    }

    .btn-close {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        font-size: 1.5rem;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        padding: 0;
        line-height: 1;
    }

    .btn-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }

    /* Mobile Responsive */
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

        .col-md-6 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

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

    .text-danger {
        color: var(--danger-color);
    }

    .text-muted {
        color: var(--gray-500);
    }
</style>

<!-- Mobile Overlay -->
<div class="mobile-overlay" onclick="toggleMobileSidebar()"></div>

<!-- Admin Layout -->
<div class="admin-layout">
    <!-- Left Sidebar -->
<?include APP_ROOT . '/views/admin/admin-navbar.php' ?>;

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="mobile-menu-btn" onclick="toggleMobileSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Users Management</h1>
                <div class="breadcrumb">
                    <span>Home</span>
                    <span>/</span>
                    <span>Users</span>
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
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card purple">
                    <div class="stat-header">
                        <div class="stat-info">
                            <h3>Total Users</h3>
                            <div class="stat-number"><?= count($users ?? []) ?></div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card green">
                    <div class="stat-header">
                        <div class="stat-info">
                            <h3>Verified Users</h3>
                            <div class="stat-number">
                                <?= count(array_filter($users ?? [], fn($u) => $u['isVerified'] == 1)) ?>
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card orange">
                    <div class="stat-header">
                        <div class="stat-info">
                            <h3>Students</h3>
                            <div class="stat-number">
                                <?= count(array_filter($users ?? [], fn($u) => $u['userType'] == 'Student')) ?>
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card blue">
                    <div class="stat-header">
                        <div class="stat-info">
                            <h3>Faculty</h3>
                            <div class="stat-number">
                                <?= count(array_filter($users ?? [], fn($u) => $u['userType'] == 'Faculty')) ?>
                            </div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Control Bar -->
            <div class="control-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search users by ID, email, or phone...">
                </div>
                
                <div class="filter-group">
                    <select id="userTypeFilter" class="filter-select">
                        <option value="">All User Types</option>
                        <option value="Admin">Admin</option>
                        <option value="Student">Student</option>
                        <option value="Teacher">Teacher</option>
                    </select>
                    
                    <select id="statusFilter" class="filter-select">
                        <option value="">All Status</option>
                        <option value="verified">Verified</option>
                        <option value="unverified">Unverified</option>
                    </select>
                </div>

                <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus"></i>
                    Add New User
                </button>
            </div>

            <!-- Users Table -->
            <div class="table-container">
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Avatar</th>
                                <th>User ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>User Type</th>
                                <th>Gender</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="table-user-avatar">
                                                <?= strtoupper(substr($user['username'] ?? $user['userId'] ?? 'U', 0, 2)) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($user['userId'] ?? 'N/A') ?></strong>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($user['username'] ?? 'N/A') ?></strong>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($user['emailId'] ?? 'No email') ?><br>
                                            <small class="text-muted"><?= htmlspecialchars($user['userType'] ?? 'Unknown') ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($user['phoneNumber'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                ($user['userType'] ?? '') === 'Admin' ? 'primary' : 
                                                (($user['userType'] ?? '') === 'Teacher' ? 'info' : 'success') 
                                            ?>">
                                                <?= htmlspecialchars($user['userType'] ?? 'Unknown') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($user['gender'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if (($user['isVerified'] ?? 0) == 1): ?>
                                                <span class="status-badge verified">
                                                    <i class="fas fa-check-circle"></i> Verified
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge unverified">
                                                    <i class="fas fa-clock"></i> Unverified
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-action btn-view" 
                                                        onclick='viewUser(<?= json_encode($user, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                                                        title="View">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn-action btn-edit" 
                                                        onclick='editUser(<?= json_encode($user, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if (($user['userId'] ?? '') !== $currentAdminId): ?>
                                                <button class="btn-action btn-delete" 
                                                        onclick="deleteUser('<?= htmlspecialchars($user['userId'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($user['username'] ?? $user['emailId'] ?? 'this user', ENT_QUOTES) ?>')"
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9">
                                        <div class="empty-state">
                                            <i class="fas fa-users"></i>
                                            <h3>No Users Found</h3>
                                            <p>Start by adding your first user to the system</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
            
        <!-- Admin Footer -->
        <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>
            
    </main>
</div>

<!-- Add User Modal -->
<div class="modal" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus"></i>
                    Add New User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">×</button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>admin/users/add">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="emailId" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phoneNumber" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" required minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">User Type <span class="text-danger">*</span></label>
                            <select class="form-control" name="userType" required>
                                <option value="Student">Student</option>
                                <option value="Faculty">Faculty</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-control" name="gender" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="dob">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="isVerified" value="1" id="add_isVerified">
                                <label class="form-check-label" for="add_isVerified">
                                    Account Verified
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i>
                    Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">×</button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>admin/users/edit">
                <div class="modal-body">
                    <input type="hidden" name="userId" id="edit_userId">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" id="edit_username" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="emailId" id="edit_emailId" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="phoneNumber" id="edit_phoneNumber" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">User Type <span class="text-danger">*</span></label>
                            <select class="form-control" name="userType" id="edit_userType" required>
                                <option value="Student">Student</option>
                                <option value="Faculty">Faculty</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-control" name="gender" id="edit_gender" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="dob" id="edit_dob">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" id="edit_address" rows="2"></textarea>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="isVerified" value="1" id="edit_isVerified">
                                <label class="form-check-label" for="edit_isVerified">
                                    Account Verified
                                </label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <hr>
                            <label class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" name="new_password" placeholder="Enter new password" minlength="6">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div class="modal" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                <h5 class="modal-title">
                    <i class="fas fa-user"></i>
                    User Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">×</button>
            </div>
            <div class="modal-body" id="viewUserContent">
                <!-- Content loaded by JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #ef4444;">
                <h5 class="modal-title">
                    <i class="fas fa-trash"></i>
                    Delete User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">×</button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>admin/users/delete">
                <div class="modal-body">
                    <input type="hidden" name="userId" id="delete_userId">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning!</strong> This action cannot be undone.
                    </div>
                    <p>Are you sure you want to delete user <strong id="delete_username"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i>
                        Delete User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Sidebar functions
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

// Modal functionality
document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(btn => {
    btn.addEventListener('click', function() {
        const modal = this.closest('.modal');
        bootstrap.Modal.getInstance(modal).hide();
    });
});

document.querySelectorAll('[data-bs-toggle="modal"]').forEach(btn => {
    btn.addEventListener('click', function() {
        const targetId = this.getAttribute('data-bs-target');
        const modal = document.querySelector(targetId);
        new bootstrap.Modal(modal).show();
    });
});

document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            bootstrap.Modal.getInstance(this).hide();
        }
    });
});

// View User - FIXED
function viewUser(user) {
    const userId = user.userId || 'N/A';
    const username = user.username || 'N/A';
    const emailId = user.emailId || 'N/A';
    
    const content = `
        <div class="row">
            <div class="col-md-12 text-center" style="margin-bottom: 1.5rem;">
                <div class="user-avatar" style="width: 80px; height: 80px; font-size: 2rem; margin: 0 auto;">
                    ${username.substring(0, 2).toUpperCase()}
                </div>
                <h4 style="margin-top: 1rem; font-weight: 600;">${username}</h4>
                <p style="color: #64748b;">${emailId}</p>
                <p style="color: #94a3b8; font-size: 0.9rem;">ID: ${userId}</p>
            </div>
            <div class="col-md-6">
                <strong>Username:</strong><br>
                ${username}
            </div>
            <div class="col-md-6">
                <strong>Email:</strong><br>
                ${emailId}
            </div>
            <div class="col-md-6">
                <strong>Phone:</strong><br>
                ${user.phoneNumber || 'N/A'}
            </div>
            <div class="col-md-6">
                <strong>User Type:</strong><br>
                <span class="badge bg-primary">${user.userType || 'N/A'}</span>
            </div>
            <div class="col-md-6">
                <strong>Gender:</strong><br>
                ${user.gender || 'N/A'}
            </div>
            <div class="col-md-6">
                <strong>Date of Birth:</strong><br>
                ${user.dob || 'Not set'}
            </div>
            <div class="col-md-6">
                <strong>Status:</strong><br>
                ${user.isVerified ? '<span class="badge bg-success">Verified</span>' : '<span class="badge bg-warning">Unverified</span>'}
            </div>
            <div class="col-md-12">
                <strong>Address:</strong><br>
                ${user.address || 'Not set'}
            </div>
        </div>
    `;
    document.getElementById('viewUserContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('viewUserModal')).show();
}

// Edit User - FIXED
function editUser(user) {
    document.getElementById('edit_userId').value = user.userId || '';
    document.getElementById('edit_username').value = user.username || '';
    document.getElementById('edit_emailId').value = user.emailId || '';
    document.getElementById('edit_phoneNumber').value = user.phoneNumber || '';
    document.getElementById('edit_userType').value = user.userType || 'Student';
    document.getElementById('edit_gender').value = user.gender || 'Male';
    document.getElementById('edit_dob').value = user.dob || '';
    document.getElementById('edit_address').value = user.address || '';
    document.getElementById('edit_isVerified').checked = user.isVerified == 1;
    
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

// Delete User - FIXED
function deleteUser(userId, displayName) {
    document.getElementById('delete_userId').value = userId || '';
    document.getElementById('delete_username').textContent = displayName || 'this user';
    new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
}

// Search and Filter - FIXED
function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const userTypeFilter = document.getElementById('userTypeFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    const rows = document.querySelectorAll('.table tbody tr');
    
    rows.forEach(row => {
        if (row.cells.length === 1) return; // Skip empty state row
        
        const userId = row.cells[1].textContent.toLowerCase();
        const username = row.cells[2].textContent.toLowerCase();
        const email = row.cells[3].textContent.toLowerCase();
        const phone = row.cells[4].textContent.toLowerCase();
        const userType = row.cells[5].textContent.trim();
        const statusText = row.cells[7].textContent.toLowerCase();
        
        const matchesSearch = searchTerm === '' || 
            userId.includes(searchTerm) || 
            username.includes(searchTerm) ||
            email.includes(searchTerm) || 
            phone.includes(searchTerm);
        
        const matchesUserType = userTypeFilter === '' || userType === userTypeFilter;
        
        let matchesStatus = true;
        if (statusFilter === 'verified') {
            matchesStatus = statusText.includes('verified') && !statusText.includes('unverified');
        } else if (statusFilter === 'unverified') {
            matchesStatus = statusText.includes('unverified');
        }
        
        if (matchesSearch && matchesUserType && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

document.getElementById('searchInput').addEventListener('input', applyFilters);
document.getElementById('userTypeFilter').addEventListener('change', applyFilters);
document.getElementById('statusFilter').addEventListener('change', applyFilters);
</script>