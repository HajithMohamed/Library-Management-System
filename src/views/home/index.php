<?php
$pageTitle = 'Home';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --glass-bg: rgba(255, 255, 255, 0.1);
        --glass-border: rgba(255, 255, 255, 0.2);
    }
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    .home-container {
        min-height: 100vh;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    }
    
    /* Animated Background */
    .home-container::before {
        content: '';
        position: absolute;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.1) 0%, transparent 50%);
        animation: rotate 20s linear infinite;
    }
    
    @keyframes rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .floating-shapes {
        position: absolute;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 0;
    }
    
    .shape {
        position: absolute;
        opacity: 0.1;
        animation: float 15s infinite ease-in-out;
    }
    
    .shape:nth-child(1) {
        top: 20%;
        left: 10%;
        width: 80px;
        height: 80px;
        background: white;
        border-radius: 50%;
        animation-delay: 0s;
    }
    
    .shape:nth-child(2) {
        top: 60%;
        right: 15%;
        width: 120px;
        height: 120px;
        background: white;
        border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
        animation-delay: 2s;
    }
    
    .shape:nth-child(3) {
        bottom: 20%;
        left: 15%;
        width: 100px;
        height: 100px;
        background: white;
        border-radius: 20px;
        animation-delay: 4s;
        transform: rotate(45deg);
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-30px) rotate(180deg); }
    }
    
    .content-wrapper {
        position: relative;
        z-index: 1;
        padding: 80px 20px 60px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    /* Hero Section */
    .hero-section {
        text-align: center;
        margin-bottom: 60px;
        animation: fadeInUp 0.8s ease-out;
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
    
    .hero-icon {
        font-size: 80px;
        margin-bottom: 20px;
        background: linear-gradient(135deg, #fff, #f0f0f0);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    .hero-title {
        font-size: clamp(2.5rem, 5vw, 4rem);
        font-weight: 800;
        color: white;
        margin-bottom: 20px;
        text-shadow: 0 4px 20px rgba(0,0,0,0.2);
        line-height: 1.2;
    }
    
    .hero-subtitle {
        font-size: clamp(1.1rem, 2vw, 1.4rem);
        color: rgba(255,255,255,0.95);
        max-width: 700px;
        margin: 0 auto 40px;
        line-height: 1.7;
    }
    
    /* Action Buttons */
    .hero-actions {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 60px;
    }
    
    .btn-hero {
        padding: 16px 40px;
        font-size: 1.1rem;
        font-weight: 600;
        border: none;
        border-radius: 50px;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    
    .btn-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }
    
    .btn-hero:hover::before {
        left: 100%;
    }
    
    .btn-primary {
        background: white;
        color: #667eea;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.3);
    }
    
    .btn-secondary {
        background: var(--glass-bg);
        color: white;
        border: 2px solid white;
        backdrop-filter: blur(10px);
    }
    
    .btn-secondary:hover {
        background: white;
        color: #667eea;
        transform: translateY(-3px);
    }
    
    /* Features Grid */
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        margin-bottom: 60px;
    }
    
    .feature-card {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 24px;
        padding: 35px;
        text-align: center;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }
    
    .feature-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
        opacity: 0;
        transition: opacity 0.4s;
    }
    
    .feature-card:hover {
        transform: translateY(-10px) scale(1.02);
        background: rgba(255,255,255,0.15);
        box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    }
    
    .feature-card:hover::before {
        opacity: 1;
    }
    
    .feature-icon {
        font-size: 3.5rem;
        margin-bottom: 20px;
        display: inline-block;
        transition: transform 0.4s;
    }
    
    .feature-card:hover .feature-icon {
        transform: scale(1.2) rotate(5deg);
    }
    
    .feature-card:nth-child(1) .feature-icon { color: #FFD700; }
    .feature-card:nth-child(2) .feature-icon { color: #FF6B6B; }
    .feature-card:nth-child(3) .feature-icon { color: #4ECDC4; }
    .feature-card:nth-child(4) .feature-icon { color: #95E1D3; }
    
    .feature-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: white;
        margin-bottom: 12px;
    }
    
    .feature-desc {
        color: rgba(255,255,255,0.85);
        line-height: 1.6;
        font-size: 1rem;
    }
    
    /* Stats Section */
    .stats-section {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 30px;
        padding: 50px 40px;
        margin-top: 40px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 40px;
    }
    
    .stat-item {
        text-align: center;
        position: relative;
    }
    
    .stat-item::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background: white;
        border-radius: 2px;
        opacity: 0.3;
    }
    
    .stat-number {
        font-size: clamp(2.5rem, 4vw, 3.5rem);
        font-weight: 900;
        color: white;
        margin-bottom: 10px;
        display: block;
        animation: countUp 2s ease-out;
    }
    
    @keyframes countUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .stat-label {
        color: rgba(255,255,255,0.9);
        font-size: 1.1rem;
        font-weight: 500;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .content-wrapper {
            padding: 40px 15px;
        }
        
        .hero-icon {
            font-size: 60px;
        }
        
        .hero-actions {
            flex-direction: column;
            align-items: center;
        }
        
        .btn-hero {
            width: 100%;
            max-width: 300px;
            justify-content: center;
        }
        
        .features-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .stats-section {
            padding: 30px 20px;
        }
    }
    
    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }
    }
</style>

<div class="home-container">
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="content-wrapper">
        <div class="hero-section">
            <div class="hero-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <h1 class="hero-title">
                University Library<br>Management System
            </h1>
            <p class="hero-subtitle">
                Your gateway to knowledge and learning. Discover, borrow, and manage 
                thousands of books with our cutting-edge digital library platform.
            </p>
            
            <div class="hero-actions">
                <a href="<?php echo BASE_URL; ?>login" class="btn-hero btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Get Started</span>
                </a>
                <a href="<?php echo BASE_URL; ?>signup" class="btn-hero btn-secondary">
                    <i class="fas fa-user-plus"></i>
                    <span>Sign Up Free</span>
                </a>
                <a href="<?php echo BASE_URL; ?>books" class="btn-hero btn-secondary">
                    <i class="fas fa-book"></i>
                    <span>Browse Library</span>
                </a>
            </div>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-search"></i>
                </div>
                <div class="feature-title">Smart Search</div>
                <div class="feature-desc">
                    Find any book instantly with our intelligent search powered by advanced algorithms
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="feature-title">Digital Borrowing</div>
                <div class="feature-desc">
                    Seamless borrowing experience with real-time availability and instant notifications
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="feature-title">Personalized Profile</div>
                <div class="feature-desc">
                    Track your reading journey with detailed history and personalized recommendations
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="feature-title">Advanced Analytics</div>
                <div class="feature-desc">
                    Gain insights into reading patterns with comprehensive statistics and reports
                </div>
            </div>
        </div>
        
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-number">10K+</span>
                    <div class="stat-label">Books Available</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number">5K+</span>
                    <div class="stat-label">Active Users</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number">50+</span>
                    <div class="stat-label">Categories</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number">24/7</span>
                    <div class="stat-label">Online Access</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>