<?php
$pageTitle = 'Home';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
        .home-container {
            min-height: calc(100vh - 200px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            z-index: 1;
        }
        
        .home-content {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 800px;
            width: 100%;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .home-title {
            font-size: 3rem;
            color: #333;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .home-subtitle {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .home-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .feature-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .feature-icon {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .feature-title {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
        
        .feature-desc {
            color: #666;
            font-size: 0.9rem;
        }
        
        .home-actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-home {
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }
        
        .stats-section {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #eee;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .home-title {
                font-size: 2rem;
            }
            
            .home-content {
                padding: 20px;
            }
            
            .home-actions {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="home-container">
        <div class="home-content">
            <h1 class="home-title">
                <i class="fas fa-book-open"></i>
                University Library Management System
            </h1>
            
            <p class="home-subtitle">
                Welcome to our comprehensive library management system. 
                Discover, borrow, and manage books with ease. 
                Your gateway to knowledge and learning.
            </p>
            
            <div class="home-features">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="feature-title">Book Search</div>
                    <div class="feature-desc">Find books quickly with our advanced search system</div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="feature-title">Easy Borrowing</div>
                    <div class="feature-desc">Borrow and return books with just a few clicks</div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="feature-title">User Management</div>
                    <div class="feature-desc">Manage your profile and track your reading history</div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="feature-title">Analytics</div>
                    <div class="feature-desc">Track reading patterns and library statistics</div>
                </div>
            </div>
            
            <div class="home-actions">
                <a href="<?php echo BASE_URL; ?>login" class="btn-home btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="<?php echo BASE_URL; ?>signup" class="btn-home btn-secondary">
                    <i class="fas fa-user-plus"></i> Sign Up
                </a>
                <a href="<?php echo BASE_URL; ?>books" class="btn-home btn-secondary">
                    <i class="fas fa-book"></i> Browse Books
                </a>
            </div>
            
            <div class="stats-section">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">10,000+</div>
                        <div class="stat-label">Books Available</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">5,000+</div>
                        <div class="stat-label">Active Users</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Categories</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Online Access</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
