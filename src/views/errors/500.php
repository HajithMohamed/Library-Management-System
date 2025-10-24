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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-container {
            text-align: center;
            color: white;
            max-width: 600px;
        }

        .error-icon {
            font-size: 8rem;
            margin-bottom: 2rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        h1 {
            font-size: 4rem;
            margin-bottom: 1rem;
            font-weight: 800;
        }

        h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .btn-home {
            display: inline-block;
            padding: 1rem 2rem;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        .error-code {
            background: rgba(255, 255, 255, 0.2);
            padding: 1rem 2rem;
            border-radius: 10px;
            margin-top: 2rem;
            font-family: monospace;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1>500</h1>
        <h2>Internal Server Error</h2>
        <p>Oops! Something went wrong on our end. We're working to fix it.</p>
        <a href="<?= BASE_URL ?>" class="btn-home">
            <i class="fas fa-home"></i> Go Back Home
        </a>
        
        <?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
        <div class="error-code">
            <strong>Debug Info:</strong><br>
            Error occurred while processing your request.<br>
            Please contact the administrator if this persists.
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
