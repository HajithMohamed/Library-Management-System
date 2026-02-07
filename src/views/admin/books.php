<?php
// Prevent direct access
if (!defined('APP_ROOT')) {
    exit('Direct access not allowed');
}

use App\Helpers\ImageHelper;

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

    .sidebar.collapsed~.main-content {
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
        width: 60px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .no-image {
        width: 60px;
        height: 80px;
        background: linear-gradient(135deg, var(--gray-200), var(--gray-300));
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gray-500);
        font-size: 1.5rem;
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
        z-index: 9999 !important;
        /* FIXED: Modal appears above all content */
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(17, 24, 39, 0.75);
        backdrop-filter: blur(8px);
        animation: fadeIn 0.2s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
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
        z-index: 10000 !important;
        /* FIXED: Dialog layer */
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
        z-index: 10001 !important;
        /* FIXED: Content layer */
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
        max-height: calc(90vh - 200px);
        /* Ensure footer is always visible */
    }

    .modal-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 1.5rem 2rem;
        border-top: 1px solid var(--gray-200);
        background: var(--gray-50);
        gap: 1rem;
        flex-shrink: 0;
        /* Prevent footer from being hidden */
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

        /* FIXED: Blur background when modal is open */
        body.modal-open {
            overflow: hidden;
        }

        body.modal-open .main-content {
            filter: blur(3px);
            pointer-events: none;
            transition: filter 0.2s ease;
        }

        body.modal-open .sidebar {
            filter: blur(2px);
            pointer-events: none;
            transition: filter 0.2s ease;
        }

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
        to {
            transform: rotate(360deg);
        }
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

        .sidebar.collapsed~.main-content {
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

    .text-muted {
        color: var(--gray-500);
    }

    /* Grid Layout - MISSING CSS */
    .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -0.75rem;
    }

    .col-md-4 {
        flex: 0 0 33.333333%;
        max-width: 33.333333%;
        padding: 0 0.75rem;
        margin-bottom: 1.5rem;
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

    .form-check {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
        padding: 1rem;
        background: var(--gray-50);
        border-radius: 12px;
        border: 2px solid var(--gray-200);
        transition: all 0.2s ease;
    }

    .form-check:hover {
        border-color: var(--primary-color);
        background: rgba(99, 102, 241, 0.05);
    }

    .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        cursor: pointer;
        border: 2px solid var(--gray-400);
        border-radius: 4px;
    }

    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .form-check-label {
        cursor: pointer;
        font-weight: 600;
        color: var(--gray-700);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-check-label i {
        color: var(--warning-color);
    }

    @media (max-width: 768px) {

        .col-md-4,
        .col-md-6 {
            flex: 0 0 100%;
            max-width: 100%;
        }
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
            <!-- Session Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" onclick="this.parentElement.remove()">×</button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" onclick="this.parentElement.remove()">×</button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

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
                                <th>Cover</th>
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
                                        <td>
                                            <?= ImageHelper::renderBookCover($book['bookImage'] ?? null, $book['bookName'] ?? 'Book cover', 'book-cover') ?>
                                        </td>
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
                                                <span class="badge"
                                                    style="background: #fbbf24; color: #92400e; margin-left: 0.5rem;">
                                                    <i class="fas fa-fire"></i> Trending
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
                                                <span class="badge"
                                                    style="background: #fbbf24; color: #92400e;"><?= $book['borrowed'] ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($book['available'] >= 5): ?>
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
                                    <td colspan="11">
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
            <form method="POST" action="<?= BASE_URL ?>admin/books/add" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label">
                                <i class="fas fa-image"></i>
                                Book Cover Image
                            </label>
                            <div class="image-upload-container">
                                <div class="image-preview">
                                    <div class="preview-box" id="add_imagePlaceholder">
                                        <div class="preview-placeholder">
                                            <i class="fas fa-image"></i>
                                            <p style="font-size: 0.85rem; margin-top: 0.5rem;">No image</p>
                                        </div>
                                    </div>
                                    <div class="preview-box" id="add_imagePreview" style="display:none;">
                                        <img id="add_previewImage" src="" alt="Preview">
                                    </div>
                                </div>
                                <div style="flex: 1;">
                                    <input type="file" class="form-control" name="image" id="add_coverImage"
                                        accept="image/*">
                                    <small class="text-muted">Accepted formats: JPG, PNG, GIF, WebP (Max 2MB)</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="add_isbn" class="form-label">
                                <i class="fas fa-barcode"></i>
                                ISBN <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="isbn" required
                                placeholder="Enter ISBN (e.g., 9780134685991)">
                        </div>

                        <div class="col-md-6">
                            <label for="add_barcode" class="form-label">
                                <i class="fas fa-qrcode"></i>
                                Barcode
                            </label>
                            <input type="text" class="form-control" name="barcode"
                                placeholder="Leave empty for auto-generation">
                            <small class="text-muted">Auto-generated if left empty</small>
                        </div>

                        <div class="col-md-12">
                            <label for="add_bookName" class="form-label">
                                <i class="fas fa-book"></i>
                                Book Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="bookName" required
                                placeholder="Enter book title">
                        </div>

                        <div class="col-md-6">
                            <label for="add_author" class="form-label">
                                <i class="fas fa-user"></i>
                                Author Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="authorName" required
                                placeholder="Enter author name">
                        </div>

                        <div class="col-md-6">
                            <label for="add_publisher" class="form-label">
                                <i class="fas fa-building"></i>
                                Publisher <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="publisherName" required
                                placeholder="Enter publisher name">
                        </div>

                        <div class="col-md-12">
                            <label for="add_description" class="form-label">
                                <i class="fas fa-align-left"></i>
                                Description
                            </label>
                            <textarea class="form-control" name="description" rows="3"
                                placeholder="Enter book description"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="add_category" class="form-label">
                                <i class="fas fa-tag"></i>
                                Category
                            </label>
                            <input type="text" class="form-control" name="category"
                                placeholder="e.g., Computer Science, Literature">
                        </div>

                        <div class="col-md-6">
                            <label for="add_publicationYear" class="form-label">
                                <i class="fas fa-calendar"></i>
                                Publication Year
                            </label>
                            <input type="number" class="form-control" name="publicationYear" min="1800"
                                max="<?= date('Y') ?>" placeholder="e.g., <?= date('Y') ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="add_totalCopies" class="form-label">
                                <i class="fas fa-copy"></i>
                                Total Copies <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" name="totalCopies" min="1" value="1" required>
                        </div>

                        <div class="col-md-4">
                            <label for="add_available" class="form-label">
                                <i class="fas fa-check-circle"></i>
                                Available Copies <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" name="available" min="0" value="1" required>
                        </div>

                        <div class="col-md-4">
                            <label for="add_borrowed" class="form-label">
                                <i class="fas fa-book-reader"></i>
                                Borrowed Copies
                            </label>
                            <input type="number" class="form-control" name="borrowed" min="0" value="0">
                        </div>

                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="isTrending" value="1"
                                    id="add_isTrending">
                                <label class="form-check-label" for="add_isTrending">
                                    <i class="fas fa-fire"></i>
                                    Mark as Trending Book
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="isSpecial" value="1"
                                    id="add_isSpecial">
                                <label class="form-check-label" for="add_isSpecial">
                                    <i class="fas fa-star"></i>
                                    Mark as Special Book
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12" id="add_specialBadgeContainer" style="display: none;">
                            <label for="add_specialBadge" class="form-label">
                                <i class="fas fa-award"></i>
                                Special Badge
                            </label>
                            <input type="text" class="form-control" name="specialBadge" id="add_specialBadge"
                                placeholder="e.g., Bestseller, Classic, Award Winner">
                            <small class="text-muted">Only shown if 'Mark as Special' is checked</small>
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
            <form method="POST" action="<?= BASE_URL ?>admin/books/edit" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="isbn" id="edit_isbn">

                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label">
                                <i class="fas fa-image"></i>
                                Book Cover Image
                            </label>
                            <div class="image-upload-container">
                                <div class="image-preview">
                                    <div class="preview-box" id="edit_imagePreview">
                                        <img id="edit_previewImage" src="" alt="Preview">
                                    </div>
                                </div>
                                <div style="flex: 1;">
                                    <input type="file" class="form-control" name="image" id="edit_coverImage"
                                        accept="image/*">
                                    <small class="text-muted">Leave empty to keep current image. Accepted formats: JPG,
                                        PNG, GIF, WebP</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">ISBN</label>
                            <input type="text" class="form-control" id="edit_isbn_display" disabled>
                            <small class="text-muted">ISBN cannot be changed</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Barcode</label>
                            <input type="text" class="form-control" id="edit_barcode_display" disabled>
                            <small class="text-muted">Auto-generated</small>
                        </div>

                        <div class="col-md-12">
                            <label for="edit_bookName" class="form-label">
                                Book Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="bookName" id="edit_bookName" required>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_author" class="form-label">
                                Author <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="authorName" id="edit_author" required>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_publisher" class="form-label">
                                Publisher <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="publisherName" id="edit_publisher" required>
                        </div>

                        <div class="col-md-12">
                            <label for="edit_description" class="form-label">
                                <i class="fas fa-align-left"></i>
                                Description
                            </label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_category" class="form-label">
                                <i class="fas fa-tag"></i>
                                Category
                            </label>
                            <input type="text" class="form-control" name="category" id="edit_category">
                        </div>

                        <div class="col-md-6">
                            <label for="edit_publicationYear" class="form-label">
                                <i class="fas fa-calendar"></i>
                                Publication Year
                            </label>
                            <input type="number" class="form-control" name="publicationYear" id="edit_publicationYear"
                                min="1800" max="<?= date('Y') ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="edit_totalCopies" class="form-label">
                                Total Copies <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" name="totalCopies" id="edit_totalCopies" required
                                min="1">
                        </div>

                        <div class="col-md-4">
                            <label for="edit_available" class="form-label">
                                Available Copies <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" name="available" id="edit_available" required
                                min="0">
                        </div>

                        <div class="col-md-4">
                            <label for="edit_borrowed" class="form-label">
                                Borrowed Copies
                            </label>
                            <input type="number" class="form-control" name="borrowed" id="edit_borrowed" min="0"
                                value="0">
                        </div>

                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="isTrending" value="1"
                                    id="edit_isTrending">
                                <label class="form-check-label" for="edit_isTrending">
                                    <i class="fas fa-fire"></i>
                                    Mark as Trending Book
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="isSpecial" value="1"
                                    id="edit_isSpecial">
                                <label class="form-check-label" for="edit_isSpecial">
                                    <i class="fas fa-star"></i>
                                    Mark as Special Book
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12" id="edit_specialBadgeContainer" style="display: none;">
                            <label for="edit_specialBadge" class="form-label">
                                <i class="fas fa-award"></i>
                                Special Badge
                            </label>
                            <input type="text" class="form-control" name="specialBadge" id="edit_specialBadge">
                            <small class="text-muted">Only shown if 'Mark as Special' is checked</small>
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

<script src="<?= BASE_URL ?>assets/js/form-validation.js"></script>
<script>
    /**
     * Centralized book image URL resolution (mirrors PHP ImageHelper logic)
     */
    function getBookImageUrl(bookImage) {
        if (!bookImage) return '<?= ImageHelper::getPlaceholderUrl() ?>';
        const baseUrl = '<?= rtrim(BASE_URL, "/") ?>';
        // Normalize: strip existing path prefixes to avoid double-path
        let filename = bookImage.replace(/^\/?(uploads\/books\/|assets\/uploads\/books\/)/, '');
        return baseUrl + '/uploads/books/' + filename;
    }

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
    document.addEventListener('DOMContentLoaded', function () {
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
        btn.addEventListener('click', function () {
            const modal = this.closest('.modal');
            bootstrap.Modal.getInstance(modal).hide();
        });
    });

    // Modal toggle button functionality
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-bs-target');
            const modal = document.querySelector(targetId);
            new bootstrap.Modal(modal).show();
        });
    });

    // Close modal on outside click
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                bootstrap.Modal.getInstance(this).hide();
            }
        });
    });

    // Compress image before upload
    function compressImage(file, maxWidth = 800, quality = 0.7) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = new Image();
                img.onload = function () {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;

                    if (width > maxWidth) {
                        height = (height * maxWidth) / width;
                        width = maxWidth;
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob((blob) => {
                        resolve(new File([blob], file.name, { type: 'image/jpeg' }));
                    }, 'image/jpeg', quality);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    // Set even more aggressive client-side upload limit (500KB instead of 2MB)
    const MAX_UPLOAD_SIZE = 500 * 1024; // 500KB

    // ULTRA-AGGRESSIVE image optimizer
    async function optimizeImage(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = new Image();
                img.onload = async function () {
                    let quality = 0.7;
                    let maxWidth = 1200;
                    let result = file;

                    // Try up to 8 compression passes
                    for (let attempt = 0; attempt < 8; attempt++) {
                        const canvas = document.createElement('canvas');
                        let width = img.width;
                        let height = img.height;

                        if (width > maxWidth) {
                            height = (height * maxWidth) / width;
                            width = maxWidth;
                        }

                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        const blob = await new Promise(res => {
                            canvas.toBlob(res, 'image/jpeg', quality);
                        });

                        result = new File([blob], file.name, { type: 'image/jpeg' });

                        console.log(`Attempt ${attempt + 1}: ${(result.size / 1024).toFixed(1)}KB (quality: ${quality}, size: ${width}x${height})`);

                        if (result.size <= MAX_UPLOAD_SIZE) {
                            resolve(result);
                            return;
                        }

                        // Reduce quality and size for next attempt

                        maxWidth = Math.max(600, Math.floor(maxWidth * 0.8));
                    }

                    reject(new Error(`Could not compress to ${(MAX_UPLOAD_SIZE / 1024).toFixed(0)}KB. Try a smaller image.`));
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    // Modified Image Preview for Add Book with ULTRA-aggressive optimization
    document.getElementById('add_coverImage').addEventListener('change', async function () {
        if (this.files && this.files[0]) {
            let file = this.files[0];
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (!allowedTypes.includes(file.type)) {
                alert('Invalid file type. Please upload JPG, PNG, GIF, or WebP image.');
                this.value = '';
                document.getElementById('add_imagePreview').style.display = 'none';
                document.getElementById('add_imagePlaceholder').style.display = 'flex';
                return;
            }

            // Always optimize images to ensure they're under limit
            try {
                const originalSize = file.size;
                file = await optimizeImage(file);

                console.log(`✓ Compression complete: ${(originalSize / 1024).toFixed(1)}KB → ${(file.size / 1024).toFixed(1)}KB`);

                // Update the file input with optimized file
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                this.files = dataTransfer.files;

                // Show preview
                const reader = new FileReader();
                reader.onload = function (event) {
                    document.getElementById('add_previewImage').src = event.target.result;
                    document.getElementById('add_imagePreview').style.display = 'block';
                    document.getElementById('add_imagePlaceholder').style.display = 'none';
                };
                reader.readAsDataURL(file);

            } catch (error) {
                alert(`Image optimization failed: ${error.message}`);
                this.value = '';
                document.getElementById('add_imagePreview').style.display = 'none';
                document.getElementById('add_imagePlaceholder').style.display = 'flex';
            }
        }
    });

    // Same for edit modal
    document.getElementById('edit_coverImage').addEventListener('change', async function () {
        if (this.files && this.files[0]) {
            let file = this.files[0];
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (!allowedTypes.includes(file.type)) {
                alert('Invalid file type. Please upload JPG, PNG, GIF, or WebP image.');
                this.value = '';
                return;
            }

            try {
                const originalSize = file.size;
                file = await optimizeImage(file);

                console.log(`✓ Compression complete: ${(originalSize / 1024).toFixed(1)}KB → ${(file.size / 1024).toFixed(1)}KB`);

                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                this.files = dataTransfer.files;

                const reader = new FileReader();
                reader.onload = function (event) {
                    document.getElementById('edit_previewImage').src = event.target.result;
                };
                reader.readAsDataURL(file);

            } catch (error) {
                alert(`Image optimization failed: ${error.message}`);
                this.value = '';
            }
        }
    });

    // Show/hide special badge field in Add form
    document.getElementById('add_isSpecial').addEventListener('change', function () {
        document.getElementById('add_specialBadgeContainer').style.display = this.checked ? 'block' : 'none';
    });

    // Show/hide special badge field in Edit form
    document.getElementById('edit_isSpecial').addEventListener('change', function () {
        document.getElementById('edit_specialBadgeContainer').style.display = this.checked ? 'block' : 'none';
    });

    // Open Edit Modal with book data - UPDATED
    function openEditModal(book) {
        document.getElementById('edit_isbn').value = book.isbn;
        document.getElementById('edit_isbn_display').value = book.isbn;
        document.getElementById('edit_barcode_display').value = book.barcode || 'Auto-generated';
        document.getElementById('edit_bookName').value = book.bookName;
        document.getElementById('edit_author').value = book.authorName;
        document.getElementById('edit_publisher').value = book.publisherName;
        document.getElementById('edit_description').value = book.description || '';
        document.getElementById('edit_category').value = book.category || '';
        document.getElementById('edit_publicationYear').value = book.publicationYear || '';
        document.getElementById('edit_totalCopies').value = book.totalCopies;
        document.getElementById('edit_available').value = book.available;
        document.getElementById('edit_borrowed').value = book.borrowed || 0;
        document.getElementById('edit_isTrending').checked = book.isTrending == 1;
        document.getElementById('edit_isSpecial').checked = book.isSpecial == 1;
        document.getElementById('edit_specialBadge').value = book.specialBadge || '';

        // Show/hide special badge container
        document.getElementById('edit_specialBadgeContainer').style.display = book.isSpecial == 1 ? 'block' : 'none';

        // Set image preview using centralized URL resolution
        if (book.bookImage) {
            document.getElementById('edit_previewImage').src = getBookImageUrl(book.bookImage);
        } else {
            document.getElementById('edit_previewImage').src = '<?= ImageHelper::getPlaceholderUrl() ?>';
        }

        new bootstrap.Modal(document.getElementById('editBookModal')).show();
    }

    // Delete Book - UPDATED (simpler confirmation)
    function deleteBook(isbn, bookName) {
        if (!confirm(`Are you sure you want to delete "${bookName}"?\n\nThis action cannot be undone.`)) {
            return;
        }

        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>admin/books/delete';

        const isbnInput = document.createElement('input');
        isbnInput.type = 'hidden';
        isbnInput.name = 'isbn';
        isbnInput.value = isbn;

        form.appendChild(isbnInput);
        document.body.appendChild(form);
        form.submit();
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

    // Search and Filter Functionality
    function applyFilters() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const publisherFilter = document.getElementById('publisherFilter').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;

        const rows = document.querySelectorAll('.table tbody tr');

        rows.forEach(row => {
            if (row.cells.length === 1) return;

            const isbn = row.cells[1].textContent.toLowerCase();
            const bookName = row.cells[3].textContent.toLowerCase();
            const author = row.cells[4].textContent.toLowerCase();
            const publisher = row.cells[5].textContent.toLowerCase();
            const availableText = row.cells[7].textContent.trim();
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

    // Add form validation to Add Book Modal
    document.querySelector('#addBookModal form').addEventListener('submit', function (e) {
        let isValid = true;

        // Clear errors
        this.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
            el.style.borderColor = '';
        });
        this.querySelectorAll('.error-message').forEach(el => el.remove());

        const isbn = this.querySelector('[name="isbn"]');
        const bookName = this.querySelector('[name="bookName"]');
        const authorName = this.querySelector('[name="authorName"]');
        const publisherName = this.querySelector('[name="publisherName"]');
        const totalCopies = this.querySelector('[name="totalCopies"]');
        const available = this.querySelector('[name="available"]');

        // Validate ISBN
        if (!isbn.value.trim() || isbn.value.length < 10) {
            showError(isbn, 'ISBN must be at least 10 characters');
            isValid = false;
        }

        // Validate book name
        if (!bookName.value.trim() || bookName.value.length < 2) {
            showError(bookName, 'Book title must be at least 2 characters');
            isValid = false;
        }

        // Validate author name
        if (!authorName.value.trim() || authorName.value.length < 2) {
            showError(authorName, 'Author name must be at least 2 characters');
            isValid = false;
        }

        // Validate publisher name
        if (!publisherName.value.trim() || publisherName.value.length < 2) {
            showError(publisherName, 'Publisher name must be at least 2 characters');
            isValid = false;
        }

        // Validate copies
        if (parseInt(totalCopies.value) < 1) {
            showError(totalCopies, 'Total copies must be at least 1');
            isValid = false;
        }

        if (parseInt(available.value) < 0) {
            showError(available, 'Available copies cannot be negative');
            isValid = false;
        }

        if (parseInt(available.value) > parseInt(totalCopies.value)) {
            showError(available, 'Available copies cannot exceed total copies');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            const firstError = this.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });

    // Add form validation to Edit Book Modal
    document.querySelector('#editBookModal form').addEventListener('submit', function (e) {
        // Same validation as Add Book Modal
        let isValid = true;

        this.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
            el.style.borderColor = '';
        });
        this.querySelectorAll('.error-message').forEach(el => el.remove());

        const bookName = this.querySelector('[name="bookName"]');
        const authorName = this.querySelector('[name="authorName"]');
        const publisherName = this.querySelector('[name="publisherName"]');
        const totalCopies = this.querySelector('[name="totalCopies"]');
        const available = this.querySelector('[name="available"]');

        if (!bookName.value.trim() || bookName.value.length < 2) {
            showError(bookName, 'Book title must be at least 2 characters');
            isValid = false;
        }

        if (!authorName.value.trim() || authorName.value.length < 2) {
            showError(authorName, 'Author name must be at least 2 characters');
            isValid = false;
        }

        if (!publisherName.value.trim() || publisherName.value.length < 2) {
            showError(publisherName, 'Publisher name must be at least 2 characters');
            isValid = false;
        }

        if (parseInt(totalCopies.value) < 1) {
            showError(totalCopies, 'Total copies must be at least 1');
            isValid = false;
        }

        if (parseInt(available.value) < 0) {
            showError(available, 'Available copies cannot be negative');
            isValid = false;
        }

        if (parseInt(available.value) > parseInt(totalCopies.value)) {
            showError(available, 'Available copies cannot exceed total copies');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            const firstError = this.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });

    // Error display function
    function showError(input, message) {
        input.classList.add('is-invalid');
        const errorMessage = document.createElement('div');
        errorMessage.className = 'error-message';
        errorMessage.style.color = '#dc3545';
        errorMessage.style.fontSize = '0.875rem';
        errorMessage.style.marginTop = '0.25rem';
        errorMessage.textContent = message;
        input.parentElement.appendChild(errorMessage);
    }
</script>