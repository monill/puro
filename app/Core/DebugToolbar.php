<?php

declare(strict_types=1);

namespace App\Core;

class DebugToolbar
{
    private static array $config = [
        'enabled' => false,
        'position' => 'bottom-right',
        'max_tabs' => 10,
        'exclude_patterns' => [
            '/_debugbar/',
            '/favicon.ico',
        ],
    ];

    private static array $data = [
        'queries' => [],
        'memory' => [],
        'request' => [],
        'session' => [],
        'cache' => [],
        'routes' => [],
        'logs' => [],
        'timers' => [],
    ];

    private static float $startTime;
    private static array $timers = [];

    public static function configure(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }

    public static function enable(): void
    {
        self::$config['enabled'] = true;
        self::$startTime = microtime(true);
        
        register_shutdown_function([self::class, 'render']);
    }

    public static function disable(): void
    {
        self::$config['enabled'] = false;
    }

    public static function isEnabled(): bool
    {
        return self::$config['enabled'] && !self::shouldExclude();
    }

    public static function startTimer(string $name): void
    {
        self::$timers[$name] = ['start' => microtime(true)];
    }

    public static function endTimer(string $name): void
    {
        if (isset(self::$timers[$name])) {
            self::$timers[$name]['end'] = microtime(true);
            self::$timers[$name]['duration'] = self::$timers[$name]['end'] - self::$timers[$name]['start'];
        }
    }

    public static function addQuery(string $sql, array $bindings = [], float $time = 0): void
    {
        if (!self::isEnabled()) {
            return;
        }

        self::$data['queries'][] = [
            'sql' => $sql,
            'bindings' => $bindings,
            'time' => $time,
            'memory' => memory_get_usage(),
            'backtrace' => self::getBacktrace(),
        ];
    }

    public static function addLog(string $level, string $message, array $context = []): void
    {
        if (!self::isEnabled()) {
            return;
        }

        self::$data['logs'][] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'time' => microtime(true),
            'backtrace' => self::getBacktrace(),
        ];
    }

    public static function addCacheOperation(string $operation, string $key, mixed $value = null, bool $hit = false): void
    {
        if (!self::isEnabled()) {
            return;
        }

        self::$data['cache'][] = [
            'operation' => $operation,
            'key' => $key,
            'value' => $value,
            'hit' => $hit,
            'time' => microtime(true),
            'memory' => memory_get_usage(),
        ];
    }

    public static function setRouteInfo(array $route): void
    {
        if (!self::isEnabled()) {
            return;
        }

        self::$data['routes'] = $route;
    }

    private static function shouldExclude(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        foreach (self::$config['exclude_patterns'] as $pattern) {
            if (fnmatch($pattern, $uri)) {
                return true;
            }
        }
        
        return false;
    }

    private static function render(): void
    {
        if (!self::isEnabled()) {
            return;
        }

        self::collectData();
        $toolbar = self::generateToolbar();
        
        $content = ob_get_contents();
        $position = strpos($content, '</body>');
        
        if ($position !== false) {
            $content = substr($content, 0, $position) . $toolbar . substr($content, $position);
            echo $content;
        }
    }

    private static function collectData(): void
    {
        // Request data
        self::$data['request'] = [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'uri' => $_SERVER['REQUEST_URI'] ?? '/',
            'headers' => getallheaders(),
            'get' => $_GET,
            'post' => $_POST,
            'files' => $_FILES,
            'ajax' => !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest',
        ];

        // Memory usage
        self::$data['memory'] = [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => ini_get('memory_limit'),
        ];

        // Session data
        if (session_status() === PHP_SESSION_ACTIVE) {
            self::$data['session'] = $_SESSION;
        }

        // Total execution time
        if (isset(self::$startTime)) {
            self::$timers['total'] = [
                'start' => self::$startTime,
                'end' => microtime(true),
                'duration' => microtime(true) - self::$startTime,
            ];
        }
    }

    private static function generateToolbar(): string
    {
        $tabs = self::generateTabs();
        $panels = self::generatePanels();

        return '<script id="debugbar-data" type="application/json">' . json_encode([
            'tabs' => $tabs,
            'panels' => $panels,
            'position' => self::$config['position']
        ]) . '</script>' . self::getToolbarScript();
    }

    private static function getToolbarScript(): string
    {
        return '<script>
(function() {
    var debugbarData = document.getElementById("debugbar-data");
    if (!debugbarData) return;
    
    var data = JSON.parse(debugbarData.textContent);
    var debugbar = document.createElement("div");
    debugbar.id = "debugbar";
    debugbar.innerHTML = createDebugbarHTML(data);
    document.body.appendChild(debugbar);
    
    setupDebugbarEvents();
    
    function createDebugbarHTML(data) {
        var tabs = data.tabs.map(function(tab, index) {
            return \'<li class="debugbar-tab \' + (index === 0 ? \'active\' : \'\') + \'" data-tab="\' + tab.id + \'">\' + 
                   \'<span class="debugbar-icon">\' + tab.icon + \'</span>\' + 
                   \'<span class="debugbar-label">\' + tab.label + \'</span>\' + 
                   \'<span class="debugbar-count">\' + (tab.count || \'\') + \'</span></li>\';
        }).join("");
        
        var panels = data.panels.map(function(panel, index) {
            return \'<div class="debugbar-panel \' + (index === 0 ? \'active\' : \'\') + \'" id="debugbar-panel-\' + panel.id + \'">\' + panel.content + \'</div>\';
        }).join("");
        
        return \'<div class="debugbar-header">\' +
               \'<div class="debugbar-tabs">\' + tabs + \'</div>\' +
               \'<div class="debugbar-controls">\' +
               \'<button onclick="toggleDebugbar()" title="Toggle">⚙️</button>\' +
               \'<button onclick="closeDebugbar()" title="Close">✕</button>\' +
               \'</div>\' +
               \'</div>\' +
               \'<div class="debugbar-panels">\' + panels + \'</div>\';
    }
    
    function setupDebugbarEvents() {
        document.querySelectorAll(".debugbar-tab").forEach(function(tab) {
            tab.addEventListener("click", function() {
                var tabId = this.getAttribute("data-tab");
                switchTab(tabId);
            });
        });
        
        document.addEventListener("keydown", function(e) {
            if (e.ctrlKey && e.shiftKey && e.key === "d") {
                toggleDebugbar();
            }
        });
    }
    
    function switchTab(tabId) {
        document.querySelectorAll(".debugbar-tab").forEach(function(tab) {
            tab.classList.remove("active");
        });
        document.querySelectorAll(".debugbar-panel").forEach(function(panel) {
            panel.classList.remove("active");
        });
        
        document.querySelector(\'[data-tab="\' + tabId + \'"]\').classList.add("active");
        document.getElementById("debugbar-panel-" + tabId).classList.add("active");
    }
    
    function toggleDebugbar() {
        var debugbar = document.getElementById("debugbar");
        debugbar.classList.toggle("minimized");
    }
    
    function closeDebugbar() {
        var debugbar = document.getElementById("debugbar");
        debugbar.style.display = "none";
    }
})();
</script>' . self::getToolbarStyles();
    }

    private static function getToolbarStyles(): string
    {
        return '<style>
#debugbar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #1e1e1e;
    color: #d4d4d4;
    font-family: "Monaco", "Menlo", "Ubuntu Mono", monospace;
    font-size: 12px;
    z-index: 999999;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.3);
    transition: transform 0.3s ease;
}

#debugbar.minimized {
    transform: translateY(calc(100% - 40px));
}

.debugbar-header {
    display: flex;
    align-items: center;
    background: #2d2d2d;
    border-bottom: 1px solid #444;
}

.debugbar-tabs {
    display: flex;
    flex: 1;
    overflow-x: auto;
}

.debugbar-tab {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    cursor: pointer;
    border-right: 1px solid #444;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.debugbar-tab:hover {
    background: #3a3a3a;
}

.debugbar-tab.active {
    background: #007bff;
    color: white;
}

.debugbar-icon {
    margin-right: 6px;
    font-size: 14px;
}

.debugbar-label {
    font-size: 11px;
    font-weight: 500;
}

.debugbar-count {
    background: #dc3545;
    color: white;
    border-radius: 10px;
    padding: 2px 6px;
    font-size: 10px;
    margin-left: 6px;
    min-width: 16px;
    text-align: center;
}

.debugbar-controls {
    display: flex;
    gap: 5px;
}

.debugbar-controls button {
    background: none;
    border: none;
    color: #888;
    cursor: pointer;
    padding: 8px;
    border-radius: 3px;
    transition: color 0.2s ease;
}

.debugbar-controls button:hover {
    color: #fff;
}

.debugbar-panels {
    max-height: 300px;
    overflow-y: auto;
    background: #252526;
}

.debugbar-panel {
    display: none;
    padding: 15px;
    overflow-x: auto;
}

.debugbar-panel.active {
    display: block;
}

.debugbar-panel table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
}

.debugbar-panel th,
.debugbar-panel td {
    padding: 6px 8px;
    text-align: left;
    border-bottom: 1px solid #444;
    vertical-align: top;
}

.debugbar-panel th {
    background: #2d2d2d;
    color: #61dafb;
    font-weight: normal;
    white-space: nowrap;
}

.debugbar-panel td {
    word-break: break-all;
}

.debugbar-panel .sql {
    font-family: "Monaco", "Menlo", "Ubuntu Mono", monospace;
    background: #1e1e1e;
    padding: 2px 4px;
    border-radius: 2px;
}

.debugbar-panel .time {
    color: #ffc107;
    font-weight: bold;
}

.debugbar-panel .memory {
    color: #17a2b8;
}

.debugbar-panel .error {
    color: #dc3545;
}

.debugbar-panel .warning {
    color: #ffc107;
}

.debugbar-panel .info {
    color: #17a2b8;
}

.debugbar-panel pre {
    background: #1e1e1e;
    padding: 10px;
    border-radius: 4px;
    overflow-x: auto;
    font-size: 10px;
}
</style>';
    }

    private static function generateTabs(): string
    {
        $tabs = [];
        
        if (!empty(self::$data['queries'])) {
            $tabs[] = [
                'id' => 'queries',
                'label' => 'Queries',
                'icon' => '🗄️',
                'count' => count(self::$data['queries']),
            ];
        }
        
        if (!empty(self::$data['memory'])) {
            $tabs[] = [
                'id' => 'memory',
                'label' => 'Memory',
                'icon' => '💾',
            ];
        }
        
        if (!empty(self::$data['request'])) {
            $tabs[] = [
                'id' => 'request',
                'label' => 'Request',
                'icon' => '📡',
            ];
        }
        
        if (!empty(self::$data['cache'])) {
            $tabs[] = [
                'id' => 'cache',
                'label' => 'Cache',
                'icon' => '🗂️',
                'count' => count(self::$data['cache']),
            ];
        }
        
        if (!empty(self::$data['logs'])) {
            $tabs[] = [
                'id' => 'logs',
                'label' => 'Logs',
                'icon' => '📝',
                'count' => count(self::$data['logs']),
            ];
        }
        
        if (!empty(self::$data['timers'])) {
            $tabs[] = [
                'id' => 'timers',
                'label' => 'Timers',
                'icon' => '⏱️',
            ];
        }
        
        return json_encode(array_slice($tabs, 0, self::$config['max_tabs']));
    }

    private static function generatePanels(): string
    {
        $panels = [];
        
        // Queries panel
        if (!empty(self::$data['queries'])) {
            $content = '<table><thead><tr><th>Query</th><th>Bindings</th><th>Time</th><th>Memory</th></tr></thead><tbody>';
            foreach (self::$data['queries'] as $query) {
                $content .= '<tr>';
                $content .= '<td><div class="sql">' . htmlspecialchars($query['sql']) . '</div></td>';
                $content .= '<td>' . json_encode($query['bindings']) . '</td>';
                $content .= '<td class="time">' . number_format($query['time'] * 1000, 2) . 'ms</td>';
                $content .= '<td class="memory">' . self::formatBytes($query['memory']) . '</td>';
                $content .= '</tr>';
            }
            $content .= '</tbody></table>';
            
            $panels[] = ['id' => 'queries', 'content' => $content];
        }
        
        // Memory panel
        if (!empty(self::$data['memory'])) {
            $content = '<table>';
            $content .= '<tr><th>Current</th><td class="memory">' . self::formatBytes(self::$data['memory']['current']) . '</td></tr>';
            $content .= '<tr><th>Peak</th><td class="memory">' . self::formatBytes(self::$data['memory']['peak']) . '</td></tr>';
            $content .= '<tr><th>Limit</th><td>' . self::$data['memory']['limit'] . '</td></tr>';
            $content .= '</table>';
            
            $panels[] = ['id' => 'memory', 'content' => $content];
        }
        
        // Request panel
        if (!empty(self::$data['request'])) {
            $content = '<table>';
            $content .= '<tr><th>Method</th><td>' . self::$data['request']['method'] . '</td></tr>';
            $content .= '<tr><th>URI</th><td>' . htmlspecialchars(self::$data['request']['uri']) . '</td></tr>';
            $content .= '<tr><th>AJAX</th><td>' . (self::$data['request']['ajax'] ? 'Yes' : 'No') . '</td></tr>';
            $content .= '<tr><th>GET</th><td><pre>' . json_encode(self::$data['request']['get'], JSON_PRETTY_PRINT) . '</pre></td></tr>';
            $content .= '<tr><th>POST</th><td><pre>' . json_encode(self::$data['request']['post'], JSON_PRETTY_PRINT) . '</pre></td></tr>';
            $content .= '</table>';
            
            $panels[] = ['id' => 'request', 'content' => $content];
        }
        
        // Cache panel
        if (!empty(self::$data['cache'])) {
            $content = '<table><thead><tr><th>Operation</th><th>Key</th><th>Value</th><th>Hit</th></tr></thead><tbody>';
            foreach (self::$data['cache'] as $cache) {
                $content .= '<tr>';
                $content .= '<td>' . $cache['operation'] . '</td>';
                $content .= '<td>' . $cache['key'] . '</td>';
                $content .= '<td>' . (is_scalar($cache['value']) ? $cache['value'] : json_encode($cache['value'])) . '</td>';
                $content .= '<td>' . ($cache['hit'] ? '✅' : '❌') . '</td>';
                $content .= '</tr>';
            }
            $content .= '</tbody></table>';
            
            $panels[] = ['id' => 'cache', 'content' => $content];
        }
        
        // Logs panel
        if (!empty(self::$data['logs'])) {
            $content = '<table><thead><tr><th>Time</th><th>Level</th><th>Message</th></tr></thead><tbody>';
            foreach (array_reverse(self::$data['logs']) as $log) {
                $levelClass = $log['level'];
                $content .= '<tr>';
                $content .= '<td>' . date('H:i:s', (int)$log['time']) . '</td>';
                $content .= '<td class="' . $levelClass . '">' . $log['level'] . '</td>';
                $content .= '<td>' . htmlspecialchars($log['message']) . '</td>';
                $content .= '</tr>';
            }
            $content .= '</tbody></table>';
            
            $panels[] = ['id' => 'logs', 'content' => $content];
        }
        
        // Timers panel
        if (!empty(self::$data['timers'])) {
            $content = '<table>';
            foreach (self::$data['timers'] as $name => $timer) {
                $content .= '<tr>';
                $content .= '<th>' . $name . '</th>';
                $content .= '<td class="time">' . number_format($timer['duration'] * 1000, 2) . 'ms</td>';
                $content .= '</tr>';
            }
            $content .= '</table>';
            
            $panels[] = ['id' => 'timers', 'content' => $content];
        }
        
        return json_encode($panels);
    }

    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    private static function getBacktrace(): array
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        return array_slice($backtrace, 0, 5);
    }

    public static function getData(): array
    {
        return self::$data;
    }

    public static function clear(): void
    {
        self::$data = [];
        self::$timers = [];
    }
}
