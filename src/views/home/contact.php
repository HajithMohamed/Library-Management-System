<?php
$pageTitle = 'Contact Us';
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
    
    .contact-container {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        padding: 80px 20px 60px;
        position: relative;
        overflow: hidden;
    }
    
    /* Animated Background Elements */
    .bg-shapes {
        position: absolute;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 0;
    }
    
    .circle {
        position: absolute;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
        animation: float 15s infinite ease-in-out;
    }
    
    .circle:nth-child(1) {
        width: 300px;
        height: 300px;
        top: 10%;
        left: 5%;
        animation-delay: 0s;
    }
    
    .circle:nth-child(2) {
        width: 200px;
        height: 200px;
        top: 50%;
        right: 10%;
        animation-delay: 3s;
    }
    
    .circle:nth-child(3) {
        width: 150px;
        height: 150px;
        bottom: 15%;
        left: 15%;
        animation-delay: 6s;
    }
    
    @keyframes float {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(30px, -30px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
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
        margin-bottom: 50px;
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
        max-width: 600px;
        margin: 0 auto;
    }
    
    .contact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }
    
    .contact-card {
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 24px;
        padding: 40px 30px;
        text-align: center;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        animation: fadeInUp 0.8s ease-out;
        animation-fill-mode: both;
    }
    
    .contact-card:nth-child(1) { animation-delay: 0.1s; }
    .contact-card:nth-child(2) { animation-delay: 0.2s; }
    .contact-card:nth-child(3) { animation-delay: 0.3s; }
    .contact-card:nth-child(4) { animation-delay: 0.4s; }
    
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
    
    .contact-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #FFD700, #FF6B6B, #4ECDC4);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
    }
    
    .contact-card:hover::before {
        transform: scaleX(1);
    }
    
    .contact-card:hover {
        transform: translateY(-15px) scale(1.02);
        background: rgba(255,255,255,0.15);
        box-shadow: 0 25px 60px rgba(0,0,0,0.3);
    }
    
    .contact-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.15);
        border-radius: 20px;
        font-size: 2.5rem;
        transition: all 0.4s ease;
    }
    
    .contact-card:hover .contact-icon {
        transform: rotateY(360deg) scale(1.1);
        background: white;
    }
    
    .contact-card:nth-child(1) .contact-icon {
        color: #FFD700;
    }
    
    .contact-card:nth-child(1):hover .contact-icon {
        color: var(--primary);
    }
    
    .contact-card:nth-child(2) .contact-icon {
        color: #4ECDC4;
    }
    
    .contact-card:nth-child(2):hover .contact-icon {
        color: var(--primary);
    }
    
    .contact-card:nth-child(3) .contact-icon {
        color: #FF6B6B;
    }
    
    .contact-card:nth-child(3):hover .contact-icon {
        color: var(--primary);
    }
    
    .contact-card:nth-child(4) .contact-icon {
        color: #95E1D3;
    }
    
    .contact-card:nth-child(4):hover .contact-icon {
        color: var(--primary);
    }
    
    .contact-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: white;
        margin-bottom: 15px;
    }
    
    .contact-details {
        color: rgba(255,255,255,0.9);
        line-height: 1.8;
        font-size: 1.05rem;
    }
    
    .contact-details a {
        color: rgba(255,255,255,0.95);
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
    }
    
    .contact-details a:hover {
        color: white;
        transform: translateX(5px);
    }
    
    .map-section {
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 24px;
        padding: 40px;
        animation: fadeInUp 0.8s ease-out 0.5s both;
    }
    
    .map-title {
        font-size: 2rem;
        font-weight: 700;
        color: white;
        text-align: center;
        margin-bottom: 30px;
    }
    
    .map-container {
        width: 100%;
        height: 400px;
        border-radius: 16px;
        overflow: hidden;
        border: 2px solid rgba(255,255,255,0.3);
    }
    
    .map-container iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .contact-container {
            padding: 40px 15px;
        }
        
        .contact-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .contact-card {
            padding: 30px 25px;
        }
        
        .map-section {
            padding: 25px;
        }
        
        .map-container {
            height: 300px;
        }
    }
</style>

<div class="contact-container">
    <div class="bg-shapes">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>
    
    <div class="content-wrapper">
        <a href="<?php echo BASE_URL; ?>" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Home</span>
        </a>
        
        <div class="page-header">
            <h1 class="page-title">Get In Touch</h1>
            <p class="page-subtitle">
                We're here to help! Reach out to our team for assistance, support, or inquiries.
            </p>
        </div>
        
        <div class="contact-grid">
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="contact-title">Visit Us</div>
                <div class="contact-details">
                    University Library<br>
                    Main Campus Building<br>
                    University of Ruhuna<br>
                    Matara, Sri Lanka
                </div>
            </div>
            
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <div class="contact-title">Call Us</div>
                <div class="contact-details">
                    <a href="tel:+94412222222">Main: +94 41 222 2222</a><br>
                    <a href="tel:+94412222223">Support: +94 41 222 2223</a><br>
                    <small style="opacity: 0.8;">8:00 AM - 6:00 PM</small>
                </div>
            </div>
            
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="contact-title">Email Us</div>
                <div class="contact-details">
                    <a href="mailto:library@ruh.ac.lk">library@ruh.ac.lk</a><br>
                    <a href="mailto:support@ruh.ac.lk">support@ruh.ac.lk</a><br>
                    <a href="mailto:admin@ruh.ac.lk">admin@ruh.ac.lk</a>
                </div>
            </div>
            
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="contact-title">Library Hours</div>
                <div class="contact-details">
                    Mon - Fri: 8:00 AM - 8:00 PM<br>
                    Saturday: 9:00 AM - 5:00 PM<br>
                    Sunday: 10:00 AM - 4:00 PM
                </div>
            </div>
        </div>
        
        <div class="map-section">
            <h2 class="map-title">Find Us on the Map</h2>
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3967.887962137212!2d80.5744783147684!3d5.988338995648839!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae143e440c30249%3A0x4f123a317135b675!2sFaculty%20of%20Technology%2C%20University%20of%20Ruhuna!5e0!3m2!1sen!2slk!4v1672233555555!5m2!1sen!2slk" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
