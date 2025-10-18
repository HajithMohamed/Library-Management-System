<?php
$pageTitle = 'About Us';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
        .page-container {
            min-height: calc(100vh - 200px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            z-index: 1;
        }
        
        .page-content {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .page-title {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="page-content">
            <a href="<?php echo BASE_URL; ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
            
            <h1 class="page-title">About Our Library System</h1>
            
            <div style="line-height: 1.8; color: #555;">
                <p>Welcome to the University Library Management System, a comprehensive digital solution designed to modernize and streamline library operations for educational institutions.</p>
                
                <h3 style="color: #333; margin-top: 30px;">Our Mission</h3>
                <p>To provide an efficient, user-friendly platform that connects students, faculty, and library staff in a seamless digital environment, promoting easy access to knowledge and resources.</p>
                
                <h3 style="color: #333; margin-top: 30px;">Key Features</h3>
                <ul style="margin-left: 20px;">
                    <li>Advanced book search and cataloging</li>
                    <li>Digital borrowing and return system</li>
                    <li>User profile management</li>
                    <li>Fine management and payment processing</li>
                    <li>Comprehensive reporting and analytics</li>
                    <li>Admin panel for library management</li>
                </ul>
                
                <h3 style="color: #333; margin-top: 30px;">Technology</h3>
                <p>Built with modern web technologies including PHP, MySQL, and responsive design principles to ensure optimal performance across all devices.</p>
            </div>
        </div>
    </div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
