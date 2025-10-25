<?php
$pageTitle = '404 - Page Not Found';
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
            animation: float 3s ease-in-out infinite;
            display: inline-block;
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.3));
        }

        @keyframes float {
            0%, 100% { 
                transform: translateY(0) rotate(0deg);
            }
            50% { 
                transform: translateY(-20px) rotate(5deg);
            }
        }

        h1 {
            font-size: clamp(5rem, 15vw, 8rem);
            margin-bottom: 1rem;
            font-weight: 900;
            letter-spacing: -5px;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: glitch 3s infinite;
        }

        @keyframes glitch {
            0%, 95%, 100% {
                transform: translate(0);
            }
            97% {
                transform: translate(-2px, 2px);
            }
            98% {
                transform: translate(2px, -2px);
            }
        }

        h2 {
            font-size: clamp(1.5rem, 4vw, 2.5rem);
            margin-bottom: 1rem;
            font-weight: 700;
        }

        p {
            font-size: clamp(1rem, 2vw, 1.2rem);
            margin-bottom: 2.5rem;
            opacity: 0.95;
            line-height: 1.6;
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

        /* Feature Pills */
        .feature-pills {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .pill:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .pill i {
            font-size: 1.1rem;
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
                letter-spacing: -3px;
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
        }

        @media (max-width: 480px) {
            .glass-card {
                padding: 1.5rem 1rem;
                border-radius: 20px;
            }

            .feature-pills {
                flex-direction: column;
                align-items: stretch;
            }

            .pill {
                justify-content: center;
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
                <i class="fas fa-book-dead"></i>
            </div>
            <h1>404</h1>
            <h2>Oops! Page Not Found</h2>
            <p>The page you're looking for seems to have wandered off into the digital void. Don't worry, we'll help you find your way back!</p>
            
            <div class="button-group">
                <a href="<?= BASE_URL ?>" class="btn-modern">
                    <i class="fas fa-home"></i>
                    <span>Go Home</span>
                </a>
                <a href="javascript:history.back()" class="btn-modern btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <span>Go Back</span>
                </a>
            </div>

            <div class="feature-pills">
                <div class="pill">
                    <i class="fas fa-book"></i>
                    <span>Browse Library</span>
                </div>
                <div class="pill">
                    <i class="fas fa-search"></i>
                    <span>Search Books</span>
                </div>
                <div class="pill">
                    <i class="fas fa-question-circle"></i>
                    <span>Get Help</span>
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

        // Add click functionality to feature pills
        document.querySelectorAll('.pill').forEach(pill => {
            pill.style.cursor = 'pointer';
            pill.addEventListener('click', function() {
                const text = this.querySelector('span').textContent;
                if (text.includes('Browse')) {
                    window.location.href = '<?= BASE_URL ?>library';
                } else if (text.includes('Search')) {
                    window.location.href = '<?= BASE_URL ?>user/books';
                } else if (text.includes('Help')) {
                    window.location.href = '<?= BASE_URL ?>contact';
                }
            });
        });
    </script>
</body>
</html>