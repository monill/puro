<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Log;

class LoggingMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): mixed
    {
        $startTime = microtime(true);
        
        Log::request([
            'start_time' => $startTime,
        ]);

        $response = $next($request);
        
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        
        Log::info('Request completed', [
            'duration' => round($duration * 1000, 2) . 'ms',
            'memory_usage' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB',
        ]);

        return $response;
    }
}
