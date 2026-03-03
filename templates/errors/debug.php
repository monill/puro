<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Error - <?= htmlspecialchars($error['message']) ?></title>
    <style>
        body {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: #2d2d2d;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin-bottom: 0;
            border: 1px solid #444;
            border-bottom: none;
        }
        .error-type {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .error-type.error {
            background: #dc3545;
            color: white;
        }
        .error-type.warning {
            background: #ffc107;
            color: #212529;
        }
        .error-type.notice {
            background: #17a2b8;
            color: white;
        }
        .error-type.exception {
            background: #6f42c1;
            color: white;
        }
        .main {
            background: #252526;
            border: 1px solid #444;
            border-radius: 0 0 8px 8px;
            padding: 20px;
        }
        .message {
            font-size: 18px;
            color: #ff6b6b;
            margin-bottom: 20px;
            word-break: break-word;
        }
        .details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .detail {
            background: #2d2d2d;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #444;
        }
        .detail h3 {
            margin: 0 0 10px 0;
            color: #61dafb;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .detail p {
            margin: 5px 0;
            color: #b4b4b4;
        }
        .detail strong {
            color: #ffffff;
        }
        .code-context {
            background: #1e1e1e;
            border: 1px solid #444;
            border-radius: 6px;
            margin-bottom: 30px;
        }
        .code-context h3 {
            margin: 0 0 15px 0;
            color: #61dafb;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .code-line {
            display: flex;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 13px;
            line-height: 1.4;
        }
        .line-number {
            background: #2d2d2d;
            color: #858585;
            padding: 2px 8px;
            border-right: 1px solid #444;
            text-align: right;
            min-width: 50px;
            user-select: none;
        }
        .line-code {
            padding: 2px 10px;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .line-code.current {
            background: #44475a;
        }
        .stack-trace {
            background: #1e1e1e;
            border: 1px solid #444;
            border-radius: 6px;
        }
        .stack-trace h3 {
            margin: 0 0 15px 0;
            color: #61dafb;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stack-trace pre {
            margin: 0;
            padding: 15px;
            overflow-x: auto;
            font-size: 12px;
            line-height: 1.4;
        }
        .request-info {
            background: #2d2d2d;
            border: 1px solid #444;
            border-radius: 6px;
            margin-top: 20px;
            padding: 15px;
        }
        .request-info h3 {
            margin: 0 0 15px 0;
            color: #61dafb;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .request-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .request-info th,
        .request-info td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #444;
        }
        .request-info th {
            color: #61dafb;
            font-weight: normal;
            width: 150px;
        }
        .request-info td {
            color: #b4b4b4;
            word-break: break-all;
        }
        .toggle {
            background: #44475a;
            border: 1px solid #61dafb;
            color: #61dafb;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 10px 0;
        }
        .toggle:hover {
            background: #61dafb;
            color: #1e1e1e;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="error-type <?= strtolower($error['level']) ?>">
                <?= $error['level'] ?>
            </span>
            <span style="float: right; color: #858585; font-size: 12px;">
                <?= $error['timestamp'] ?>
            </span>
        </div>
        
        <div class="main">
            <div class="message">
                <?= htmlspecialchars($error['message']) ?>
            </div>
            
            <div class="details">
                <div class="detail">
                    <h3>Type</h3>
                    <p><strong>Type:</strong> <?= $error['type'] ?></p>
                    <p><strong>Level:</strong> <?= $error['level'] ?></p>
                    <?php if (isset($error['exception'])): ?>
                        <p><strong>Exception:</strong> <?= $error['exception'] ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="detail">
                    <h3>Location</h3>
                    <p><strong>File:</strong> <?= htmlspecialchars($error['file']) ?></p>
                    <p><strong>Line:</strong> <?= $error['line'] ?></p>
                </div>
                
                <div class="detail">
                    <h3>Request</h3>
                    <p><strong>Method:</strong> <?= $_SERVER['REQUEST_METHOD'] ?? 'Unknown' ?></p>
                    <p><strong>URI:</strong> <?= $_SERVER['REQUEST_URI'] ?? 'Unknown' ?></p>
                    <p><strong>IP:</strong> <?= $_SERVER['REMOTE_ADDR'] ?? 'Unknown' ?></p>
                </div>
            </div>
            
            <?php if (!empty($context)): ?>
            <div class="code-context">
                <h3>Code Context</h3>
                <?php foreach ($context as $lineNumber => $line): ?>
                    <div class="code-line <?= $line['current'] ? 'current' : '' ?>">
                        <div class="line-number"><?= $lineNumber ?></div>
                        <div class="line-code"><?= htmlspecialchars($line['code']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($error['trace'])): ?>
            <div class="stack-trace">
                <h3>Stack Trace</h3>
                <button class="toggle" onclick="this.nextElementSibling.classList.toggle('hidden')">
                    Toggle Stack Trace
                </button>
                <pre class="hidden"><?= htmlspecialchars($error['trace']) ?></pre>
            </div>
            <?php endif; ?>
            
            <div class="request-info">
                <h3>Request Information</h3>
                <table>
                    <tr>
                        <th>Headers</th>
                        <td><?= json_encode(getallheaders(), JSON_PRETTY_PRINT) ?></td>
                    </tr>
                    <tr>
                        <th>GET Data</th>
                        <td><?= json_encode($_GET, JSON_PRETTY_PRINT) ?></td>
                    </tr>
                    <tr>
                        <th>POST Data</th>
                        <td><?= json_encode($_POST, JSON_PRETTY_PRINT) ?></td>
                    </tr>
                    <tr>
                        <th>Session</th>
                        <td><?= json_encode($_SESSION ?? [], JSON_PRETTY_PRINT) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
