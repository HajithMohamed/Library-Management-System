<?php
$pageTitle = 'Library Information';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    :root {
        --primary: #667eea;
        --secondary: #764ba2;
        --accent: #f093fb;
    }
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    .library-container {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 80px 20px 60px;
        position: relative;
        overflow: hidden;
    }
    
    /* Animated Particles */
    .particles {
        position: absolute;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 0;
    }
    
    .particle {
        position: absolute;
        width: 4px;
        height: 4px;
        background: white;
        border-radius: 50%;
        opacity: 0.3;
        animation: rise 10s infinite ease-in;
    }
    
    .particle:nth-child(1) { left: 10%; animation-delay: 0s; }
    .particle:nth-child(2) { left: 20%; animation-delay: 2s; }
    .particle:nth-child(3) { left: 30%; animation-delay: 4s; }
    .particle:nth-child(4) { left: 40%; animation-delay: 1s; }
    .particle:nth-child(5) { left: 50%; animation-delay: 3s; }
    .particle:nth-child(6) { left: 60%; animation-delay: 5s; }
    .particle:nth-child(7) { left: 70%; animation-delay: 2.5s; }
    .particle:nth-child(8) { left: 80%; animation-delay: 4.5s; }
    .particle:nth-child(9) { left: 90%; animation-delay: 1.5s; }
    
    @keyframes rise {
        0% {
            bottom: -10%;
            transform: translateX(0) scale(1);
        }
        50% {
            transform: translateX(50px) scale(1.5);
        }
        100% {
            bottom: 110%;
            transform: translateX(-50px) scale(0.5);
        }
    }
    
    .content-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }
    
    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 28px;
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        color: white;
        text-decoration: none;
        border-radius: 50px;
        border: 1px solid rgba(255,255,255,0.2);
        font-weight: 600;
        transition: all 0.3s ease;
        margin-bottom: 40px;
    }
    
    .back-btn:hover {
        background: white;
        color: var(--primary);
        transform: translateX(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .page-header {
        text-align: center;
        margin-bottom: 60px;
        animation: fadeInDown 0.8s ease-out;
    }
    
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .page-title {
        font-size: clamp(2.5rem, 5vw, 4rem);
        font-weight: 800;
        color: white;
        margin-bottom: 15px;
        text-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }
    
    .page-subtitle {
        font-size: 1.2rem;
        color: rgba(255,255,255,0.9);
        max-width: 700px;
        margin: 0 auto;
    }
    
    /* Hero Info Card */
    .hero-info {
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 24px;
        padding: 50px;
        margin-bottom: 40px;
        animation: fadeInUp 0.8s ease-out 0.2s both;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .hero-info p {
        color: rgba(255,255,255,0.95);
        font-size: 1.15rem;
        line-height: 1.8;
        text-align: center;
    }
    
    /* Services Section */
    .section-title {
        font-size: 2.2rem;
        font-weight: 700;
        color: white;
        text-align: center;
        margin-bottom: 40px;
    }
    
    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 25px;
        margin-bottom: 50px;
    }
    
    .service-card {
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 20px;
        padding: 35px 25px;
        text-align: center;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        animation: fadeInUp 0.8s ease-out both;
        position: relative;
        overflow: hidden;
    }
    
    .service-card:nth-child(1) { animation-delay: 0.3s; }
    .service-card:nth-child(2) { animation-delay: 0.4s; }
    .service-card:nth-child(3) { animation-delay: 0.5s; }
    .service-card:nth-child(4) { animation-delay: 0.6s; }
    
    .service-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.6s;
    }
    
    .service-card:hover::before {
        left: 100%;
    }
    
    .service-card:hover {
        transform: translateY(-10px) scale(1.03);
        background: rgba(255,255,255,0.15);
        box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    }
    
    .service-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.15);
        border-radius: 18px;
        font-size: 2rem;
        transition: all 0.4s ease;
    }
    
    .service-card:hover .service-icon {
        transform: rotateY(360deg) scale(1.1);
        background: white;
    }
    
    .service-card:nth-child(1) .service-icon { color: #FFD700; }
    .service-card:nth-child(2) .service-icon { color: #4ECDC4; }
    .service-card:nth-child(3) .service-icon { color: #FF6B6B; }
    .service-card:nth-child(4) .service-icon { color: #95E1D3; }
    
    .service-card:hover .service-icon {
        color: var(--primary);
    }
    
    .service-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: white;
        margin-bottom: 12px;
    }
    
    .service-desc {
        color: rgba(255,255,255,0.9);
        line-height: 1.6;
    }
    
    /* Policies & Getting Started */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 30px;
        margin-top: 40px;
    }
    
    .info-card {
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 24px;
        padding: 40px;
        animation: fadeInUp 0.8s ease-out both;
    }
    
    .info-card:nth-child(1) { animation-delay: 0.7s; }
    .info-card:nth-child(2) { animation-delay: 0.8s; }
    
    .info-card h3 {
        font-size: 1.8rem;
        font-weight: 700;
        color: white;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .info-card h3 i {
        font-size: 2rem;
        color: #4ECDC4;
    }
    
    .info-card ul,
    .info-card ol {
        margin-left: 20px;
        color: rgba(255,255,255,0.9);
        line-height: 2;
        font-size: 1.05rem;
    }
    
    .info-card li {
        margin-bottom: 12px;
        padding-left: 10px;
    }
    
    .info-card li::marker {
        color: #4ECDC4;
        font-weight: bold;
    }
    
    /* Call to Action */
    .cta-section {
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 24px;
        padding: 50px;
        text-align: center;
        margin-top: 50px;
        animation: fadeInUp 0.8s ease-out 0.9s both;
    }
    
    .cta-title {
        font-size: 2rem;
        font-weight: 700;
        color: white;
        margin-bottom: 20px;
    }
    
    .cta-text {
        color: rgba(255,255,255,0.9);
        font-size: 1.1rem;
        margin-bottom: 30px;
        line-height: 1.7;
    }
    
    .cta-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .cta-btn {
        padding: 14px 35px;
        font-size: 1.1rem;
        font-weight: 600;
        border: none;
        border-radius: 50px;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    
    .cta-btn-primary {
        background: white;
        color: var(--primary);
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .cta-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.3);
    }
    
    .cta-btn-secondary {
        background: transparent;
        color: white;
        border: 2px solid white;
    }
    
    .cta-btn-secondary:hover {
        background: white;
        color: var(--primary);
        transform: translateY(-3px);
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .library-container {
            padding: 40px 15px;
        }
        
        .hero-info,
        .info-card,
        .cta-section {
            padding: 30px 25px;
        }
        
        .services-grid {
            grid-template-columns: 1fr;
        }
        
        .info-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .cta-buttons {
            flex-direction: column;
            align-items: center;
        }
        
        .cta-btn {
            width: 100%;
            max-width: 300px;
            justify-content: center;
        }
    }
</style>

<div class="library-container">
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    
    <div class="content-wrapper">
        <a href="<?php echo BASE_URL; ?>" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Home</span>
        </a>
        
        <div class="page-header">
            <h1 class="page-title">Library Information</h1>
            <p class="page-subtitle">
                Your comprehensive guide to accessing and utilizing our library services
            </p>
        </div>
        
        <div class="hero-info">
            <p>
                The University Library serves as the central hub for academic resources and research materials. 
                We provide access to thousands of books, journals, digital resources, and specialized collections 
                to support the academic and research needs of our university community. Experience seamless access 
                to knowledge with our state-of-the-art digital platform.
            </p>
        </div>
        
        <h2 class="section-title">Our Services</h2>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="service-title">Book Lending</div>
                <div class="service-desc">
                    Borrow physical and digital books for academic and research purposes with flexible loan periods
                </div>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-search"></i>
                </div>
                <div class="service-title">Research Support</div>
                <div class="service-desc">
                    Professional assistance with research projects, literature reviews, and academic inquiries
                </div>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-laptop"></i>
                </div>
                <div class="service-title">Digital Resources</div>
                <div class="service-desc">
                    24/7 access to online databases, e-journals, e-books, and academic resources
                </div>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="service-title">Study Spaces</div>
                <div class="service-desc">
                    Quiet study areas, group discussion rooms, and collaborative workspaces
                </div>
            </div>
        </div>
        
        <div class="info-grid">
            <div class="info-card">
                <h3>
                    <i class="fas fa-clipboard-list"></i>
                    Library Policies
                </h3>
                <ul>
                    <li>Books can be borrowed for up to 14 days</li>
                    <li>Renewals allowed if no holds are placed</li>
                    <li>Late returns incur fines of Rs. 10 per day</li>
                    <li>Lost books must be replaced or paid for</li>
                    <li>Food and drinks not allowed in library</li>
                    <li>Mobile phones on silent mode please</li>
                </ul>
            </div>
            
            <div class="info-card">
                <h3>
                    <i class="fas fa-rocket"></i>
                    Getting Started
                </h3>
                <ol>
                    <li>Create your account by signing up</li>
                    <li>Verify your email address</li>
                    <li>Login to access the system</li>
                    <li>Browse and search for books</li>
                    <li>Start borrowing and managing your account</li>
                </ol>
            </div>
        </div>
        
        <div class="cta-section">
            <h2 class="cta-title">Ready to Get Started?</h2>
            <p class="cta-text">
                Join thousands of students and faculty members already using our digital library. 
                Sign up today and unlock access to our vast collection of resources!
            </p>
            <div class="cta-buttons">
                <a href="<?php echo BASE_URL; ?>signup" class="cta-btn cta-btn-primary">
                    <i class="fas fa-user-plus"></i>
                    <span>Create Account</span>
                </a>
                <a href="<?php echo BASE_URL; ?>books" class="cta-btn cta-btn-secondary">
                    <i class="fas fa-book"></i>
                    <span>Browse Collection</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>