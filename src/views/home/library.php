<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Information - University Library Management System</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/login.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/fontawesome-free-6.7.2-web/css/all.min.css">
    <style>
        .page-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .page-content {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            width: 100%;
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
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .info-title {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 5px;
        }
        
        .info-content {
            color: #555;
            line-height: 1.8;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .service-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .service-icon {
            font-size: 1.5rem;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .service-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="page-content">
            <a href="<?php echo BASE_URL; ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
            
            <h1 class="page-title">Library Information</h1>
            
            <div class="info-section">
                <h3 class="info-title">About Our Library</h3>
                <div class="info-content">
                    <p>The University Library serves as the central hub for academic resources and research materials. We provide access to thousands of books, journals, digital resources, and specialized collections to support the academic and research needs of our university community.</p>
                </div>
            </div>
            
            <div class="info-section">
                <h3 class="info-title">Our Services</h3>
                <div class="services-grid">
                    <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="service-title">Book Lending</div>
                        <div>Borrow books for academic and research purposes</div>
                    </div>
                    
                    <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="service-title">Research Support</div>
                        <div>Assistance with research and academic projects</div>
                    </div>
                    
                    <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-laptop"></i>
                        </div>
                        <div class="service-title">Digital Resources</div>
                        <div>Access to online databases and e-books</div>
                    </div>
                    
                    <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="service-title">Study Spaces</div>
                        <div>Quiet study areas and group discussion rooms</div>
                    </div>
                </div>
            </div>
            
            <div class="info-section">
                <h3 class="info-title">Library Policies</h3>
                <div class="info-content">
                    <ul style="margin-left: 20px;">
                        <li>Books can be borrowed for up to 14 days</li>
                        <li>Renewals are allowed if no holds are placed</li>
                        <li>Late returns incur fines of Rs. 10 per day</li>
                        <li>Lost books must be replaced or paid for</li>
                        <li>Food and drinks are not allowed in the library</li>
                        <li>Mobile phones should be on silent mode</li>
                    </ul>
                </div>
            </div>
            
            <div class="info-section">
                <h3 class="info-title">Getting Started</h3>
                <div class="info-content">
                    <p>To start using our library services:</p>
                    <ol style="margin-left: 20px;">
                        <li>Create an account by signing up</li>
                        <li>Verify your email address</li>
                        <li>Login to access the system</li>
                        <li>Browse and search for books</li>
                        <li>Borrow books and manage your account</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
