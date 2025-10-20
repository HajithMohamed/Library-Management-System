<?php
$pageTitle = 'About Us';
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
    
    .about-container {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 80px 20px 60px;
        position: relative;
        overflow: hidden;
    }
    
    .about-container::before {
        content: '';
        position: absolute;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        top: -200px;
        right: -200px;
        animation: float 6s ease-in-out infinite;
    }
    
    .about-container::after {
        content: '';
        position: absolute;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
        bottom: -150px;
        left: -150px;
        animation: float 8s ease-in-out infinite reverse;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-20px) scale(1.05); }
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
        font-size: 1.3rem;
        color: rgba(255,255,255,0.9);
    }
    
    .content-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }
    
    .content-card {
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 24px;
        padding: 40px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        animation: fadeInUp 0.8s ease-out;
        animation-fill-mode: both;
    }
    
    .content-card:nth-child(1) { animation-delay: 0.1s; }
    .content-card:nth-child(2) { animation-delay: 0.2s; }
    .content-card:nth-child(3) { animation-delay: 0.3s; }
    
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
    
    .content-card:hover {
        transform: translateY(-10px);
        background: rgba(255,255,255,0.15);
        box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    }
    
    .card-icon {
        font-size: 3rem;
        margin-bottom: 20px;
        display: inline-block;
        padding: 20px;
        background: rgba(255,255,255,0.15);
        border-radius: 20px;
        transition: transform 0.3s;
    }
    
    .content-card:hover .card-icon {
        transform: scale(1.1) rotate(5deg);
    }
    
    .content-card:nth-child(1) .card-icon { color: #FFD700; }
    .content-card:nth-child(2) .card-icon { color: #4ECDC4; }
    .content-card:nth-child(3) .card-icon { color: #FF6B6B; }
    
    .card-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: white;
        margin-bottom: 15px;
    }
    
    .card-content {
        color: rgba(255,255,255,0.9);
        line-height: 1.8;
        font-size: 1.05rem;
    }
    
    .full-width-card {
        grid-column: 1 / -1;
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 24px;
        padding: 50px;
        animation: fadeInUp 0.8s ease-out 0.4s both;
    }
    
    .features-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        margin-top: 30px;
    }
    
    .feature-item {
        display: flex;
        align-items: start;
        gap: 15px;
        padding: 20px;
        background: rgba(255,255,255,0.08);
        border-radius: 16px;
        transition: all 0.3s ease;
    }
    
    .feature-item:hover {
        background: rgba(255,255,255,0.15);
        transform: translateX(5px);
    }
    
    .feature-item i {
        font-size: 1.5rem;
        color: #4ECDC4;
        flex-shrink: 0;
        margin-top: 2px;
    }
    
    .feature-text {
        color: rgba(255,255,255,0.95);
        line-height: 1.6;
    }
    
    .tech-stack {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 25px;
        justify-content: center;
    }
    
    .tech-badge {
        padding: 12px 24px;
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.3);
        border-radius: 50px;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: default;
    }
    
    .tech-badge:hover {
        background: white;
        color: var(--primary);
        transform: translateY(-3px);
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .about-container {
            padding: 40px 15px;
        }
        
        .content-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .content-card,
        .full-width-card {
            padding: 30px 25px;
        }
        
        .features-list {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="about-container">
    <div class="content-wrapper">
        <a href="<?php echo BASE_URL; ?>" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Home</span>
        </a>
        
        <div class="page-header">
            <h1 class="page-title">About Our Library</h1>
            <p class="page-subtitle">Empowering Education Through Technology</p>
        </div>
        
        <div class="content-grid">
            <div class="content-card">
                <div class="card-icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <h2 class="card-title">Our Mission</h2>
                <p class="card-content">
                    To provide an efficient, user-friendly platform that connects students, 
                    faculty, and library staff in a seamless digital environment, promoting 
                    easy access to knowledge and fostering a culture of continuous learning.
                </p>
            </div>
            
            <div class="content-card">
                <div class="card-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h2 class="card-title">Our Vision</h2>
                <p class="card-content">
                    To become the leading library management platform that revolutionizes 
                    how educational institutions manage their resources, making knowledge 
                    accessible to everyone, anywhere, anytime.
                </p>
            </div>
            
            <div class="content-card">
                <div class="card-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h2 class="card-title">Our Values</h2>
                <p class="card-content">
                    Innovation, accessibility, reliability, and user satisfaction drive 
                    everything we do. We believe in the power of technology to transform 
                    education and make learning more engaging and effective.
                </p>
            </div>
            
            <div class="full-width-card">
                <h2 class="card-title">Key Features</h2>
                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-search"></i>
                        <div class="feature-text">Advanced search and cataloging system</div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-sync-alt"></i>
                        <div class="feature-text">Real-time borrowing and return tracking</div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-user-cog"></i>
                        <div class="feature-text">Comprehensive user profile management</div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-receipt"></i>
                        <div class="feature-text">Automated fine calculation and payment</div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-chart-bar"></i>
                        <div class="feature-text">Detailed analytics and reporting</div>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-shield-alt"></i>
                        <div class="feature-text">Secure admin panel with role management</div>
                    </div>
                </div>
            </div>
            
            <div class="full-width-card">
                <h2 class="card-title" style="text-align: center;">Built With Modern Technology</h2>
                <p class="card-content" style="text-align: center; margin-bottom: 20px;">
                    Our platform leverages cutting-edge web technologies to deliver 
                    a fast, secure, and reliable experience.
                </p>
                <div class="tech-stack">
                    <span class="tech-badge"><i class="fab fa-php"></i> PHP</span>
                    <span class="tech-badge"><i class="fas fa-database"></i> MySQL</span>
                    <span class="tech-badge"><i class="fab fa-html5"></i> HTML5</span>
                    <span class="tech-badge"><i class="fab fa-css3-alt"></i> CSS3</span>
                    <span class="tech-badge"><i class="fab fa-js"></i> JavaScript</span>
                    <span class="tech-badge"><i class="fas fa-mobile-alt"></i> Responsive Design</span>
                    <span class="tech-badge"><i class="fas fa-lock"></i> Security First</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>