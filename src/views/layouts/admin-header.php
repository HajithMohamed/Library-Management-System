<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin Dashboard' ?> - Library Management System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
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
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            background: #f0f2f5;
        }
        
        /* Flash Messages Styling */
        .flash-messages {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        }
        
        .flash-message {
            margin-bottom: 1rem;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            animation: slideInRight 0.3s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .flash-message.success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .flash-message.error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .flash-message.warning {
            background: #fef3c7;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }
        
        .flash-message.info {
            background: #dbeafe;
            color: #1e40af;
            border-left: 4px solid #3b82f6;
        }
        
        .flash-message i {
            font-size: 1.25rem;
        }
        
        .flash-message-content {
            flex: 1;
        }
        
        .flash-message-content strong {
            display: block;
            margin-bottom: 0.25rem;
            font-weight: 600;
        }
        
        .flash-message-close {
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            font-size: 1.25rem;
            padding: 0;
            opacity: 0.6;
            transition: opacity 0.2s;
        }
        
        .flash-message-close:hover {
            opacity: 1;
        }
        
        .flash-message ul {
            margin: 0.5rem 0 0 1.5rem;
            padding: 0;
        }
        
        .flash-message ul li {
            margin-bottom: 0.25rem;
        }
        
        /* Print Styles */
        @media print {
            .sidebar,
            .top-header,
            .flash-messages,
            .back-to-top,
            .mobile-overlay {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
            }
        }
    </style>
</head>
<body>

<!-- Flash Messages Container -->
<div class="flash-messages" id="flashMessages">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="flash-message success">
            <i class="fas fa-check-circle"></i>
            <div class="flash-message-content">
                <strong>Success!</strong>
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <button class="flash-message-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="flash-message error">
            <i class="fas fa-exclamation-circle"></i>
            <div class="flash-message-content">
                <strong>Error!</strong>
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <button class="flash-message-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['warning'])): ?>
        <div class="flash-message warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="flash-message-content">
                <strong>Warning!</strong>
                <?= htmlspecialchars($_SESSION['warning']) ?>
            </div>
            <button class="flash-message-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['warning']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['info'])): ?>
        <div class="flash-message info">
            <i class="fas fa-info-circle"></i>
            <div class="flash-message-content">
                <strong>Info!</strong>
                <?= htmlspecialchars($_SESSION['info']) ?>
            </div>
            <button class="flash-message-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['info']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['validation_errors'])): ?>
        <div class="flash-message warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="flash-message-content">
                <strong>Validation Errors:</strong>
                <ul>
                    <?php foreach ($_SESSION['validation_errors'] as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <button class="flash-message-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['validation_errors']); ?>
    <?php endif; ?>
</div>

<script>
    // Auto-dismiss flash messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const flashMessages = document.querySelectorAll('.flash-message');
        flashMessages.forEach(message => {
            setTimeout(() => {
                message.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => message.remove(), 300);
            }, 5000);
        });
    });
    
    // Animation for slide out
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideOutRight {
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