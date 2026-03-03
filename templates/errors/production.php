<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Something went wrong</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .error-container {
            text-align: center;
            background: white;
            padding: 60px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            animation: fadeIn 0.5s ease-out;
        }
        .error-icon {
            font-size: 64px;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }
        .error-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
        }
        .error-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .error-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }
        .btn-secondary {
            background: #e2e8f0;
            color: #333;
        }
        .btn-secondary:hover {
            background: #cbd5e0;
        }
        .error-code {
            font-size: 14px;
            color: #999;
            margin-top: 20px;
            font-family: monospace;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 4px;
            display: inline-block;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        @media (max-width: 768px) {
            .error-container {
                padding: 40px 20px;
                margin: 20px;
            }
            .error-title {
                font-size: 24px;
            }
            .error-actions {
                flex-direction: column;
                align-items: center;
            }
            .btn {
                width: 100%;
                max-width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1 class="error-title">Oops! Something went wrong</h1>
        <p class="error-message">
            We're sorry, but something unexpected happened. Our team has been notified and is working on a fix.
        </p>
        <div class="error-actions">
            <a href="/" class="btn">Go Home</a>
            <button onclick="history.back()" class="btn btn-secondary">Go Back</button>
        </div>
        <div class="error-code">
            Error ID: <?= uniqid('err_') ?>
        </div>
    </div>
    
    <script>
        // Auto-refresh after 30 seconds
        setTimeout(function() {
            if (confirm('The page encountered an error. Would you like to try refreshing?')) {
                window.location.reload();
            }
        }, 30000);
        
        // Report error (optional)
        function reportError() {
            // You can implement error reporting here
            console.log('Error reported to monitoring service');
        }
        
        // Auto-report after 5 seconds
        setTimeout(reportError, 5000);
    </script>
</body>
</html>
