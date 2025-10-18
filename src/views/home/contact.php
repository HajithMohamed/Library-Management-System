<?php
$pageTitle = 'Contact Us';
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
        
        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        
        .contact-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .contact-icon {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .contact-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .contact-details {
            color: #666;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="page-content">
            <a href="<?php echo BASE_URL; ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
            
            <h1 class="page-title">Contact Us</h1>
            
            <p style="text-align: center; color: #666; font-size: 1.1rem; margin-bottom: 30px;">
                Get in touch with our library team for assistance, support, or inquiries.
            </p>
            
            <div class="contact-info">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-title">Library Location</div>
                    <div class="contact-details">
                        University Library<br>
                        Main Campus Building<br>
                        University of Ruhuna<br>
                        Matara, Sri Lanka
                    </div>
                </div>
                
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-title">Phone Support</div>
                    <div class="contact-details">
                        Main Desk: +94 41 222 2222<br>
                        IT Support: +94 41 222 2223<br>
                        Hours: 8:00 AM - 6:00 PM
                    </div>
                </div>
                
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-title">Email Support</div>
                    <div class="contact-details">
                        General: library@ruh.ac.lk<br>
                        Technical: support@ruh.ac.lk<br>
                        Admin: admin@ruh.ac.lk
                    </div>
                </div>
                
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="contact-title">Library Hours</div>
                    <div class="contact-details">
                        Monday - Friday: 8:00 AM - 8:00 PM<br>
                        Saturday: 9:00 AM - 5:00 PM<br>
                        Sunday: 10:00 AM - 4:00 PM
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
