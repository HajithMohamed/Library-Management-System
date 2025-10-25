<?php
$pageTitle = '403 - Access Forbidden';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Animated Background Elements */
        .bg-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            animation: float-shapes 20s infinite ease-in-out;
        }

        .shape:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 200px;
            height: 200px;
            top: 50%;
            right: -80px;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 250px;
            height: 250px;
            bottom: -100px;
            left: 50%;
            animation-delay: 4s;
        }

        @keyframes float-shapes {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(30px, -30px) rotate(90deg);
            }
            50% {
                transform: translate(-20px, 20px) rotate(180deg);
            }
            75% {
                transform: translate(40px, 30px) rotate(270deg);
            }
        }

        /* Particles */
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            animation: float-particle 15s infinite ease-in-out;
        }

        @keyframes float-particle {
            0%, 100% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) translateX(50px);
                opacity: 0;
            }
        }

        /* Error Container */
        .error-container {
            text-align: center;
            color: white;
            max-width: 700px;
            position: relative;
            z-index: 10;
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

        /* Glass Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 3rem 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .error-icon {
            font-size: 8rem;
            margin-bottom: 1.5rem;
            animation: shake-warning 2s ease-in-out infinite;
            display: inline-block;
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.3));
        }

        @keyframes shake-warning {
            0%, 100% { 
                transform: translateX(0) rotate(0deg);
            }
            10%, 30%, 50%, 70%, 90% {
                transform: translateX(-5px) rotate(-5deg);
            }
            20%, 40%, 60%, 80% {
                transform: translateX(5px) rotate(5deg);
            }
        }

        h1 {
            font-size: clamp(4rem, 12vw, 7rem);
            margin-bottom: 1rem;
            font-weight: 900;
            letter-spacing: -3px;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        h2 {
            font-size: clamp(1.5rem, 4vw, 2.5rem);
            margin-bottom: 1rem;
            font-weight: 700;
        }

        p {
            font-size: clamp(1rem, 2vw, 1.2rem);
            margin-bottom: 1rem;
            opacity: 0.95;
            line-height: 1.6;
        }

        p:last-of-type {
            margin-bottom: 2.5rem;
        }

        /* Modern Buttons */
        .button-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-modern {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-modern:hover::before {
            left: 100%;
        }

        .btn-modern:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        .btn-modern:active {
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: transparent;
            border: 2px solid white;
            color: white;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Security Shield */
        .shield-container {
            margin: 2rem 0;
            position: relative;
        }

        .shield {
            width: 80px;
            height: 90px;
            margin: 0 auto;
            position: relative;
        }

        .shield-icon {
            font-size: 4rem;
            color: rgba(255, 255, 255, 0.9);
            animation: pulse-shield 2s ease-in-out infinite;
        }

        @keyframes pulse-shield {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        /* Info Cards */
        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1.25rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .info-card:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-3px);
        }

        .info-card i {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            display: block;
        }

        .info-card h3 {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .info-card p {
            font-size: 0.9rem;
            margin: 0;
            opacity: 0.9;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .glass-card {
                padding: 2rem 1.5rem;
            }

            .error-icon {
                font-size: 5rem;
            }

            h1 {
                letter-spacing: -2px;
            }

            .button-group {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-modern {
                width: 100%;
                justify-content: center;
            }

            .shape {
                display: none;
            }

            .info-cards {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .glass-card {
                padding: 1.5rem 1rem;
                border-radius: 20px;
            }

            .shield-icon {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <!-- Background Shapes -->
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Particles -->
    <div class="particles" id="particles"></div>

    <!-- Error Container -->
    <div class="error-container">
        <div class="glass-card">
            <div class="error-icon">
                <i class="fas fa-ban"></i>
            </div>
            <h1>403</h1>
            <h2>Access Forbidden</h2>
            
            <div class="shield-container">
                <div class="shield">
                    <i class="fas fa-shield-alt shield-icon"></i>
                </div>
            </div>

            <p>You don't have permission to access this resource.</p>
            <p>This area is restricted. Please login with appropriate credentials or contact an administrator.</p>
            
            <div class="button-group">
                <a href="<?= BASE_URL ?>" class="btn-modern">
                    <i class="fas fa-home"></i>
                    <span>Go Home</span>
                </a>
                <a href="<?= BASE_URL ?>login" class="btn-modern btn-secondary">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </a>
            </div>

            <div class="info-cards">
                <div class="info-card">
                    <i class="fas fa-user-shield"></i>
                    <h3>Authentication</h3>
                    <p>Login required to access</p>
                </div>
                <div class="info-card">
                    <i class="fas fa-key"></i>
                    <h3>Authorization</h3>
                    <p>Proper permissions needed</p>
                </div>
                <div class="info-card">
                    <i class="fas fa-headset"></i>
                    <h3>Need Help?</h3>
                    <p>Contact support team</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Generate floating particles
        const particlesContainer = document.getElementById('particles');
        const particleCount = window.innerWidth > 768 ? 30 : 15;
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 10 + 's';
            particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
            particlesContainer.appendChild(particle);
        }

        // Add click functionality to info cards
        document.querySelectorAll('.info-card').forEach((card, index) => {
            card.style.cursor = 'pointer';
            card.addEventListener('click', function() {
                if (index === 0) {
                    window.location.href = '<?= BASE_URL ?>login';
                } else if (index === 2) {
                    window.location.href = '<?= BASE_URL ?>contact';
                }
            });
        });
    </script>
</body>
</html>