<?php
$pageTitle = '500 - Internal Server Error';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
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
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            animation: float-shapes 20s infinite ease-in-out;
        }

        .shape:nth-child(1) {
            width: 350px;
            height: 350px;
            top: -150px;
            left: -150px;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 250px;
            height: 250px;
            top: 50%;
            right: -100px;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 300px;
            height: 300px;
            bottom: -120px;
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

        /* Grid Pattern Overlay */
        .grid-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: gridMove 20s linear infinite;
            z-index: 0;
        }

        @keyframes gridMove {
            0% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(50px, 50px);
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
            width: 3px;
            height: 3px;
            background: rgba(255, 255, 255, 0.5);
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
            max-width: 750px;
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
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 3rem 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .error-icon {
            font-size: 8rem;
            margin-bottom: 1.5rem;
            animation: pulse-warning 2s ease-in-out infinite;
            display: inline-block;
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.4));
        }

        @keyframes pulse-warning {
            0%, 100% { 
                transform: scale(1);
                opacity: 1;
            }
            50% { 
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        h1 {
            font-size: clamp(4rem, 12vw, 7rem);
            margin-bottom: 1rem;
            font-weight: 900;
            letter-spacing: -3px;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
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

        /* Modern Button */
        .btn-modern {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2.5rem;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
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
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-modern:hover::before {
            left: 100%;
        }

        .btn-modern:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }

        .btn-modern:active {
            transform: translateY(-1px);
        }

        /* Status Pills */
        .status-pills {
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
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }

        .pill i {
            font-size: 1.1rem;
        }

        /* Debug Info Section */
        .debug-section {
            margin-top: 2.5rem;
            animation: fadeIn 1s ease-out 0.5s both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .error-code {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1.5rem;
            border-radius: 16px;
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            font-size: 0.9rem;
            text-align: left;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            line-height: 1.6;
        }

        .error-code strong {
            display: block;
            font-size: 1rem;
            margin-bottom: 0.75rem;
            color: #c4b5fd;
        }

        .error-code .code-line {
            display: block;
            opacity: 0.9;
            margin: 0.25rem 0;
        }

        .error-code .code-highlight {
            color: #c4b5fd;
            font-weight: 600;
        }

        /* Loading Spinner */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Info Box */
        .info-box {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid rgba(255, 255, 255, 0.5);
            padding: 1.25rem;
            border-radius: 12px;
            margin-top: 2rem;
            text-align: left;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .info-box i {
            margin-right: 0.5rem;
            color: #c4b5fd;
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

            .status-pills {
                flex-direction: column;
                align-items: stretch;
            }

            .pill {
                justify-content: center;
            }

            .error-code {
                font-size: 0.8rem;
                padding: 1rem;
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

    <!-- Grid Overlay -->
    <div class="grid-overlay"></div>

    <!-- Particles -->
    <div class="particles" id="particles"></div>

    <!-- Error Container -->
    <div class="error-container">
        <div class="glass-card">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h1>500</h1>
            <h2>Internal Server Error</h2>
            <p>Oops! Something went wrong on our end. Our team has been notified and we're working to fix it as quickly as possible.</p>
            
            <a href="<?= BASE_URL ?>" class="btn-modern">
                <i class="fas fa-home"></i>
                <span>Return to Homepage</span>
            </a>

            <div class="status-pills">
                <div class="pill">
                    <div class="spinner"></div>
                    <span>Auto-fixing in progress</span>
                </div>
                <div class="pill">
                    <i class="fas fa-server"></i>
                    <span>Server Status: Error</span>
                </div>
            </div>

            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <strong>What can you do?</strong><br>
                Try refreshing the page, or come back in a few minutes. If the problem persists, please contact our support team.
            </div>

            <?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
            <div class="debug-section">
                <div class="error-code">
                    <strong><i class="fas fa-bug"></i> Debug Information:</strong>
                    <span class="code-line"><span class="code-highlight">Error Type:</span> Internal Server Error</span>
                    <span class="code-line"><span class="code-highlight">Status Code:</span> 500</span>
                    <span class="code-line"><span class="code-highlight">Timestamp:</span> <?= date('Y-m-d H:i:s') ?></span>
                    <span class="code-line"><span class="code-highlight">Message:</span> Error occurred while processing your request.</span>
                    <span class="code-line" style="margin-top: 0.75rem;">Please contact the administrator if this issue persists.</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Generate floating particles
        const particlesContainer = document.getElementById('particles');
        const particleCount = window.innerWidth > 768 ? 25 : 12;
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 10 + 's';
            particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
            particlesContainer.appendChild(particle);
        }

        // Add subtle shake animation on load
        const errorIcon = document.querySelector('.error-icon');
        setTimeout(() => {
            errorIcon.style.animation = 'pulse-warning 2s ease-in-out infinite';
        }, 100);
    </script>
</body>
</html>