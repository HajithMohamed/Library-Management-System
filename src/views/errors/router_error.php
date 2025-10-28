<!DOCTYPE html>
<html>
<head>
  <title>Application Error</title>
  <style>
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background: #f5f5f5;
      padding: 20px;
    }

    .error-container {
      max-width: 1200px;
      margin: 0 auto;
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .error-header {
      background: #ef4444;
      color: white;
      padding: 20px 30px;
    }

    .error-header h1 {
      margin: 0;
      font-size: 24px;
    }

    .error-header p {
      margin: 10px 0 0 0;
      opacity: 0.9;
    }

    .error-body {
      padding: 30px;
    }

    .error-section {
      margin-bottom: 30px;
    }

    .error-section h2 {
      color: #1f2937;
      font-size: 18px;
      margin-bottom: 15px;
      border-bottom: 2px solid #e5e7eb;
      padding-bottom: 10px;
    }

    .error-code {
      background: #fee2e2;
      border-left: 4px solid #ef4444;
      padding: 15px;
      border-radius: 4px;
      font-family: 'Courier New', monospace;
      font-size: 14px;
      color: #991b1b;
      overflow-x: auto;
    }

    .stack-trace {
      background: #f9fafb;
      border: 1px solid #e5e7eb;
      padding: 15px;
      border-radius: 4px;
      font-family: 'Courier New', monospace;
      font-size: 12px;
      color: #374151;
      overflow-x: auto;
      white-space: pre-wrap;
    }

    .info-box {
      background: #dbeafe;
      border-left: 4px solid #3b82f6;
      padding: 15px;
      border-radius: 4px;
      margin-bottom: 15px;
    }

    .info-box strong {
      color: #1e40af;
    }
  </style>
</head>
<body>
  <div class="error-container">
    <div class="error-header">
      <h1>⚠️ Application Error</h1>
      <p>An error occurred while processing your request</p>
    </div>
    <div class="error-body">
      <div class="error-section">
        <h2>Context</h2>
        <div class="info-box">
          <strong>Location:</strong> <?= htmlspecialchars($context) ?>
        </div>
      </div>

      <div class="error-section">
        <h2>Error Message</h2>
        <div class="error-code">
          <?= htmlspecialchars($exception->getMessage()) ?>
        </div>
      </div>

      <div class="error-section">
        <h2>Error Details</h2>
        <div class="info-box">
          <strong>Type:</strong> <?= get_class($exception) ?><br>
          <strong>File:</strong> <?= htmlspecialchars($exception->getFile()) ?><br>
          <strong>Line:</strong> <?= $exception->getLine() ?>
        </div>
      </div>

      <div class="error-section">
        <h2>Stack Trace</h2>
        <div class="stack-trace"><?= htmlspecialchars($exception->getTraceAsString()) ?></div>
      </div>

      <div class="error-section">
        <h2>Request Information</h2>
        <div class="info-box">
          <strong>Method:</strong> <?= htmlspecialchars($_SERVER['REQUEST_METHOD']) ?><br>
          <strong>URI:</strong> <?= htmlspecialchars($_SERVER['REQUEST_URI']) ?><br>
          <strong>Session User:</strong> <?= isset($_SESSION['userId']) ? htmlspecialchars($_SESSION['userId']) : 'Not logged in' ?><br>
          <strong>User Type:</strong> <?= isset($_SESSION['userType']) ? htmlspecialchars($_SESSION['userType']) : 'N/A' ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
